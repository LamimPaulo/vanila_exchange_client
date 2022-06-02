<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 * 
 */
class NotificacaoMoedaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotificacaoMoeda());
        } else {
            $this->conexao = new GenericModel($adapter, new NotificacaoMoeda());
        }
    }
    
    
    public function salvar(NotificacaoMoeda &$notificacaoMoeda) {
        
        if ($notificacaoMoeda->id > 0) {
            $aux = new NotificacaoMoeda(Array("id" => $notificacaoMoeda->id));
            $this->conexao->carregar($aux);
            $notificacaoMoeda->id = $aux->id;
        }        
        
        if (empty($notificacaoMoeda->tituloPortugues)) {
            throw new \Exception("O título deve ser informado - Português");
        }
        
        if (empty($notificacaoMoeda->tituloIngles)) {
            throw new \Exception("O título deve ser informado - Inglês");
        }
        
        if (empty($notificacaoMoeda->descricaoPortugues)) {
            throw new \Exception("A descrição deve ser informada - Português");
        }
        
        if (empty($notificacaoMoeda->descricaoIngles)) {
            throw new \Exception("A descrição deve ser informada - Português");
        }
        
        if (!isset($notificacaoMoeda->dataInicial->data) || $notificacaoMoeda->dataInicial->data == null) {
            throw new \Exception("Data inicial inválida");
        }
        
        if (!isset($notificacaoMoeda->dataFinal->data) || $notificacaoMoeda->dataFinal->data == null) {
            throw new \Exception("Data final inválida");
        }
        
        /*if (empty($notificacaoMoeda->idMoeda)) {
            throw new \Exception("Selecione uma moeda.");
        }*/
        
        if (empty($notificacaoMoeda->idUsuario)) {
            throw new \Exception("Usuário inválido.");
        }
        
        if (empty($notificacaoMoeda->publicacao)) {
            throw new \Exception("Selecione uma publicação.");
        }
        
        if (empty($notificacaoMoeda->prioridade)) {
            throw new \Exception("Selecione uma prioridade.");
        }

        $this->conexao->salvar($notificacaoMoeda);
    }
    
    
    public function alterarStatus(NotificacaoMoeda &$notificacaoMoeda) {
        try {
            $this->conexao->carregar($notificacaoMoeda);
        } catch (\Exception $ex) {
            throw new \Exception("Notificação não localizada.");
        }        
        $notificacaoMoeda->publicacao = ($notificacaoMoeda->publicacao == \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO ? \Utils\Constantes::NOTIFICACAO_MOEDA_DESATIVADO : \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO);
        $this->conexao->update(Array("publicacao" => $notificacaoMoeda->publicacao), Array("id" => $notificacaoMoeda->id));
    }    
    
    
}

?>