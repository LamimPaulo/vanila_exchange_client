<?php

namespace Modules\configuracoes\Controllers;

class NotificacaoMoeda {
    
    private $codigoModulo = "configuracoes";
    
    public function __construct($params) {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        \Utils\Layout::view("notificacao_moeda", $params);
    }
    
    public function listar($params) {
        
        try {
            
            $notificacaoMoedaRn = new \Models\Modules\Cadastro\NotificacaoMoedaRn();
            $notificacoes = $notificacaoMoedaRn->conexao->listar(null, "id DESC", null, null);
            $qtd = sizeof($notificacoes);
            $qtdAtiva = 0;
            
            ob_start();
            if (sizeof($notificacoes) > 0) {
                foreach ($notificacoes as $notificacao) {   
                    if($notificacao->publicacao == 1){
                        $qtdAtiva++;
                    }
                    $this->tableNotificacao($notificacao);
                }
            } else {
                ?>
                <li class="text-center">
                    <span>
                        Nenhuma notificação cadastrada
                    </span>
                </li>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["qtdNotificacaoAtiva"] = $qtdAtiva;
            $json["qtdNotificacao"] = $qtd;
            $json["html"] = $html;            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    private function tableNotificacao(\Models\Modules\Cadastro\NotificacaoMoeda $notificacao) {
        
        $totalVisualizacao = 0;
        $notificacaoHasLidoRn = new \Models\Modules\Cadastro\NotificacaoMoedaHasLidoRn();
        $result = $notificacaoHasLidoRn->somarQtdVisualizacao($notificacao->id);
        
        if(sizeof($result) > 0){
           foreach ($result as $dados){
            $totalVisualizacao = $dados["total"];
           }
        }       
        
        
        $moeda = new \Models\Modules\Cadastro\Moeda();
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
        $moeda->id = $notificacao->idMoeda;
        $moedaRn->carregar($moeda);     
        
        $usuario = new \Models\Modules\Cadastro\Usuario();
        $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
        $usuario->id = $notificacao->idUsuario;
        $usuarioRn->conexao->carregar($usuario);
        
        ?>
        <li>
            <div class="row">
                <div class="col-lg-12">
                <div class="panel panel-default">
                <div class="panel-body">
                <div class="col-lg-5">
                    <div class="row">
                        <i class="fa fa-circle" style="color: <?php echo $notificacao->publicacao ? "#00cf7a" : "#ff1e1e" ?>"></i>&nbsp;&nbsp;<?php echo $notificacao->publicacao ? "" : "Não " ?>Publicado
                    </div>
                    <div class="row m-t-xs">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Moeda</label></br>
                                <?php echo $moeda->nome ?>
                            </div> 
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Prioridade</label></br>
                                <?php echo $notificacao->prioridade == 1 ? "Alta" : $notificacao->prioridade == 2 ? "Média" : "Baixa" ?>
                            </div> 
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Criado Por</label></br>
                                <?php echo $usuario->nome ?>
                            </div> 
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Data Início</label></br>
                                <?php echo $notificacao->dataInicial->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">   
                                <label>Data Final</label></br>
                                <?php echo $notificacao->dataFinal->formatar(\Utils\Data::FORMATO_PT_BR) ?>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label>Visualização</label></br>
                                <?php echo $totalVisualizacao ?>                                
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                   <div class="row">&nbsp;</div>
                   <div class="col-lg-12 m-t-xs">
                       <div class="form-group">   
                            <label>Titulo PT</label></br>
                            <?php echo $notificacao->tituloPortugues ?>
                        </div>
                        <div class="form-group">   
                            <label>Titulo EN</label></br>
                            <?php echo $notificacao->tituloIngles ?>                                
                        </div>
                    </div>                    
                </div>
                <div class="col-lg-3">
                    <div class="row">&nbsp;</div>
                    <div class="row">
                        <a class="btn btn-primary btn-md m-t-xs" target="_blank" href="<?php echo URLBASE_CLIENT . \Utils\Rotas::R_NOTIFICACAO_MOEDA_ONLY_VIEW ?>/<?php echo \Utils\Criptografia::encriptyPostId($notificacao->id)?>" style="width: 120px;">Visualizar</a>
                    </div>                    
                    <div class="row">
                        <button class="btn btn-warning btn-md m-t-xs" onclick="javascript:editarNotificacao('<?php echo $notificacao->id ?>');" style="width: 120px;">Editar</button>
                    </div>
                    <div class="row">
                        <button class="btn btn-<?php echo $notificacao->publicacao ? "danger" : "primary" ?> btn-md m-t-xs" onclick="javascript:alterarStatusPublicado('<?php echo $notificacao->id ?>');" style="width: 120px;"><?php echo $notificacao->publicacao ? "Desativar" : "Ativar" ?></button>
                    </div>                      
                </div> 
                </div>
                </div>
                </div>
            </div>            
        </li>
        <?php
    }
    
    
    public function salvar($params) {
        try {            
            $notificacaoMoeda = new \Models\Modules\Cadastro\NotificacaoMoeda();
            $notificacaoMoedaRn = new \Models\Modules\Cadastro\NotificacaoMoedaRn();
            
            $notificacaoMoeda->id = \Utils\Post::get($params, "codigo", 0);

            $usuario = \Utils\Geral::getLogado();
            
            if ($notificacaoMoeda->id > 0) {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_NOTIFICACAO_MOEDA, \Utils\Constantes::EDITAR)) {
                    throw new \Exception("Você não tem permissão para editar registros");
                }
            } else {
                if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_NOTIFICACAO_MOEDA, \Utils\Constantes::CADASTRAR)) {
                    throw new \Exception("Você não tem permissão para criar registros");
                }
            }
            
            $notificacaoMoeda->tituloPortugues = \Utils\Post::getBase64($params, "tituloPort", null);
            $notificacaoMoeda->tituloIngles = \Utils\Post::getBase64($params, "tituloIng", null);
            $notificacaoMoeda->descricaoPortugues = \Utils\Post::getBase64($params, "descricaoPort", null);
            $notificacaoMoeda->descricaoIngles = \Utils\Post::getBase64($params, "descricaoIng", null);
            $notificacaoMoeda->dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $notificacaoMoeda->dataFinal = \Utils\Post::getData($params, "dataFinal", null, "00:00:00");
            $notificacaoMoeda->idUsuario = $usuario->id;
            $notificacaoMoeda->idMoeda = \Utils\Post::get($params, "idMoedas", null);
            $notificacaoMoeda->prioridade = \Utils\Post::get($params, "prioridade", null);
            $notificacaoMoeda->publicacao = \Utils\Post::get($params, "publicacao", 0);
            
            $notificacaoMoedaRn->salvar($notificacaoMoeda);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Notificação salva com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function editarNotificacao($params){
        try{
            $notificacaoMoeda = new \Models\Modules\Cadastro\NotificacaoMoeda();
            $notificacaoMoeda->id = \Utils\Post::get($params, "codigo", 0);
            $notificacaoMoedaRn = new \Models\Modules\Cadastro\NotificacaoMoedaRn();
            $notificacaoMoedaRn->conexao->carregar($notificacaoMoeda);
            
            $json["id"] = $notificacaoMoeda->id;
            $json["tituloPortugues"] = $notificacaoMoeda->tituloPortugues;
            $json["tituloIngles"] = $notificacaoMoeda->tituloIngles;
            $json["descricaoPortugues"] = $notificacaoMoeda->descricaoPortugues;            
            $json["descricaoIngles"] = $notificacaoMoeda->descricaoIngles;
            $json["dataInicial"] = $notificacaoMoeda->dataInicial->formatar(\Utils\Data::FORMATO_PT_BR);
            $json["dataFinal"] = $notificacaoMoeda->dataFinal->formatar(\Utils\Data::FORMATO_PT_BR);     
            $json["idMoeda"] = $notificacaoMoeda->idMoeda;
            $json["prioridade"] = $notificacaoMoeda->prioridade;
            $json["publicacao"] = $notificacaoMoeda->publicacao;
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Alteração efetuada com sucesso.";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);

    }
    
    public function alterarStatus($params) {
        try {
            $notificacaoMoeda = new \Models\Modules\Cadastro\NotificacaoMoeda();
            $notificacaoMoeda->id = \Utils\Post::get($params, "codigo", 0);
            
            $notificacaoMoedaRn = new \Models\Modules\Cadastro\NotificacaoMoedaRn();
            $notificacaoMoedaRn->alterarStatus($notificacaoMoeda);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Publicação de Notificação alterada.";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}