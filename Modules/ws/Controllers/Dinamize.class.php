<?php

namespace Modules\ws\Controllers;

class Dinamize {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    /*public function listarCliente($params){
        
        $dinamize = new \Dinamize();
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $email = \Utils\Post::get($params, "email", null);
        
        //Autenticar Dinamize
        $token = $dinamize->Auth();

        $clientes = $clienteRn->conexao->listar(" email = '{$email}' ", "id ASC");
         
        if(sizeof($clientes) > 0 && !empty($token)){
            foreach ($clientes as $cliente){                   
                //Verificar email adicionado
                $clienteAdicionado = $dinamize->listarCliente($cliente, $token);

                if(!empty($clienteAdicionado)){
                    print_r($clienteAdicionado);
                }  
            }
        }        
    }*/
    
    public function adicionarClienteDia($params){
            
        try {
            $dinamize = new \Dinamize();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            $dataGet = \Utils\Get::get($params, "dataInicio", null);
            $dataFinal = \Utils\Get::get($params, "dataFinal", null);
            $email = \Utils\Get::get($params, "email", null);

            if (empty($dataGet)) {
                $dataAtual = new \Utils\Data(date("Y-m-d 00:00:00"));
                $dataFinal = new \Utils\Data(date("Y-m-d 23:59:59"));
            } else {
                $dataAtual = new \Utils\Data(date("{$dataGet} 00:00:00"));
                $dataFinal = new \Utils\Data(date("{$dataFinal} 23:59:59"));
            }

            //Autenticar Dinamize
            $token = $dinamize->Auth();

            if(empty($email)){
                $clientes = $clienteRn->conexao->listar(" status = 1 AND email_confirmado = 1 AND data_cadastro BETWEEN '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}'", "id ASC");
            } else {
                $clientes = $clienteRn->conexao->listar(" email = '{$email}' ", "id ASC");
            }

            if (sizeof($clientes) > 0 && !empty($token)) {
                foreach ($clientes as $cliente) {
                    //Verificar email adicionado
                    $clienteAdicionado = $dinamize->listarCliente($cliente, $token);
                    //exit(print_r($clienteAdicionado));
                    usleep(900000);
                    if (!empty($clienteAdicionado)) {
                        $getArray = $clienteAdicionado->body->items;
                        if (sizeof($getArray) > 0) {
                            $dados = $getArray[0];
                            $email = $dados->email;

                            if ($email == $cliente->email) {
                                echo "Cliente já adicionado. - {$cliente->nome} <br>";
                            }
                        } else {
                            //Adicionar Cliente
                            $status = $dinamize->adcionarCliente($cliente, $token);
                            if ($status) {
                                echo "Cliente {$cliente->nome} adicionado com sucesso <br>";
                            } else {
                                echo "Falha para adicionar o cliente {$cliente->nome} <br>";
                            }
                        }
                    }
                    usleep(800000);
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }
    
    public function removerClientes(){
        
        try {
            $dinamize = new \Dinamize();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            //Autenticar Dinamize
            $token = $dinamize->Auth();

            //Listar clientes cadastro na data atual;
            $clientes = $clienteRn->conexao->listar(" status = 2 ", "id ASC");
            //$clientes = $clienteRn->conexao->listar(" email = 'willianchiquetto_mc@hotmail.com' ", "id ASC");

            if (sizeof($clientes) > 0 && !empty($token)) {
                foreach ($clientes as $cliente) {
                    //Verificar email adicionado
                    $clienteAdicionado = $dinamize->listarCliente($cliente, $token);

                    if (!empty($clienteAdicionado)) {
                        $getArray = $clienteAdicionado->body->items;
                        if (sizeof($getArray) > 0) {
                            //Remover Cliente
                            $dados = $getArray[0];
                            $code = $dados->code;
                            $object = $dinamize->removerCliente($code, $token);
                            if ($object->code == 480001) {
                                if ($object->body->affected_rows == 1) {
                                    echo "Cliente {$cliente->nome} removido com sucesso <br>";
                                } else {
                                    echo "Falha para remover o cliente {$cliente->nome} <br>";
                                }
                            } else {
                                echo "Falha para remover o cliente {$cliente->nome} <br>";
                            }
                        }
                    }
                     usleep(1500000);
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }
    
    public function atualizarClientes($params) {
        try {
            $dinamize = new \Dinamize();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $listaVal = array();

            $email = \Utils\Get::get($params, "email", null);
            
            //Autenticar Dinamize
            $token = $dinamize->Auth();

            //Listar clientes cadastro na data atual;
            if (empty($email)) {
                $clientes = $clienteRn->conexao->listar(" email_confirmado = 1 AND status = 1 ", "id DESC");
            } else {
                $clientes = $clienteRn->conexao->listar(" email = '{$email}' ", "id ASC");
            }
            
            if (sizeof($clientes) > 0 && !empty($token)) {

                $objectValores = $dinamize->listaValores($token);

                $listaValores = $objectValores->body->list;

                foreach ($listaValores as $valores) {
                    $listaVal[$valores->code] = $valores->value;
                }

                foreach ($clientes as $cliente) {
                    //Verificar email adicionado
                    $clienteAdicionado = $dinamize->listarCliente($cliente, $token);
                     usleep(700000);
                    if (!empty($clienteAdicionado)) {
                        $getArray = $clienteAdicionado->body->items;
                        if (sizeof($getArray) > 0) {

                            $moedas = "[" . $this->listaMoedas($cliente, $listaVal) . "]";

                            $dados = $getArray[0];
                            $clienteCode = $dados->code;
                            $object = $dinamize->atualizarCliente($cliente, $clienteCode, $token, $moedas);                           
                            
                            if ($object->code == 480001) {
                                if ($object->body->affected_rows == 1) {
                                    echo "Cliente {$cliente->nome} atualizado com sucesso <br>";
                                } else {
                                    echo "Cliente já atualizado - {$cliente->nome} - {$cliente->email}<br>";
                                }
                            } else {
                                echo "Falha para atualizar o cliente {$cliente->nome} - {$cliente->email}<br>";
                            }
                        }
                    }
                     usleep(700000);
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex);
        }
    }
    
    
    public function listaMoedas($cliente, $listaValores){
        
        $bd = new \Io\BancoDados(BDREADMANAGER);
        
        //$clienteRn = new \Models\Modules\Cadastro\ClienteRn($bd);
        
        //$cliente = new \Models\Modules\Cadastro\Cliente();
        
        //$result = $clienteRn->conexao->listar(" email_confirmado = 1 AND status = 1");
        
        //$statusEmail = "";
        //$statusCliente = "";
        //$moedas = "";
        
        $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn($bd);
        $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn($bd);
        
        //echo "nome;email;statusEmail;statusCliente;moeda <br>";
        
        //if(sizeof($result) > 0){
            //foreach ($result as $cliente){
                
                /*if($cliente->emailConfirmado == 1){
                    $statusEmail = "E-mail confirmado";
                } else {
                    $statusEmail = "Não confirmado";
                }
                
                switch ($cliente->status) {
                    case 0: $statusCliente = "Espera";
                        break;
                    case 1: $statusCliente = "Ativo";
                        break;
                    case 2: $statusCliente = "Bloqueado";
                        break;
                    default: $statusCliente = "Bloqueado";
                        break;
                }*/
                
                $real = $contaCorrenteReaisRn->lista(" id_cliente = {$cliente->id} ", null, null, 2, false);
                
                if(!empty($real) && sizeof($real) > 0){
                    $moedas .= array_search("Real", $listaValores);
                }
                
                $query = "SELECT 
                        m.nome
                        FROM conta_corrente_btc cc
                        INNER JOIN moedas m  ON (cc.id_moeda = m.id)
                        WHERE id_cliente = {$cliente->id}
                        GROUP BY m.nome;";
                
                
                $cripto = $contaCorrenteBtcRn->conexao->adapter->query($query)->execute();
                
                if(sizeof($cripto) > 0){
                    foreach ($cripto as $moeda) {
                        $aux = $moeda["nome"];                         
                        $indice = array_search($aux, $listaValores);
                        
                        if(empty($moedas)){
                            $moedas .= $indice;
                        } else {
                            $moedas .= "," . $indice;
                        }
                        
                    }
                }
                
                if(substr($moedas, -1) == ","){
                    substr($moedas, 0, -1);
                }
               
                return $moedas;
                //$moedas = (sizeof($moedas) > 0 ? implode(",", $moedas) : "");
                
                //echo $cliente->nome . ";" . $cliente->email . ";" . $statusEmail . ";" . $statusCliente . ";" . $moedas . "<br>";
                
//                $moedas = "";
//                $statusCliente  = "";
//                $statusEmail = "";
            //}
        //}
        
    }

}