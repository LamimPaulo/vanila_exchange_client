<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 * 
 */
class NotificacaoMoedaHasLidoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotificacaoMoedaHasLido());
        } else {
            $this->conexao = new GenericModel($adapter, new NotificacaoMoedaHasLido());
        }
    }
    
    
    public function salvar(NotificacaoMoedaHasLido &$notificacaoMoedaHasLido) {
        
        //exit(print_r($notificacaoMoedaHasLido));
        
        if ($notificacaoMoedaHasLido->id > 0) {
            $aux = new NotificacaoMoedaHasLido(Array("id" => $notificacaoMoedaHasLido->id));
            $this->conexao->carregar($aux);            
            //$notificacaoMoeda->status = $aux->status;
        } else {
            //$notificacaoMoeda->status = \Utils\Constantes::NOTIFICACAO_MOEDA_ATIVADO;
            
        }        
       
        if (empty($notificacaoMoedaHasLido->idNotificacao)) {
            throw new \Exception("Verifique o ID da notificação.");
        }
         
        if (empty($notificacaoMoedaHasLido->idCliente)) {
            throw new \Exception("Verifique o ID do cliente.");
        }
        
        /*if (!isset($notificacaoMoedaHasLido->dataLeitura->data) || $notificacaoMoedaHasLido->dataLeitura->data == null) {
            throw new \Exception("Data de leitura inválida");
        }*/
       
        $this->conexao->salvar($notificacaoMoedaHasLido);
    }
    
    public function notificacoesNaoLidas(Cliente $cliente) {
        
        $query = "SELECT notificacao_moeda.id, titulo_portugues, titulo_ingles, descricao_portugues, descricao_ingles, id_moeda, prioridade, id_cliente
            FROM notificacao_moeda 
            LEFT JOIN notificacao_moeda_has_lido ON notificacao_moeda.id = notificacao_moeda_has_lido.id_notificacao AND id_cliente = {$cliente->id}
            WHERE  publicacao = 1 AND id_cliente IS NULL AND date(data_final) >= curdate() ORDER BY prioridade DESC, data_inicial DESC;";
                
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
    
    
    public function somarQtdVisualizacao(String $id) {

        $query = "SELECT id_notificacao,  COUNT(id_notificacao) as total FROM notificacao_moeda_has_lido WHERE id_notificacao = {$id};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
    
}

?>