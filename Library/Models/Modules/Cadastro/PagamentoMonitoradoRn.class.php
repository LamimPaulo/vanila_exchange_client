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
class PagamentoMonitoradoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new PagamentoMonitorado());
        } else {
            $this->conexao = new GenericModel($adapter, new PagamentoMonitorado());
        }
    }
    
    public function salvar(PagamentoMonitorado &$pagamentoMonitorado) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            if ($pagamentoMonitorado->id > 0) {
                $aux = new PagamentoMonitorado(Array("id" => $pagamentoMonitorado->id));
                $this->conexao->carregar($aux);
                
                $pagamentoMonitorado->idCliente = $aux->idCliente;
                $pagamentoMonitorado->status = $aux->status;
                $pagamentoMonitorado->dataCadastro = $aux->dataCadastro;
                
                if ($aux->status == "P") {
                    throw new \Exception($this->idioma->getText("naoPossivelAtualizarRegPago"));
                }
                if ($aux->status == "C") {
                    throw new \Exception($this->idioma->getText("naoPosAtualiRegistroCancelado"));
                }
                
                
            }  else {
                $pagamentoMonitorado->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $pagamentoMonitorado->status = "A";
            }
            
            if (empty($pagamentoMonitorado->enderecoOrigem)) {
                throw new \Exception($this->idioma->getText("enderecoOrigemPrecisaInformado"));
            }
            
            if (!\Utils\BitcoinAddress::validate($pagamentoMonitorado->enderecoOrigem)) {
                throw new \Exception($this->idioma->getText("endOrigemInvalido"));
            }
            
            if (empty($pagamentoMonitorado->enderecoDestino)) {
                throw new \Exception($this->idioma->getText("endDestinoInvalido"));
            }
            
            if (!\Utils\BitcoinAddress::validate($pagamentoMonitorado->enderecoDestino)) {
                throw new \Exception($this->idioma->getText("endDestinoInvalido"));
            }
            
            if ($pagamentoMonitorado->volume <= 0) {
                throw new \Exception($this->idioma->getText("valumeMaiorQueZero"));
            }
            
            if (!$pagamentoMonitorado->idCliente > 0) {
                throw new \Exception($this->idioma->getText("identiClienteInvalida"));
            }
            
            $this->conexao->salvar($pagamentoMonitorado);
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function getByHash($hash) {
        $result = $this->conexao->select(Array("hash" => $hash));
        if (sizeof($result) > 0){
            return $result->current();
        }
        return null;
    }
    
}

?>