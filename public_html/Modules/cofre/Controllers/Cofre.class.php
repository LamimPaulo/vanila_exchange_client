<?php

namespace Modules\cofre\Controllers;

class Cofre {
    
    private $codigoModulo = "cofre";
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("cofre", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);
        
    }
    
    
    public function index($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $investimentoAtivo = "teste";
            $qtdInvestimento = false;
            
            $contratoRn = new \Models\Modules\Cadastro\InvestimentoContratosRn();
            $contratos = $contratoRn->conexao->listar("ativo = 1", "tempo_meses ASC");
            
            $moedasCofre = Array();
            
            $bitcoin = new \Models\Modules\Cadastro\Moeda(Array("id" => 2));
            $moedaRn->carregar($bitcoin);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();

            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $saldoBitcoin = $cofreRn->calcularSaldo($cliente, $bitcoin);

            $resultQtdInvestimento = $cofreRn->conexao->listar(" id_cliente = {$cliente->id} ");
            $resultInvestimentoAtivo = $cofreRn->conexao->listar(" id_cliente = {$cliente->id} AND sacado = 0 ");
            
            if(sizeof($resultQtdInvestimento) > 0){
                $qtdInvestimento = true;
            } else {
                $qtdInvestimento = false;
            }
            
            if(sizeof($resultInvestimentoAtivo) > 0){
                $investimentoAtivo = true;
            } else {
                $investimentoAtivo = false;
            }
            
            $saldoContaBtc = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $bitcoin->id, false);
                
                $moedasCofre[] = Array(
                    "moeda" => $bitcoin,
                    "saldo" => $saldoContaBtc,
                    "rendimento" => $saldoBitcoin["rendimentos"],
                    "depositado" => $saldoBitcoin["depositados"],
                    "recebido" => $saldoBitcoin["recebido"]
                );
                
            $params["qtdInvestimento"] = $qtdInvestimento;     
            $params["investimentoAtivo"] = $investimentoAtivo;     
            $params["contratos"] = $contratos;       
            $params["2fa"] = $cliente->tipoAutenticacao;    
            $params["moedas"] = $moedasCofre;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("investimento", $params);
    }
    
    public function dadosGrafico($params){
        try{
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $cliente = \Utils\Geral::getCliente();
            $dadosEmpresa = Array();
            $dadosEmpresaRendimento = Array();
            $dadosCliente = Array();
            $dadosClienteRendimento = Array();
            $i = 0;
            $a = 0;
            $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataInicial->subtrair(0, 12);
            $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $resultCliente = $cofreRn->volumeRendimentoDatas($cliente->id, null, null);

            foreach ($resultCliente as $dados){                
                $dadosCliente[] = [$i, $dados["volume"] * 5];
                $dadosClienteRendimento[] = [$i, ($dados["volume"] * 10) + ($dados["rendimento"] * 10)];
                $i++;
            }
            
            $resultEmpresa = $cofreRn->volumeRendimentoDatas(null, null, null);

            foreach ($resultEmpresa as $dados){                
                $dadosEmpresa[] = [$a, $dados["volume"] * 5];
                $dadosEmpresaRendimento[] = [$a, ($dados["volume"] * 10) + ($dados["rendimento"] * 10)];
                $a++;
            }

            $json["dadosCliente"] = $dadosCliente;
            $json["dadosClienteRend"] = $dadosClienteRendimento;
            $json["dadosEmpresa"] = $dadosEmpresa;
            $json["dadosEmpresaRend"] = $dadosEmpresaRendimento;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);        
    }
    
    public function dadosEmpresa($params){
        try{
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $totalQtdInvestimentos = 0;
            $totalVolumeInvestimentos = 0;

            $result = $cofreRn->qtdInvestidoEmpresa();
            
            $totalRendimentos = $cofreRn->qtdRendimentosEmpresa();

            foreach ($result as $dados){
                $totalQtdInvestimentos = $dados["total"];
                $totalVolumeInvestimentos = $dados["volumeDepositado"];
            } 
            
            ob_start(); 
            
            ?>
            
            <tr>
                <td>
                    <button type="button" class="btn btn-primary m-r-sm w-150"><?php echo number_format($totalVolumeInvestimentos, 4, ".", "") ?> BTC</button>
                    <?php echo $this->idioma->getText("invest40") ?>
                </td>                                                        
            </tr>
            <tr>
                <td>
                    <button type="button" class="btn m-r-sm w-150" style="color: #FFF; background-color: #127e68;"><?php echo number_format($totalRendimentos, 4, ".", "") ?> BTC</button>
                    <?php echo $this->idioma->getText("invest41") ?>
                </td>                                                        
            </tr>
            <tr>
                <td>
                    <button type="button" class="btn btn-success m-r-sm w-150"><?php echo $totalQtdInvestimentos ?></button>
                    <?php echo $this->idioma->getText("invest42") ?>
                </td>                                                        
            </tr>
            
            <?php
            $htmlEmpresa = ob_get_contents();
            ob_end_clean();
            
            $json["htmlEmpresa"] = $htmlEmpresa;
            $json["totalEmpresa"] = number_format($totalVolumeInvestimentos + $totalRendimentos, 4, ".", "");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);        
    }
    
    
    public function getSaldo($params) {
        
        try {
            $cliente = \Utils\Geral::getCliente();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            
            
            $moeda->id = 2; // Bitcoin
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->carregar($moeda);
            
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            $saldo = $cofreRn->calcularSaldo($cliente, $moeda);            
            
            //exit(print_r($saldo));
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $saldoCC = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false);
            
            $resultInvestimentoAtivo = $cofreRn->conexao->listar(" id_cliente = {$cliente->id} AND sacado = 0 ");
            
            if($saldoCC >= 0){
               $saldoCC = number_format($saldoCC, $moeda->casasDecimais, ".", "");
            } else {
               $saldoCC = 0.00000000; 
            }
            ob_start();
            ?>
            
            <tr>
                <td>
                    <button type="button" class="btn btn-primary m-r-sm w-150"><?php echo number_format($saldo["depositados"], 4, ".", ""); ?> BTC</button>
                    <?php echo $this->idioma->getText("invest43") ?>
                </td>                                                        
            </tr>
            <tr>
                <td>
                    <button type="button" class="btn m-r-sm w-150" style="color: #FFF; background-color: #127e68;"><?php echo number_format($saldo["recebido"], 4, ".", ""); ?> BTC</button>
                    <?php echo $this->idioma->getText("invest44") ?>
                </td>                                                        
            </tr>
            <tr>
                <td>
                    <button type="button" class="btn btn-success m-r-sm w-150"><?php echo sizeof($resultInvestimentoAtivo) ?></button>
                    <?php echo $this->idioma->getText("invest45") ?>
                </td>                                                        
            </tr>
            
            <?php
            $htmlCliente = ob_get_contents();
            ob_end_clean();
            
            $json["codigo"] = $moeda->id;
            $json["saldo"] = $saldoCC;
            $json["depositado"] = number_format($saldo["depositados"], $moeda->casasDecimais, ".", "");
            $json["rendimento"] = number_format($saldo["rendimentos"], $moeda->casasDecimais, ".", "");
            $json["recebido"] = number_format($saldo["recebido"], $moeda->casasDecimais, ".", "");
            $json["investidoRendimento"] = number_format($saldo["recebido"] + $saldo["depositados"], $moeda->casasDecimais, ".", "");
            $json["htmlCliente"] = $htmlCliente;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getInvestimentosCofre($params) {
        
        try {            
            $investimentosBitcoin = $cofreRn->conexao->listar("id_moeda = 2 {$sacadoSql}", "data_entrada");

            
            $statusCoreRn = new \Models\Modules\Cadastro\StatusCoreRn($cofreRn->conexao->adapter);
            $coreBitcoin = $statusCoreRn->getByIdMoeda(new \Models\Modules\Cadastro\Moeda(Array("id" => 2)));

            $dadosBitcoin = $this->htmlInvestimento($investimentosBitcoin, $coreBitcoin);

            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn($cofreRn->conexao->adapter);
            
            $qtdClientesBtc = $contaCorrenteBtcRn->getQuantidadeClientesComSaldo(2);

            
            
            $json["bitcoin"] = $dadosBitcoin;

            $json["clientesBitcoin"] = $qtdClientesBtc - $dadosBitcoin["clientes"];

            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlInvestimento($investimentos, \Models\Modules\Cadastro\StatusCore $statusCore = null) {
        $valorInvestido = 0;
        $valorRendimento = 0;
        $valorTotal = 0;
        $clientes = Array();
        $pagamentomanha = 0;
        $pagamentoSeteDias = 0;
        
        $data = new \Utils\Data(date("d/m/Y") . " 23:59:59");
        
        
        $dias = Array();
        
        $i = 0;
        while($i < 7) {
            $data->somar(0, 0, 1);
            $dataProvisao = new \Utils\Data($data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO));
            
            $dias[] = Array(
                "data" => $dataProvisao,
                "saldo" => 0,
                "acumulado" => 0
            );
            
            $i++;
        }
        
        ob_start();
        $cofreRn = new \Models\Modules\Cadastro\CofreRn();
        if (sizeof($investimentos) > 0) {
            foreach ($investimentos as $cofre) {
                //$cofre = new \Models\Modules\Cadastro\Cofre();
                $cofreRn->carregar($cofre, false, true);
                $clientes[$cofre->idCliente] = $cofre->cliente;
                $valorInvestido += number_format($cofre->volumeDepositado, 8, ".", "");
                $valorRendimento += number_format($cofre->volumeCobradoTaxa, 8, ".", "");
                $valorTotal += number_format(($cofre->volumeDepositado + $cofre->volumeCobradoTaxa), 8, ".", "");
                
                foreach($dias as $key=>$d) {
                    $data = $d["data"];
                    if (($cofre->dataProvisaoSaque != null && $cofre->dataProvisaoSaque->formatar(\Utils\Data::FORMATO_PT_BR) == $data->formatar(\Utils\Data::FORMATO_PT_BR)) || $cofre->dataExpiracaoContrato->igual($data)) {
                        
                        $dias[$key]["saldo"] += number_format(($cofre->volumeDepositado + $cofre->volumeCobradoTaxa), 8, ".", "");
                    } 
                    
                    if (($cofre->dataProvisaoSaque != null && $cofre->dataProvisaoSaque->menorIgual($data)) || $cofre->dataExpiracaoContrato->menorIgual($data)) {
                        $dias[$key]["acumulado"] += number_format(($cofre->volumeDepositado + $cofre->volumeCobradoTaxa), 8, ".", "");
                    }
                }
                
                
                ?>
                <tr>
                    <td><?php echo $cofre->cliente->nome ?></td>
                    <td class="text-center"><?php echo $cofre->dataEntrada->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
                    <td class="text-center"><?php echo number_format($cofre->taxa, 2, ",", ".") ?></td>
                    <td class="text-center"><?php echo number_format($cofre->volumeDepositado, 8, ",", ".") ?></td>
                    <td class="text-center"><?php echo number_format($cofre->volumeCobradoTaxa, 8, ",", ".") ?></td>
                    <td class="text-center">
                        <?php
                        if ($cofre->dataProvisaoSaque == null) {
                            echo $cofre->dataExpiracaoContrato->formatar(\Utils\Data::FORMATO_PT_BR);
                        } else {
                            if ($cofre->dataProvisaoSaque->menor($cofre->dataExpiracaoContrato)) {
                                echo $cofre->dataProvisaoSaque->formatar(\Utils\Data::FORMATO_PT_BR);
                            } else {
                                echo $cofre->dataExpiracaoContrato->formatar(\Utils\Data::FORMATO_PT_BR);
                            }
                        }
                        ?>
                    </td>
                    
                    <td class="text-center"><?php echo number_format(($cofre->volumeDepositado + $cofre->volumeCobradoTaxa), 8, ",", ".") ?></td>
                    <td class="text-center"><?php echo $cofre->getStatus() ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
                <tr>
                    <td colspan="8" class="text-center">Nenhum dado</td>
                </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        
        ob_start();
        ?>
        <tr>
        <?php
        foreach ($dias as $d) {
            $data = $d["data"];
        ?>
            <td>
                <div class="alert alert-<?php echo ($d["saldo"] > 0 ? "danger" : "info")?> text-center">
                    <?php echo $data->getDiaSemana() ?> - <?php echo $data->formatar(\Utils\Data::FORMATO_PT_BR) ?> <br>
                    <h4 class="">
                        <strong >
                            Saldo do dia: <?php echo number_format($d["saldo"], 8, ".", ""); ?>
                        </strong>
                        <br>
                        <strong >
                            Saldo acumulado: <?php echo number_format($d["acumulado"], 8, ".", ""); ?>
                        </strong>
                    </h4>
                </div>
            </td>
        <?php
        }
        ?>
        </tr>
        <?php
        $htmlDias = ob_get_contents();
        ob_end_clean();
        
        return Array(
            "investido" => $valorInvestido, 
            'rendimento' => $valorRendimento, 
            'html' => $html, 
            "valorTotal" => $valorTotal, 
            "pagamentoAmanha" => $amanha, 
            "pagamentoSemana" => $pagamentoSeteDias,
            "dias" => $htmlDias,
            "clientes" => sizeof($clientes),
            "core" => (double) number_format(($statusCore != null ? $statusCore->balance : 0), 8, ".", ""),
            "diferenca" => (double) number_format(($statusCore != null ? ($statusCore->balance - $valorTotal) : $valorTotal), 8, ".", "")
        );
    }
    
    public function token($params) {
        try {
            
            
            
            $cliente = \Utils\Geral::getLogado();
            
            $auth = new \Models\Modules\Cadastro\Auth();
            $saldo = 0;
            $tipo = "";
            $email = "";
            $telefone = "";
            if (\Utils\Geral::isUsuario()) {
                $usuario = \Utils\Geral::getLogado();
                $email = $usuario->email;
                $telefone = $usuario->celular;
                $auth->idUsuario = $usuario->id;
                $tipo = $usuario->tipoAutenticacao;
                
            } else {
                $cliente = \Utils\Geral::getCliente();
                
                if (empty($cliente->pin)) {
                    throw new \Exception($this->idioma->getText("precisaCadastrarPin"));
                }
                
                $email = $cliente->email;
                $telefone = $cliente->celular;
                $auth->idCliente = $cliente->id;
                $tipo = $cliente->tipoAutenticacao;
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                if ($moeda->id > 0) {
                    $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false);
                }
            }
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->salvar($auth);
            
            $json["google"] = false;
            if ($tipo == \Utils\Constantes::TIPO_AUTH_EMAIL)  {
                $json["meio"] = $this->idioma->getText("foiEnviadoEmail") . " {$email} " .  $this->idioma->getText("porFavorInsiraTokenEmail");
                $json["google"] = true;
            } 
            
            /*if ($tipo == \Utils\Constantes::TIPO_AUTH_SMS){
                $json["meio"] = $this->idioma->getText("foiEnviadoSms") . " {$telefone} " . $this->idioma->getText("porFavorInsiraTokenSms");
            }*/
            
            /*if ($tipo == \Utils\Constantes::TIPO_AUTH_GOOGLE){
                $json["meio"] = $this->idioma->getText("useGoogle");
            }*/

            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function depositar($params) {
        try {
            
            $volume = \Utils\Post::getNumeric($params, "volume", 0);
            $descricao = \Utils\Post::get($params, "descricao", null);
            $token = \Utils\Post::get($params, "token", NULL);
            $pin = \Utils\Post::get($params, "pin", NULL);
            $contrato = \Utils\Post::get($params, "contrato", NULL);
            $cliente = \Utils\Geral::getCliente();
            
            $cofre = new \Models\Modules\Cadastro\Cofre();
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            
            $cofre->id = 0;
            $cofre->idMoeda = 2;
            $cofre->idCliente = $cliente->id;
            $cofre->volumeDepositado = $volume;
            $cofre->descricao = $descricao;
            $cofre->contrato = $contrato;
            
            if (empty($pin)) {
                throw new \Exception($this->idioma->getText("pinDeveSerInformado"));
            }
            
            if (empty($token)) {
                throw new \Exception($this->idioma->getText("tokenDeveSerInformado"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
           
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token);
            
            if (empty($cliente->pin)) {
                throw new \Exception($this->idioma->getText("precisaCadastrarPin"));
            }
            
            if ($pin != $cliente->pin) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }
             
            $cofreRn->salvar($cofre);
            
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("depositoSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function sacar($params) {
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $pin = \Utils\Post::get($params, "pin", NULL);
            $cliente = \Utils\Geral::getCliente();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params, "moeda", 0);
            
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            
            if (empty($pin)) {
                throw new \Exception($this->idioma->getText("pinDeveSerInformado"));
            }
            
            if (empty($token)) {
                throw new \Exception($this->idioma->getText("tokenDeveSerInformado"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token);
            
            if (empty($cliente->pin)) {
                throw new \Exception($this->idioma->getText("precisaCadastrarPin"));
            }
            
            if ($pin != $cliente->pin) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }
            
            $cofreRn->solicitarRetirada($cliente, $moeda);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("retiradaSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function retirar($params) {
        
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $pin = \Utils\Post::get($params, "pin", NULL);
            $cliente = \Utils\Geral::getCliente();
            $cofre = new \Models\Modules\Cadastro\Cofre();
            $cofre->id = \Utils\Post::getEncrypted($params, "investimento", 0);
            
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            
            if (empty($pin)) {
                throw new \Exception($this->idioma->getText("pinDeveSerInformado"));
            }
            
            if (empty($token)) {
                throw new \Exception($this->idioma->getText("tokenDeveSerInformado"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $authRn->validar($token);
            
            if (empty($cliente->pin)) {
                throw new \Exception($this->idioma->getText("precisaCadastrarPin"));
            }
            
            if ($pin != $cliente->pin) {
                throw new \Exception($this->idioma->getText("pinInvalido"));
            }
            
            $cofreRn->solicitarSaqueInvestimento($cofre);
            
            $json["moeda"] = \Utils\Criptografia::encriptyPostId($cofre->idMoeda);
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("retiradaSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
    
    public function filtrarSolicitacoesSaque($params) {
        try {
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            
            $cliente = \Utils\Geral::getCliente();
            $moedaAtual = \Modules\principal\Controllers\Principal::getCurrency();
            
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            //$result = $cofreRn->filtrarSolicitacoesSaque($cliente, $moeda);
            $result = $cofreRn->filtrarSolicitacoesSaque($cliente, $moedaAtual);
            
            ob_start();
            if (sizeof($result) > 0) {
           
                foreach ($result as $value) {
                    $moeda = new \Models\Modules\Cadastro\Moeda();
                    $moeda->id = $value["id_moeda"];
                    $moedaRn->carregar($moeda);
                    
                    $dataSolicitacao = new \Utils\Data(substr($value["data_solicitacao_saque"], 0, 19));
                    $dataProvisionamento = new \Utils\Data(substr($value["data_provisao_saque"], 0, 19));
                ?>
                <tr>
                    <td style="vertical-align: middle;" class="text-left">
                        <a class="count-info" href="#" class="pull-left">
                            <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="max-width: 20px; max-height: 20px;" />
                        </a>
                        &nbsp;
                        <?php echo $moeda->simbolo ?> - 
                        <?php echo $moeda->nome ?>
                    </td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo $dataSolicitacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                    
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format($value["saldo"], $moeda->casasDecimais, ".", "") ?></td>
                    
                    <td style="vertical-align: middle;" class="text-center"><?php echo $dataProvisionamento->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                </tr>
                <?php
                }
            
            } else {
                ?>
                <tr>
                    <td style="vertical-align: middle;" class="text-center" colspan="8"><?php echo $this->idioma->getText("nenhumaSolicitacao") ?></td>
                </tr>
                
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
    
    public function filtrarRendimentos($params) {
        try {
            $dias = \Utils\Post::get($params, "dias", 7);
            $moeda = 2; // Moeda Bitcoin;
            $consolidado = \Utils\Post::getBoolean($params, "consolidado", false);
            $moedaSql = "";
            $dataSql = null;
            $cliente = \Utils\Geral::getCliente();
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaDados = new \Models\Modules\Cadastro\Moeda();
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            
            if($moeda != "T"){
                $moedaSql = " id_moeda = {$moeda} AND ";    
            } else {
                $moedaSql = "";
            }
            
            if($dias != "todos"){
                $dataInicial = new \Utils\Data(date("d/m/Y") . " 00:00:00");
                $dataInicial->subtrair(0, 0, $dias);
                $dataFinal = new \Utils\Data(date("d/m/Y") . " 23:59:59");
                $dataSql = "AND data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}'";
            } else {
                $dataSql = null;
            }
            
            if(!$consolidado){                
                $result = $contaCorrenteBtcRn->lista(
                    "id_cliente = {$cliente->id} AND {$moedaSql} origem = 3 AND tipo = 'E' {$dataSql}", 
                    "data_cadastro DESC", null, null, false, true);     
                    
            } else {
                $resultBtc = $contaCorrenteBtcRn->saldoConsolidadoInvestimento($cliente->id, $moeda, $dataInicial, $dataFinal);                    
                    $result = Array();
                    foreach ($resultBtc as $dados){                        
                        $contaCorrente = new \Models\Modules\Cadastro\ContaCorrenteBtc();
                        //$contaCorrenteBtcRn->carregar($contaCorrente, false, false, true);
                        $moedaDados->id = $moeda;
                        $moedaRn->conexao->carregar($moedaDados);
                        $contaCorrente->totalDia = $dados["totalDia"];
                        $result[] = $contaCorrente;
                    }
            }

            ob_start();
            if (sizeof($result) > 0) {           
                foreach ($result as $contaCorrente) {
                ?>
                <tr>
                    <td style="vertical-align: middle;" class="text-center"><?php  if($consolidado && !empty($dataSql)) {
                        echo $dataInicial->formatar(\Utils\Data::FORMATO_PT_BR) . " - " . $dataFinal->formatar(\Utils\Data::FORMATO_PT_BR);                    
                    } else if($consolidado && empty($dataSql)) {
                        echo "-";
                    } else {
                        echo $contaCorrente->data->formatar(\Utils\Data::FORMATO_PT_BR);
                    }?></td>
                    <td style="vertical-align: middle;" class="text-center">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $consolidado ? $moedaDados->icone : $contaCorrente->moeda->icone ?>" style="max-width: 20px; max-height: 20px;" />
                        &nbsp;
                        <?php echo $consolidado ? $moedaDados->simbolo : $contaCorrente->moeda->simbolo ?> - 
                        <?php echo $consolidado ? $moedaDados->nome : $contaCorrente->moeda->nome ?>
                    </td>
                    <td style="vertical-align: middle;"><?php echo $consolidado ? "Investimento Bitcoin" : $contaCorrente->descricao ?></td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo $consolidado == true ? 
                            number_format($contaCorrente->totalDia, 8, ".", "") : number_format($contaCorrente->valor, $contaCorrente->moeda->casasDecimais, ".", "") ?></td>
                </tr>
                <?php
                }
            
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="4"><?php echo $this->idioma->getText("meuHistoricoNenhumRendimentos") ?></td>
                </tr>
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
    
    
    public function filtrarInvestimentos($params) {
        try {
            
            /*$sacado = \Utils\Post::getBoolean($params, "sacado", false);
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();*/
            
            
            
            
            
            $resgate = \Utils\Post::getBoolean($params, "resgate", false);
            $moedaId = 2; // Bitcoin
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = $moedaId;
            $moedaRn->carregar($moeda);
            
            if($resgate){
                $sacadoSql = " AND sacado = 0" ;
            } else {
                $sacadoSql = "" ;
            }
            
            $cliente = \Utils\Geral::getCliente();
            
            $cofreRn = new \Models\Modules\Cadastro\CofreRn();
            //$result = $cofreRn->filtrarSolicitacoesSaque($cliente, $moeda);
            $result = $cofreRn->conexao->listar("id_cliente = {$cliente->id} {$sacadoSql} AND id_moeda = {$moeda->id}", "sacado ASC, data_entrada DESC", null, null);
            
            ob_start();
            if (sizeof($result) > 0) {
           
                foreach ($result as $cofre) {
                    //$moeda = new \Models\Modules\Cadastro\Moeda();

                ?>
                <tr id="<?php echo $cofre->id ?>">
                    <td style="vertical-align: middle;" class="text-center">
                        <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="max-width: 20px; max-height: 20px;" />
                        &nbsp;
                        <?php echo $moeda->simbolo ?>
                       
                    </td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo $cofre->id ?></td>
                    <td style="vertical-align: middle;"><?php echo !empty($cofre->descricao) ? $cofre->descricao : "---" ?></td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo !$resgate ? $cofre->dataEntrada->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : $cofre->dataEntrada->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format($cofre->volumeDepositado, $moeda->casasDecimais, ".", "") ?></td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format($cofre->volumeCobradoTaxa, $moeda->casasDecimais, ".", "") ?></td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo number_format(($cofre->volumeDepositado + $cofre->volumeCobradoTaxa), $moeda->casasDecimais, ".", "") ?></td>
                    <td style="vertical-align: middle;" class="text-center"><?php echo $cofre->dataExpiracaoContrato->formatar(\Utils\Data::FORMATO_PT_BR)?></td>
                    
                    <td style="vertical-align: middle;" class="text-center"><?php echo $cofre->getStatus(); ?></td>
                      <?php  if ($resgate) { ?>
                    <td style="vertical-align: middle;" class="text-center">
                        <?php if ($cofre->saqueSolicitado < 1) { ?>                           
                        <button class="btn btn-outline btn-default btn-xs" type="button" onclick="saqueInvestimentoToken('<?php echo \Utils\Criptografia::encriptyPostId($cofre->id)?>'); marcaContratoResgate('<?php echo $cofre->id ?>')">
                            <?php echo $this->idioma->getText("sacar") ?>
                        </button>
                        <?php }  ?> 
                    </td>
                      <?php } ?>
                </tr>
                <?php
                }
            
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="10"><?php echo $this->idioma->getText("nenhumaSolicitacao") ?></td>
                </tr>
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
}
