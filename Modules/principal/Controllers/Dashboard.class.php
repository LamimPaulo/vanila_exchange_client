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
            
            Geral::setCliente($cliente);
            
            # 05/07/2019
            $clienteRn->setUltimaAtividade();   

//            $brand = \Models\Modules\Cadastro\BrandRn::getBrand();
//            $moeda1->id = $brand->idMoedaDashboardPrimary;
//            $moeda2->id = $brand->idMoedaDashboardSecondary;
//            
//            $moedaRn->carregar($moeda1);            
//            $moedaRn->carregar($moeda2);
//            
//            $_parameters["moeda1"] = $moeda1;
//            $_parameters["moeda2"] = $moeda2;
//                        
//            if($cliente->moedaFavorita != null){                
//                $moeda->id = $cliente->moedaFavorita;
//                $moedaRn->carregar($moeda);
//            } else {
//                $moeda->id = 2;
//                $moedaRn->carregar($moeda);
//            }

            $_parameters["moedaFavorita"] = $moeda;
            
        }
        
        
        $moedas = $moedaRn->listar("ativo > 0 AND id > 1",  "principal DESC, simbolo");
        $_parameters["moedas"] = $moedas;
        
        Layout::view($view, $_parameters);
    }  
    
    
    public function minhasMoedas(){
        
        try {
            $bd = new \Io\BancoDados(BDBOOK);
            
            $clienteRn= new \Models\Modules\Cadastro\ClienteRn($bd);
            $cliente = Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);
            
           
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn($bd);
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn($bd);
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();

            $saldos = Array();
            
            $saldoReais = $contaCorrenteReaisRn->calcularSaldoConta($cliente, true);
            
            $moedaBrl = \Models\Modules\Cadastro\MoedaRn::get(1);
            
            $moedaReal = null;
            $moedaReal->saldoTotal = number_format(($saldoReais["saldo"] + $saldoReais["bloqueado"]), 2, ",", ".");
            $moedaReal->saldoDisponivel = number_format($saldoReais["saldo"], 2, ",", ".");
            $moedaReal->saldoBloqueado = number_format($saldoReais["bloqueado"], 2, ",", ".");
            $moedaReal->cor = $moedaBrl->mainColor;
            $moedaReal->fonte = $moedaBrl->corFonte;
            $moedaReal->nome = $moedaBrl->nome;
            $moedaReal->id = \Utils\Criptografia::encriptyPostId($moedaBrl->id);
            $moedaReal->simbolo = $moedaBrl->simbolo;
            $moedaReal->imagem = IMAGES . "currencies/" . $moedaBrl->icone;
            $moedaReal->saque = $moedaBrl->statusSaque;
            $moedaReal->deposito = $moedaBrl->statusDeposito;
            $moedaReal->favorita = (empty($cliente->moedaFavorita) || $cliente->moedaFavorita == $moedaBrl->id) ? true : false;
            
            
            $saldos[] = $moedaReal;

            $moedas = $moedaRn->listar(" ativo > 0 and  id > 1 ");

            foreach ($moedas as $moeda) {

                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, true, false);

                if (($saldo["saldo"] + $saldo["bloqueado"]) != 0) {

                    $moedaAux = null;
                    $moedaAux->saldoTotal = number_format(($saldo["saldo"] + $saldo["bloqueado"]), $moeda->casasDecimais, ".", "");
                    $moedaAux->saldoDisponivel = number_format($saldo["saldo"], $moeda->casasDecimais, ".", "");
                    $moedaAux->saldoBloqueado = number_format($saldo["bloqueado"], $moeda->casasDecimais, ".", "");
                    $moedaAux->cor = $moeda->mainColor;
                    $moedaAux->fonte = $moeda->corFonte;
                    $moedaAux->nome = $moeda->nome;
                    $moedaAux->simbolo = $moeda->simbolo;
                    $moedaAux->id = \Utils\Criptografia::encriptyPostId($moeda->id);
                    $moedaAux->imagem = IMAGES . "currencies/" . $moeda->icone;
                    $moedaAux->saque = $moeda->statusSaque;
                    $moedaAux->deposito = $moeda->statusDeposito;
                    $moedaAux->mercado = URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD_REDIRECT . "/" .$moeda->simbolo . "/" . "b";
                    $moedaAux->favorita = $cliente->moedaFavorita == $moeda->id;

                    if($moedaAux->favorita){
                        array_unshift($saldos, $moedaAux);
                    } else {
                        $saldos[] = $moedaAux;
                    }  
                }
            }

            $json["dados"] = $saldos;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function htmlItemMinhasMoedas($params) {
        
        try {
            $rankingGeral = array();
            $posicaoRanking = null;
            $esconderZerados = \Utils\Post::getBoolean($params, "esconderZerados", false);
            
            $bd = new \Io\BancoDados(BDBOOK);            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn($bd);
            
            $moedas = $moedaRn->listar("ativo > 0 AND id > 1", "principal DESC, nome ASC");
            $carteira = new \Models\Modules\Cadastro\Carteira();
            $carteiraRn = new \Models\Modules\Cadastro\CarteiraRn();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();

            $clienteRn= new \Models\Modules\Cadastro\ClienteRn();
            $cliente = Geral::getCliente();
            $clienteRn->conexao->carregar($cliente);
            
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn($bd);
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn($bd);
            
                        
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