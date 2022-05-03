<?php

namespace Modules\contas\Controllers;

class LogReais {
    
    public function __construct($params) {
        if (!(\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR)) {
            $get = $params["_parameters"];
            if (isset($get[0])) {
                $id = \Utils\Get::get($params, 0, 0);

                if (\Utils\Geral::getCliente()->id !== $id) {
                    \Utils\Geral::redirect(URLBASE_CLIENT. \Utils\Rotas::R_CONTACORRENTEREAIS ."/".\Utils\Geral::getCliente()->id);
                }
            }
        }
    }
    
    public function index($params) {
        $adm = (\Utils\Geral::isUsuario() && \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR);
        if (!$adm) {
            $cliente = \Utils\Geral::getCliente();
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_CONTACORRENTEREAIS . "/{$cliente->id}");
        } else {
            
            try {
                $cliente = new \Models\Modules\Cadastro\Cliente();
                $cliente->id = \Utils\Get::get($params, 0, 0);

                try {
                    $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                    $clienteRn->conexao->carregar($cliente);
                } catch (\Exception $ex) {
                    throw new \Exception("Cliente não encontrado no sistema.");
                }

                $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
                $usuarios = $usuarioRn->conexao->listar("ativo > 0", "nome");

                $params["cliente"] = $cliente;
                $params["usuarios"] = $usuarios;

                $params["sucesso"] = true;
            } catch (\Exception $ex) {
                $params["sucesso"] = false;
                $params["mensagem"] = \Utils\Excecao::mensagem($ex);
            }
            \Utils\Layout::view("log_reais", $params);
        }
    }
    
    
    public function filtrar($params) {
        try {
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $filtro = \Utils\Post::get($params, "filtro", NULL);
            $idUsuario = \Utils\Post::get($params, "idUsuario", 0);
            $idCliente = \Utils\Post::get($params, "idCliente", 0);
            
            $logContaCorrenteReaisRn = new \Models\Modules\Cadastro\LogContaCorrenteReaisRn();
            $lista = $logContaCorrenteReaisRn->filtrar($idCliente, $dataInicial, $dataFinal, $filtro, $idUsuario);
            
            ob_start();
            ?>
            <li class="list-group-item" style="font-size: 10px; font-weight: bold;">
                <div class="row">
                    <div class="col col-lg-2 text-center">
                        <strong>Data</strong>
                    </div>
                    <div class="col col-lg-6">
                        <strong>Descrição</strong>
                    </div>
                    <div class="col col-lg-3">
                        <strong>Usuário</strong>
                    </div>
                    <div class="col-lg-1 text-center">
                        <strong>Controle</strong>
                    </div>
                </div>
            </li>
            <?php
            if (sizeof($lista) > 0) {
                
                foreach ($lista as $logContaCorrente) {
                    //$logContaCorrente = new \Models\Modules\Cadastro\LogContaCorrenteReais();
                    
                    ?>
                    <li class="list-group-item" style="font-size: 10px; font-weight: bold;">
                        <div class="row">
                            <div class="col col-lg-2 text-center">
                                <?php echo $logContaCorrente->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?>
                            </div>
                            <div class="col col-lg-6">
                                <?php echo $logContaCorrente->descricao ?>
                            </div>
                            <div class="col col-lg-3">
                                <?php echo $logContaCorrente->getNome(); ?>
                            </div>
                            <div class="col-lg-1 text-center">
                                <?php echo $logContaCorrente->idContaCorrenteReais ?>
                            </div>
                        </div>
                    </li>
                    <?php
                    
                }
                
            } else {
                ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col col-lg-12 text-center">
                            Nenhum log encontrado para os filtros informados
                        </div>
                    </div>
                </li>
                <?php
            }
            
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function imprimir($params) {
        try {
            
            $d = \Utils\Get::get($params, 0, 0);
            $a = explode("|", \Utils\SQLInjection::clean(base64_decode($d)));
            
            $dataInicial = (isset($a[0]) && strlen(trim($a[0])) == 10) ?
                    new \Utils\Data(trim($a[0]) . " 00:00:00") : null;
            $dataFinal = (isset($a[1]) && strlen(trim($a[1])) == 10) ?
                    new \Utils\Data(trim($a[1]) . " 23:59:59") : null;
            $idCliente = isset($a[4]) ? $a[4] : 0;
            $idUsuario = isset($a[2]) ? $a[2] : "T";
            $filtro = isset($a[3]) ? $a[3] : "T";
            
            $logContaCorrenteReaisRn = new \Models\Modules\Cadastro\LogContaCorrenteReaisRn();
            $lista = $logContaCorrenteReaisRn->filtrar($idCliente, $dataInicial, $dataFinal, $filtro, $idUsuario);
            
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $idCliente));
            try {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
            } catch (\Exception $ex) {
                throw new \Exception("Cliente não localizado no sistema.");
            }
            
            $params["cliente"] = $cliente;
            $params["logs"] = $lista;
            
            $pdf = new \Utils\PDF();
            ob_start();
            \Utils\Layout::view("impressos/extrato_log_reais", $params);
            $html = ob_get_contents();
            ob_end_clean();
            $pdf->conteudo($html);
            
            $pdf->gerar("extrato_logs_conta_corrente_rs.pdf", "D", false, false, false);
        } catch (\Exception $ex) {
            ?>
            <html>
                <head>
                    <title>Erro</title>
                </head>
                <body>
                    <h1>Ops...</h1>
                    <h3><?php echo \Utils\Excecao::mensagem($ex) ?></h3>
                </body>
            </html>
            <?php
        }
    }
    
}