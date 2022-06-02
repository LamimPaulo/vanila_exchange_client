<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class LogErroRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogErro());
        } else {
            $this->conexao = new GenericModel($adapter, new LogErro());
        }
    }
    
    
    public static function registrar($mensagem, $codigo) {
        
        try {
            
            $logErro = new LogErro();
            
            $logErro->codigo = $codigo;
            $logErro->mensagem = htmlentities($mensagem);
            $logErro->data = new \Utils\Data(date("d/m/Y H:i:s"));
            
            if (\Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
                $logErro->idCliente = $cliente->id;
            }
            
            if (\Utils\Geral::isUsuario()) {
                $usuario = \Utils\Geral::getLogado();
                $logErro->idUsuario = $usuario->id;
            }
            
            $logErro->id = 0;
            
            $logErroRn = new LogErroRn();
            
            //exit(print_r($logErro));
            //$logErroRn->conexao->salvar($logErro);
            
        } catch (\Exception $ex) {
            
        }
        
    }
    
    
}

?>
