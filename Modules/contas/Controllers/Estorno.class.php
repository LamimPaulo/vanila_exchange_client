<?php

namespace Modules\contas\Controllers;

class Estorno {
    
    private $codigoModulo = "estorno";
    
    public static $motivosRejeicao = Array(
        "Conta bancária divergente",
        "Dados insuficientes",
        "Outros"
    );
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
    }


    
    public function index($params) {
        try {
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("index_estorno", $params);
    }
    
    public function listar($params) {
        try {
            
            
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $status = \Utils\Post::get($params, "status", "T");
            $nregistros = \Utils\Post::get($params, "nresultado", 10);
            $filtro = \Utils\Post::get($params, "filtro", null);
            $comDadosBancarios = \Utils\Post::getBoolean($params, "comDadosBancarios", false);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornos = $estornoRn->filtrar($dataInicial, $dataFinal, $status, $nregistros, $filtro, $comDadosBancarios);
            
            $json["html"] = $this->htmlListaEstornos($estornos);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaEstornos($estornos) {
        ob_start();
        if (sizeof($estornos) > 0) {
            foreach ($estornos as $estorno) {
                $this->htmlItemEstorno($estorno);
            }
        } else {
            ?>
            <tr>
                <td class="text-center" colspan="9">Não existem estornos para os dados informados.</td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    private function htmlItemEstorno(\Models\Modules\Cadastro\Estorno $estorno) {
        ?>
        <tr>
            <td class="text-center"><?php echo $estorno->id ?></td>
            <td class="text-center"><?php echo $estorno->idDeposito ?></td>
            <td><?php echo $estorno->cliente->nome ?></td>
            <td class="text-center"><?php echo number_format($estorno->deposito->valorDepositado, 2, ",", ".") ?></td>
            <td class="text-center"><?php echo number_format($estorno->valor, 2, ",", ".") ?></td>
            <td class="text-center"><?php echo number_format($estorno->valorTaxa + $estorno->taxaTed, 2, ",", ".") ?></td>
            <td class="text-center"><?php echo $estorno->getStatus() ?></td>
            <td class="text-center">
                <?php if ($estorno->status == \Utils\Constantes::EXTORNO_PENDENTE || $estorno->status == \Utils\Constantes::EXTORNO_REJEITADO) { ?>
                <button type="button" class="btn btn-xs btn-primary" style="font-size: 11px" onclick="modalAprovarEstorno('<?php echo \Utils\Criptografia::encriptyPostId($estorno->id)?>');">
                    Analizar
                </button>
                <?php } else if ($estorno->status == \Utils\Constantes::EXTORNO_APROVADO) {  ?>
                <button type="button" class="btn btn-xs btn-primary" style="font-size: 11px" onclick="modalFinalizarEstorno('<?php echo \Utils\Criptografia::encriptyPostId($estorno->id)?>');">
                    Finalizar
                </button>
                <?php } ?>
            </td>
            
            <td class="text-center">
                <?php if ($estorno->status != \Utils\Constantes::EXTORNO_CANCELADO && $estorno->status != \Utils\Constantes::EXTORNO_FINALIZADO) { ?>
                <button type="button" class="btn btn-xs btn-danger" style="font-size: 11px" onclick="modalCancelarEstorno('<?php echo \Utils\Criptografia::encriptyPostId($estorno->id)?>');">
                    Cancelar
                </button>
                <?php }   ?>
            </td>
        </tr>
        <?php
    }
    
    public function analisar($params) {
        try {
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->carregar($estorno, true, true, true, true, true, true);
            
            $json["estornoTitular"] = $estorno->nomeTitular;
            $json["estornoCnpjCpf"] = $estorno->cpfCnpj;
            $json["estornoAgencia"] = $estorno->agencia;
            $json["estornoConta"] = $estorno->conta;
            $json["estornoTipoConta"] = $estorno->getTipoConta();
            $json["estornoBanco"] = $estorno->banco->nome;
            $json["estornoValorEstorno"] = "R$ " . number_format($estorno->valor, 2, ",", ".");
            $json["estornoPercentualTaxaEstorno"] = "R$ " . number_format($estorno->percentualTaxa, 2, ",", ".");
            $json["taxaEstorno"] = "R$ " . number_format($estorno->valorTaxa, 2, ",", ".");
            $json["estornoValorTed"] = "R$ " . number_format($estorno->taxaTed, 2, ",", ".");

            $json["depositoTitular"] = $estorno->cliente->nome;
            $json["depositoCnpjCpf"] = $estorno->cliente->documento;
            $json["depositoAgencia"] = $estorno->deposito->contaBancariaEmpresa->agencia;
            $json["depositoConta"] = $estorno->deposito->contaBancariaEmpresa->conta;
            $json["depositoTipoConta"] = $estorno->deposito->contaBancariaEmpresa->getTipoConta();
            $json["depositoBanco"] = $estorno->deposito->contaBancariaEmpresa->banco->nome;
            $json["depositoValor"] = "R$ " . number_format($estorno->deposito->valorDepositado, 2, ",", ".");
            $json["depositoTipo"] = $estorno->deposito->getTipoDeposito();
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function iniciar($params) {
        
        try {
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $deposito->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->iniciar($deposito);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "O Procedimento de estorno foi iniciado. O cliente será solicitado a informar os dados bancários para crédito.";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getListaContas($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            try {
                $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
                $estornoRn->carregar($estorno, true, false, FALSE, false, false, true);
            } catch (\Exception $ex) {
                throw new \Exception("Estorno não localizado no sistema");
            }
            
            $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
            $contas = $contaBancariaRn->listar("id_cliente = {$cliente->id} AND ativo > 0", "id", null, null, true);
            
            $htmlContas = $this->selectContasBancarias($contas);
            
            $json["numeroDeposito"] = $estorno->idDeposito;
            $json["valorDeposito"] = number_format($estorno->deposito->valorDepositado, 2, ",", ".");
            $json["taxaExtorno"] = number_format($estorno->percentualTaxa, 2, ",", ".");
            $json["tarifaTed"] = number_format($estorno->taxaTed, 2, ",", ".");
            $json["valorExtorno"] = number_format($estorno->valor, 2, ",", ".");
            
            $json["rejeitado"] = ($estorno->status == \Utils\Constantes::EXTORNO_REJEITADO);
            $json["motivoRejeicao"] = $estorno->motivoRejeicao;
            
            $json["agencia"] = $estorno->agencia;
            $json["conta"] = $estorno->conta;
            $json["cpfCnpj"] = $estorno->cpfCnpj;
            $json["banco"] = \Utils\Criptografia::encriptyPostId(($estorno->idBanco > 0 ? $estorno->idBanco : 0));
            $json["nomeTitular"] = $estorno->nomeTitular;
            $json["tipoConta"] = ($estorno->tipoConta != null ? $estorno->tipoConta : \Utils\Constantes::CONTA_CORRENTE);
            
            $json["htmlContas"] = $htmlContas;
            $json["temContas"] = (sizeof($contas) > 0);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function selectContasBancarias($contas) {
        ob_start();
        
        foreach ($contas as $contaBancaria) {
            ?>
            <option value="<?php echo \Utils\Criptografia::encriptyPostId($contaBancaria->id) ?>">
                <?php echo "{$contaBancaria->banco->codigo} - {$contaBancaria->banco->nome} - AG {$contaBancaria->agencia} - CC {$contaBancaria->banco->nome}"; ?>
            </option>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    public function salvarDadosBancarios($params) {
        try {
            $utilizarContaBancaria = \Utils\Post::getBoolean($params, "utilizarContaBancaria", false);
            $utilizarContaNaoCadastrada = \Utils\Post::getBoolean($params, "utilizarContaNaoCadastrada", false);
            
            $contaBancaria = new \Models\Modules\Cadastro\ContaBancaria();
            $contaBancaria->id = \Utils\Post::getEncrypted($params, "contaBancaria", 0);
            
            $titular = \Utils\Post::get($params, "titular", 0);
            $cpfCnpj = \Utils\Post::get($params, "cpfCnpj", 0);
            $banco = \Utils\Post::getEncrypted($params, "banco", 0);
            $agencia = \Utils\Post::get($params, "agencia", 0);
            $conta = \Utils\Post::get($params, "conta", 0);
            $tipoConta = \Utils\Post::get($params, "tipoConta", 0);
            
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            if ($utilizarContaBancaria) {
                if ($contaBancaria->id > 0) {
                    
                    try {
                        $contaBancariaRn = new \Models\Modules\Cadastro\ContaBancariaRn();
                        $contaBancariaRn->conexao->carregar($contaBancaria);
                        
                        $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $contaBancaria->idCliente));
                        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                        $clienteRn->conexao->carregar($cliente);
                        
                    } catch (\Exception $ex) {
                        throw new \Exception("Conta bancária inválida ou não informada");
                    }
                } else {
                    throw new \Exception("É necessário informar uma conta bancária");
                }
                
                $estorno->agencia = $contaBancaria->agencia;
                $estorno->conta = $contaBancaria->conta;
                $estorno->cpfCnpj = $cliente->documento;
                $estorno->idBanco = $contaBancaria->idBanco;
                $estorno->nomeTitular = $cliente->nome;
                $estorno->tipoConta = $contaBancaria->tipoConta;
                
            } else {
                
                $estorno->agencia = $agencia;
                $estorno->conta = $conta;
                $estorno->cpfCnpj = $cpfCnpj;
                $estorno->idBanco = $banco;
                $estorno->nomeTitular = $titular;
                $estorno->tipoConta = $tipoConta;
                
            }
            //exit(print_r($estorno));
            $estornoRn =  new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->cadastrarInformacoesBancarias($estorno);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function aprovar($params) {
        try {
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->aprovarEstorno($estorno);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Estorno aprovado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function rejeitar($params) {
        try {
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $estorno->motivoRejeicao = \Utils\Post::get($params, "motivo", 0);
            
            if (isset(self::$motivosRejeicao[$estorno->motivoRejeicao])) {
                $estorno->motivoRejeicao = self::$motivosRejeicao[$estorno->motivoRejeicao];
            } else {
                throw new \Exception("Motivo inválido!");
            }
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->rejeitar($estorno);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Os dados para depósito foram  rejeitados com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function cancelar($params) {
        try {
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->cancelarEstorno($estorno);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Estorno cancelado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function finalizar($params) {
        try {
            $estorno = new \Models\Modules\Cadastro\Estorno();
            $estorno->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $estornoRn = new \Models\Modules\Cadastro\EstornoRn();
            $estornoRn->finalizarEstorno($estorno);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Estorno finalizado com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}