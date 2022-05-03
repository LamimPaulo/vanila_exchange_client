<?php

namespace Utils;

class Notificacao {
    
    
    
    public static function notificar($mensagem, $email = true, $sms = true, $cliente = null, $semCliente = false) {

        $observacaoCliente = new \Models\Modules\Cadastro\ObservacaoCliente();
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

        
        $infAdicional = "";
        
        if (!$semCliente) {

            if (empty($cliente)) {
                $cliente = Geral::getCliente();
            }
            
            $infAdicional = "POST = " . implode("|", $_POST) . " - GET = " . implode("|", $_GET) . " - _SERVER['QUERY_STRING']: " . $_SERVER['QUERY_STRING'] . " -  _SERVER['HTTP_REFERER']: " . $_SERVER["HTTP_REFERER"];

            if(!empty($cliente)){
                $observacaoCliente->idCliente = $cliente->id;
                $observacaoCliente->observacoes = $infAdicional;

                $observacaoClienteRn = new \Models\Modules\Cadastro\ObservacaoClienteRn();
                $observacaoClienteRn->salvar($observacaoCliente);
                
                $clienteRn->conexao->update(Array("analise_cliente" => 1), Array(id => $cliente->id));
            }
        }
        
        
         //PRODUCAO
        if (AMBIENTE == "producao") {
            
            $usuarios = Array();
            
            $clienteRenato = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064543893));
            $clienteGerson = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064543892));
            $clienteWillian = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064543895));
            
            $clienteDanilo = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064587638));
            $clienteLeandro = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064579742));

            $clienteRn->conexao->carregar($clienteGerson);
            $clienteRn->conexao->carregar($clienteRenato);
            $clienteRn->conexao->carregar($clienteWillian);
            
            $clienteRn->conexao->carregar($clienteDanilo);
            $clienteRn->conexao->carregar($clienteLeandro);
            
            $usuarios[] = $clienteWillian;
            $usuarios[] = $clienteRenato;
            $usuarios[] = $clienteGerson;
            
            $usuarios[] = $clienteDanilo;
            $usuarios[] = $clienteLeandro;
            
            if(is_array($mensagem)){
                $dados["mensagem"] = $mensagem;
                
            } else {
                $dados["mensagem"]["mensagem"] = $mensagem;
                $dados["mensagem"]["informacao_adicional"] = $infAdicional;
            }
            
            for ($i = 0; $i < sizeof($usuarios); $i++) {
                \LambdaAWS\LambdaNotificacao::notificar($usuarios[$i], true, 14, false, $dados);
                usleep(500000);
            }
            
        //DESENVOLVIMENTO
        } else {
            $clienteWillian = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064576565));

            $clienteRn->conexao->carregar($clienteWillian);
             
            if(is_array($mensagem)){
                $dados["mensagem"] = $mensagem;
                
            } else {
                $dados["mensagem"]["mensagem"] = $mensagem;
                $dados["mensagem"]["informacao_adicional"] = $infAdicional;
            }
            
            \LambdaAWS\LambdaNotificacao::notificar($clienteWillian, true, 14, false, $dados);
        }
    }

}