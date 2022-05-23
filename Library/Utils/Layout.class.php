<?php
namespace Utils;
use Utils\Geral;
use \Models\Modules\Acesso\ModuloRn;
use \Models\Modules\Acesso\RotinaRn;
use Models\Modules\Cadastro\Usuario;

class Layout {
    public static function append($_name, $_data = null) {
        $state = false;
        $path = null;
        try {
            $path = DIR_LAYOUTS . "{$_name}.phtml";
            if (file_exists($path)) {
                if (is_array($_data)) {
                    $_data != null ? extract($_data) : null;
                }
                $state = include_once $path;
            }
            if ($state == false)
                throw new \Exception("[LAYOUTS]:: FILE NOT EXISTS: {$path} --- {$_data}");
        } catch (\Exception $e) {
            //echo "<script>alert('" . Excecao::mensagem($e) . "')</script>";
        }
    }

    public static function view($_view, $_data = null) {
        $state = false;
        $path = null;
        try {
            $path = DIR_MODULES . _MODULE_ . "/views/{$_view}.phtml";
            if (file_exists($path)) {
                if (is_array($_data))
                    $_data != null ? extract($_data) : null;

                include_once $path;
                $state = true;
            }else {
            //    throw new \Exception("View não existe.", 104);
            }
            return $state;
        } catch (\Exception $e) {
            //Geral::redirect('error/index/index/' . $e->getCode(), 2);
        }
    }

    public static function menu($_data) {
        try {
            $idioma = new \Utils\PropertiesUtils("menu", 'IDIOMA'); 
            $rota = $_data["_rota"];
            $dadosMenu = Geral::getMenu();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            if (Geral::isCliente()) {
                $permissoes = \Models\Modules\Acesso\PermissaoClienteRn::getPermissoesCliente(\Utils\Geral::getLogado());
            } else {

            }
            ob_start();
            foreach ($permissoes as $modulo) {
                $permitido = true;
            # 04/07/2019 - Caique - fix
                if(isset($modulo->id)){
                    if ($modulo->id == 2) {
                        if ($configuracao->statusCarteiras < 1) {
                            $permitido = false;
                        }
                    } 

                    if ($modulo->id == 15 || $modulo->id == 5) {
                        if (Geral::isCliente()) {
                            $cliente = Geral::getCliente();
                            if ($cliente->utilizaSaqueDepositoBrl < 1) {
                                $permitido = false;
                            }
                        }
                    }
                }

                if(isset($modulo['id'])){
                    if ($modulo['id'] == 2) {
                        if ($configuracao->statusCarteiras < 1) {
                            $permitido = false;
                        }
                    } 

                    if ($modulo['id'] == 15 || $modulo['id'] == 5) {
                        if (Geral::isCliente()) {
                            $cliente = Geral::getCliente();
                            if ($cliente->utilizaSaqueDepositoBrl < 1) {
                                $permitido = false;
                            }
                        }
                    }
                }


                if ($permitido) {
                    $url = (empty($modulo["url"]) ? "#" :trim($modulo["url"]));
                    $dropdown = (empty(trim($modulo["url"])) ? "class='dropdown-toggle' data-toggle='dropdown'" : "");
                    ?>
                    <li class="dropdown menu_new_style">
                        <a aria-expanded="false" role="button" href="<?php echo $url ?>" <?php echo $dropdown ?>>
                            <?php echo $idioma->getText("sidebarMenu{$modulo["id"]}"); ?>               
                            <?php if (empty(trim($modulo["url"]))) { ?>
                            <span class="caret"></span>
                            <?php } ?>
                        </a>   
                        <?php if (sizeof($modulo["rotinas"])) { ?>
                        <ul role="menu" class="dropdown-menu">
                            <?php foreach ($modulo["rotinas"] as $rotina) {
                                $url = (empty($rotina["url"]) ? "#" : $rotina["url"]);
                                ?>
                                <li class="dropdown">
                                    <a aria-expanded="false" role="button" href="<?php echo $url ?>" >
                                        <?php echo $idioma->getText("sidebarRotina{$rotina["id"]}"); ?>
                                    </a>
                                </li>
                                <?php } ?>
                        </ul>
                        <?php } ?>
                    <?php } ?>
                    </li>
                <?php } ?>
            <?php
            $menu = ob_get_contents();
            ob_end_clean();
            return $menu;
        } catch (\Exception $e) {
           // exit(Excecao::mensagem($e));
        }
    }
    
}
