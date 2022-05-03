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
class LogAcessoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogAcesso());
        } else {
            $this->conexao = new GenericModel($adapter, new LogAcesso());
        }
    }
    
    
    public function registrarLog(LogAcesso &$logAcesso) {
        
        $server = $_SERVER;
        
        $cliente = \Utils\Geral::getCliente();
        $usuario = \Utils\Geral::getLogado();
        
        $logAcesso->id = 0;
        $logAcesso->agente = (isset($server["HTTP_USER_AGENT"]) ? $server["HTTP_USER_AGENT"] : "");
        $logAcesso->dataHora = new \Utils\Data(date("d/m/Y H:i:s"));
        $logAcesso->email = "";
        $logAcesso->idCliente = (\Utils\Geral::isCliente() && $cliente != null ? $cliente->id : null);
        $logAcesso->idUsuario = (\Utils\Geral::isUsuario() && $usuario != null ? $usuario->id : null);
        $logAcesso->ip = (isset($server["REMOTE_ADDR"]) ? $server["REMOTE_ADDR"] : "");
        
        $browser = get_browser($server["HTTP_USER_AGENT"]);
        
        $logAcesso->navegador = (isset($browser->browser_name_pattern) ? $browser->browser_name_pattern : "");
        $logAcesso->sistemaoperacional = (isset($browser->platform) ? $browser->platform : "");
        $logAcesso->versaonavegador = (isset($browser->version) ? $browser->version : "");
        
        $this->conexao->salvar($logAcesso);
    }
    
}

?>