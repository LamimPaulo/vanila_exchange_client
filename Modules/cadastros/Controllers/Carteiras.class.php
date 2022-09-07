<?php

namespace Modules\cadastros\Controllers;

class Carteiras {
    
    private  $codigoModulo = "carteiras";
    private $idioma = null;
    
    function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("index_carteiras", IDIOMA);
        try {
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            if ($configuracao->statusCarteiras < 1) {
                \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
            }
            
        } catch (\Exception $ex) {

        }
    }
    
    public function index($params) {
 
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $clienteRn->conexao->carregar($cliente);
            # 18/07/2019 - Caique feature/online v2
            $clienteRn->setUltimaAtividade();
            # end
            $clienteVerificado = $cliente->documentoVerificado == 1 ? true : false;
            
            $atarCliente = null;
            $atarClientesRn = new \Models\Modules\Cadastro\AtarClientesRn();
            $result = $atarClientesRn->conexao->listar(" id_cliente = {$cliente->id} ");
            
            if (sizeof($result) > 0) {
                foreach ($result as $atar) {
                    $atarCliente = $atar;
                }
            }
            
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contasBancarias = $contaBancariaEmpresaRn->listar(" ativo = 1 ", "id", NULL, null, true);
            
            $contaBoleto = $contaBancariaEmpresaRn->listar("id = 15052774463552 AND ativo > 0", "id", NULL, null, true);
            
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $comissao = 0;
            $comissaoBoleto = $configuracao->taxaDepositoBoleto;
            $tarifaBoleto = $configuracao->tarifaDepositoBoleto;
            if ($cliente->considerarTaxaDepositoCliente) {
                $comissao = $cliente->taxaComissaoDeposito;
            } else {
                $comissao = $configuracao->taxaDeposito;
            }
            
            $params["moedas"] = $this->listar($cliente, $clienteVerificado);
            $params["boletoAtivo"] = sizeof($contaBoleto) > 0 ? true : false;
            $params["atarCliente"] = $atarCliente;
            $params["verificado"] = $clienteVerificado;
            $params["configuracao"] = $configuracao;
            $params["comissaoBoleto"] = $comissaoBoleto;
            $params["tarifaBoleto"] = $tarifaBoleto;
            $params["contas"] = $contasBancarias;
            $params["comissao"] = $comissao;
            $params["sucesso"] = true;
        
        \Utils\Layout::view("index_carteiras", $params);
    }
    
    private function listar(\Models\Modules\Cadastro\Cliente &$cliente, $clienteVerificado) {
        try {
                       
            $categoriaMoedaRn = new \Models\Modules\Cadastro\CategoriaMoedaRn();
            $categorias = $categoriaMoedaRn->conexao->listar("ativo = 1");
            $mostrarAbaDepositoReais = $clienteVerificado;
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            
            $arrayMoedas = null;
            
            $jsonMoedas = (object)null;
            $jsonMoedas->text = "Criptomoedas";
            
            $jsonStablecoin = (object)null;
            $jsonStablecoin->text = "Stablecoins";
            
            if (empty($cliente->moedaFavorita)) {
                $cliente->moedaFavorita = 2;
            }

            if ($cliente->documentoVerificado == 1) {
                
                $jsonContas = (object)null;
                $jsonContas->text = "Reais";

                $bancos = $contaBancariaEmpresaRn->listar(" ativo = 1 ", "ordem DESC", null, NULL, true);

                foreach ($bancos as $contaBancariaEmpresa) {

                    if ($contaBancariaEmpresa->id == 15052774463549) { //Atar     
                        if ($configuracao->atarAtivo == 1) {
                            $object = (object)null;
                            $object->id = \Utils\Criptografia::encriptyPostId($contaBancariaEmpresa->id);
                            $object->text = $contaBancariaEmpresa->banco->nome;
                            $object->icone = IMAGES . "bancos/" . $contaBancariaEmpresa->banco->logo;
                            $object->tipo = "b";

                            $jsonContas->children[] = $object;
                        }
                    } else {
                        /* Boleto ou conta bancaria empresa */
                        $object = (object)null;
                        $object->id = \Utils\Criptografia::encriptyPostId($contaBancariaEmpresa->id);
                        $object->text = $contaBancariaEmpresa->banco->nome;
                        $object->tipo = "b";
                        $object->icone = IMAGES . "bancos/" . $contaBancariaEmpresa->banco->logo;

                        $jsonContas->children[] = $object;
                    }
                }
                
                $arrayMoedas[] = $jsonContas;
            }

            foreach ($categorias as $categoriaCarteira) {
                
                if ($categoriaCarteira->id == 1 && $configuracao->statusDepositoBrl > 0 && $mostrarAbaDepositoReais) {
                    
                } else if ($categoriaCarteira->id > 1) { 
                    $moedas = $moedaRn->conexao->listar("id_categoria_moeda = {$categoriaCarteira->id} AND ativo = 1 AND visualizar_deposito = 1", "principal DESC, nome", null, null);
                    foreach ($moedas as $moeda) {
                       
                       $object = (object)null;
                       $object->id = \Utils\Criptografia::encriptyPostId($moeda->id);
                       $object->text = $moeda->simbolo . " - " . $moeda->nome;    
                       $object->tipo = "c";
                       $object->icone = IMAGES . "currencies/" . $moeda->icone;
                       $object->selected = $cliente->moedaFavorita == $moeda->id ? true : false;
                        
                       if($categoriaCarteira->id == 2){
                           if($cliente->documentoVerificado == 1){
                               $jsonStablecoin->children[] = $object;
                           }
                       } else {
                           $jsonMoedas->children[] = $object;
                       }
                    }
                } 
            }
            
            if($cliente->documentoVerificado == 1){
                $arrayMoedas[] = $jsonStablecoin;
            }
            
            $arrayMoedas[] = $jsonMoedas;
            
            return json_encode($arrayMoedas);
            
        } catch (\Exception $ex) {
         
            return null;
        }
    }
    
    public function showDados($params) {
        try {
            $clienteSessao = \Utils\Geral::getCliente();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente->id = $clienteSessao->id;
            $clienteRn->conexao->carregar($cliente);
            
            $redes = "";
            $topo = "";
                    
            $id = \Utils\Post::getEncrypted($params, "codigo", 0);
            $tipo = \Utils\Post::get($params, "tipo", null);
          
            if (!in_array(strtolower($tipo), Array("b", "c"))) {
                throw new \Exception("Código inválido");
            }
             
            if ($tipo == "b") {
                $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
                $contaBancariaEmpresa = new \Models\Modules\Cadastro\ContaBancariaEmpresa();
                $contaBancariaEmpresa->id = $id;
                
                $contaBancariaEmpresaRn->carregar($contaBancariaEmpresa, true, true);

                if($contaBancariaEmpresa->banco->codigo == 1000){ // Atar
                    $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
                    
                    $json["idAtarEmpresa"] = $configuracao->atarIdEmpresa;
                    if (!file_exists("qrcodes/{$configuracao->atarIdEmpresa}.png")) {
                        \QRcode::png($configuracao->atarIdEmpresa, "qrcodes/{$configuracao->atarIdEmpresa}.png");
                    }
                    $json["tarifaAtar"] = $configuracao->atarTarifaDeposito;
                    $json["taxaAtar"] = $configuracao->atarTaxaDeposito;
                }
                
                
                
                $json["banco"] = $contaBancariaEmpresa->banco->nome;
                $json["titular"] = $contaBancariaEmpresa->titular;
                $json["cnpj"] = $contaBancariaEmpresa->cnpj;
                $json["logo"] = IMAGES . "bancos/{$contaBancariaEmpresa->banco->logo}";
                $json["codigo"] = $contaBancariaEmpresa->banco->codigo;
                $json["agencia"] = $contaBancariaEmpresa->agencia;
                $json["conta"] = $contaBancariaEmpresa->conta;
                $json["tipoConta"] = ($contaBancariaEmpresa->tipoConta == \Utils\Constantes::CONTA_CORRENTE ? "Conta Corrente" : "Conta Poupança") ;
                $json["observacoes"] = $contaBancariaEmpresa->observacoes;
                
                $moeda = new \Models\Modules\Cadastro\Moeda(Array("id" => 1));
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moedaRn->conexao->carregar($moeda);
                
                $dados = \Utils\ValidarLimiteOperacional::validar($cliente, $moeda, \Utils\Constantes::ENTRADA, 0, false);

                if(!empty($dados) && $dados["limiteMensal"] > 0){
                    $json["limiteDisponivel"] = "Limite Disponível: " . number_format($dados["limiteDisponivelMensal"], $moeda->casasDecimais, ",", ".") . " de " . number_format($dados["limiteMensal"], $moeda->casasDecimais, ",", ".") . " {$moeda->simbolo}";
                    
                    if($dados["limiteDisponivelMensal"] <= 0){
                        $json["limiteInformacao"] = "PARA SOLICITAR O AUMENTO DO LIMITE DE DEPÓSITO, POR FAVOR, ENVIE UM E-MAIL PARA SUPPORT@ExchangeCX.COM";
                    } else {
                        $json["limiteInformacao"] = "";
                    }
                } else {
                    $json["limiteDisponivel"] = "";
                }
            } else {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get($id);
                
                //exit(print_r($moeda));
                $carteiraClienteRn = new \Models\Modules\Cadastro\CarteiraRn();
                $carteira = $carteiraClienteRn->getPrincipalCarteira($cliente, $moeda);
                   
                if ($carteira != null) {
                    $json["valido"] = true;
                    $json["endereco"] = $carteira->endereco;
                    if (!file_exists("qrcodes/{$carteira->endereco}.png")) {
                        \QRcode::png($carteira->endereco, "qrcodes/{$carteira->endereco}.png");
                    }
                } else {
                    $json["valido"] = false;
                    $json["endereco"] = "";
                }   
                
                if (!empty($moeda->redesDeposito)) {
                    $object = json_decode($moeda->redesDeposito);

                    $redes = "Essa carteira aceita depósito da(s) rede(s) ";

                    if ($object->ERC20) {
                        $redes = $redes . \Utils\Constantes::REDE_ERC20;

                        $topo = \Utils\Constantes::REDE_ERC20;
                    }

                    if ($object->BEP20) {
                        if (!empty($topo)) {

                            $redes = $redes . " e ";
                            $redes = $redes . \Utils\Constantes::REDE_BEP20;

                            $topo = $topo . " | " . \Utils\Constantes::REDE_BEP20;
                        } else {
                            
                            $redes = $redes . \Utils\Constantes::REDE_BEP20;
                            $topo = \Utils\Constantes::REDE_BEP20;
                        }
                    }
                }

                $json["topo"] = $topo;
                $json["aceitaRede"] = $redes;
                $json["nomeMoeda"] =  $moeda->nome;
                $json["simbolo"] = $moeda->simbolo;
                $json["icone"] = IMAGES . "currencies/{$moeda->icone}";
                
                $taxaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
                $taxa = $taxaRn->getByMoeda($moeda->id);
                                
                if(empty($moeda->idMoedaConversao)){
                   $json["naoTaxaDeposito"] = str_replace("{var}", $moeda->nome, $this->idioma->getText("aNewCashNao")); 
                   
                } else {
                    $moedaConversao = \Models\Modules\Cadastro\MoedaRn::get($moeda->idMoedaConversao);
                    
                    $var = str_replace("{var}", $moeda->nome, $this->idioma->getText("taxaConversao")); 
                    $var = str_replace("{var1}", $moedaConversao->nome, $var); 
                    $taxa = number_format($taxa->taxaConversao, 2, ",", ".") . "%";
                    
                    $json["naoTaxaDeposito"] = str_replace("{var2}", $taxa, $var); 
                }
                
                if(!empty($taxa->valorMinimoDeposito) && $taxa->valorMinimoDeposito > 0){
                    $json["depositoMinimo"] = "Somente é permitido o depósito com volume acima de {$taxa->valorMinimoDeposito} {$moeda->simbolo}. Caso o volume seja abaixo do volume mínimo, o depósito não será creditado. ";
                } else {
                    $json["depositoMinimo"] = "";
                }
                
                $json["envieSomente"] = str_replace("{var}", $moeda->nome, $this->idioma->getText("envieSomente"));
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);    
    }
    
    public function listaDepositos($params) {
        try{
            $cliente = \Utils\Geral::getCliente();
            $categoria = \Utils\Post::getEncrypted($params, "categoria", null);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $dados = $contaCorrenteBtcRn->ultimosDepositosBtcBrl($cliente, 'T', $categoria);
            
            $json["html"] = $this->htmlListaUltimosDepositos($dados);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlListaUltimosDepositos($dados) {        
        ob_start();

        if (sizeof($dados) > 0) {
            foreach ($dados as $contaCorrenteBtc) {
                $this->itemListaUltimosDepositos($contaCorrenteBtc);                
            }
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    
    private function itemListaUltimosDepositos($dados) {
        $data = new \Utils\Data(date($dados["data"]));
        $hora = new \Utils\Data(date($dados["data"])); 
        
        
        
        if($dados["categoria"] > 1){
        $moeda = new \Models\Modules\Cadastro\Moeda();
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moeda->id = $dados["idMoeda"];
        $moedaRn->carregar($moeda);
        }

         
        ?>
        <tr style="text-decoration: <?php echo $dados["descricao"] == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO ? "line-through;" : "none;" ?>">
            <td class="text-center"><?php echo $data->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
            <td class="text-center"><?php echo $hora->formatar(\Utils\Data::FORMATO_HORA) ?></td>
            <td class="text-center"><?php echo $dados["moeda"] ?></td>
            <td class="text-center">
                <?php if ($dados["categoria"] == "1") { ?>
                R$ <?php echo number_format($dados["volume"], 2, ",", ".")?>
                <?php } else { ?>
                <?php echo number_format($dados["volume"], 8, ",", ".") . " " . $moeda->simbolo ?> 
                <?php } ?>                
            </td>
            <td style='text-align: center;'>
                <?php if ($dados["categoria"] == "1") { 
                    if($dados["descricao"] == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) { ?>
                        <i style="color: #333 !important;" class="fa fa-file-pdf-o"></i>         
                <?php } else if($dados["descricao"] != 9 && $dados["moeda"] != "Atar" && $dados["tipo_deposito"] != \Utils\Constantes::GERENCIA_NET){ ?>
                        <a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_FILESMANAGER . "/". \Utils\Criptografia::encriptyPostId($dados["comprovante"]) ?>" target="COMPROVANTE">
                            <i style="color: #d00000 !important;" class="fa fa-file-pdf-o"></i>
                        </a>
                    <?php } else if($dados["moeda"] == "Atar") {
                        echo "-";
                    } else if($dados["tipo_deposito"] == \Utils\Constantes::GERENCIA_NET){ ?>
                        <a href="<?php echo $dados["link"] ?>" target="COMPROVANTE">
                            <i style="color: #d00000 !important;" class="fa fa-file-pdf-o"></i>
                        </a>
                   <?php }
                    if($dados["descricao"] == 9){ echo "Transf. Programada"; }?>                
                <?php } else if($dados["comprovante"] == 13){
                        echo "Transf. Programada";
                
                } else if($dados["direcao"] == "I" && $dados["origem"] == "0"){        
                       echo "Transf. Interna"; 
                } else { ?>
                <a href="<?php echo $moeda->getUrlExplorer($dados["comprovante"])?>" target="EXPLORER"><i style="color: #1c84c6 !important;" class="fa fa-link"></i></a>
                <?php } ?>
            </td>
            
            <td><?php if ($dados["categoria"] == "1") {
                    if($dados["descricao"]  == \Utils\Constantes::STATUS_DEPOSITO_CONFIRMADO || $dados["descricao"] == 9 || $dados["moeda"] == "Atar"){
                       echo $this->idioma->getText("confirmado"); 
                    } else if ($dados["descricao"]  == \Utils\Constantes::STATUS_DEPOSITO_PENDENTE) {
                        echo $this->idioma->getText("pendente");
                    } else if ($dados["descricao"]  == \Utils\Constantes::STATUS_DEPOSITO_CANCELADO) {
                        echo $this->idioma->getText("cancelado");
                    }
            } else { ?>
                <?php echo $this->idioma->getText("confirmado") ?>
           <?php }?></td>
        </tr>
        <?php
    }
    
    public function criarCarteira($params) {
        try {
            $carteira = new \Models\Modules\Cadastro\Carteira();
            $carteira->id = \Utils\Post::getEncrypted($params, 'id', 0);
            $carteira->nome = \Utils\Post::get($params, "nome", null);
            $idMoeda = \Utils\Post::getEncrypted($params, "idMoeda", null);

            $carteira->idMoeda = $idMoeda;

            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $carteiraRn->salvar($carteira);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function marcarComoPrincipal($params) {
        try {
            //Testes
            
            $carteira = new \Models\Modules\Cadastro\Carteira();
            $carteira->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $carteiraRn->marcarComoPrincipal($carteira);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}
