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
class ClienteHasCloseOfferRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasCloseOffer());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasCloseOffer());
        }
    }  
    
    public function getAccess(Cliente $cliente, $offerId) {
        
        $result = $this->conexao->listar("cliente_id = {$cliente->id} AND close_offer_id = {$offerId}", "id", null, NULL);
        return count($result);
        // $lista = Array();
        // foreach ($result as $dados) {
        //     $lista[$dados->idParidade] = $dados;
        // }
        // return $lista;
    }
    
    
}

?>