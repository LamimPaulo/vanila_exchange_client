<?php

namespace Modules\contas\Controllers;

class Atar {

    private $codigoModulo = "saques";
    private $idioma = null;

    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        $cliente = \Utils\Geral::getCliente();
        
        if (\Utils\Geral::isCliente() && $cliente->utilizaSaqueDepositoBrl < 1) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        
        $this->idioma = new \Utils\PropertiesUtils("saque", IDIOMA);
    }
    
    public function token($params) {
        try {
            $atarApi = new \Atar\AtarApi();
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $cliente = \Utils\Geral::getCliente();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $valor = \Utils\Post::getNumeric($params, "valorReais", 0);
            $atarContaRn = new \Models\Modules\Cadastro\AtarContasRn();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $clienteRn->conexao->carregar($cliente);
            
            if($cliente->documentoVerificado != 1 ){
                throw new \Exception($this->idioma->getText("verifiqueSuaConta"));
            }
            
            \Utils\ValidarSeguranca::validar($cliente);
            
            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($cliente);
            
            $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 1));
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->conexao->carregar($moeda);
            
            \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::SAQUE, $valor, true);  
            
            $atarCliente = null;
            $atarClientesRn = new \Models\Modules\Cadastro\AtarClientesRn();
            $result = $atarClientesRn->conexao->listar(" id_cliente = {$cliente->id} ");
            
            if (sizeof($result) > 0) {
                foreach ($result as $atar) {
                    $atarCliente = $atar;
                }
                
                if($atarCliente->ativo == 0){
                    throw new \Exception("Saque não disponível no momento. COD - 3");
                }
            } else {
                throw new \Exception("Por favor, faça o cadastro de sua conta ATAR no menu Depósito.");
            }
            
            if($cliente->analiseCliente == 1 || $cliente->status != 1 ){
                throw new \Exception("Por favor, contate o suporte para verificar sua conta.");
            }

            if($configuracao->atarAtivo == 0){
                throw new \Exception("Saque não disponível no momento. COD - 0");
            }
            
            if($atarContaRn->saldoAtarDisponivel($configuracao) < $valor){
                throw new \Exception("Saque não disponível no momento. COD - 1");
            }
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
               
            
            if ($saldo < $valor) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($cliente);
            
            $object = $atarApi->consultarSaldo();
            
            if(!empty($object) && ($object->amount / 100) < $valor){
                throw new \Exception("Saque não disponível no momento. COD - 2");
            }
            
            /*$documento = \Utils\Validacao::limparString($documento);

            if(\Utils\Validacao::cpf($documento)){
                $atarContas->documentAtar = $documento;
            } else if (\Utils\Validacao::cnpj($documento)) {
                $atarContas->documentAtar = $documento;
            } else {
                throw new \Exception("Documento informado não aceito.");
            }*/
            
            $valorSaque = ($valor - ($valor * ($configuracao->atarTaxaSaque / 100))) - $configuracao->atarTarifaSaque;

            $auth = new \Models\Modules\Cadastro\Auth();
            $auth->idCliente = $cliente->id;
            $authRn->salvar($auth);


            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL) {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoEmail1") . " " . $cliente->email . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_SMS) {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoSMS1") . " " . $cliente->celular . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                $json["mensagem"] = $this->idioma->getText("useGoogle1");
            }
            
            $json["documentoCPF"] = $cliente->documento;
            $json["valorSolicitado"] = number_format($valor, 2, ",", ".");
            $json["valorSaque"] = number_format($valorSaque, 2, ",", ".");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $atarContas = new \Models\Modules\Cadastro\AtarContas();
            $atarContasRn = new \Models\Modules\Cadastro\AtarContasRn();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
             
            $cliente = \Utils\Geral::getCliente();
            $valor = \Utils\Post::getNumeric($params, "valorReais", 0);
            $token = \Utils\Post::get($params, "token", null);
            $pin = \Utils\Post::get($params, "pin", null);

            if (empty($token)) {
                throw new \Exception($this->idioma->getText("tokenInvalido"));
            }

            if (empty($pin)) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }

            $clienteRn->conexao->carregar($cliente);

            if ($cliente->pin != $pin) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }
            
            if($cliente->documentoVerificado != 1 ){
                throw new \Exception($this->idioma->getText("verifiqueSuaConta"));
            }

            $authRn->validar($token, $cliente);
            
            \Utils\ValidarSeguranca::validar($cliente);
            
            \Models\Modules\Cadastro\ClienteHasCreditoRn::validar($cliente);
            
            $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 1));
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->conexao->carregar($moeda);
            
            \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::SAQUE, $valor, true);  

            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente);
     
            if ($saldo < $valor) {
                throw new \Exception($this->idioma->getText("saldoInsuficiente"));
            }
            
            $valorCreditado = $valor - ($valor * ($configuracao->atarTaxaSaque / 100)) - $configuracao->atarTarifaSaque;
            
            $atarContas->idCliente = $cliente->id;
            $atarContas->dataCadastro = new \Utils\Data(date('Y-m-d H:i:s'));
            $atarContas->documentAtar = $cliente->documento;
            $atarContas->tipo = \Utils\Constantes::SAIDA;
            $atarContas->valor = $valor;
            $atarContas->valorCreditado = number_format($valorCreditado, 2, ".", "");
            $atarContas->taxa = $valor * ($configuracao->atarTaxaSaque / 100);
            $atarContas->taxaPorcentagem = $configuracao->atarTaxaSaque;
            $atarContas->tarifa = $configuracao->atarTarifaSaque;
            
            $atarContasRn->salvarTransacao($atarContas);            

            $json["mensagem"] = $this->idioma->getText("saqueSucesso");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listar($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $data = \Utils\Post::get($params, "data", "semana");
            $nresultado = \Utils\Post::get($params, "filtro", "T");            
            
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
            $lista = $atarRn->filtrar($cliente->id, $dataInicial, $dataFinal, \Utils\Constantes::SAIDA, true, $nresultado);

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
                <td colspan='7' class='text-center'>
                   <?php echo $this->idioma->getText("nenhumSaqueC") ?>
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
            <td><?php echo $atar->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($atar->valor, 2, ",", ".") ?></td>
            <td><?php echo number_format($atar->taxaPorcentagem , 2, ",", ".") ?>%</td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($atar->tarifa, 2, ",", ".") ?></td>
            <td><?php echo $this->idioma->getText("rS") ?> <?php echo number_format($atar->valorCreditado, 2, ",", ".") ?></td>
            <td><?php echo $atar->documentAtar ?></td>
            <td><?php echo $atar->confirmado == 1 ? "Confirmado" : "-" ?></td>
        </tr>

        <?php
    }
    
    
    public function atarSaldo() {
        try {     
            
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $cliente = \Utils\Geral::getCliente();
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            
            if($configuracao->atarSaldo > $saldo){
                $saldoAtar = number_format($saldo, 2, ",", ".");
            } else {
                $saldoAtar = number_format($configuracao->atarSaldo, 2, ",", ".");
            }
            
            $json["atarSaldo"] = "R$ " . $saldoAtar;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }

}
