<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class LogQueueRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogQueue());
        } else {
            $this->conexao = new GenericModel($adapter, new LogQueue());
        }
    }

    public  function registrar($id_process,$tipo,$queue,  $mensagem, $body) {
        
        try {
            
            $LogQueue = new LogQueue();
            $LogQueue->id_process = $id_process;
            $LogQueue->data = date("Y-m-d H:i:s");
            $LogQueue->tipo = $tipo;
            $LogQueue->queue = $queue;
            $LogQueue->mensagem = $mensagem;
            $LogQueue->body = $body;
            $this->conexao->salvar($LogQueue);
            
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
        
    }
    
    
}

?>
