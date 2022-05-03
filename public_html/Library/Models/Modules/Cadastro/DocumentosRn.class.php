<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * Description of 
 *
 * @author willianchiquetto
 */
class DocumentosRn { 
    
        /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Documentos());
        } else {
            $this->conexao = new GenericModel($adapter, new Documentos());
        }
    }
    
    public function salvar(Documentos &$documento) {
        
        if ($documento->id > 0) {
            $aux = new Documentos(Array("id" => $documento->id));
            $this->conexao->carregar($aux);
            $documento->id = $aux->id;
        }        
        
        if (empty($documento->idCliente)) {
            throw new \Exception("O cliente não pode estar vazio.");
        }
        
        if (empty($documento->nomeArquivo)) {
            throw new \Exception("O nome do arquivo não pode estar vazio.");
        }
        
        if (empty($documento->dataEnvio->data)) {
            throw new \Exception("A data de envio não pode estar vazia.");
        }
        
        if (empty($documento->status)) {
            throw new \Exception("O status não pode estar vazio.");
        }
        
        if (empty($documento->tipoDocumento)) {
            throw new \Exception("O tipo de documento não pode estar vazio.");
        }

        $this->conexao->salvar($documento);
    }
    
    
    /*public function alterarStatus(NotificacaoMoeda &$notificacaoMoeda) {
        try {
            $this->conexao->carregar($notificacaoMoeda);
        } catch (\Exception $ex) {
            throw new \Exception("Notificação não localizada.");
        }        
        $notificacaoMoeda->publicacao = ($notificacaoMoeda->publicacao == \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO ? \Utils\Constantes::NOTIFICACAO_MOEDA_DESATIVADO : \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO);
        $this->conexao->update(Array("publicacao" => $notificacaoMoeda->publicacao), Array("id" => $notificacaoMoeda->id));
    }
    */
    
       

}
