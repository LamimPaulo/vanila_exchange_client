<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class NotaFiscalRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotaFiscal());
        } else {
            $this->conexao = new GenericModel($adapter, new NotaFiscal());
        }
        
    }
    
    public function salvar(NotaFiscal &$notaFiscal) {
        
        try {
            
            if (!empty($notaFiscal->linkDownloadPdf)) { 
                if ($notaFiscal->idDeposito > 0) {
                    $depositoRn = new DepositoRn();
                    $depositoRn->conexao->update(Array("nota_fiscal" => $notaFiscal->linkDownloadPdf), Array("id" => $notaFiscal->idDeposito));
                } else if ($notaFiscal->idSaque > 0) {
                    $saqueRn = new SaqueRn();
                    $saqueRn->conexao->update(Array("nota_fiscal" => $notaFiscal->linkDownloadPdf), Array("id" => $notaFiscal->idSaque));
                }
            }
            
            $this->conexao->salvar($notaFiscal);
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function filtrar(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $filtro = null, $status = "T") {
        
        $whereDeposito = Array();
        $whereSaque = Array();
        
        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception($this->idioma->getText("dataInicialInvalida"));
        }
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception($this->idioma->getText("dataFinalInvalida"));
        }
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
        }
        
        $whereDeposito[] = " d.data_solicitacao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        $whereSaque[] = " s.data_solicitacao BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        
        $whereDeposito[] = " d.status = '".\Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO."' ";
        $whereSaque[] = " s.status = '".\Utils\Constantes::STATUS_SAQUE_CONFIRMADO."' ";
        
        if (!empty($filtro)) {
            
            $whereDeposito[] = " ( "
                    . " ( CAST(d.id AS CHAR(200)) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.email) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.documento) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(nf.idnf) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(nf.id_externo) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(nf.numero_nf) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
                    
            $whereSaque[] = " ( "
                    . " ( CAST(s.id AS CHAR(200) ) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.nome) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.email) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(c.documento) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(nf.idnf) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(nf.id_externo) LIKE LOWER('%{$filtro}%') ) OR "
                    . " ( LOWER(nf.numero_nf) LIKE LOWER('%{$filtro}%') ) "
                    . " ) ";
        }
        
        if ($status != "T") {
            $whereDeposito[] = " nf.status = '{$status}' ";
            $whereSaque[] = " nf.status = '{$status}' ";
        }
        
        $whereDeposito = (sizeof($whereDeposito) > 0 ? " WHERE " . implode(" AND ", $whereDeposito) : "");
        $whereSaque = (sizeof($whereSaque) > 0 ? " WHERE " . implode(" AND ", $whereSaque) : "");
        
        $sqlDeposito = " SELECT nf.*, d.id AS deposito FROM notas_fiscais nf "
                . " INNER JOIN depositos d ON (nf.id_deposito = d.id) "
                . " INNER JOIN clientes c ON (d.id_cliente = c.id) "
                . " {$whereDeposito} "
                . " ORDER BY d.data_solicitacao;";
                    
        $sqlSaques = " SELECT nf.*, s.id AS saque FROM notas_fiscais nf "
                . " INNER JOIN saques s ON (nf.id_saque = s.id) "
                . " INNER JOIN clientes c ON (s.id_cliente = c.id) "
                . " {$whereSaque} "
                . " ORDER BY s.data_solicitacao;";
               
                
        $dadosDepositos = $this->conexao->adapter->query($sqlDeposito)->execute();
        $dadosSaques = $this->conexao->adapter->query($sqlSaques)->execute();
        
        $lista = Array();
        
        if (sizeof($dadosDepositos) > 0) {
            $depositoRn = new DepositoRn();
            foreach ($dadosDepositos as $dados) {
                $notaFiscal = new NotaFiscal($dados);

                $deposito = new Deposito();
                $deposito->id = $dados["deposito"];
                $depositoRn->carregar($deposito, true, false, FALSE, true);
                
                $lista[$deposito->dataSolicitacao->timestamp()] = Array("deposito" => $deposito, "nf" => $notaFiscal);
            }
        }
       
        if (sizeof($dadosSaques) > 0) {
            $saqueRn = new SaqueRn();
            foreach ($dadosSaques as $dados) {
                $notaFiscal = new NotaFiscal($dados);
                
                $saque = new Saque();
                $saque->id = $dados["saque"];
                $saqueRn->carregar($saque, true, false, false, true);
                
                $lista[$saque->dataSolicitacao->timestamp()] = Array("saque" => $saque, "nf" => $notaFiscal);
            }
        }
         
        krsort($lista);
        
        
        return $lista;
    }
    
    public static function setNotaFiscalFromJson(NotaFiscal &$notaFiscal, $jsonNotaFiscal) {
       
        $notaFiscal->ambiente = ($jsonNotaFiscal->ambienteEmissao == "Homologacao" ? "H" : "P" );
        $notaFiscal->dataAutorizacao = (strlen($jsonNotaFiscal->dataAutorizacao) == 20 ? new \Utils\Data(substr(str_replace("T", " ", $jsonNotaFiscal->dataAutorizacao), 0, 19)): null);
        $notaFiscal->dataUltimaAlteracao =  (strlen($jsonNotaFiscal->dataUltimaAlteracao) == 20 ? new \Utils\Data(substr(str_replace("T", " ", $jsonNotaFiscal->dataUltimaAlteracao), 0, 19)): null);
        $notaFiscal->dataCriacao =  (strlen($jsonNotaFiscal->dataCriacao) == 20 ? new \Utils\Data(substr(str_replace("T", " ", $jsonNotaFiscal->dataCriacao), 0, 19)): null);
        $notaFiscal->enviadaPorEmail = ($jsonNotaFiscal->enviadaPorEmail ? 1 : 0 );
        $notaFiscal->idExterno = $jsonNotaFiscal->idExterno;
        $notaFiscal->idnf = $jsonNotaFiscal->id;
        $notaFiscal->json = json_encode($jsonNotaFiscal);
        $notaFiscal->linkDownloadPdf = isset($jsonNotaFiscal->linkDownloadPDF) ? $jsonNotaFiscal->linkDownloadPDF : null;
        $notaFiscal->linkDownloadXml = isset($jsonNotaFiscal->linkDownloadXML) ? $jsonNotaFiscal->linkDownloadXML : null;
        $notaFiscal->motivoStatus = $jsonNotaFiscal->motivoStatus;
        $notaFiscal->numeroNf = isset($jsonNotaFiscal->numero) ? $jsonNotaFiscal->numero : null;
        $notaFiscal->status = $jsonNotaFiscal->status;
        $notaFiscal->tipo = $jsonNotaFiscal->tipo;
        $notaFiscal->valorTotal = $jsonNotaFiscal->valorTotal;
        
    }
    
    public function filtrarCliente($idCliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, 
            $tipoNota = "Q", $status = "T", $filtro = null, $qtdRegitros = "T") {
        
        $where = Array();
        
        if ($idCliente > 0) {
            $where[] = " n.id_cliente = {$idCliente} ";
        }
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            
            $where[] = " n.data_criacao BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        }
        
        if ($tipoNota != "Q") {
            switch ($tipoNota) {
                case \Utils\Constantes::OPERACAO_DEPOSITO:
                    $where[] = " n.id_deposito IS NOT NULL ";
                    break;
                case \Utils\Constantes::OPERACAO_SAQUE:
                    $where[] = " n.id_saque IS NOT NULL ";
                    break;
                case \Utils\Constantes::OPERACAO_BOLETO:
                    $where[] = " n.id_boleto IS NOT NULL ";
                    break;
                case \Utils\Constantes::OPERACAO_REMESSA_VALORES:
                    $where[] = " n.id_remessa_dinheiro IS NOT NULL ";
                    break;
            }            
        }        
        
        if ($status != "T") {
            $where[] = " n.status = '{$status}' ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ("
                    . " (DATE_FORMAT(data_criacao, '%d/%m/%Y %H:%i:%S') LIKE LOWER('%{$filtro}%')) OR "
                    . " (DATE_FORMAT(data_autorizacao, '%d/%m/%Y %H:%i:%S') LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(n.status) LIKE LOWER('%{$filtro}%')) OR " 
                    . " (LOWER(n.idnf) LIKE LOWER('%{$filtro}%')) OR "  
                    . " (LOWER(n.numero_nf) LIKE LOWER('%{$filtro}%')) OR "   
                    . " (n.id LIKE '%{$filtro}%') OR "
                    . " (LOWER(valor_total) LIKE LOWER('%{$filtro}%')) "
                    . ") ";
        }
        
        $limit = "";
        if ($qtdRegitros != "T") {
            $limit = " limit {$qtdRegitros} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT n.* FROM notas_fiscais n "
                . "INNER JOIN clientes c ON (c.id = n.id_cliente) "
                . " {$where} "
                . " ORDER BY n.data_criacao DESC"
                . " {$limit}; ";
              
        $result = $this->conexao->adapter->query($query)->execute();
        
         //exit($query); 
        $lista = Array();
        foreach ($result as $dados) {
            $nota = new NotaFiscal($dados);
            $this->conexao->carregar($nota);
            $lista[] = $nota;            
        }
        //exit(print_r(sizeof($lista)));
        return $lista;
    }
    
    
    
    public function getByNfId($nfid) {
        $result = $this->conexao->select(Array("idnf" => $nfid));
        if (sizeof($result) > 0) {
            return $result->current();
        } else {
            return null;
        }
    }
    
    public function getByCliente($idCliente) {

        $query = "SELECT * FROM notas_fiscais WHERE id_cliente = {$idCliente} ORDER BY data_criacao DESC;";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
    
    
    
}

?>