<?php

namespace Modules\trade\Controllers;

class PreVendas {
    
    private $codigoModulo = "trade";
    private $idioma = null;
    
    public function __construct() {
        
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("pre-sales", IDIOMA);
    }
    
    public function index($params) {
        
        try {
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar(null, "nome", null, null);
            
            $params["moedas"] = $moedas;
        } catch (\Exception $ex) {
            
        }
        \Utils\Layout::view("pre_vendas", $params);
    }
    
    public function cadastro($params) {
        try {
            $ico = new \Models\Modules\ICO\Ico();
            $ico->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            if ($ico->id > 0) {
                $icoRn = new \Models\Modules\ICO\IcoRn();
                $icoRn->conexao->carregar($ico);
                
            }
            
            $ico->id = \Utils\Criptografia::encriptyPostId($ico->id);
            $ico->idMoeda = \Utils\Criptografia::encriptyPostId(($ico->idMoeda > 0 ? $ico->idMoeda : 0));
            
            if ($ico->idClienteContaCredito > 0) {
                $cliente = new \Models\Modules\Cadastro\Cliente(Array("id" => $ico->idClienteContaCredito));
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $clienteRn->conexao->carregar($cliente);
                
                $json["emailContaCliente"] = $cliente->email;
            } else {
                $json["emailContaCliente"] = $cliente->email;
            }
            
            $json["ico"] = $ico;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listar($params) {
        try {
            $icoRn = new \Models\Modules\ICO\IcoRn();
            
            $where = null;
            if (\Utils\Geral::isCliente()) {
                $where = " ativo > 0  AND exibir_publico > 0 ";
            }
            
            $icos = $icoRn->listar($where, "preferencial DESC, id DESC", null, null);

            
            $json["html"] = $this->htmlIco($icos);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlIco($lista) {
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $ico) {
                $this->htmlItemIco($ico);
            }
        } else {
            ?>
            <div class="col-lg-12 text-center">
                <?php echo $this->idioma->getText("preVendas1"); ?>
            </div>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    private function htmlItemIco(\Models\Modules\ICO\Ico $ico) {
         
       
        $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn();
        $fasesIco = $faseIcoRn->conexao->listar("id_ico = {$ico->id}", "ordem", null, null);
       
        $faseAtual = $faseIcoRn->getFaseIco($ico->id);
        
        $tabelaConversaoIcoRn = new \Models\Modules\ICO\TabelaConversaoIcoRn($faseIcoRn->conexao->adapter);
        $dadosTabelas = $tabelaConversaoIcoRn->getTabelaPrecosIco($ico);
        
        $tabela = $dadosTabelas["tabela"];
        $fasesTabelas = $dadosTabelas["fases"];
        $percentual = $dadosTabelas["percentual"];
        
        $icoRn = new \Models\Modules\ICO\IcoRn($faseIcoRn->conexao->adapter);
        $icoRn->carregar($ico, false, true);
        
        $percentualFaseAtual = number_format(($faseAtual->tokensParaVenda > 0 ? ($faseAtual->tokensVendidos / $faseAtual->tokensParaVenda * 100) : 0), 2, ".", "");
        
        ?>
        <div class="col-lg-12" id="ico-<?php echo $ico->id ?>">
            <div class="contact-box">
                <input type="hidden" id="comprar-ico-<?php echo $ico->id ?>-nome-moeda" value="<?php echo $ico->moeda->simbolo ?>" />
                <div class="col-sm-3 text-center p-md">
                    <br>
                    <img alt="image" class="img-circle " style="min-width: 120px; min-height: 120px; max-width: 180px; max-height: 180px;" src="<?php echo URLBASE_CLIENT .  $ico->logo ?>">
                </div>
                <div class="col-sm-6 p-md">
                    <div>
                        <h3><strong><?php echo $ico->sigla ?> - <?php echo $ico->nome ?></strong></h3>
                        <p>
                            <strong><?php echo $this->idioma->getText("preVendas49");?></strong> <?php echo $faseAtual->nome ?> (<?php echo $percentualFaseAtual ?>%)<br>
                            <strong><?php echo $this->idioma->getText("preVendas2");?></strong><?php echo $ico->responsavel ?>. <br>
                            <strong><?php echo $this->idioma->getText("preVendas3");?></strong><?php echo $ico->email ?>. <br>
                            <strong><?php echo $this->idioma->getText("preVendas4");?></strong><a href="<?php echo $ico->site ?>" target="_BLANK"><?php echo $ico->site ?></a> <br>
                            <strong><?php echo $this->idioma->getText("preVendas5");?></strong><a href="<?php echo $ico->whitepaper ?>" target="_BLANK"><?php echo $ico->whitepaper  ?></a> <br>
                            <strong><?php echo $this->idioma->getText("preVendas6");?></strong><?php echo $ico->descricao ?> <br>
                        </p>
                    </div>
                </div>
                
                <div class="col-sm-3">
                    
                    <div class="row">
                        <div class="col col-xs-12 text-center">
                            <strong><?php echo $this->idioma->getText("preVendas7");?> <?php echo $percentual ?>%</strong>
                        </div>
                        
                        <div class="col col-xs-12">
                            <div class="progress progress-bar-default" >
                                <div style="width: <?php echo $percentual ?>%" aria-valuemax="100" aria-valuemin="0" id="progress-ico-<?php echo $ico->id ?>" aria-valuenow="<?php echo $percentual ?>" role="progressbar" class="progress-bar">
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (\Utils\Geral::isUsuario()) { ?>
                    
                    <div class="row">
                        <div class="col col-lg-4 text-center">
                            <button type="button" class="btn btn-info btn-sm" onclick="modalCadastrarPrevenda('<?php echo \Utils\Criptografia::encriptyPostId($ico->id)?>')" >
                                Editar
                            </button>
                        </div>
                        <div class="col col-lg-4 text-center">
                            <button type="button" class="btn  btn-sm btn-<?php echo ($ico->ativo > 0 ? "danger" : "primary") ?>" onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($ico->id)?>')" >
                                <?php echo ($ico->ativo > 0 ? "Desativar" : "Ativar") ?>
                            </button>
                        </div>
                        <div class="col col-lg-4 text-center">
                            <button type="button" class="btn btn-info  btn-sm" onclick="modalTabelaPrecos('<?php echo \Utils\Criptografia::encriptyPostId($ico->id)?>')" >
                                Tabelas
                            </button>
                        </div>
                    </div>
                    
                    <?php } else {
                        
                        ?>
                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="form-group">
                                <select class="form-control comprar-ico-moeda" data-ico="<?php echo $ico->id ?>" id="comprar-ico-<?php echo $ico->id ?>-moeda">
                                    <label><?php echo $this->idioma->getText("preVendas41");?></label>
                                    <?php 
                                    foreach ($tabela as $dadosTabela) {
                                        if ($dadosTabela["id"] != 34) {
                                        $precoFaseAtual = 0;
                                        if ($faseAtual != null && isset($dadosTabela["fases"][$faseAtual->id]["volume"])) {
                                            $precoFaseAtual = $dadosTabela["fases"][$faseAtual->id]["volume"];
                                        }
                                        ?>
                                        <option value="<?php echo \Utils\Criptografia::encriptyPostId($dadosTabela["id"])?>" data-volume="<?php echo number_format($precoFaseAtual, (in_array($dadosTabela["id"], Array(1, 34)) ? 4 : 8), ".", "")?>"><?php echo $dadosTabela["moeda"] ?></option>
                                        <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label  ><?php echo $this->idioma->getText("preVendas42");?> <?php echo $faseAtual->nome ?>: <strong id="comprar-ico-<?php echo $ico->id ?>-precoFase" ></strong></label><br>
                                <label  >
                                    <?php echo $this->idioma->getText("preVendas56");?> 
                                    <a href="javascript:setSaldoParaCompra(<?php echo $ico->id ?>);" id="comprar-ico-<?php echo $ico->id ?>-saldo" ></a>
                                </label>
                            </div>
                            
                            
                            <label id="comprar-ico-<?php echo $ico->id ?>-sigla" ></label>
                            <div class="input-group m-b">
                                <input type="text" class="form-control comprar-ico-volume" data-ico="<?php echo $ico->id ?>" id="comprar-ico-<?php echo $ico->id ?>-volume" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary" onclick="comprarIco(<?php echo $ico->id ?>)"><?php echo $this->idioma->getText("preVendas40");?></button> 
                                </span> 
                            </div>
                            
                            <div class="form-group">
                                <label  ><?php echo $this->idioma->getText("preVendas43");?> <strong id="comprar-ico-<?php echo $ico->id ?>-receber" ></strong> <?php echo $ico->moeda->nome ?></label>
                            </div>
                        </div>
                    </div>
                        <?php
                    } 
                    ?>
                </div>
                
                
                    <?php if (\Utils\Geral::isUsuario()) { ?> 
                    <div class="col col-lg-12 text-center">
                        <button type="button" class="btn btn-info" onclick="modalCadastroFase('<?php echo \Utils\Criptografia::encriptyPostId($ico->id)?>', '<?php echo \Utils\Criptografia::encriptyPostId(0)?>')" >
                            <?php echo $this->idioma->getText("preVendas38");?>
                        </button>
                    </div>
                    <?php } ?>
                    
                    <div class="col col-lg-12">
                        
                            <div class="panel-group" id="accordion">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#ico-tabelas-tab-1-<?php echo $ico->id ?>" aria-expanded="false" class="collapsed"><?php echo $this->idioma->getText("preVendas44");?> </a>
                                        </h5>
                                    </div>
                                    <div id="ico-tabelas-tab-1-<?php echo $ico->id ?>" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                                        <div class="panel-body">
                                            <table class="table table-bordered table-hover table-condensed table-striped" style="font-size: 12px;">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center"><strong><?php echo $this->idioma->getText("preVendas32");?></strong></th>
                                                        <th class="text-center"><strong><?php echo $this->idioma->getText("preVendas33");?></strong></th>
                                                        <th class="text-center"><strong><?php echo $this->idioma->getText("preVendas34");?></strong></th>
                                                        <th class="text-center"><strogn><?php echo $this->idioma->getText("preVendas35");?></strogn></th>
                                                        <th class="text-center">
                                                            <strogn><?php echo $this->idioma->getText("preVendas36");?></strogn>
                                                        </th>
                                                        <?php if (\Utils\Geral::isUsuario()) { ?>
                                                        <th class="text-center"><strogn><?php echo $this->idioma->getText("preVendas37");?></strogn></th>
                                                        <?php } ?>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    <?php 
                                                    foreach ($fasesIco as $fase) {
                                                        //$fase = new \Models\Modules\ICO\FaseIco();
                                                        $p = number_format(($fase->tokensParaVenda > 0 ? ($fase->tokensVendidos / $fase->tokensParaVenda * 100) : 0), 2, ".", "");
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $fase->nome ?></td>
                                                            <td class="text-center"><?php echo $fase->ordem ?></td>
                                                            <td class="text-center"><?php echo number_format($fase->tokensParaVenda, 8, ".", "") ?></td>
                                                            <td class="text-center"><?php echo number_format($fase->tokensVendidos, 8, ".", "") ?></td>
                                                            <td>
                                                                <div class="progress progress-bar-default" style="margin-bottom: 0px !important;">
                                                                    <div style="width: <?php echo $p ?>%" aria-valuemax="100" aria-valuemin="0"  aria-valuenow="<?php echo $p ?>" role="progressbar" class="progress-bar">
                                                                        <span class="sr-only"><?php echo $p ?>% <?php echo $this->idioma->getText("preVendas8");?></span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <?php if (\Utils\Geral::isUsuario()) { ?>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-info" onclick="modalCadastroFase('<?php echo \Utils\Criptografia::encriptyPostId($ico->id)?>', '<?php echo \Utils\Criptografia::encriptyPostId($fase->id)?>')" >
                                                                    <?php echo $this->idioma->getText("preVendas37");?>
                                                                </button>
                                                            </td>
                                                            <?php } ?>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion" href="#ico-tabelas-tab-2-<?php echo $ico->id ?>" class="collapsed" aria-expanded="false"><?php echo $this->idioma->getText("preVendas45");?></a>
                                        </h4>
                                    </div>
                                    <div id="ico-tabelas-tab-2-<?php echo $ico->id ?>" class="panel-collapse collapse" aria-expanded="false">
                                        <div class="panel-body">

                                            <table class="table table-bordered table-hover table-condensed table-striped" style="font-size: 12px;">
                                                <thead>
                                                    <tr>
                                                        <th>Moeda</th>
                                                        <?php 
                                                        foreach ($fasesTabelas as $dadosFase) {
                                                            ?>
                                                            <th class="text-center"><?php echo $dadosFase["fase"] ?></th>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tr>
                                                </thead>
                                                
                                                <tbody>
                                                    
                                                    <?php 
                                                    foreach ($tabela as $dadosTabela) {
                                                        if ($dadosTabela["id"] != 34) { 
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $dadosTabela["moeda"] ?></td>
                                                            
                                                            <?php 
                                                            foreach ($fasesTabelas as $dadosFase) {
                                                                
                                                                $volume = (isset($dadosTabela["fases"][$dadosFase["id"]]) ? $dadosTabela["fases"][$dadosFase["id"]]["volume"] : 0);
                                                                $preco = (isset($dadosTabela["fases"][$dadosFase["id"]]) ? $dadosTabela["fases"][$dadosFase["id"]]["dollar"] : 0);
                                                                
                                                                ?>
                                                                <td class="text-center"><?php echo number_format($volume, 8, ".", "") ?> <?php echo $dadosTabela["sigla"] ?></td>
                                                                <?php
                                                            }
                                                            ?>
                                                            
                                                        </tr>
                                                        <?php
                                                        }
                                                    }
                                                    ?>
                                                    
                                                </tbody>
                                                
                                            </table>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        
                    </div>
                    
                
                <div class="clearfix"></div>
            </div>
            
            
        </div>
        <?php
        
    }
    
    
    public function salvar($params) {
        try {
            $ico = new \Models\Modules\ICO\Ico();
            
            $ico->id = \Utils\Post::getEncrypted($params, "modalCadastrarIcoId", 0);
            $ico->descricao = \Utils\Post::get($params, "modalCadastrarIcoDescricao", null);
            $ico->logo = \Utils\File::get($params, "modalCadastrarIcoLogo", null, Array("PNG", "JPG"), null, "ico", true);
            $ico->email = \Utils\Post::get($params, "modalCadastrarIcoEmail", null);
            $ico->nome = \Utils\Post::get($params, "modalCadastrarIcoName", null);
            $ico->responsavel = \Utils\Post::get($params, "modalCadastrarIcoResponsavel", null);
            $ico->sigla = \Utils\Post::get($params, "modalCadastrarIcoSigla", null);
            $ico->site = \Utils\Post::get($params, "modalCadastrarIcoSite", null);
            $ico->whitepaper = \Utils\Post::get($params, "modalCadastrarIcoWhitepapper", null);
            $ico->idMoeda = \Utils\Post::getEncrypted($params, "modalCadastrarIcoIdMoeda", 0);
            $ico->exibirPublico = \Utils\Post::getBooleanAsInt($params, "modalCadastrarIcoExibirPublico", 0);
            
            $emailContaCliente = \Utils\Post::get($params, "modalCadastrarIcoEmailContaCliente", null);
            
            if (!empty($emailContaCliente)) {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $cliente = $clienteRn->getByEmail($emailContaCliente);
                if ($cliente != null) {
                    $ico->idClienteContaCredito = $cliente->id;
                } else {
                    throw new \Exception($this->idioma->getText("preVendas50"));
                }
            }
            
            $icoRn = new \Models\Modules\ICO\IcoRn();
            $icoRn->salvar($ico);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("preVendas9");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function alterarStatusAtivo($params) {
        try {
            $ico = new \Models\Modules\ICO\Ico();
            $ico->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $icoRn = new \Models\Modules\ICO\IcoRn();
            $icoRn->alterarStatusAtivo($ico);
            
            ob_start();
            $this->htmlItemIco($ico);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["codigo"] = $ico->id;
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("preVendas26");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function cadastroFaseIco($params) {
        try {
            $faseIco = new \Models\Modules\ICO\FaseIco();
            $faseIco->id = \Utils\Post::getEncrypted($params, "id", 0);
            $idIco = \Utils\Post::getEncrypted($params, "idIco", 0);
            
            if($faseIco->id > 0) {
                $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn();
                $faseIcoRn->conexao->carregar($faseIco);
            } else {
                $faseIco->idIco = $idIco;
            }
            
            $faseIco->id = \Utils\Criptografia::encriptyPostId(($faseIco->id > 0 ? $faseIco->id : 0));
            $faseIco->idIco = \Utils\Criptografia::encriptyPostId(($faseIco->idIco > 0 ? $faseIco->idIco : 0));
            
            $json["fase"] = $faseIco;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvarFaseIco($params) {
        
        try {
            
            $faseIco = new \Models\Modules\ICO\FaseIco();
            $faseIco->id = \Utils\Post::getEncrypted($params, "id", 0);
            $faseIco->idIco = \Utils\Post::getEncrypted($params, "idIco", 0);
            $faseIco->bonus = \Utils\Post::getNumeric($params, "bonus", 0);
            $faseIco->nome = \Utils\Post::get($params, "nome", null);
            $faseIco->ordem = \Utils\Post::get($params, "ordem", null);
            $faseIco->tokensParaVenda = \Utils\Post::getNumeric($params, "tokensParaVenda", 0);
            
            $faseIco->percentualBonusNewc = \Utils\Post::getNumeric($params, "percentualBonusNewc", 0);
            $faseIco->percentualComissaoExchange = \Utils\Post::getNumeric($params, "percentualComissaoExchange", 0);
            $faseIco->percentualBonusIcoNewcash = \Utils\Post::getNumeric($params, "percentualBonusIcoNewcash", 0);
            
            $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn();
            $faseIcoRn->salvar($faseIco);
            
            $ico = new \Models\Modules\ICO\Ico(Array("id" => $faseIco->idIco));
            $icoRn = new \Models\Modules\ICO\IcoRn();
            $icoRn->conexao->carregar($ico);
            
            ob_start();
            $this->htmlItemIco($ico);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["codigo"] = $ico->id;
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("preVendas39");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function carregarTabelaPreco($params) {
        try {
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            $idIco = \Utils\Post::getEncrypted($params, "idIco", 0);
            if ($moeda->id > 0) {
                
            } else {
                
            }
            
            $faseIco->id = \Utils\Criptografia::encriptyPostId(($faseIco->id > 0 ? $faseIco->id : 0));
            $faseIco->idIco = \Utils\Criptografia::encriptyPostId(($faseIco->idIco > 0 ? $faseIco->idIco : 0));
            
            $json["fase"] = $faseIco;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function comprar($params) {
        try {
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params,  "moeda", 0);
            
            $ico = new \Models\Modules\ICO\Ico();
            $ico->id = \Utils\Post::get($params, "ico", 0);
            
            $volume = \Utils\Post::getNumeric($params, "volume", 0);
            
            $depositoIcoRn = new \Models\Modules\ICO\DepositoIcoRn();
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($cliente == null) {
                throw new \Exception($this->idioma->getText("preVendas47"));
            }
            
            if (!$ico->id > 0) {
                throw new \Exception($this->idioma->getText("preVendas48"));
            }
            
            if($volume > 0){
                $depositoIcoRn->comprarTokens($moeda, $cliente, $volume, $ico->id);
                $json["sucesso"] = true;
                $json["mensagem"] = "Compra realizada com sucesso!";
            } else {
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
                $cliente->status = 2;
                $clienteRn->alterarStatusCliente($cliente);
            }
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function getSaldoDisponivel($params) {
        try {
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moeda->id = \Utils\Post::getEncrypted($params,  "moeda", 0);
            
            $cliente = \Utils\Geral::getCliente();
            
            if ($moeda->id > 1) { 
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $moeda->id, false, true);
            } else {
                $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
                $saldo = $contaCorrenteReaisRn->calcularSaldoConta($cliente, false, true);
            }
            
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedaRn->conexao->carregar($moeda);
            
            if ($saldo < 0) {
                $saldo = 0;
            }
            
            $json["moeda"] = $moeda->simbolo;
            $json["saldo"] = number_format($saldo, $moeda->casasDecimais, ",", "");
            
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}