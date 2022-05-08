<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * Description of NotificacaoClienteOperacao
 *
 * @author willianchiquetto
 */
class NotaFiscalOperacaoClienteRn { 
    
               /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NotaFiscalOperacaoCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new NotaFiscalOperacaoCliente());
        }
    }
    
    public function salvar(NotaFiscalOperacaoCliente &$notaFiscalOpeCliente) {
        
        if ($notaFiscalOpeCliente->id > 0) {
            $aux = new NotaFiscalOperacaoCliente(Array("id" => $notaFiscalOpeCliente->id));
            $this->conexao->carregar($aux);
            $notaFiscalOpeCliente->id = $aux->id;
        }        
        
        if (empty($notaFiscalOpeCliente->idCliente)) {
            throw new \Exception("Cliente não identificado");
        }

        $this->conexao->salvar($notaFiscalOpeCliente);
    }
    
    public function alterarStatus(NotaFiscalOperacaoCliente &$notaFiscalOpeCliente) {

        $this->conexao->update(Array("saque_ativo" => $notaFiscalOpeCliente->saqueAtivo, "deposito_ativo" => $notaFiscalOpeCliente->depositoAtivo),
                Array("id" => $notaFiscalOpeCliente->id));
    }
    
    public function filtrarCliente($idCliente) {
        try{
            $nfOpeCliente = "";
            if(empty($idCliente)){
                throw new \Exception("Cliente inválido.");
            }
            $result = $this->conexao->listar(" id_cliente = {$idCliente} ");
            if(sizeof($result) > 0){
                foreach ($result as $dados) {
                $nfOpeCliente = $dados;    
                } 
            }
        } catch (Exception $ex) {
            throw new \Exception("Operação de Nota Fiscal não encontrada.");
        }
        return $nfOpeCliente;        
    }

}
