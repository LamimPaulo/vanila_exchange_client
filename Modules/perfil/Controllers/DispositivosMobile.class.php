<?php

namespace Modules\perfil\Controllers;

require_once getcwd() . '/Library/Models/Modules/Cadastro/DispositivoMobile.class.php';
require_once getcwd() . '/Library/Models/Modules/Cadastro/DispositivoMobileRn.class.php';

class DispositivosMobile {
    
    private  $codigoModulo = Array("perfil", "cadastros");
    
    function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("perfil", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function parear() {
        try {            
            $cliente = \Utils\Geral::getCliente();
            
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $auth = new \Models\Modules\Cadastro\Auth();
            
            $auth->idCliente = $cliente->id;
            $codigo = $authRn->salvar($auth, null, false);
            
            $hash = base64_encode($cliente->clientid . ":" . $cliente->apiKey . "]" . $codigo);
            //exit($hash);
            if (!file_exists("qrcodes/{$hash}.png")) {
                \QRcode::png($hash, "qrcodes/{$hash}.png");
            }

            $json["qrcode"] = $hash;
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        
        print json_encode($json);
    }
    
    public function ativarPareamento() {
        try {            
            $cliente = \Utils\Geral::getCliente();
            
            $dispositivoMobileRn = new \Models\Modules\Cadastro\DispositivoMobileRn();
            $lista = $dispositivoMobileRn->filtrar($cliente->id);
            
            if(sizeof($lista) > 0){
                $json["ativado"] = true;
            } else {
                $json["ativado"] = false;
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        
        print json_encode($json);
    }
    
    
    
    public function listar($params) {
        try {
            if (\Utils\Geral::isCliente()) {
                $cliente = \Utils\Geral::getCliente();
            } else {
                $cliente = new \Models\Modules\Cadastro\Cliente();
                $cliente->id = \Utils\Post::getEncrypted($params, "cliente", null);
            }
            
            $dispositivoMobileRn = new \Models\Modules\Cadastro\DispositivoMobileRn();
            $lista = $dispositivoMobileRn->filtrar($cliente->id);
            
            $json["html"] = $this->listaHtml($lista);
            $json["sucesso"] = true;
        } catch (\Exception $e) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        
        print json_encode($json);
    }
    
    
    private function listaHtml($lista) {
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $dispositivoMobile) {
                //$dispositivoMobile = new \Models\Modules\Cadastro\DispositivoMobile();
                
                ?>
                <tr>
                    <td class="text-center"><?php echo $dispositivoMobile->marcaFabricante ?></td>
                    <td class="text-center"><?php echo $dispositivoMobile->modelo ?></td>
                    <td class="text-center"><?php echo $dispositivoMobile->sistemaOperacional ?></td>
                    <td class="text-center"><?php echo $dispositivoMobile->versaoSo ?></td>
                    <td class="text-center"><?php echo $dispositivoMobile->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
                    <td class="text-center">
                        <button style="width: 63px !important;" type="button" class="btn btn-xs btn-<?php echo ($dispositivoMobile->status == 1 ? "danger" : "primary" )?>" onclick="alterarStatusAtivoDispositivoMobile('<?php echo \Utils\Criptografia::encriptyPostId($dispositivoMobile->id)?>')" >
                            <small><?php echo ($dispositivoMobile->status == 1 ? $this->idioma->getText("desativarMobile") : $this->idioma->getText("ativarMobile") )?></small>
                        </button>
                    </td>
                </tr>
                <?php
                
            }
        } else {
            ?>
            <tr>
                <td class="text-center" colspan="9"><?php echo $this->idioma->getText("respostaFiltroMobile") ?></td>
            </tr>
            <?php
        }
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function alterarStatusAtivo($params) {
        
        try {
            $dispositivoMobile = new \Models\Modules\Cadastro\DispositivoMobile();
            $dispositivoMobile->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $dispositivoMobileRn = new \Models\Modules\Cadastro\DispositivoMobileRn();
            $dispositivoMobileRn->conexao->carregar($dispositivoMobile);
            
            if ($dispositivoMobile->status == 1) {
                $dispositivoMobileRn->desativar($dispositivoMobile, \Utils\Geral::isUsuario());
            } else {
                $dispositivoMobileRn->ativar($dispositivoMobile, \Utils\Geral::isUsuario());
            }
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("statusMobile");
        } catch (\Exception $e) {
            exit(print_r($e));
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($e);
        }
        
        print json_encode($json);
    }
    
    
}