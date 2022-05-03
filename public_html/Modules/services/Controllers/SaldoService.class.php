<?php

namespace Modules\services\Controllers;

class SaldoService {
    
    public function update($params) {
        
        try {
            
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $cartoes = $pedidoCartaoRn->conexao->listar("ativo = 1", null, null, null);
            
            foreach ($cartoes as $pedidoCartao) {
                try {
                    $consulta = new \APICartao\Consulta();
                    $dados = $consulta->saldo($pedidoCartao->numeroCartao);
                    if ($dados->title == "Saldo") {
                        $saldo = trim(str_replace("R$", "", $dados->balance));

                        $pedidoCartaoRn->conexao->update(
                                Array("saldo" => str_replace(",", ".", $saldo), "ultima_atualizacao_saldo" => date("Y-m-d H:i:s")), 
                                Array("id" => $pedidoCartao->id));

                    }
                } catch(\Exception $e) {
                    
                }
            }
            
            exit("process exited successfully");
        } catch (\Exception $ex) {
            
        }
        
    }
    
}