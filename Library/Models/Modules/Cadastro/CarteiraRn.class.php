<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Banco
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class CarteiraRn {

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;
    public $idioma = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Carteira());
        } else {
            $this->conexao = new GenericModel($adapter, new Carteira());
        }
    }

    public function salvar(Carteira &$carteira) {
       
        try {
            $this->conexao->adapter->iniciar();
            
            
            if ($carteira->id > 0) {
                $aux = new Carteira(Array("id" => $carteira->id));
                $this->conexao->carregar($aux);

                if ($aux->inutilizada > 0) {
                    throw new \Exception($this->idioma->getText("carteiraInutilizada"));
                }
                
                $carteira->principal = $aux->principal;
                $carteira->endereco = $aux->endereco;
                $carteira->data = $aux->data;
                $carteira->idCliente = $aux->idCliente;
                $carteira->idMoeda = $aux->idMoeda;
                $carteira->inutilizada = $aux->inutilizada;
            } else {
                
                $carteira->inutilizada = 0;
                if (!$carteira->idMoeda > 0) {
                    throw new \Exception($this->idioma->getText("moedaInvalida"));
                }
                
                $cliente = \Utils\Geral::getCliente();
                if ($cliente !== null) {
                    $carteira->idCliente = $cliente->id;
                } else {
                    if (!$carteira->idCliente > 0) {
                        throw new \Exception($this->idioma->getText("identificacaoClienteInformada"));
                    }
                }
                
                $moeda = new Moeda();
                $moeda->id = $carteira->idMoeda;
                $moedaRn = new MoedaRn($this->conexao->adapter);
                $moedaRn->carregar($moeda);
                
                if($moeda->token == 1){
                    $carteira->idMoeda = $moeda->idMoedaPrincipal;
                } else {
                    $carteira->idMoeda = $moeda->id;
                }
                
                $carteira->nome = $moeda->nome . " Wallet";
                
                $count = $this->conexao->listar("id_moeda = {$carteira->idMoeda} AND id_cliente = {$carteira->idCliente} AND inutilizada < 1");
                if (sizeof($count) >= $moeda->qtdMaximaCarteiras) {
                    throw new \Exception($this->idioma->getText("voceJaExcedeuLimite"));
                }
                
                $carteira->data = new \Utils\Data(date("d/m/Y H:i:s"));
                
                $carteiraGeradaRn = new CarteiraGeradaRn();
                $carteiraGerada = $carteiraGeradaRn->getWallet(($moeda->token > 0 ? $moeda->idMoedaPrincipal : $moeda->id));
                $carteira->endereco = $carteiraGerada->address;
                $carteira->seed = $carteiraGerada->seed;
                $carteira->callbackDeposito = 0;
                
                // if (AMBIENTE == "producao") {

                    $moedaCarteira = $moeda;
                    if (($moedaCarteira->token > 0 && $moedaCarteira->idMoedaPrincipal > 0)) {
                        $moedaCarteira = $moedaRn->get($moedaCarteira->idMoedaPrincipal);
                    }

                    if ($moedaCarteira->gerarCarteira == 1) {

                        // $queueName = 'inbound';
                        $exchangeName = 'ex.wallet_new';
                        $params = [
                            'id_moeda' => $moedaCarteira->id,
                            'rede_moeda' => $moedaCarteira->coinType,
                            'qtd' => 1
                        ];
                        $result = \LambdaAWS\QueueKYC::sendQueue($exchangeName , $params);
                    }
                // }
                
                
                if (empty($carteira->endereco)) {
                    throw new \Exception($this->idioma->getText("naoFoiPossivelCriarCarteiraMomento"));
                }
                
                $result = $this->conexao->listar(" id_cliente = {$carteira->idCliente} AND id_moeda = {$carteira->idMoeda} AND inutilizada < 1");
                $carteira->principal = (sizeof($result) < 1 ? 1 : 0);
            }
            
            
            unset($carteira->moeda);
            $this->conexao->salvar($carteira);
            // exit(print_r('teste'));
            
            $this->conexao->adapter->finalizar();
            
            
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }


    public function getByEndereco($endereco, $idMoeda = 0, $inutilizada = false, $cliente = null) {
        if ($idMoeda > 0) {
            $moeda = new Moeda(Array("id" => $idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($moeda);

            if ($moeda->token > 0) {
                $idMoeda = $moeda->idMoedaPrincipal;
            }
        }
        $inut = ($inutilizada ? " " : " AND inutilizada < 1 ");
        $wMoeda = ($idMoeda > 0 ? " AND id_moeda = {$idMoeda} " : "");
        $qCliente = (!empty($cliente) ? " AND id_cliente = {$cliente->id} " : "");
        $result = $this->conexao->listar("endereco = '{$endereco}' {$wMoeda} {$inut} {$qCliente}" );
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    public function getPrincipal(Cliente $cliente, $idMoeda, Moeda $objetoMoeda = null) {
        $result = $this->conexao->listar("principal = 1 AND inutilizada < 1 AND id_cliente = {$cliente->id} AND id_moeda = {$idMoeda}", null, null, null);
        if (sizeof($result) > 0) {
            $carteiraPrincipal = $result->current();
            return $carteiraPrincipal;
        }
        return null;
    }
    
    public function getPrincipalCarteira(Cliente $cliente, Moeda $objetoMoeda) {
        
        if($objetoMoeda->token == 1){
            $idMoeda = $objetoMoeda->idMoedaPrincipal;
        } else {
            $idMoeda = $objetoMoeda->id;
        }
        
        $result = $this->conexao->listar("principal = 1 AND inutilizada < 1 AND id_cliente = {$cliente->id} AND id_moeda = {$idMoeda}", null, null, null);
        if (sizeof($result) > 0) {
            $carteiraPrincipal = $result->current();
            return $carteiraPrincipal;
        }
        return null;
    }
    
    public function marcarComoPrincipal(Carteira $carteira) {
        try {
            $this->conexao->adapter->iniciar();
            
            try {
                $this->conexao->carregar($carteira);
                
                $cliente = \Utils\Geral::getLogado();
                
                if ($carteira->idCliente != $cliente->id) {
                    throw new \Exception($this->idioma->getText("voceNaoTemPermissaoAcessarCarteira"));
                }
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("carteiraNaoLocalizado"));
            }
            
            $this->conexao->update(Array("principal" => 1), Array("id" => $carteira->id));
            
            $where = new \Zend\Db\Sql\Where();
            $where->notEqualTo("id", $carteira->id);
            $where->equalTo("id_cliente", $cliente->id);
            $where->equalTo("id_moeda", $carteira->idMoeda);
            $this->conexao->update(Array("principal" => 0), $where);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function carregar(Carteira &$carteira, $carregar = true, $carregarMoeda = true) {
        if ($carregar) {
            $this->conexao->carregar($carteira);
        }
        
        if ($carregarMoeda && $carteira->idMoeda > 0) {
            $carteira->moeda = new Moeda(Array("id" => $carteira->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($carteira->moeda);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarMoeda = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        foreach ($result as $carteira) {
            $this->carregar($carteira, FALSE, $carregarMoeda);
            $lista[] = $carteira;
        }
        
        return $lista;
    }
    
    public function marcarCallback($idCarteira) {
        $this->conexao->update(Array("callback_deposito" => 1), Array("id" => $idCarteira));        
    }
    
}

?>
