<?php

namespace Modules\api\Controllers;

class Payment {
    
    private $cliente;
    private $erro;
    
    public function __construct($params) {
        try {
            
            $chave = \Utils\Post::get($params, "key", null);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $result = $clienteRn->conexao->select(Array(
                "api_key" => $chave 
            ));
            
            if (sizeof($result) > 0) {
                $this->cliente = $result->current();
            } else {
                throw new \Exception("Chave inválida ou não localizada");
            }
            
        } catch (\Exception $ex) {
            $this->erro = \Utils\Excecao::mensagem($ex);
        }
    }
    
    private function validar() {
        if ($this->cliente == null) {
            throw new \Exception($this->erro);
        }
    }
    
    
    public function index($params) {
        try {
            
            $this->validar();
            
            $id = \Utils\Post::get($params, "codigo", 0);
            
            if ($id > 0) {
                $pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado();
                $pagamentoMonitorado->id = $id;
                
                
                try {
                    $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
                    $pagamentoMonitoradoRn->conexao->carregar($pagamentoMonitorado);
                } catch (\Exception $ex) {
                    throw new \Exception("Pagamento não localizado");
                }
                
                $json["pagamento"] = Array(
                    "codigo" => $pagamentoMonitorado->id,
                    "destino" => $pagamentoMonitorado->enderecoDestino,
                    "origem" => $pagamentoMonitorado->enderecoOrigem,
                    "volume" => number_format($pagamentoMonitorado->volume, 8, ".", ""),
                    "callback" => $pagamentoMonitorado->callback,
                    "status" => $pagamentoMonitorado->status,
                    "hash" => $pagamentoMonitorado->hash,
                    "volumePago" => number_format($pagamentoMonitorado->volumePago, 8, ".", ""),
                    "dataPagamento" => ($pagamentoMonitorado->dataPagamento != null ? $pagamentoMonitorado->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR) : null),
                    "parametroUm" => $pagamentoMonitorado->parametroUm,
                    "parametroDois" => $pagamentoMonitorado->parametroDois,
                    "parametroTres" => $pagamentoMonitorado->parametroTres
                );
                
            } else {
                
                $pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado();
                $pagamentoMonitorado->id = 0;
                $pagamentoMonitorado->callback = \Utils\Post::get($params, "callback", null);
                $pagamentoMonitorado->enderecoDestino = \Utils\Post::get($params, "destino", null);
                $pagamentoMonitorado->enderecoOrigem = \Utils\Post::get($params, "origem", null);
                $pagamentoMonitorado->parametroUm = \Utils\Post::get($params, "parametroUm", null);
                $pagamentoMonitorado->parametroDois = \Utils\Post::get($params, "parametroDois", null);
                $pagamentoMonitorado->parametroTres = \Utils\Post::get($params, "parametroTres", null);
                $pagamentoMonitorado->volume = \Utils\Post::getNumeric($params, "volume", null);
                $pagamentoMonitorado->idCliente = $this->cliente->id;
                
                $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
                $pagamentoMonitoradoRn->salvar($pagamentoMonitorado);
                
                $json["pagamento"] = Array(
                    "codigo" => $pagamentoMonitorado->id,
                    "destino" => $pagamentoMonitorado->enderecoDestino,
                    "origem" => $pagamentoMonitorado->enderecoOrigem,
                    "volume" => number_format($pagamentoMonitorado->volume, 8, ".", ""),
                    "callback" => $pagamentoMonitorado->callback,
                    "status" => $pagamentoMonitorado->status,
                    "hash" => $pagamentoMonitorado->hash,
                    "volumePago" => number_format($pagamentoMonitorado->volumePago, 8, ".", ""),
                    "dataPagamento" => ($pagamentoMonitorado->dataPagamento != null ? $pagamentoMonitorado->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR) : null),
                    "parametroUm" => $pagamentoMonitorado->parametroUm,
                    "parametroDois" => $pagamentoMonitorado->parametroDois,
                    "parametroTres" => $pagamentoMonitorado->parametroTres
                );
                
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function change($params) {
        try {
            $pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado();
            $pagamentoMonitorado->id = \Utils\Post::get($params, "codigo", 0);
            
            $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
            try {
                $pagamentoMonitoradoRn->conexao->carregar($pagamentoMonitorado);
                
                if ($pagamentoMonitorado->idCliente != $this->cliente->id) {
                    throw new \Exception("Você não tme permissão para alterar o registro solicitado");
                }
                
            } catch (\Exception $ex) {
                throw new \Exception("Registro inválido ou não encontrado");
            }
            
            $pagamentoMonitorado->callback = \Utils\Post::get($params, "callback", $pagamentoMonitorado->callback);
            $pagamentoMonitorado->enderecoDestino = \Utils\Post::get($params, "destino", $pagamentoMonitorado->enderecoDestino);
            $pagamentoMonitorado->enderecoOrigem = \Utils\Post::get($params, "origem", $pagamentoMonitorado->enderecoOrigem);
            $pagamentoMonitorado->parametroUm = \Utils\Post::get($params, "parametroUm", $pagamentoMonitorado->parametroUm);
            $pagamentoMonitorado->parametroDois = \Utils\Post::get($params, "parametroDois", $pagamentoMonitorado->parametroDois);
            $pagamentoMonitorado->parametroTres = \Utils\Post::get($params, "parametroTres", $pagamentoMonitorado->parametroTres);
            $pagamentoMonitorado->volume = \Utils\Post::getNumeric($params, "volume", $pagamentoMonitorado->volume);
            
            $pagamentoMonitoradoRn->salvar($pagamentoMonitorado);
            
            $json["pagamento"] = Array(
                "codigo" => $pagamentoMonitorado->id,
                "destino" => $pagamentoMonitorado->enderecoDestino,
                "origem" => $pagamentoMonitorado->enderecoOrigem,
                "volume" => number_format($pagamentoMonitorado->volume, 8, ".", ""),
                "callback" => $pagamentoMonitorado->callback,
                "status" => $pagamentoMonitorado->status,
                "hash" => $pagamentoMonitorado->hash,
                "volumePago" => number_format($pagamentoMonitorado->volumePago, 8, ".", ""),
                "dataPagamento" => ($pagamentoMonitorado->dataPagamento != null ? $pagamentoMonitorado->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR) : null),
                "parametroUm" => $pagamentoMonitorado->parametroUm,
                "parametroDois" => $pagamentoMonitorado->parametroDois,
                "parametroTres" => $pagamentoMonitorado->parametroTres
            );
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    public function pendentes($params) {
        try {
            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataAtual->subtrair(0, 0, 5);
            $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
            
            $pagamentoMonitoradoRn->conexao->update(
                    Array(
                        "status" => "C"
                    ), 
                    "data_cadastro <= '{$dataAtual->formatar(\Utils\Data::FORMATO_ISO)} 00:00:00' AND status = 'A'" 
                );
            $result = $pagamentoMonitoradoRn->conexao->listar("status = 'A'");
            
            $lista = Array();
            foreach ($result as $pagamentoMonitorado) {
                //$pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado();
                $lista[] = Array(
                    "id" => $pagamentoMonitorado->id,
                    "destino" => $pagamentoMonitorado->enderecoDestino,
                    "origem" => $pagamentoMonitorado->enderecoOrigem,
                    "volume" => number_format($pagamentoMonitorado->volume, 8, ".", "")
                );
            }
            
            $json["pagamentos"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function update($params) {
        
        try {
            $json = \Utils\Post::get($params, "pagamentos", NULL);
            $pagamentos = \Zend\Json\Json::decode($json);
            
            $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
            foreach ($pagamentos as $p) {
                try {
                    
                    $pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado(Array("id" => $p->id));
                    $pagamentoMonitoradoRn->conexao->carregar($pagamentoMonitorado);
                    
                    if (isset($p->hashes) && sizeof($p->hashes) > 0) {
                        
                        
                        foreach ($p->hashes as $dados) {
                            
                            $paid = $pagamentoMonitoradoRn->getByHash($dados->hash);
                            if ($paid == null) {
                                
                                if ($dados->amount >= $pagamentoMonitorado->volume) {
                                    $pagamentoMonitoradoRn->conexao->update(
                                        Array(
                                            "data_pagamento" => date("Y-m-d H:i:s"),
                                            "status" => "P",
                                            "hash" => $dados->hash,
                                            "volume_pago" => number_format($dados->amount, 8, ".", "")
                                        ), 
                                        Array(
                                            "id" => $p->id
                                        )
                                    );
                                    
                                    break;
                                }
                                
                            }
                            
                        }
                        
                    }
                    
                    $pagamentoMonitoradoRn->conexao->carregar($pagamentoMonitorado);
                    
                    if (!empty($pagamentoMonitorado->callback)) {
                        $callback = $pagamentoMonitorado->callback;
                        $curl = curl_init();

                        if (substr($callback, strlen($callback) - 1, 1) == "/") {
                            $callback = substr($callback, 0, strlen($callback) - 1);
                        }

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "{$callback}?codigo={$pagamentoMonitorado->id}",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_HTTPHEADER => array(
                              "cache-control: no-cache"
                            ),
                        ));

                        $result = curl_exec($curl);
                        //exit($result);
                        curl_close($curl);

                    }
                    
                } catch (\Exception $ex) {
                    
                }
            }
            
            $j["sucesso"] = true;
        } catch (\Exception $ex) {
            $j["sucesso"] = false;
            $j["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($j);
    }
    
}