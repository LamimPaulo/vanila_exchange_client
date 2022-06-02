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
            $clienteVerificado = $clienteRn->clienteVerificado($cliente);
            
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            $paridades = $paridadeRn->getListaTodasParidades(false);
             
            $di = new \Utils\Data(date("d/m/Y H:i:s"));
            $di->subtrair(0, 0, 0, 24);
            $df = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $atarCliente = null;
            $atarClientesRn = new \Models\Modules\Cadastro\AtarClientesRn();
            $result = $atarClientesRn->conexao->listar(" id_cliente = {$cliente->id} ");
            
            
            if (sizeof($result) > 0) {
                foreach ($result as $atar) {
                    $atarCliente = $atar;
                }
            }



            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $processosDeposito = $depositoRn->calcularQuantiadeHorasMediasValidacaoDeposito($di, $df);
           
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            $contasBancarias = $contaBancariaEmpresaRn->listar("ativo > 0", "id", NULL, null, true);
            
            $contaBoleto = $contaBancariaEmpresaRn->listar("id = 15052774463551 AND ativo > 0", "id", NULL, null, true);
            
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $comissao = 0;
            $comissaoBoleto = $configuracao->taxaDepositoBoleto;
            $tarifaBoleto = $configuracao->tarifaDepositoBoleto;
            if ($cliente->considerarTaxaDepositoCliente) {
                $comissao = $cliente->taxaComissaoDeposito;
            } else {
                $comissao = $configuracao->taxaDeposito;
            }
            
            $params["boletoAtivo"] = sizeof($contaBoleto) > 0 ? true : false;
            $params["atarCliente"] = $atarCliente;
            $params["verificado"] = $clienteVerificado;
            $params["configuracao"] = $configuracao;
            $params["comissaoBoleto"] = $comissaoBoleto;
            $params["tarifaBoleto"] = $tarifaBoleto;
            $params["contas"] = $contasBancarias;
            $params["comissao"] = $comissao;
            $params["sucesso"] = true;
            $params["processoDeposito"] = $processosDeposito;
            $params["paridades"]  = $paridades;
        
        \Utils\Layout::view("index_carteiras", $params);
    }
    
    public function listar($params) {
        try {
            
            $cliente = \Utils\Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);            
            $categoriaMoedaRn = new \Models\Modules\Cadastro\CategoriaMoedaRn();
            $categorias = $categoriaMoedaRn->conexao->listar("ativo = 1");
            $mostrarAbaDepositoReais = $clienteRn->clienteVerificado($cliente);
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $contaBancariaEmpresaRn = new \Models\Modules\Cadastro\ContaBancariaEmpresaRn();
            
            $moedasStablecoin = $moedaRn->conexao->listar("id_categoria_moeda = 2 AND ativo = 1 AND status_deposito = 1", null, null, null);
            ob_start();
            foreach ($categorias as $categoriaCarteira) {
                if($categoriaCarteira->id == 1){
                    if($configuracao->statusDepositoBrl > 0 && $mostrarAbaDepositoReais){ ?>
                       <a href="javascript:ativaUltimosDepositos('<?php echo \Utils\Criptografia::encriptyPostId($categoriaCarteira->id) ?>');" style="text-decoration: none; color: #5d6163;"><h5><?php echo $this->idioma->getText("fiat") ?></h5></a>
                    <?php }
                } else {
                    if($categoriaCarteira->id == 2 && sizeof($moedasStablecoin) > 0){ ?>
                      <a href="javascript:ativaUltimosDepositos('<?php echo \Utils\Criptografia::encriptyPostId($categoriaCarteira->id) ?>');" style="text-decoration: none; color: #5d6163;"><h5><?php echo $this->idioma->getText("stablecoins") ?></h5></a>  
                   <?php }
                    if($categoriaCarteira->id == 3){ ?>
                      <a href="javascript:ativaUltimosDepositos('<?php echo \Utils\Criptografia::encriptyPostId($categoriaCarteira->id) ?>');" style="text-decoration: none; color: #5d6163;"><h5><?php echo $this->idioma->getText("criptomoeda") ?></h5></a>  
                   <?php }
                }
                ?>   
                <ul class="folder-list m-b-md" style="padding: 0">
                <?php
                if ($categoriaCarteira->id == 1 && $configuracao->statusDepositoBrl > 0 && $mostrarAbaDepositoReais) {
                    $bancos = $contaBancariaEmpresaRn->listar("ativo = 1", "ordem DESC", null, NULL, true);
                    foreach ($bancos as $contaBancariaEmpresa) {
                        if($contaBancariaEmpresa->id == 15052774463549){ //Atar     
                         if($configuracao->atarAtivo == 1){  ?>                    
                                <li>
                                    <a  href="javascript:showCoin('<?php echo \Utils\Criptografia::encriptyPostId($contaBancariaEmpresa->id) ?>', 'b'); <?php echo $contaBancariaEmpresa->idBanco == 396 ? "filtrarAtar();" : "filtrarReais();"?>"> 
                                        <img src="<?php echo IMAGES ?>bancos/<?php echo $contaBancariaEmpresa->banco->logo ?>" style="width: 20px; height: 20px;" /> <?php echo $contaBancariaEmpresa->banco->nome ?>
                                    </a>
                                </li>
                         <?php } //}
                        } else { ?>
                                <li>
                                    <a  href="javascript:showCoin('<?php echo \Utils\Criptografia::encriptyPostId($contaBancariaEmpresa->id) ?>', 'b'); <?php echo $contaBancariaEmpresa->idBanco == 389 ? "boletoAux();" : "filtrarReais();"?>"> 
                                        <img src="<?php echo IMAGES ?>bancos/<?php echo $contaBancariaEmpresa->banco->logo ?>" style="width: 20px; height: 20px;" /> <?php echo $contaBancariaEmpresa->banco->nome ?>
                                    </a>
                                </li>
                            <?php }
                    }
                } else if ($categoriaCarteira->id > 1) { 
                    $moedas = $moedaRn->conexao->listar("id_categoria_moeda = {$categoriaCarteira->id} AND ativo = 1 AND status_deposito = 1", "principal DESC, nome", null, null);
                    foreach ($moedas as $moeda) {
                        ?>
                        <li>
                            <a  href="javascript:showCoin('<?php echo \Utils\Criptografia::encriptyPostId($moeda->id) ?>', 'c')"> 
                                <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="width: 20px; height: 20px;" /> <?php echo $moeda->nome ?>
                            </a>
                        </li>
                        <?php
                    }
                } 
                ?>
                </ul>
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
    
    public function showDados($params) {
        try {
            $clienteSessao = \Utils\Geral::getCliente();
            $cliente = new \Models\Modules\Cadastro\Cliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $cliente->id = $clienteSessao->id;
            $clienteRn->conexao->carregar($cliente);
            
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

                
            } else {
                $moeda = \Models\Modules\Cadastro\MoedaRn::get($id);
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
                
                $json["nomeMoeda"] =  $moeda->nome;
                $json["simbolo"] = $moeda->simbolo;
                $json["icone"] = IMAGES . "currencies/{$moeda->icone}";
                $json["naoTaxaDeposito"] = str_replace("{var}", $moeda->nome, $this->idioma->getText("aNewCashNao"));
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
