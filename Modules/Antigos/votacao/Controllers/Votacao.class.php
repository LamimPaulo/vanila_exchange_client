<?php

namespace Modules\votacao\Controllers;

class Votacao {
    
    private $codigoModulo = "votacao";
    private $idioma = null;
    
    public function __construct() {
        //\Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("votacao", 'IDIOMA');
    }
    
    public function index($params) {
        
        try {


            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            $moeda = new \Models\Modules\Cadastro\Moeda();
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moeda->id = $configuracao->votacaoIdmoedaCompra;
            $moedaRn->carregar($moeda);
            $params["moeda"] = $moeda;   
            $params["configuracao"] = $configuracao;  
            $params["site"] = $configuracao->votacaoSiteMoeda;  
            
            $cliente = \Utils\Geral::getCliente();


            if (\Utils\Geral::isCliente()) {
                $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
                $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $configuracao->votacaoIdmoedaCompra, false);
                
                $params["saldo"] = 500000;//$saldo;
                $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

                # 18/07/2019 - Caique feature/online v2
                $clienteRn->setUltimaAtividade();
                # end
            }
            
            $comunidadeRn = new \Models\Modules\Cadastro\ComunidadeRn();
            $comunidades = $comunidadeRn->conexao->listar("ativo > 0 AND visivel_votacao > 0");
            $params["comunidades"] = $comunidades;
        } catch (\Exception $ex) {
            
        }
        \Utils\Layout::view("votacao", $params);
    }
    
    
    public function listar($params) {
        try {
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            $votacaoListagemRn = new \Models\Modules\Cadastro\VotacaoListagemRn();
            $cliente = \Utils\Geral::getCliente();
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            
            $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $configuracao->votacaoIdmoedaCompra, false);

            $sWhere = implode(" AND ", $where);
            $votacoes = $votacaoListagemRn->conexao->listar($sWhere, "data_cadastro DESC", null, null);

            $emVotacao = Array();
            $proximas = Array();
            $encerradas = Array();
            $pendentes = Array();
            $implantacao = Array();
            
            $clienteHasVotoRn = new \Models\Modules\Cadastro\ClienteHasVotoRn($votacaoListagemRn->conexao->adapter);

            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            foreach ($votacoes as $votacaoListagem) {
                $v = Array(
                    "votacao" => $votacaoListagem,
                    "votosAdquiridos" => $clienteHasVotoRn->getQuantidadeVotos($votacaoListagem)
                );
              
                if ($votacaoListagem->aprovado > 0) {
                    if ($votacaoListagem->ativo > 0) {
                        if($v["votosAdquiridos"] >= $votacaoListagem->votosNecessarios){
                          $implantacao[] = $v;                           
                        }
                        if ($votacaoListagem->aprovado > 0) {
                            if ($dataAtual->maior($votacaoListagem->dataFinal)) {
                                $encerradas[] = $v;                            
                            } else {
                                $emVotacao[] = $v;
                            }
                        }
                    } else {
                        if($v["votosAdquiridos"] > 0){
                           $encerradas[] = $v;     
                        } else {
                           //$proximas[] = $v;
                        }
                    }
                } else {
                    $pendentes[] = $v;
                }
            }
            
            $json["htmlEmVotacao"] = $this->htmlVotacao($emVotacao, 1);
            $json["htmlEncerradas"] = $this->htmlVotacao($encerradas, 2);
            $json["htmlFuturas"] =  $this->htmlVotacao($proximas, 3);
            $json["htmlPendentes"]  = $this->htmlVotacao($pendentes, 4);
            $json["htmlImplantacao"]  = $this->htmlVotacao($implantacao, 5);
            $json["saldo"] = number_format($saldo, 8, ".", "");
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlVotacao($lista, $tipo) {
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $dadosVotacao) {
                $this->htmlItemVotacao($dadosVotacao, $tipo);
            }
        } else {
            ?>
            <div class="col-lg-12 text-center">
                <?php echo $this->idioma->getText("votacaoSemVotacao{$tipo}"); ?>
            </div>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    private function htmlItemVotacao($dadosVotacao, $tipo) {
        $votacao = $dadosVotacao["votacao"];
        $votos = $dadosVotacao["votosAdquiridos"];
        $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
        $percentual = number_format(($votacao->votosNecessarios > 0 ? ($votos / $votacao->votosNecessarios * 100) : 0), 2, ".", "");
        
        $votacaoListagemHasComunidadeRn = new \Models\Modules\Cadastro\VotacaoListagemHasComunidadeRn();
        $comunidades = $votacaoListagemHasComunidadeRn->listar("id_votacao_listagem = {$votacao->id}", "id", null, null, true);

        ?>
        <div class="col-lg-12">
            <div class="contact-box">
                
                <div class="col-sm-3">
                    <div class="text-center">
                        <img alt="<?php echo $votacao->nomeMoeda ?>" class="img-circle m-t-xs img-responsive" style="max-width: 140px; max-height: 140px;" src="<?php echo URLBASE_CLIENT .  $votacao->logo ?>">
                    </div>
                </div>
                <div class="col-sm-6">
                    <h3><strong><?php echo $votacao->nomeMoeda ?> (<?php echo $votacao->sigla ?>)</strong></h3>
                    <p>
                        <strong><?php echo $this->idioma->getText("descricaoVotacao");?></strong><?php echo $votacao->descricao ?> <br>
                    </p>
                    <p>
                        
                        <a href="<?php echo $votacao->site ?>" target="_BLANK"><i class="fa fa-globe fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <a href="<?php echo $votacao->linkWhitepapper ?>" target="_BLANK"><i class="fa fa-file-pdf-o fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php foreach ($comunidades as $comunidade) { ?>
                        <?php if($comunidade->idComunidade == 1){ //FACEBOOK = 1?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><i class="fa fa-facebook-square fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php } ?>
                        
                        <?php if($comunidade->idComunidade == 2){ //TWITTER= 2?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><i class="fa fa-twitter-square fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php } ?>
                        
                        <?php if($comunidade->idComunidade == 3){ //TELEGRAM = 3?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><i class="fa fa-telegram fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php } ?>
                        
                        <?php if($comunidade->idComunidade == 4){ //DISCORD = 4?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><svg style="color: #676a6c !important; height: 22px !important; width: 26px !important; "aria-hidden="true" focusable="false" data-prefix="fab" data-icon="discord" class="svg-inline--fa fa-discord fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 450 450"><path fill="currentColor" d="M297.216 243.2c0 15.616-11.52 28.416-26.112 28.416-14.336 0-26.112-12.8-26.112-28.416s11.52-28.416 26.112-28.416c14.592 0 26.112 12.8 26.112 28.416zm-119.552-28.416c-14.592 0-26.112 12.8-26.112 28.416s11.776 28.416 26.112 28.416c14.592 0 26.112-12.8 26.112-28.416.256-15.616-11.52-28.416-26.112-28.416zM448 52.736V512c-64.494-56.994-43.868-38.128-118.784-107.776l13.568 47.36H52.48C23.552 451.584 0 428.032 0 398.848V52.736C0 23.552 23.552 0 52.48 0h343.04C424.448 0 448 23.552 448 52.736zm-72.96 242.688c0-82.432-36.864-149.248-36.864-149.248-36.864-27.648-71.936-26.88-71.936-26.88l-3.584 4.096c43.52 13.312 63.744 32.512 63.744 32.512-60.811-33.329-132.244-33.335-191.232-7.424-9.472 4.352-15.104 7.424-15.104 7.424s21.248-20.224 67.328-33.536l-2.56-3.072s-35.072-.768-71.936 26.88c0 0-36.864 66.816-36.864 149.248 0 0 21.504 37.12 78.08 38.912 0 0 9.472-11.52 17.152-21.248-32.512-9.728-44.8-30.208-44.8-30.208 3.766 2.636 9.976 6.053 10.496 6.4 43.21 24.198 104.588 32.126 159.744 8.96 8.96-3.328 18.944-8.192 29.44-15.104 0 0-12.8 20.992-46.336 30.464 7.68 9.728 16.896 20.736 16.896 20.736 56.576-1.792 78.336-38.912 78.336-38.912z"></path></svg></a>&nbsp;&nbsp;
                        <?php } ?>
                        
                        <?php if($comunidade->idComunidade == 5){ //MEDIUM = 5?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><i class="fa fa-medium fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php } ?>
                        
                        <?php if($comunidade->idComunidade == 6){ //INSTAGRAM = 6?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><i class="fa fa-instagram fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php } ?>
                        
                        <?php if($comunidade->idComunidade == 7){ //WHATSAPP = 7?> 
                        <a href="<?php echo $comunidade->link ?>" target="_BLANK"><i class="fa fa-whatsapp fa-2x" style="color: #676a6c !important;"></i></a>&nbsp;&nbsp;
                        <?php } ?>

                        <?php } ?>
                    </p>
                </div>
                
                <div class="col-sm-3">                    
                    <div class="row">
                        <div class="col col-xs-12 text-center">
                            <strong><?php echo $this->idioma->getText("progresso");?> <?php echo $percentual ?>%</strong>
                        </div>
                        <div class="col col-xs-6">
                            <p class="text-muted">
                                <span id="acquired-votes-<?php echo $votacao->id ?>"><?php echo number_format($votos, 0, ",", ".")?></span> <?php echo $this->idioma->getText("votos");?>
                            </p>
                        </div>
                        <div class="col col-xs-6 text-right">
                            <p class="text-muted">
                                <?php echo $this->idioma->getText("de");?> <span id="needed-votes-<?php echo $votacao->id ?>"><?php echo number_format($votacao->votosNecessarios, 0, ",", ".")?></span>
                            </p>
                        </div>
                        
                        <div class="col col-xs-12">
                            <div class="progress progress-bar-default">
                                <div style="width: <?php echo $percentual ?>%" aria-valuemax="100" aria-valuemin="0" id="progress-voting-<?php echo $votacao->id ?>" aria-valuenow="<?php echo $percentual ?>" role="progressbar" class="progress-bar">
                                    <span class="sr-only"><?php echo $percentual ?>% <?php echo $this->idioma->getText("progressoVotacao");?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($votacao->ativo > 0) {
                    if ($votos < $votacao->votosNecessarios && $tipo == 1) { ?>
                    <div class="input-group">
                        <input type="text" class="form-control votacao" id="votacao-<?php echo $votacao->id ?>"> 
                        <span class="input-group-btn"> 
                            <button type="button" onclick="votar(<?php echo $votacao->id ?>);" id="btn-votar-<?php echo $votacao->id ?>" class="btn btn-primary">
                                <?php echo $this->idioma->getText("btnVotar");?>
                            </button> 
                        </span>
                    </div>
                    <?php } ?>
                    
                    <?php if ($tipo != 4 && $tipo != 5) { ?>    
                    <div class="m-t-xs">
                        <?php echo $this->idioma->getText("encerraEm");?> <?php echo ($votacao->dataFinal != null ? $votacao->dataFinal->formatar(\Utils\Data::FORMATO_PT_BR) : "") ?>
                    </div>
                    <?php } }?>
                    
                    
                    <?php if ($votacao->aprovado > 0 && $votacao->ativo > 0 && $dataAtual->menor($votacao->dataInicial)) { ?>
                    <div class="">
                        <?php echo $this->idioma->getText("iniciaEm");?> <?php echo $votacao->dataInicial->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                    </div>
                    <?php } ?>
                </div>
                
                <div class="clearfix"></div>
            </div>
        </div>
        <?php
        
    }
    
    
    public function salvar($params) {
        try {
            $votacaoListagem = new \Models\Modules\Cadastro\VotacaoListagem();
            
            $votacaoListagem->id = 0;
            $votacaoListagem->casasDecimais = \Utils\Post::get($params, "modalCadastrarMoedaCasasDecimais", null);
            $votacaoListagem->descricao = \Utils\Post::get($params, "modalCadastrarMoedaDescricao", null);
            $votacaoListagem->email = \Utils\Post::get($params, "modalCadastrarMoedaEmail", null);
            $votacaoListagem->logo = \Utils\File::get($params, "modalCadastrarMoedaLogo", null, Array("PNG"), null, "votacao", true);
            $votacaoListagem->moedaBase = \Utils\Post::get($params, "modalCadastrarMoedaBase", null);
            $votacaoListagem->nomeMoeda = \Utils\Post::get($params, "modalCadastrarMoedaName", null);
            $votacaoListagem->responsavel = \Utils\Post::get($params, "modalCadastrarMoedaResponsavel", null);
            $votacaoListagem->sigla = \Utils\Post::get($params, "modalCadastrarMoedaSigla", null);
            $votacaoListagem->site = \Utils\Post::get($params, "modalCadastrarMoedaSite", null);
            $votacaoListagem->linkWhitepapper = \Utils\Post::get($params, "modalCadastrarMoedaLinkWhitepapper", null);
            $votacaoListagem->descricaoComunidade = \Utils\Post::get($params, "modalCadastrarDescricaoComunidade", null);
            $votacaoListagem->marketcap = \Utils\Post::get($params, "modalCadastrarMarketcap", null);
            
            
            $redesSociaisSelecionadas = \Utils\Post::get($params, "redesSociaisSelecionadas", null);

            $redesSociais = Array();
            if (!empty($redesSociaisSelecionadas)) {
                $redesSociaisSelecionadas = explode(",", $redesSociaisSelecionadas);
            } else {
                $redesSociaisSelecionadas = Array();
            }
            
            foreach ($redesSociaisSelecionadas as $rede) {
                $s = strtolower($rede);

                $votacaoListagemHasComunidade = new \Models\Modules\Cadastro\VotacaoListagemHasComunidade();
                $votacaoListagemHasComunidade->id = 0;
                $votacaoListagemHasComunidade->idVotacaoListagem = 0;
                $votacaoListagemHasComunidade->idComunidade = \Utils\Post::get($params, "codigo{$s}", null);;
                $votacaoListagemHasComunidade->membros = \Utils\Post::get($params, "membros{$s}", null);
                $votacaoListagemHasComunidade->link = \Utils\Post::get($params, "link{$s}", null);
                $redesSociais[] = $votacaoListagemHasComunidade;

            }

            
            $cliente = \Utils\Geral::getCliente();
            $votacaoListagem->idCliente = $cliente->id;
            
            
            $votacaoListagemRn = new \Models\Modules\Cadastro\VotacaoListagemRn();
            $votacaoListagemRn->salvar($votacaoListagem, $redesSociais);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("controllerVotacao1");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function aprovar($params) {
        try {
            
            $votacaoListagem = new \Models\Modules\Cadastro\VotacaoListagem();
            $votacaoListagem->id = \Utils\Post::getEncrypted($params, "id", 0);
            $votacaoListagem->dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $votacaoListagem->dataFinal = \Utils\Post::getData($params, "dataFinal", NULL, "23:59:59");
            
            $votacaoListagemRn = new \Models\Modules\Cadastro\VotacaoListagemRn();
            $votacaoListagemRn->aprovar($votacaoListagem, $votacaoListagem->dataInicial, $votacaoListagem->dataFinal);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("controllerVotacao2");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function negar($params) {
        try {
            $votacaoListagem = new \Models\Modules\Cadastro\VotacaoListagem();
            $votacaoListagem->id = \Utils\Post::getEncrypted($params, "id", 0);
            $votacaoListagemRn = new \Models\Modules\Cadastro\VotacaoListagemRn();
            $votacaoListagemRn->negar($votacaoListagem);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("controllerVotacao3");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function votar($params) {
        try {
            
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn->conexao->carregar($configuracao);
            
            $votacaoListagem = new \Models\Modules\Cadastro\VotacaoListagem();
            $votacaoListagem->id = \Utils\Post::get($params, "id", 0);
            
            if (!\Utils\Geral::isCliente()) {
                throw new \Exception($this->idioma->getText("controllerVotacao4"));
            }
            
            $cliente = \Utils\Geral::getCliente();
            
            $votos = \Utils\Post::get($params, "votos", 0);
            
            
                
            $clienteHasVoto = new \Models\Modules\Cadastro\ClienteHasVoto();
            $clienteHasVoto->id = 0;
            $clienteHasVoto->idCliente = $cliente->id;
            $clienteHasVoto->idVotacaoListagem = $votacaoListagem->id;
            $clienteHasVoto->votos = $votos;
            
            $clienteHasVotoRn = new \Models\Modules\Cadastro\ClienteHasVotoRn();
            $clienteHasVotoRn->salvar($clienteHasVoto);
            
            $votacaoListagemRn = new \Models\Modules\Cadastro\VotacaoListagemRn();
            $votacaoListagemRn->conexao->carregar($votacaoListagem);
            
            $votosRecebidos = $clienteHasVotoRn->getQuantidadeVotos($votacaoListagem);
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $saldo = $contaCorrenteBtcRn->calcularSaldoConta($cliente, $configuracao->votacaoIdmoedaCompra, false);
            
            $json["votosRecebidos"] = number_format($votosRecebidos, 0, ",", ".");
            $json["votosNecessarios"] = number_format($votacaoListagem->votosNecessarios, 0, ",", ".");
            $json["percentual"] = number_format(($votacaoListagem->votosNecessarios > 0 ? ($votosRecebidos / $votacaoListagem->votosNecessarios * 100) : 0), 2, ".", "");
            $json["saldo"] = number_format($saldo, 8, ".", "");
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("controllerVotacao5");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}