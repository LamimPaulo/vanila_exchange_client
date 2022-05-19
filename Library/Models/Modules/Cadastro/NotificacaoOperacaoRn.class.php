<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * Description of NotificacaoClienteOperacaoRn
 *
 * @author willianchiquetto
 */
class NotificacaoOperacaoRn { 
    
        /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotificacaoOperacao());
        } else {
            $this->conexao = new GenericModel($adapter, new NotificacaoOperacao());
        }
    }
       

}
