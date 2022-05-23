<?php

namespace Modules\api\Controllers;

class Wallet {
    
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    private $sandbox = false;
    
    /**
     * Função híbrida que cria uma nova carteira ou retorna os dados de uma carteira existente conforme os parametros passados <b>VIA POST</b></br></br>
     * 
     * <b>Parâmetros obrigatorios:</b> </br>
     * 
     * @param String token Token de validação do login 
     * @param String chave Chave de identificacao do estabelecimento
     * @param String referencia Referencia que indentificara o cliente no estabelecimento
     * 
     * </br></br>
     * @param Integer codigo Código da carteira. Se informado um valor maior que zero a carteira será procurada e os dados da mesma serão retornados. Caso nenhum valor seja passado será criada uma nova carteira e o 
     * os dados da nova carteira serão retornados.
     * 
     * @param Mixed $params
     */
    public function index($params) {
        try {
            
            
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            
            $chave =  \Utils\Post::get($params, "chave", NULL);
            
            $referenciaCliente = new \Models\Modules\Cadastro\ReferenciaCliente();
            $referenciaCliente->referencia = \Utils\Post::get($params, "referencia", NULL);
            
            $carteiraPdv = new \Models\Modules\Cadastro\CarteiraPdv();
            $carteiraPdv->id = \Utils\Post::get($params, "codigo", 0);
            
            $parametroUm = \Utils\Post::get($params, "parametroUm", null);
            $parametroDois = \Utils\Post::get($params, "parametroDois", null);
            $parametroTres = \Utils\Post::get($params, "parametroTres", null);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimento = $estabelecimentoRn->getByChave($chave);
            if ($estabelecimento == null) {
                throw new \Exception("Chave do estabelecimento inválida");
            }
            
            $this->sandbox = ($chave == $estabelecimento->chaveSandbox);
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            if ($carteiraPdv->id > 0) { 
                $carteiraPdvRn->validarAcessoEstabelecimento($carteiraPdv, $referenciaCliente->referencia, $estabelecimento->chave);
                
                try {
                    $carteiraPdvRn->carregar($carteiraPdv, true, false, true);
                } catch (\Exception $ex) {
                    throw new Exception("Carteira não localizada no sistema", 127);
                }
                
                //$carteiraPdvRn->atualizarCarteira($carteiraPdv);
                
                $json["codigo"] = $carteiraPdv->id; 
                $json["endereco"] = $carteiraPdv->enderecoCarteira; 
                $json["moeda"] = $carteiraPdv->moeda->simbolo; 
                $json["referencia"] = $referenciaCliente->referencia;
                $json["confirmado"] = $carteiraPdv->confirmado > 0;
                $json["parametroUm"] = $carteiraPdv->parametroUm;
                $json["parametroDois"] = $carteiraPdv->parametroDois;
                $json["parametroTres"] = $carteiraPdv->parametroTres;
                $json["saldo"] = number_format($carteiraPdv->getSaldo(), 8, ".", ""); 
                $json["mensagem"] = "";
            } else {
                $simbolo = \Utils\Post::get($params, "moeda", NULL);
            
                $moeda = \Models\Modules\Cadastro\MoedaRn::find($simbolo);
                if ($moeda == null) {
                    throw new \Exception("Moeda inválida");
                }
                
                if (!in_array($moeda->simbolo, Array("BTC", "IMO"))) {
                    throw new \Exception("Moeda não permitida");
                }
            
                $carteiraPdv = $carteiraPdvRn->criarCarteira($referenciaCliente, $estabelecimento, $moeda, $parametroUm, $parametroDois, $parametroTres, $this->sandbox);
                $carteiraPdvRn->carregar($carteiraPdv, true, false, true);
                
                $json["codigo"] = $carteiraPdv->id; 
                $json["endereco"] = $carteiraPdv->enderecoCarteira; 
                $json["moeda"] = $carteiraPdv->moeda->simbolo; 
                $json["referencia"] = $referenciaCliente->referencia;
                $json["confirmado"] = $carteiraPdv->confirmado > 0;
                $json["saldo"] = number_format($carteiraPdv->getSaldo(), 8, ".", ""); 
                $json["parametroUm"] = $carteiraPdv->parametroUm;
                $json["parametroDois"] = $carteiraPdv->parametroDois;
                $json["parametroTres"] = $carteiraPdv->parametroTres;
                $json["mensagem"] = "Carteira criada com sucesso!";
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    /**
     * 
     * Retorna a lista de carteiras de acordo com os parametros passados via <b>POST</b> obedecendo a seguinte regra: </br></br>
     * 
     * @param String $token <b>Obrigatório</b>. Token de Login 
     * @param String $chave <b>Obrigatório</b>. A chave do estabelecimento que deve ser informada. Se for informado somente a chave do estabelecimento, todas as carteiras de todos os clientes do estabelecimento serão retornadas
     * @param String $referencia Opcional. Referencia que identifica o cliente. Todas as carteiras do cliente serão retornadas
     * 
     * @param Mixed $params
     */
    public function listar($params) {
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->chave = \Utils\Post::get($params, "chave", NULL);
            
            $referenciaCliente = new \Models\Modules\Cadastro\ReferenciaCliente();
            $referenciaCliente->referencia = \Utils\Post::get($params, "referencia", NULL);
            
            $simbolo = \Utils\Post::get($params, "moeda", NULL);
            
            $moeda = \Models\Modules\Cadastro\MoedaRn::find($simbolo);
            
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            $lista = $carteiraPdvRn->filtrarApi($referenciaCliente->referencia, $estabelecimento->chave, ($moeda != null ? $moeda->id : 0));
            
            $json["wallets"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    /**
     * 
     * Retorna todas as transacoes ocorridas na carteira de acordo com os parametros passados via <b>POST</b>obedecendo a seguinte regra: </br></br>
     * 
     * @param String $token <b>Obrigatório</b>. Token de Login 
     * @param String $chave <b>Obrigatório</b>. A chave do estabelecimento que deve ser informada. Se for informado somente a chave do estabelecimento, todo o historico de transacoes do estabelecimento será retornada
     * @param String $referencia Opcional. Referencia que identifica o cliente. Se informado será retornado somente o histórico de transacoes de carteiras do cliente
     * @param Integer $codigo Opcional. Identificacao da carteira. Se informado serao retornadas as transacoes relacionadas a carteira.
     * 
     * @param Mixed $params
     */
    public function historico($params) {
        
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->chave = \Utils\Post::get($params, "chave", NULL);
            
            $referenciaCliente = new \Models\Modules\Cadastro\ReferenciaCliente();
            $referenciaCliente->referencia = \Utils\Post::get($params, "referencia", NULL);
            
            $carteiraPdv = new \Models\Modules\Cadastro\CarteiraPdv();
            $carteiraPdv->id = \Utils\Post::get($params, "codigo", 0);
            
            $simbolo = \Utils\Post::get($params, "moeda", NULL);
            
            $moeda = \Models\Modules\Cadastro\MoedaRn::find($simbolo);
            
            $historicoTransacaoReferenciaRn = new \Models\Modules\Cadastro\HistoricoTransacaoReferenciaRn();
            $lista = $historicoTransacaoReferenciaRn->getHistorico($carteiraPdv->id, $referenciaCliente->referencia, $estabelecimento->chave, ($moeda != null ? $moeda->id : 0));
            
            $json["registros"] = sizeof($lista);
            $json["transacoes"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    /**
     * 
     * Efetua um saque nas carteiras do cliente informado via <b>POST</b>obedecendo a seguinte regra: </br></br>
     * 
     * @param String $token <b>Obrigatório</b>. Token de Login 
     * @param String $chave <b>Obrigatório</b>. A chave do estabelecimento que deve ser informada. 
     * @param String $referencia <b>Obrigatório</b>. Referencia que identifica o cliente. 
     * @param Numeric $valor <b>Obrigatório</b>. Valor em BTC que será sacado
     * 
     * @param Mixed $params
     */
    public function sacar($params) {
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento();
            $estabelecimento->chave = \Utils\Post::get($params, "chave", NULL);
            
            $referenciaCliente = new \Models\Modules\Cadastro\ReferenciaCliente();
            $referenciaCliente->referencia = \Utils\Post::get($params, "referencia", NULL);
            
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
            $simbolo = \Utils\Post::get($params, "moeda", NULL);
            
            $moeda = \Models\Modules\Cadastro\MoedaRn::find($simbolo);
            if ($moeda == null) {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get(2);
            }
            $referenciaClienteRn = new \Models\Modules\Cadastro\ReferenciaClienteRn();
            
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimento = $estabelecimentoRn->getByChave($estabelecimento->chave);
            
            if (!empty($referenciaCliente->referencia)) {
                $referenciaClienteRn->validar($referenciaCliente->referencia, $estabelecimento->chave);
                
                $referenciaCliente = $referenciaClienteRn->getByReferencia($referenciaCliente->referencia, $estabelecimento);
                if ($referenciaCliente == null) {
                    throw new \Exception("Referência inválida");
                }
                $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
                $contaCorrenteBtc = $carteiraPdvRn->sacar($referenciaCliente, $moeda, $valor, $token);
            } else {
                $contaCorrenteBtc = $estabelecimentoRn->sacar($estabelecimento, $valor, $moeda);
            }
            $json["transacao"] = Array(
                "protocolo" => $contaCorrenteBtc->id,
                "data" => $contaCorrenteBtc->data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                "valor" => number_format($contaCorrenteBtc->valor, 8 , ".", "")
            );
            
            $json["sucesso"] = true;
            $json["mensagem"] = ("Saque realizado com sucesso!");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    
    
    /**
     * 
     * Retorna o saldo total do estabelecimento podendo retornar também o extrato com saldo total de cada cliente. Os parâmetros deve ser passados via <b>POST</b>obedecendo a seguinte regra: </br></br>
     * 
     * @param String $token <b>Obrigatório</b>. Token de Login 
     * @param String $chave <b>Obrigatório</b>. A chave do estabelecimento que deve ser informada. 
     * @param Integer $tipo <b>Obrigatório</b>. 1 = retorna somente o saldo do estabelecimento. 2 = retorna o saldo do estabelecimento e o saldo total de cada cliente. 
     * 
     * @param Mixed $params
     */
    public function balance($params) {
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $chaveEstabelecimento = \Utils\Post::get($params, "chave", NULL);
            
            $tipo = \Utils\Post::get($params, "tipo", 1);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimento = $estabelecimentoRn->getByChave($chaveEstabelecimento);
            
            if ($estabelecimento == null) {
                throw new \Exception("Chave inválida");
            }
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            $json = $carteiraPdvRn->getBalance($estabelecimento, $tipo);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function historicoSaques($params) {
        
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenApi = $tokenRn->validar($token);
            //$tokenApi = new \Models\Modules\Cadastro\TokenApi();
            
            $idCliente = $tokenApi->idCliente;
            
            $chaveEstabelecimento = \Utils\Post::get($params, "chave", NULL);
            $tipo = \Utils\Post::get($params, "tipo", 0);
            
            
            $simbolo = \Utils\Post::get($params, "moeda", NULL);
            
            $moeda = \Models\Modules\Cadastro\MoedaRn::find($simbolo);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimento = $estabelecimentoRn->getByChave($chaveEstabelecimento);
            
            if ($estabelecimento == null) {
                throw new \Exception("Chave inválida");
            }
            
            $sTipo = "";
            if ($tipo == 1) {
                $sTipo = " AND tipo = '".\Utils\Constantes::ENTRADA."' ";
            } else if ($tipo == 2) {
                $sTipo = " AND tipo = '".\Utils\Constantes::SAIDA."' ";
            }
            
            $resgateEstabelecimentoRn = new \Models\Modules\Cadastro\ResgateEstabelecimentoRn();
            
            $sWhereMoeda = "";
            if ($moeda != null) {
                $sWhereMoeda = " AND  id_moeda = {$moeda->id} ";
            }
            $result = $resgateEstabelecimentoRn->listar("id_estabelecimento = {$estabelecimento->id} AND id_conta_corrente_reais IS NULL {$sWhereMoeda} {$sTipo}", "data DESC", null, null, true, false, false, false);
            
            $horas24 = new \Utils\Data(date("d/m/Y H:i:s"));
            $dias7 = new \Utils\Data(date("d/m/Y H:i:s"));
            $dias30 = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $horas24->subtrair(0, 0, 0, 24, 0, 0);
            $dias7->subtrair(0, 0, 7, 0, 0, 0);
            $dias30->subtrair(0, 0, 30, 0, 0, 0);
            
            $entradasTotalUlt24Horas = 0;
            $entradasTotalUlt7Dias = 0;
            $entradasTotUlt30Dias = 0;
            $entradasTotGeral = 0;
            
            $saidasTotalUlt24Horas = 0;
            $saidasTotalUlt7Dias = 0;
            $saidasTotUlt30Dias = 0;
            $saidasTotGeral = 0;
            
            $hist = Array();
            
            foreach ($result as $resgateEstabelecimento) {
                //$resgateEstabelecimento = new \Models\Modules\Cadastro\ResgateEstabelecimento();
                
                $valor = number_format(($resgateEstabelecimento->contaCorrenteBtc->valor - $resgateEstabelecimento->contaCorrenteBtc->valorTaxa), 8, ".", "");
                
                $hist[] = Array(
                    "data" => $resgateEstabelecimento->contaCorrenteBtc->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO),
                    "descricao" => $resgateEstabelecimento->contaCorrenteBtc->descricao,
                    "valor" => number_format($valor, 8, ".", ""),
                    "tipo" => $resgateEstabelecimento->contaCorrenteBtc->tipo,
                    "hash" => $resgateEstabelecimento->contaCorrenteBtc->hash,
                    "moeda" => $resgateEstabelecimento->moeda->simbolo,
                    "status" => $resgateEstabelecimento->contaCorrenteBtc->getStatus()
                );
                
                
                if (!$resgateEstabelecimento->contaCorrenteBtc->dataCadastro->maior($horas24)) { 
                    if ($resgateEstabelecimento->contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA) {
                        $entradasTotalUlt24Horas += number_format($valor, 8, ".", "");
                    } else {
                        $saidasTotalUlt24Horas += number_format($valor, 8, ".", "");
                    }
                    
                }
                
                if (!$resgateEstabelecimento->contaCorrenteBtc->dataCadastro->maior($dias7)) { 
                    if ($resgateEstabelecimento->contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA) {
                        $entradasTotalUlt7Dias += number_format($valor, 8, ".", "");
                    } else {
                        $saidasTotalUlt7Dias += number_format($valor, 8, ".", "");
                    }
                }
                
                if (!$resgateEstabelecimento->contaCorrenteBtc->dataCadastro->maior($dias30)) { 
                    if ($resgateEstabelecimento->contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA) {
                        $entradasTotUlt30Dias += number_format($valor, 8, ".", "");
                    } else {
                        $saidasTotUlt30Dias += number_format($valor, 8, ".", "");
                    }
                }
                
                if ($resgateEstabelecimento->contaCorrenteBtc->tipo == \Utils\Constantes::ENTRADA) {
                    $entradasTotGeral += number_format($valor, 8, ".", "");
                } else {
                    $saidasTotGeral += number_format($valor, 8, ".", "");
                }
            }
            
            if ($tipo == 0) { 
                $json["geral"] = Array(
                    "entradas" => number_format($entradasTotGeral, 8, ".", ""),
                    "saidas" => number_format($saidasTotGeral, 8, ".", ""),
                );

                $json["ultimas24Horas"] = Array(
                    "entradas" => number_format($entradasTotalUlt24Horas, 8, ".", ""),
                    "saidas" => number_format($saidasTotalUlt24Horas, 8, ".", ""),
                );

                $json["ultimos7Dias"] = Array(
                    "entradas" => number_format($entradasTotalUlt7Dias, 8, ".", ""),
                    "saidas" => number_format($saidasTotalUlt7Dias, 8, ".", ""),
                );

                $json["ultimos30Dias"] = Array(
                    "entradas" => number_format($entradasTotUlt30Dias, 8, ".", ""),
                    "saidas" => number_format($saidasTotUlt30Dias, 8, ".", ""),
                );
            } else {
                $json["geral"] = number_format(($tipo > 1  ? $saidasTotGeral : $entradasTotGeral), 8, ".", "");
                $json["ultimas24Horas"] = number_format(($tipo > 1  ? $saidasTotalUlt24Horas : $entradasTotalUlt24Horas), 8, ".", "");
                $json["ultimos7Dias"] = number_format(($tipo > 1  ? $saidasTotalUlt7Dias : $entradasTotalUlt7Dias), 8, ".", "");
                $json["ultimos30Dias"] = number_format(($tipo > 1  ? $saidasTotUlt30Dias : $entradasTotUlt30Dias), 8, ".", "");
            }
            
            
            $json["historico"] = $hist;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function addfounds($params) {
        
        try {
            $token = \Utils\Post::get($params, "token", NULL);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            $tokenRn->validar($token);
            
            $chaveEstabelecimento = \Utils\Post::get($params, "chave", NULL);
            
            $volume = \Utils\Post::getNumeric($params, "volume", 0);
            $carteira = \Utils\Post::get($params, "carteira", 1);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            $estabelecimento = $estabelecimentoRn->getByChave($chaveEstabelecimento);
            $this->sandbox = ($chaveEstabelecimento == $estabelecimento->chaveSandbox);
            
            if (!$this->sandbox) {
                throw new \Exception("Método disponível somente para carteiras Sandbox");
            }
            
            if ($estabelecimento == null) {
                throw new \Exception("Chave inválida");
            }
            
            $carteiraPdvRn = new \Models\Modules\Cadastro\CarteiraPdvRn();
            $json = $carteiraPdvRn->adicionarFundosSandbox($volume, $carteira);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
    
    
    public function walletsneeded($params) {
        try {
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::get($params, "moeda", 0);
            $limit = \Utils\Post::get($params, "limit", 0);
            
            if ($moeda->id < 2) {
                throw new \Exception("Moeda inválida");
            }
            
            try {
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moedaRn->conexao->carregar($moeda);
                
            } catch (\Exception $ex) {
                throw new \Exception("Moeda inválida");
            }
            
            if ($moeda->ativo < 1) {
                //throw new \Exception("Moeda temporariamente desativada no sistema");
            }
            
            if ($moeda->statusMercado < 1) {
                //throw new \Exception("O mercado da moeda está temporariamente suspenso");
            }
            
            $walltesPorCliente = 5;
            $walletsPorEstabelecimento = 2;
            $walletsPorPdv = 2;
            
            $carteiraGeradaRn = new \Models\Modules\Cadastro\CarteiraGeradaRn();
            $dados = $carteiraGeradaRn->contarRegistros($moeda->id);
            
            $carteirasNecessariasClientes = ($dados["clientes"] * $walltesPorCliente) - $dados["carteirasClientes"];
            $outrasCarteirasNecessarias = (($dados["estabelecimentos"] * $walletsPorEstabelecimento) + ($dados["pdvs"] * $walletsPorPdv));
            
            $result = $carteiraGeradaRn->conexao->listar("id_moeda = {$moeda->id}", "id", NULL, ($limit > 0 ? intval($limit) : 0));
            $wallets = Array();
            foreach ($result as $carteira) {
                $wallets[] = Array(
                    "address" => $carteira->address,
                    "idExterno" => $carteira->id,
                    "idMoeda" => $carteira->idMoeda
                );
            }
            
            $json["carteiras"] = ($carteirasNecessariasClientes + $outrasCarteirasNecessarias - $dados["carteirasLivres"]);
            $json["listaWallets"] =  $wallets;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function savewallets($params) {
        
        $lista = Array();
        try {
            $post = $params["_POST"];
            
            //$wallets = \Utils\Post::getJson($params, "wallets", NULL);
            $wallets = (isset($post["wallets"]) ? $post["wallets"] : null);
            
            $wallets = (empty($wallets) ? Array() : json_decode($wallets));
            
            $carteiraGeradaRn = new \Models\Modules\Cadastro\CarteiraGeradaRn();
            if (sizeof($wallets) > 0) {
                foreach ($wallets->wallets as $wallet) {
                    
                    try {
                        $carteiraGerada = new \Models\Modules\Cadastro\CarteiraGerada();
                        $carteiraGerada->address = \Utils\SQLInjection::clean($wallet->address);
                        $carteiraGerada->idMoeda = \Utils\SQLInjection::clean($wallet->moeda);
                        $carteiraGerada->seed = (isset($wallet->seed) ? $wallet->seed : null);
                        $carteiraGerada->hash = \Utils\SQLInjection::clean((isset($wallet->hash) ? $wallet->hash : null));
                        $carteiraGeradaRn->salvar($carteiraGerada);
                        
                        $lista[] = Array(
                            "address" => $carteiraGerada->address,
                            "idExterno" => $carteiraGerada->id,
                            "idMoeda" => $carteiraGerada->idMoeda
                        );
                        
                    } catch (\Exception $e) {
                        //throw new \Exception($e);
                    }
                    
                }
            }
            
            $json["wallets"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function walletsinuse($params) {
        try {
            $token = \Utils\Post::get($params, "token", null);
            $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
            //$tokenRn->validar($token);
            
            $idMoeda = \Utils\Post::get($params, "moeda", 0);
            
            if (!$idMoeda > 0) {
                throw new \Exception("moeda inválida");
            }
            
            $carteirasClienteRn = new \Models\Modules\Cadastro\CarteiraRn();
            $result = $carteirasClienteRn->listar("id_moeda = {$idMoeda} AND inutilizada < 1", null, null, null, false);
            
            $walletIcoRn = new \Models\Modules\ICO\WalletIcoRn();
            $resultIco = $walletIcoRn->conexao->listar("id_moeda = {$idMoeda} AND ativo > 0");
            
            $lista = Array();
            foreach ($result as $carteiraCliente) {
                $lista[] = $carteiraCliente->endereco;
            }
            
            foreach ($resultIco as $carteiraIco) {
                $lista[] = $carteiraIco->endereco;
            }
            
            $json["wallets"] = $lista;
            $json["qtd"] = sizeof($lista);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}
