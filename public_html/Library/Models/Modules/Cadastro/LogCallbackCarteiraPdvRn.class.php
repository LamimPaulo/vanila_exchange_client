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
class LogCallbackCarteiraPdvRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LogCallbackCarteiraPdv());
        } else {
            $this->conexao = new GenericModel($adapter, new LogCallbackCarteiraPdv());
        }
    }
    
    
    public static function registrarLog($httpResponse, $bodyResponse, $idCarteiraPdv, $url, $manual) {
        $logCallbackCarteiraPdvRn = new LogCallbackCarteiraPdvRn();
        
        $cliente = \Utils\Geral::getCliente();
        
        $logCallbackCarteiraPdv = new LogCallbackCarteiraPdv();
        
        $logCallbackCarteiraPdv->id = 0;
        $logCallbackCarteiraPdv->bodyResponse = $bodyResponse;
        $logCallbackCarteiraPdv->data = new \Utils\Data(date("d/m/Y H:i:s"));
        $logCallbackCarteiraPdv->httpResponse = $httpResponse;
        $logCallbackCarteiraPdv->idCarteiraPdv = $idCarteiraPdv;
        $logCallbackCarteiraPdv->manual = $manual;
        $logCallbackCarteiraPdv->url = $url;
        
        if ($manual > 0) {
            if ($cliente != null) {
                $logCallbackCarteiraPdv->idCliente = $cliente->id;
            }
        }
        
        $logCallbackCarteiraPdvRn->conexao->salvar($logCallbackCarteiraPdv);
    }
    
}

?>