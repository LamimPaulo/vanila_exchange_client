<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * 
 *
 * @author willianchiquetto
 */
class AtarClientesRn { 
    
     /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new AtarClientes());
        } else {
            $this->conexao = new GenericModel($adapter, new AtarClientes());
        }
    }
    
    public function salvar(AtarClientes &$atarCliente) {
        
        if ($atarCliente->id > 0) {
            $aux = new AtarClientes(Array("id" => $atarCliente->id));
            $this->conexao->carregar($aux);
            $atarCliente->id = $aux->id;
            $atarCliente->dataCadastro = $aux->dataCadastro;
            $atarCliente->idCliente = $aux->idCliente;
            $atarCliente->idAtar = $aux->idAtar;
        }
        
        if (empty($atarCliente->idCliente)) {
            throw new \Exception("Cliente precisa ser preenchido.");
        }
        
        if (empty($atarCliente->idAtar)) {
            throw new \Exception("Cliente Atar precisa ser preenchido.");
        }

        if (empty($atarCliente->dataCadastro)) {
            throw new \Exception("Data de cadastro vazia.");
        }
        
        $dados = $this->conexao->listar(" id_cliente = {$atarCliente->idCliente} OR id_atar = '{$atarCliente->idAtar}' OR document_atar = '{$atarCliente->idAtar}' ");
        
        if(sizeof($dados) > 0){
            throw new \Exception("Cadastro não autorizado.");
        }
        
        if ($atarCliente->ativo != 1) {
            $atarCliente->ativo = 0;
        }
        
        $this->conexao->salvar($atarCliente);
    }
    
    public function encontrarCliente(AtarContas &$atarContas, $ativo = true) {
        try {    
            
            $queryAtivo = "";
            if($ativo){
                $queryAtivo = " AND ativo = 1";
            }
            
            $atarCliente = $this->conexao->listar(" document_atar = {$atarContas->documentAtar} AND id_atar = {$atarContas->idClienteAtar} {$queryAtivo} ");
            
            if(!sizeof($atarCliente) > 0 ){               
                $atarCliente = $this->conexao->listar(" id_atar = {$atarContas->idClienteAtar} {$queryAtivo} ");
            }
            if (!sizeof($atarCliente) > 0 ) {                
                $atarCliente = $this->conexao->listar(" id_atar = {$atarContas->documentAtar} {$queryAtivo} ");
            }
            
            return $atarCliente;
        } catch (\Exception $ex) {
            throw new \Exception("Cliente não localizado.");
        }
    } 

}
