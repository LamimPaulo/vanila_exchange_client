<?php

namespace Modules\cadastros\Controllers;

class Atar {

    private $codigoModulo = "deposito";
    private $idioma = null;

    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        $cliente = \Utils\Geral::getCliente();
        
        if (\Utils\Geral::isCliente() && $cliente->utilizaSaqueDepositoBrl < 1) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        
        $this->idioma = new \Utils\PropertiesUtils("saque", IDIOMA);
    }
    
    public function listar($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $data = \Utils\Post::get($params, "data", "semana");
            $nresultado = \Utils\Post::get($params, "filtro", null);            
            
            switch ($data) {
                case "dia":                        
                    $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
                    $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59"));
                    break;
                case "semana":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 0, 6);
                    break;
                case "mes":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 1);
                    break;
                case "todos":
                    $dataInicial =  new \Utils\Data(date("01/07/2019") . " 00:00:00");//Data que iniciou o sistema
                    $dataFinal = new \Utils\Data(date("d/m/Y") . " 23:59:59");
                    break;
            }
            
            $atarRn = new \Models\Modules\Cadastro\AtarContasRn();
            $lista = $atarRn->filtrar($cliente->id, $dataInicial, $dataFinal, \Utils\Constantes::ENTRADA, true, $nresultado);

            $dados = $this->htmlLista($lista);

            $json["html"] = $dados;

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

    public function htmlLista($lista) {
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $atar) {
                $this->itemHtmlLista($atar);
            }
        } else {
            ?>
            <tr> 
                <td colspan='6' class='text-center'>
                   <?php echo "Nenhum depósito disponível." ?>
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public function itemHtmlLista(\Models\Modules\Cadastro\AtarContas $atar) {
        
        ?>
        <tr style='text-align: center; background-color: #fff'>
            <td><?php echo $atar->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP) ?></td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($atar->valor, 2, ",", ".") ?></td>
            <td><?php echo number_format($atar->taxaPorcentagem, 2, ",", ".") ?>%</td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($atar->tarifa, 2, ",", ".") ?></td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($atar->valorCreditado, 2, ",", ".") ?></td>
            <td><?php echo $atar->confirmado == 1 ? "Confirmado" : "-" ?></td>
        </tr>

        <?php
    }
    
    
    public function token($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            
            \Utils\ValidarSeguranca::validar($cliente);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            if($cliente->documentoVerificado != 1){
                throw new \Exception($this->idioma->getText("verifiqueSuaConta"));
            }
            
            $json["idAtar"] = $cliente->documento;            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            //$atarId = \Utils\Post::get($params, "idAtar", 0);
            //$documento = \Utils\Post::get($params, "document", null);            
            $pin = \Utils\Post::get($params, "pin", null);
            
            $atarClienteRn = new \Models\Modules\Cadastro\AtarClientesRn();
            $atarCliente = new \Models\Modules\Cadastro\AtarClientes();
            
            $clienteRn->conexao->carregar($cliente);
            
            if (empty($pin)) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }

            if ($cliente->pin != $pin) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }
            
            if($cliente->documentoVerificado != 1 ){
                throw new \Exception($this->idioma->getText("verifiqueSuaConta"));
            }
            
            $atarCliente->ativo = 1;
            $atarCliente->idCliente = $cliente->id;
            $atarCliente->descricao = "Cadastro Atar";
            $atarCliente->idAtar = $cliente->documento;
            $atarCliente->dataCadastro = new \Utils\Data(date('Y-m-d H:i:s'));
            
            $atarClienteRn->salvar($atarCliente);        

            $json["mensagem"] = "Cadastro de conta Atar realizado com sucesso.";
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

}
