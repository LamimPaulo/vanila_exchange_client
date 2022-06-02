<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade P2pVenda
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class P2pVendaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
         $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new P2pVenda());
        } else {
            $this->conexao = new GenericModel($adapter, new P2pVenda());
        }
    }
    
    
    public function salvar(P2pVenda &$p2pVenda) {
        $this->conexao->adapter->iniciar();
        try {
            $p2pVenda->dataAlteracao = new \Utils\Data(date("d/m/Y H:i:s"));
            if ($p2pVenda->id > 0) {

                $aux = new P2pVenda(Array("id" => $p2pVenda->id));
                $this->conexao->carregar($aux);

                $p2pVenda->dataLancamento = $aux->dataLancamento;

            } else {

                $p2pVenda->dataLancamento = new \Utils\Data(date("d/m/Y H:i:s"));
            }

            if (empty($p2pVenda->nomeCliente)) {
                throw new \Exception($this->idioma->getText("necessarioInformarNomeCliente"));
            }

            unset($p2pVenda->usuario);
            unset($p2pVenda->banco);

            $this->conexao->salvar($p2pVenda);
            
            $orderBookRn = new OrderBookRn($this->conexao->adapter);
            $orderBook = new OrderBook();
            $orderBook->id = 0;
            $orderBook->cancelada = 0;
            $orderBook->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $orderBook->direta = 0;
            $orderBook->executada = 1;
            $orderBook->idCliente = null;
            $orderBook->idParidade = 1;
            $orderBook->percentualTaxa = 0;
            $orderBook->tipo = $p2pVenda->tipoOperacao;
            $orderBook->valorCotacao = $p2pVenda->valorCotacao;
            $orderBook->valorCotacaoReferencia = 0;
            $orderBook->valorTaxa = 0;
            $orderBook->valorTaxaExecutada = 0;
            $orderBook->volumeCurrency = $p2pVenda->volumeBtc;
            $orderBook->volumeExecutado = $p2pVenda->volumeBtc;
            
            unset($orderBook->paridade);
            
            $orderBookRn->conexao->salvar($orderBook);
            
            $this->conexao->update(
                Array(
                    "id_order_book" => $orderBook->id
                ),
                Array(
                    "id" => $p2pVenda->id
                )
            );
            
            $ordemExecutadaRn = new OrdemExecutadaRn($this->conexao->adapter);
            $ordemExecutada = new OrdemExecutada();
            $ordemExecutada->id = 0;
            $ordemExecutada->dataExecucao = new \Utils\Data(date("d/m/Y H:i:s"));
            $ordemExecutada->idOrderBookCompra = ($p2pVenda->tipoOperacao == \Utils\Constantes::ORDEM_COMPRA ? $orderBook->id : null);
            $ordemExecutada->idOrderBookVenda = ($p2pVenda->tipoOperacao == \Utils\Constantes::ORDEM_VENDA ? $orderBook->id : null);
            $ordemExecutada->tipo = $p2pVenda->tipoOperacao;
            $ordemExecutada->valorCotacao = $p2pVenda->valorCotacao;
            $ordemExecutada->volumeExecutado = $p2pVenda->volumeBtc;
            $ordemExecutadaRn->conexao->salvar($ordemExecutada);
            
            
            $contaCorrenteBtcEmpresaRn = new ContaCorrenteBtcEmpresaRn($this->conexao->adapter);
            $contaCorrenteBtcEmpresa = new ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresa->bloqueado = 1;
            $contaCorrenteBtcEmpresa->data = new \Utils\Data(date("d/m/Y H:i:s"));;
            $contaCorrenteBtcEmpresa->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));;
            $contaCorrenteBtcEmpresa->descricao = ($p2pVenda->tipoOperacao == \Utils\Constantes::ORDEM_COMPRA ? "Compra" : "Venda") .  " P2P.";
            $contaCorrenteBtcEmpresa->idMoeda = 2;
            $contaCorrenteBtcEmpresa->id = 0;
            $contaCorrenteBtcEmpresa->tipo = ($p2pVenda->tipoOperacao == \Utils\Constantes::ORDEM_COMPRA ? \Utils\Constantes::ENTRADA : \Utils\Constantes::SAIDA);
            $contaCorrenteBtcEmpresa->transferencia = 0;
            $contaCorrenteBtcEmpresa->valor = $p2pVenda->volumeBtc;
            
            $contaCorrenteBtcEmpresaRn->salvar($contaCorrenteBtcEmpresa, null);
            
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function excluir(P2pVenda $p2pVenda) {
        $this->conexao->adapter->iniciar();
        try {
            
            $this->conexao->carregar($p2pVenda);
            
            if ($p2pVenda->idOrderBook > 0) {
                $orderBook = new OrderBook();
                $orderBook->id = $p2pVenda->idOrderBook;
                
                $ordemExecutadaRn = new OrdemExecutadaRn();
                if ($p2pVenda->tipoOperacao == \Utils\Constantes::ORDEM_COMPRA) {
                    $ordemExecutadaRn->conexao->delete("id_order_book_compra = {$orderBook->id}");
                } else {
                    $ordemExecutadaRn->conexao->delete("id_order_book_venda = {$orderBook->id}");
                }
                
                $orderBookRn = new OrderBookRn();
                $orderBookRn->conexao->excluir($orderBook);
                
            }
            
            $this->conexao->excluir($p2pVenda);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function carregar(P2pVenda &$p2pVenda, $carregar = true, $carregarUsuario = true) {
        if ($carregar) {
            $this->conexao->carregar($p2pVenda);
        }
        
        if ($carregarUsuario && $p2pVenda->idUsuario > 0) {
            $p2pVenda->usuario = new Usuario(Array("id" => $p2pVenda->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($p2pVenda->usuario);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarUsuario = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $p2pVenda) {
            $this->carregar($p2pVenda, false, $carregarUsuario);
            $lista[] = $p2pVenda;
        }
        return $lista;
    }


    public function filtrar(\Utils\Data $dataInicial, \Utils\Data $dataFinal, $tipoData, $texto, $tipoDeposito, $status, $idUsuario) {
        
        $where = Array();
        
        if (!isset($dataInicial->data) || $dataInicial->data == null) {
            throw new \Exception($this->idioma->getText("dataInicialInvalida"));
        }
        
        if (!isset($dataFinal->data) || $dataFinal->data == null) {
            throw new \Exception($this->idioma->getText("dataFinalInvalida"));
        }
        
        if ($dataInicial->maior($dataFinal)) {
            throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
        }
        
        switch ($tipoData) {
            case "operacao": $tipoData = "data_operacao"; break;
            case "finalizacao": $tipoData = "data_finalizacao"; break;
            case "lancamento": $tipoData = "data_lancamento"; break;
            default:
                throw new \Exception($this->idioma->getText("tipoDataInvalida"));
        }
        
        $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
        $where[] = " {$tipoData} BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        
        if (!empty($texto)) {
            $where[] = "( LOWER(nome_cliente) LIKE LOWER('%{$texto}%') OR "
            . " LOWER(email_cliente) LIKE LOWER('%{$texto}%') OR "
            . " LOWER(telefone) LIKE LOWER('%{$texto}%') OR "
            . " LOWER(carteira) LIKE LOWER('%{$texto}%') OR "
            . " LOWER(hash) LIKE LOWER('%{$texto}%') )";
        }
        
        
        if ($tipoDeposito != 'T') {
            $where[] = " tipo_deposito = '{$tipoDeposito}' ";
        }
        
        if ($status != "T") {
            $where[] = " status = '{$status}' ";
        }
        
        if ($idUsuario > 0) {
            $where[] = " id_usuario = {$idUsuario} ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT "
                . "* "
                . "FROM p2p_venda "
                . "{$where} "
                . "ORDER BY data_operacao DESC; ";
        //exit($query);
        $lista = Array();
        
        $result = $this->conexao->adapter->query($query)->execute();
        foreach ($result as $dados) {
            $p2pVenda = new P2pVenda($dados);
            
            $this->carregar($p2pVenda, false, true);
            $lista[] = $p2pVenda;
        }
        
        return $lista;
    }
    
    
    public function getAllClientes() {
        $query = " SELECT nome_cliente, email_cliente, telefone FROM p2p_venda GROUP BY nome_cliente, email_cliente, telefone; ";
        $dados = $this->conexao->adapter->query($query)->execute();
        
        $listaIndiceEmail = Array();
        $listaIndiceTelefone = Array();
        $listaIndiceNome = Array();
        foreach ($dados as $d) {
            if (!empty($d["email_cliente"])) {
                $listaIndiceEmail[trim($d["email_cliente"])] = Array(
                    "nome" => $d["nome_cliente"],
                    "telefone" => $d["telefone"]
                );
            }
            
            if (!empty($d["nome_cliente"])) {
                $listaIndiceNome[trim($d["nome_cliente"])] = Array(
                    "email" => $d["email_cliente"],
                    "telefone" => $d["telefone"]
                );
            }
            
            
            if (!empty($d["telefone"])) {
                $listaIndiceTelefone[trim($d["telefone"])] = Array(
                    "nome" => $d["nome_cliente"],
                    "email" => $d["email_cliente"]
                );
            }
        }
        
        
        return Array("nomes" => $listaIndiceNome, "emails" => $listaIndiceEmail, "telefones" => $listaIndiceTelefone);
    }
    
    
    
    
    public function getAllDepositantes() {
        $query = " SELECT nome_depositante, email_depositante, telefone_depositante FROM p2p_venda GROUP BY nome_depositante, email_depositante, telefone_depositante; ";
        $dados = $this->conexao->adapter->query($query)->execute();
        
        $listaIndiceEmail = Array();
        $listaIndiceTelefone = Array();
        $listaIndiceNome = Array();
        foreach ($dados as $d) {
            if (!empty($d["email_depositante"])) {
                $listaIndiceEmail[trim($d["email_depositante"])] = Array(
                    "nome" => $d["nome_depositante"],
                    "telefone" => $d["telefone_depositante"]
                );
            }
            
            if (!empty($d["nome_depositante"])) {
                $listaIndiceNome[trim($d["nome_depositante"])] = Array(
                    "email" => $d["email_depositante"],
                    "telefone" => $d["telefone_depositante"]
                );
            }
            
            
            if (!empty($d["telefone_depositante"])) {
                $listaIndiceTelefone[trim($d["telefone_depositante"])] = Array(
                    "nome" => $d["nome_depositante"],
                    "email" => $d["email_depositante"]
                );
            }
        }
        
        
        return Array("nomes" => $listaIndiceNome, "emails" => $listaIndiceEmail, "telefones" => $listaIndiceTelefone);
    }
}

?>