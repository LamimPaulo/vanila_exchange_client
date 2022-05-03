<?php

namespace Modules\servicos\Controllers;

class IdentificacaoPagamentos {
    
    private  $codigoModulo = "servicos";
    
    function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function index($params) {
        \Utils\Layout::view("identificacao_pagamentos", $params);
    }
    
    public function buscar($params) {
        
        try {
            $pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado();
            $pagamentoMonitorado->id = \Utils\Post::get($params, "codigo", 0);
            
            try {
                $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
                $pagamentoMonitoradoRn->conexao->carregar($pagamentoMonitorado);
                
                $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $pagamentoMonitorado->idCliente));
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                
            } catch (\Exception $ex) {
                throw new \Exception("Registro não localizado no sistema");
            }
            
            $status = "";
            $class = "navy-bg";
            switch ($pagamentoMonitorado->status) {
                case "A":
                    $status = "Aguardando Pagamento";
                    $class = "yellow-bg";
                    break;
                case "P":
                    $status = "Pagamento Identificado";
                    $class = "navy-bg";
                    break;
                case "C":
                    $status = "Cancelado";
                    $class = "red-bg";
                    break;
            }
            
            $callback = "";
            if ($pagamentoMonitorado->status == "P") {
                ob_start();
                ?>
                <button class="btn btn-success" type="button" onclick="enviarCallback(<?php echo $pagamentoMonitorado->id ?>);">
                    Enviar Callback
                </button>
                <?php
                $callback = ob_get_contents();
                ob_end_clean();
            }
            
            $json["pagamento"] = Array(
                "origem" => $pagamentoMonitorado->enderecoOrigem,
                "destino" => $pagamentoMonitorado->enderecoDestino,
                "volume" => number_format($pagamentoMonitorado->volume, 8, ".", ""),
                "cadastro" => $pagamentoMonitorado->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                "dataPagamento" => ($pagamentoMonitorado->dataPagamento != null ? $pagamentoMonitorado->dataPagamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : ""),
                "pago" => number_format($pagamentoMonitorado->volumePago, 8, ".", ""),
                "hash" => $pagamentoMonitorado->hash,
                "cliente" => $cliente->nome,
                "status" => $status,
                "class" => $class,
                "callback" => $callback
            );
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function callback($params) {
        try {
            $pagamentoMonitorado = new \Models\Modules\Cadastro\PagamentoMonitorado();
            $pagamentoMonitorado->id = \Utils\Post::get($params, "codigo", 0);
            
            try {
                $pagamentoMonitoradoRn = new \Models\Modules\Cadastro\PagamentoMonitoradoRn();
                $pagamentoMonitoradoRn->conexao->carregar($pagamentoMonitorado);
                
                
            } catch (\Exception $ex) {
                throw new \Exception("Registro inválido ou não encontrado");
            }
            $url = "";
            if (!empty($pagamentoMonitorado->callback)) {
                $callback = $pagamentoMonitorado->callback;
                $curl = curl_init();

                if (substr($callback, strlen($callback) - 1, 1) == "/") {
                    $callback = substr($callback, 0, strlen($callback) - 1);
                }

                $url = "{$callback}?codigo={$pagamentoMonitorado->id}";
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

            } else {
                throw new \Exception("O Registro não possui uma URL de callback cadastrada");
            }
            
            
            $json["url"] = $url;
            $json["resultado"] = $result;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
}