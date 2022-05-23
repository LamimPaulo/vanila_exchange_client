<?php

namespace Modules\principal\Controllers;

use Utils\Layout;
use Utils\Geral;


class Dashboard {

    /**
     * Faço a validação das permissões de acesso no construtor
     * @param type $_parameters
     */
    private $idioma = null;
    
    function __construct(&$_parameters) {
        
        \Utils\Validacao::acesso("dashboard");
        
        $this->idioma = new \Utils\PropertiesUtils("index_wellcome", IDIOMA);
    }

    function index($_parameters) {
        $cliente = Geral::getCliente();
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
        
        $view = "index_wellcome";
        
        $_parameters["clienteVerificado"] = $clienteRn->clienteVerificado($cliente);

        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moeda = new \Models\Modules\Cadastro\Moeda();
        $moeda1 = new \Models\Modules\Cadastro\Moeda();
        $moeda2 = new \Models\Modules\Cadastro\Moeda();
        
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $_parameters["configuracao"] = $configuracao;
            
        if ($cliente != null) {
           
            $clienteRn->conexao->carregar($cliente);
        
            if(empty($cliente->idMoedaAtual)){
                $clienteRn->setParidadeAtual($cliente, 1);
                $cliente->idMoedaAtual = 1;
            }

            $paridade = Principal::getParity(empty($cliente->idMoedaAtual) ? 1 : $cliente->idMoedaAtual);
            
           
            # 05/07/2019
            $clienteRn->setUltimaAtividade();    


            $_parameters["carteiraPrincipal"] = $carteiraRn->getPrincipal($cliente, $paridade->idMoedaBook);

            $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
            $moeda1->id = $brand->idMoedaDashboardPrimary;
            $moeda2->id = $brand->idMoedaDashboardSecondary;
            
            $moedaRn->carregar($moeda1);            
            $moedaRn->carregar($moeda2);
            
            $_parameters["moeda1"] = $moeda1;
            $_parameters["moeda2"] = $moeda2;
                        
            if($cliente->moedaFavorita != null){                
                $moeda->id = $cliente->moedaFavorita;
                $moedaRn->carregar($moeda);
            } else {
                $moeda->id = 2;
                $moedaRn->carregar($moeda);
            }

            $_parameters["moedaFavorita"] = $moeda;
            
        }
        
        
        $moedas = $moedaRn->listar("ativo > 0 AND id > 1",  "principal DESC, simbolo");
        $_parameters["moedas"] = $moedas;
        
        Layout::view($view, $_parameters);
    }
    
    
    public function dadosMoeda($params) {
        
        try {
            $carteira = new \Models\Modules\Cadastro\Carteira();
            $moeda = Principal::getCurrency();
            
        if (Geral::isCliente()) {
            $cliente = Geral::getCliente();
        } else {
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064536678));
        }
            try {
                $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
                $moedaRn->conexao->carregar($moeda);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("vocePrecisaLogado"));
            }
            
            $idMoeda = ($moeda->token > 0 ? $moeda->idMoedaPrincipal : $moeda->id);
            
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $result = $carteiraRn->conexao->listar(" id_cliente = {$cliente->id} AND inutilizada < 1 AND id_moeda = {$idMoeda} AND principal > 0 ");
            
            if (sizeof($result) > 0) {
                $carteira = $result->current();
            } else {
                //Cria carteira para o cliente
                if ($moeda->token > 0) {
                    $carteira->idMoeda = $moeda->idMoedaPrincipal;
                } else {
                    $carteira->idMoeda = $moeda->id;
                }
                
                $carteira->nome = str_replace("{var}", $moeda->nome, $this->idioma->getText("carteira"));
                
                $carteiraRn->salvar($carteira);
            }
            
            //$QRCode = new \Modules\services\Controllers\QRCode();
            
            $json["topo"] = ($carteira != null ? "currencies/".$moeda->simbolo : "logo_1");;
            $json["src"] = ($carteira != null ? "https://qrcode.kaywa.com/img.php?s=7&d={$carteira->endereco}" : IMAGES."logo_1.png");
            $json["moeda"] = "{$moeda->nome}";
            $json["address"] = ($carteira != null ? $carteira->endereco : "<a href='/carteiras'>Gerar nova Carteira</a>");
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function criarCarteiraNewc($params){
        try{
            
        if (Geral::isCliente()) {
            $cliente = Geral::getCliente();
        } else {
            $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => 15093064536678));
        }
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = 33;
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->carregar($moeda);
            
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $result = $carteiraRn->conexao->listar(" id_cliente = {$cliente->id} AND inutilizada < 1 AND id_moeda = {$moeda->id} AND principal > 0 ");
            
            if (sizeof($result) > 0) {
                $carteira = $result->current();
            } else {
                $carteira = new \Models\Modules\Cadastro\Carteira();
                $carteira->idMoeda = $moeda->id;
                $carteira->nome = str_replace("{var}", $moeda->nome, $this->idioma->getText("carteira"));            
                $carteiraRn->salvar($carteira);
            }
            
            
            $json["topo"] = ($carteira != null ? "currencies/".$moeda->simbolo : "logo_1");;
            $json["src"] = ($carteira != null ? "https://qrcode.kaywa.com/img.php?s=7&d={$carteira->endereco}" : IMAGES."logo_1.png");
            $json["moeda"] = "{$moeda->nome}";
            $json["address"] = ($carteira != null ? $carteira->endereco : "<a href='/carteiras'>Gerar nova Carteira</a>");
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
    
    
    
    public function listarMinhasOrdensExecutadas($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $paridade = \Modules\principal\Controllers\Principal::getParity();
            
            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $lista = $orderBookRn->getOrders($paridade, "T", "N", "N", 7, $cliente->id);
            
            $json["html"] = $this->htmlMinhasOrdens($lista);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function htmlMinhasOrdens($lista) {
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordem) {
                $this->htmlItemMinhasOrdens($ordem);
            }
        } else {
            ?>
            <tr>
                <td colspan="6">
                    <?php echo $this->idioma->getText("nenhumaOrdemPendente") ?>
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemMinhasOrdens(\Models\Modules\Cadastro\OrderBook $ordem) {
        //$casasDecimais = (isset($_data["casasDecimais"]) ? $_data["casasDecimais"]  : 2);

        $paridade = \Modules\principal\Controllers\Principal::getParity();
        if ($ordem->cancelada > 0) {
            $color = "color: #666666;";
        } else {
            $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;");
        }
        ?>
        <tr style="<?php echo $color ?>; font-size: 11px">
            
            <td><?php echo ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? $this->idioma->getText("compraC") : $this->idioma->getText("vendaC")) ?></td>
            <td><?php echo $ordem->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?></td>
            <td>R$ <?php echo number_format($ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?></td>
            <td><?php echo number_format($ordem->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", ""); ?> <?php echo $paridade->symbol ?></td>
            <td>R$ <?php echo number_format($ordem->volumeCurrency * $ordem->valorCotacao, $paridade->moedaTrade->casasDecimais, ",", ".")?></td>
            
        </tr>
        <?php
        
    }
    
    public function htmlItemMinhasMoedas($params) {
        
        try {
            $rankingGeral = array();
            $posicaoRanking = null;
            $esconderZerados = \Utils\Post::getBoolean($params, "esconderZerados", false);
            
            ///$bd = new \Io\BancoDados(SLAVE1);            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            
            $moedas = $moedaRn->listar("ativo > 0 AND id > 1", "principal DESC, nome ASC");
            $carteira = new \Models\Modules\Cadastro\Carteira();
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            
            //$bd = new \Io\BancoDados(SLAVE1);
            $clienteRn= new \Models\Modules\Cadastro\ClienteRn();
            $cliente = Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);
            
            
            //$bd = new \Io\BancoDados(SLAVE1);
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            //$bd = new \Io\BancoDados(SLAVE1);
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            
            
            
            
            ob_start();
            $saldoReais = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
            $saldoReaisTotal = $saldoReais["bloqueado"] + $saldoReais["saldo"];
            
            
            if (!$esconderZerados  || $saldoReais["bloqueado"] > 0 || $saldoReais["saldo"] > 0) {
                ?>
        
                <tr style="font-size: 12px;">         
                    <td style="vertical-align: middle; text-align: left;">
                        <img src="<?php echo IMAGES ?>currencies/BRL.png" style="max-width: 25px; max-height: 25px;" />
                        &nbsp;&nbsp;BRL - Real
                    </td>
                    <td style="vertical-align: middle;" class="text-center">-</td>
                    <td style="vertical-align: middle;" class="text-center">R$ <?php echo number_format($saldoReais["saldo"], $configuracao->qtdCasasDecimais,",",".") ?></td>
                    <td style="vertical-align: middle;" class="text-center">R$ <?php echo number_format($saldoReais["bloqueado"], $configuracao->qtdCasasDecimais,",",".") ?></td>
                    <td style="vertical-align: middle;" class="text-center">R$ <?php echo number_format($saldoReaisTotal, $configuracao->qtdCasasDecimais, ",",".") ?></td>
                    <td class="text-center mobile-hide desktop" style="vertical-align: middle;">
                        <div class="btn-group dropdown">
                            <button type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">    <?php echo $this->idioma->getText("acao") ?>   <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_DEPOSITOS ?>"><?php echo $this->idioma->getText("depositarC") ?></a></li>
                                <li><a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_SAQUES?>"><?php echo $this->idioma->getText("sacarC") ?></a></li>

                            </ul>
                        </div>
                    </td>
                </tr>
                                
                     <?php
            }
            $p = Principal::getParity();
            
            //$bd = new \Io\BancoDados(SLAVE1);
            $paridadeRn = new \Models\Modules\Cadastro\ParidadeRn();
            foreach ($moedas as $moeda) {
                
                $paridade = $paridadeRn->find($moeda->id, $p->idMoedaTrade);
                $posicaoRanking = null;
                $rankingCliente = null;
                
                if ($paridade != null) {
                    $rankingCliente = \Models\Modules\Cadastro\RankingClienteMensalRn::getByCliente($cliente, $paridade);
                }
                if ($rankingCliente != null) {
                    $posicaoRanking = $rankingCliente->posicao;
                }
                
                $saldoConta = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true); 
                
                $saldoTotalSomado = $saldoConta["saldo"] + $saldoConta["bloqueado"];
                
                if($moeda->id == $moedaFavorita){
                    $saldoFavorita = $saldoTotalSomado;
                }
                
                if($moeda->id == 33){
                   $saldoNewcTotal =  $saldoTotalSomado;
                }
                
                $carteira = $carteiraRn->getPrincipal($cliente, $moeda->id);
                
                $mostrar = true;
                
                if ($moeda->id != 33 && $esconderZerados && $saldoTotalSomado <= 0) {
                    $mostrar = false;
                }
                
                
                
                if ($mostrar && $moeda->id != 34) {    // Remove o Dolar

                ?>
               <tr style="font-size: 12px;">   
                    

                   <td class="text-left" style="vertical-align: middle;">
                        
                            <img src="<?php echo IMAGES ?>currencies/<?php echo $moeda->icone ?>" style="max-width: 25px; max-height: 25px;" />
                            &nbsp;&nbsp;<?php echo $moeda->simbolo ?> - 
                            <?php echo $moeda->nome ?>
                             <?php if($moeda->id == $cliente->moedaFavorita) { ?>
                                 &nbsp;<i class="fa fa-star" style="color: gold;"></i>
                             <?php } ?> 

                    </td>
                    <td style="vertical-align: middle;" class="text-center">
                        <?php if ($posicaoRanking == 1) { ?>
                        <span><?php echo $posicaoRanking ?>º  <i class='fa fa-2x fa-trophy' style="color: #ccac00"></i></span>
                        <?php } ?>
                        <?php if ($posicaoRanking == 2) { ?>
                        <span><?php echo $posicaoRanking ?>º  <i class='fa fa-2x fa-trophy' style="color: #9a9a9a"></i></span>
                        <?php } ?>
                        <?php if ($posicaoRanking == 3) { ?>
                        <span><?php echo $posicaoRanking ?>º  <i class='fa fa-2x fa-trophy' style="color: #cd7f32"></i></span>
                        <?php } ?>
                        <?php if ($posicaoRanking != 1 && $posicaoRanking != 2 && $posicaoRanking != 3 && $posicaoRanking != null) { ?>
                        <span><?php echo $posicaoRanking ?>º</span>
                        <?php } ?>
                        <?php if ($posicaoRanking == null) { ?>
                        <span>-</span>
                        <?php } ?>
                     </td>
                    <td style="vertical-align: middle;<?php if (number_format($saldoConta["saldo"], $moeda->casasDecimais, ".", "") > 0) { ?>color: #252525;font-weight: bold; <?php } ?>" class="text-center"><?php echo number_format($saldoConta["saldo"], $moeda->casasDecimais, ".", "") ?></td>                    
                    <td style="vertical-align: middle;<?php if (number_format($saldoConta["bloqueado"], $moeda->casasDecimais, ".", "") > 0) { ?>color: #252525;font-weight: bold; <?php } ?>" class="text-center"><?php echo number_format($saldoConta["bloqueado"], $moeda->casasDecimais, ".", "") ?></td>
                    <td style="vertical-align: middle;<?php if (number_format($saldoTotalSomado, $moeda->casasDecimais, ".", "") > 0) { ?>color: #252525;font-weight: bold; <?php } ?>" class="text-center"><?php echo number_format($saldoTotalSomado, $moeda->casasDecimais, ".", "") ?></td>                    
                    <td class="text-center" style="vertical-align: middle;">
                         <?php if($moeda->ativo > 0 && $moeda->id != 34) { //34 = Dolar ?>  
                        <div class="btn-group dropdown">
                            <button type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">     <?php echo $this->idioma->getText("acao") ?>   <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <?php if ($moeda->statusDeposito > 0) { ?>
                                <li><a href="javascript:selectAction('<?php echo \Utils\Criptografia::encriptyPostId($moeda->id) ?>','c','R_DEPOSITOS')"><?php echo $this->idioma->getText("depositarC") ?></a></li>
                                <?php }
                                if ($moeda->statusSaque > 0) { ?>
                                <li><a href="javascript:selectAction('<?php echo \Utils\Criptografia::encriptyPostId($moeda->id) ?>','c','R_SAQUES')"><?php echo $this->idioma->getText("transferirC") ?></a></li>
                                <?php }
                                if ($moeda->statusMercado > 0) { ?>
                                <li><a href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD_REDIRECT ?>/<?php echo $moeda->simbolo ?>/b"><?php echo $this->idioma->getText("compraVenda") ?></a></li>
                                <?php } ?>
                                <?php if($moeda->id != 33) { // NEWC Não favorita ?>
                                <li><a href="javascript:setMoedaFavorita('<?php echo \Utils\Criptografia::encriptyPostId($moeda->id)?>')"><?php echo $this->idioma->getText("favorita") ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                         <?php } ?>
                    </td>
                </tr>
                    
                <?php } 
                
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
    
    
    public function listarUltimasTransacoesCarteirasRemotas($params) {
        
        try {
            $historicoTransacaoReferenciaRn = new \Models\Modules\Cadastro\HistoricoTransacaoReferenciaRn();
            $historico = $historicoTransacaoReferenciaRn->listar(NULL, "data DESC", null, 20, true, true);
            
            $estabelecimentoRn = new \Models\Modules\Cadastro\EstabelecimentoRn();
            
            ob_start();
            if (sizeof($historico) > 0) {
                foreach ($historico as $historicoReferenciaCliente) {
                    //$historicoReferenciaCliente = new \Models\Modules\Cadastro\HistoricoTransacaoReferencia();
                    
                    $estabelecimento = new \Models\Modules\Cadastro\Estabelecimento(Array("id" => $historicoReferenciaCliente->referenciaCliente->idEstabelecimento));
                    $estabelecimentoRn->conexao->carregar($estabelecimento);
                    
                    $icon = "fa-arrow-up";
                    $classIcon = "text-danger";
                    if ($historicoReferenciaCliente->tipo == "D") {
                        $icon = "fa-arrow-down";
                        $classIcon = "text-info";
                    }
                    ?>
                <tr>
                    <td class="text-center <?php echo $classIcon ?>" style="vertical-align: middle;">
                        <i class="fa <?php echo $icon ?>"></i>
                    </td>
                    <td><?php echo $estabelecimento->nome ?></td>
                    <td><?php echo ($historicoReferenciaCliente->carteiraPdv != null ? $historicoReferenciaCliente->carteiraPdv->enderecoCarteira : "") ?></td>
                    <td><?php echo $historicoReferenciaCliente->data->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?></td>
                    <td><?php echo number_format($historicoReferenciaCliente->valor, 8, ".", "")?></td>
                    <td><?php echo ($historicoReferenciaCliente->tipo == "D" ? "Depósito" : "Saque")?></td>
                </tr>
                    <?php
                    
                }
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="6">Ainda não houve movimentações.</td>
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
    
    
    public function listarUltimasTransacoesInvoices($params) {
        try {
            $invoicesPdvRn = new \Models\Modules\Cadastro\InvoicePdvRn();
            $historico = $invoicesPdvRn->listar("status IN ('P', 'S')", "data_deposito DESC", null, 20, true, true);
            
            ob_start();
            if (sizeof($historico) > 0) {
                foreach ($historico as $invoicePdv) {
                    //$invoicePdv = new \Models\Modules\Cadastro\InvoicePdv();
                    
                    ?>
                <tr>
                    <td><?php echo $invoicePdv->pontoPdv->estabelecimento->nome ?></td>
                    <td><?php echo $invoicePdv->pontoPdv->descricao ?></td>
                    <td><?php echo $invoicePdv->dataDeposito->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?></td>
                    <td><?php echo number_format($invoicePdv->valorBrl, 4, ".", "")?></td>
                    <td><?php echo number_format($invoicePdv->valorBtc, 8, ".", "")?></td>
                    <td><?php echo number_format($invoicePdv->saldoRecebido, 8, ".", "")?></td>
                    <td><?php echo ($invoicePdv->status == \Utils\Constantes::STATUS_INVOICE_PDV_PAGO ? "Pago" : "Pago+")?></td>
                </tr>
                    <?php
                    
                }
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="7">Ainda não houve movimentações.</td>
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


    public function setMoedaFavorita($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $moeda = \Utils\Post::getEncrypted($params, "moeda", 0);
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            
            $clienteRn->setMoedaFavorita($cliente, $moeda);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function calculaSaldoDashboard($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $moedaFavorita = \Utils\Post::getEncrypted($params, "moedaFavorita", null);
            $moeda1 = \Utils\Post::getEncrypted($params, "moeda1", null);
            $moeda2 = \Utils\Post::getEncrypted($params, "moeda2", null);
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();  
            
            
            if($moeda1 > 1){
                $saldoMoeda1 = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda1, true); 
                $saldoMoeda1Total = $saldoMoeda1["saldo"] + $saldoMoeda1["bloqueado"];
                $saldoMoeda1Total = number_format($saldoMoeda1Total, 8, ".", "");
            } else {
                $saldoMoeda1 = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);            
                $saldoMoeda1Total = $saldoMoeda1["bloqueado"] + $saldoMoeda1["saldo"];
            }
            
            if($moeda2 > 1){
                $saldoMoeda2 = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda2, true); 
                $saldoMoeda2Total = $saldoMoeda2["saldo"] + $saldoMoeda2["bloqueado"];
                $saldoMoeda2Total = number_format($saldoMoeda2Total, 8, ".", "");
            } else {
                $saldoMoeda2 = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);            
                $saldoMoeda2Total = $saldoMoeda2["bloqueado"] + $saldoMoeda2["saldo"];
            }
            
            $saldoMoedaFavorita = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moedaFavorita, true); 
            $saldoTotalfavorita = $saldoMoedaFavorita["saldo"] + $saldoMoedaFavorita["bloqueado"];
            

            $json["saldoFavorita"] = number_format($saldoTotalfavorita, 8, ".", "");
            $json["saldoMoeda2"] = $saldoMoeda2Total;
            $json["saldoMoeda1"] = $saldoMoeda1Total;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}