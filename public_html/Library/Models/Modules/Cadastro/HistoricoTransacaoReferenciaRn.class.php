<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade HistoricoTransacaoReferencia
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class HistoricoTransacaoReferenciaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new HistoricoTransacaoReferencia());
        } else {
            $this->conexao = new GenericModel($adapter, new HistoricoTransacaoReferencia());
        }
    }
    
    public function salvar(HistoricoTransacaoReferencia &$historicoTransacaoReferencia) {
        
        $historicoTransacaoReferencia->id = 0;
        $historicoTransacaoReferencia->data = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (!$historicoTransacaoReferencia->idMoeda > 0) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        if (!$historicoTransacaoReferencia->idReferenciaCliente > 0) {
            throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
        }
        
        if (!$historicoTransacaoReferencia->idCarteiraPdv > 0) {
            throw new \Exception($this->idioma->getText("identicacaoCarteiraDeveInformada"));
        }
        
        if ($historicoTransacaoReferencia->tipo != \Utils\Constantes::SAQUE && 
                $historicoTransacaoReferencia->tipo != \Utils\Constantes::DEPOSITO && 
                $historicoTransacaoReferencia->tipo != \Utils\Constantes::ATUALIZACAO) {
            throw new \Exception($this->idioma->getText("tipoTransacaoInvalida"));
        }
        
        if (!$historicoTransacaoReferencia->valor > 0) {
            throw new \Exception($this->idioma->getText("valorePrecisaInformado"));
        }
        
        unset($historicoTransacaoReferencia->referenciaCliente);
        unset($historicoTransacaoReferencia->carteiraPdv);
        unset($historicoTransacaoReferencia->moeda);
        $this->conexao->salvar($historicoTransacaoReferencia);
    }
    
    public function carregar(HistoricoTransacaoReferencia &$historicoTransacaoReferencia, $carregar = true, $carregarReferenciaCliente = true, $carregarCarteiraPdv = true, $carregarMoeda = true) {
        if ($carregar) {
            $this->conexao->carregar($historicoTransacaoReferencia);
        }
        
        if ($carregarReferenciaCliente && $historicoTransacaoReferencia->idReferenciaCliente > 0) {
            $historicoTransacaoReferencia->referenciaCliente = new ReferenciaCliente(Array("id" => $historicoTransacaoReferencia->idReferenciaCliente));
            $referenciaClienteRn = new ReferenciaClienteRn();
            $referenciaClienteRn->conexao->carregar($historicoTransacaoReferencia->referenciaCliente);
        }
        
        if ($carregarCarteiraPdv && $historicoTransacaoReferencia->idCarteiraPdv > 0) {
            $historicoTransacaoReferencia->carteiraPdv = new CarteiraPdv(Array("id" => $historicoTransacaoReferencia->idCarteiraPdv));
            $carteiraPdvRn = new CarteiraPdvRn();
            $carteiraPdvRn->carregar($historicoTransacaoReferencia->carteiraPdv, true, TRUE);
        }
        
        if ($carregarMoeda && $historicoTransacaoReferencia->idCarteiraPdv > 0) {
            $historicoTransacaoReferencia->moeda = new Moeda(Array("id" => $historicoTransacaoReferencia->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($historicoTransacaoReferencia->moeda);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarReferenciaCliente = true, $carregarCarteiraPdv = true, $carregarMoeda = true) {
        
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $historicoTransacaoReferencia) {
            $this->carregar($historicoTransacaoReferencia, false, $carregarReferenciaCliente, $carregarCarteiraPdv, $carregarMoeda);
            $lista[] = $historicoTransacaoReferencia;
        }
        return $lista;
    }
    
    
    public function getHistorico($idCarteiraPdv, $referencia, $chaveEstabelecimento, $idMoeda = 0) {
        
        if (empty($chaveEstabelecimento)) {
            throw new \Exception($this->idioma->getText("necessarioChaveEstabelecimento"));
        }
        
        $where = Array();
        
        $where[] = " (e.chave = '{$chaveEstabelecimento}' OR e.chave_sandbox = '{$chaveEstabelecimento}') ";
        
        if (!empty($referencia)) {
            $where[] = " c.referencia = '{$referencia}' ";
        }
        
        
        if ($idCarteiraPdv > 0) {
            $where[] = " h.id_carteira_pdv = {$idCarteiraPdv} ";
        }
        if ($idMoeda > 0) {
            $where[] = " h.id_moeda = {$idMoeda} ";
        }
        
        $where[] = " h.tipo = 'D' ";
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT h.id AS controle, c.referencia, cp.endereco_carteira AS endereco, h.valor, h.tipo, h.data, m.simbolo AS moeda "
                . " FROM estabelecimentos e "
                . " INNER JOIN referencias_clientes c ON (e.id = c.id_estabelecimento) "
                . " INNER JOIN historico_transacoes_referencias h ON (h.id_referencia_cliente = c.id) "
                . " INNER JOIN moedas m ON (h.id_moeda = m.id) "
                . " INNER JOIN carteiras_pdv cp ON (h.id_carteira_pdv = cp.id) "
                . " {$where} "
                . " ORDER BY h.data DESC ";
           
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            switch ($dados["tipo"]) {
                case \Utils\Constantes::DEPOSITO:
                    $dados["tipo"] = "Deposito";
                    break;
                case \Utils\Constantes::SAQUE:
                    $dados["tipo"] = "Saque";
                    break;
                case \Utils\Constantes::ATUALIZACAO:
                    $dados["tipo"] = "Atualizacao";
                    break;
            }
            $lista[] = $dados;
        }
        
        return $lista;
    }
    
    
    public function calcularVolumeEntradasSaidas(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $tipo = "T", $idMoeda = 0) {
        
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("A data inicial não pode ser maior que a data final");
            }
            
            $where[] = " data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP)}' ";
            
        }
        
        if ($tipo != "T") {
            if (!in_array($tipo, Array("D", "S"))) {
                throw new \Exception("Tipo de movimento inválido");
            }
            
            $where[] = " tipo = '{$tipo}' ";
        }
        
        if ($idMoeda > 0) {
            $where[] = " id_moeda = '{$idMoeda}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = "SELECT "
                . " SUM(valor) AS volume "
                . " FROM historico_transacoes_referencias "
                . " {$sWhere}; ";
                
                
        $dados = $this->conexao->adapter->query($query)->execute();
        $volume = 0;
        foreach ($dados as $d) {
            $volume = (isset($d["volume"]) ? $d["volume"] : 0);
        }
        
        return $volume;
    }
    
    
    
    
    public function getListaNegociacoes(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $tipo = "T") {
        
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("A data inicial não pode ser maior que a data final");
            }
            
            $where[] = " data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP)}' ";
            
        }
        
        if ($tipo != "T") {
            if (!in_array($tipo, Array("D", "S"))) {
                throw new \Exception("Tipo de movimento inválido");
            }
            
            $where[] = " tipo = '{$tipo}' ";
        }
        
        $sWhere = (sizeof($where) > 0 ? implode(" AND ", $where) : null);
        
        $lista = $this->conexao->listar($sWhere, "data DESC", null, null);
        return $lista;
        
    }
    
}

?>