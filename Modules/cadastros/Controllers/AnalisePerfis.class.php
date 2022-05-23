<?php

namespace Modules\cadastros\Controllers;

class AnalisePerfis {
    
    private  $codigoModulo = "cadastros";
    
    public static $motivos = Array(
        "Documentos inválidos ou informações divergentes.",
        "Outro motivo qualquer"
    );
    
    public function __construct() {
        \Utils\Validacao::acesso($this->codigoModulo);
        
        if (!(\Utils\Geral::isUsuario() || \Utils\Geral::getLogado()->tipo == \Utils\Constantes::ADMINISTRADOR)) {
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
    }
    
    public function index($params) {
        try {
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("analise_perfis", $params);
    }
    
    public function listar($params) {
        try {
            $filtro = \Utils\Post::get($params, "filtro", "");
            
            $clienteHasLicencaRn = new \Models\Modules\Cadastro\ClienteHasLicencaRn();
            $lista = $clienteHasLicencaRn->filtrarSolicitacoes($filtro);
            
            
            $json["html"] = $this->htmlLista($lista);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlLista($lista) {
        ob_start();
        
        if (sizeof($lista)) {
            foreach ($lista as $clienteHasLicenca) {
                $this->htmlItemLista($clienteHasLicenca);
            }
            
        } else {
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-12 text-center">
                        Nenhuma solicitação de upgrade de Licença
                    </div>
                </div>
            </li>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    private function htmlItemLista(\Models\Modules\Cadastro\ClienteHasLicenca $clienteHasLicenca) {
        ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-6">
                        <strong>Cliente: </strong><?php echo $clienteHasLicenca->cliente->nome ?>
                    </div>
                    <div class="col col-lg-6">
                        <strong>Email: </strong><?php echo $clienteHasLicenca->cliente->email ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-lg-6">
                        <strong>Data da solicitação: </strong><?php echo $clienteHasLicenca->dataAdesao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) ?>
                    </div>
                    <div class="col col-lg-6">
                        <strong>Licença Solicitada: </strong><?php echo $clienteHasLicenca->licencaSoftware->nome ?>
                    </div>
                </div>
                
                
                <?php if (\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PERFIL_UPGRADE_ANALISAR, \Utils\Constantes::EDITAR)) { ?>
                <div class="row">
                    <div class="col col-lg-6 text-center">
                        <button class="btn btn-danger" onclick="dialogNegarSolicitacaoUpgrade('<?php echo \Utils\Criptografia::encriptyPostId($clienteHasLicenca->id) ?>');">
                            Negar Upgrade
                        </button>
                    </div>
                    <div class="col col-lg-6 text-center">
                        <button class="btn btn-primary" onclick="dialogAprovarSolicitacaoUpgrade('<?php echo \Utils\Criptografia::encriptyPostId($clienteHasLicenca->id) ?>');">
                            Aprovar Upgrade
                        </button>
                    </div>
                </div>
                <?php } ?>
                
            </li>
        <?php
    }
    
    public function aprovarSolicitacaoUpgrade($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PERFIL_UPGRADE_ANALISAR, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para executar essa ação");
            }
            
            $clienteHasLicenca = new \Models\Modules\Cadastro\ClienteHasLicenca();
            $clienteHasLicenca->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $clienteHasLicencaRn = new \Models\Modules\Cadastro\ClienteHasLicencaRn();
            $clienteHasLicencaRn->aprovar($clienteHasLicenca);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function negarSolicitacaoUpgrade($params) {
        try {
            if (!\Models\Modules\Acesso\RotinaRn::validar(\Utils\Rotas::R_PERFIL_UPGRADE_ANALISAR, \Utils\Constantes::EDITAR)) {
                throw new \Exception("Você não tem permissão para executar essa ação");
            }
            $clienteHasLicenca = new \Models\Modules\Cadastro\ClienteHasLicenca();
            $clienteHasLicenca->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $posMotivo = \Utils\Post::get($params, "motivo", -1);
            
            if ($posMotivo < 0) {
                throw new \Exception("Selecione um motivo na lista");
            }
            
            if (!isset(self::$motivos[$posMotivo])) {
                throw new \Exception("Motivo inválido");
            }
            
            $motivo = self::$motivos[$posMotivo];
            
            $clienteHasLicencaRn = new \Models\Modules\Cadastro\ClienteHasLicencaRn();
            $clienteHasLicencaRn->negar($clienteHasLicenca, $motivo);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}

