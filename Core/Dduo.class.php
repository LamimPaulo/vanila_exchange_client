<?php

//Apelidos usados apenas para facilitar a declaração das classes
use Io\BancoDados;
use Utils\Geral;
use Utils\Layout;


class Dduo {

    /**
     *
     * @var BancoDados
     */
    private static $_conexao = null;
    public $_dadosConfiguracao = null;

    function __construct() {
        $this->carregarConfiguracoes();

    }

    private function carregarConfiguracoes() {

        try {
            self::$_conexao = new BancoDados();

        } catch (\Exception $ex) {

            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }

        define('TITLE', "");
        define('DESCRIPTION', "");
        define('AUTHOR', "");
        define('KEYWORDS', "");
        define('ROBOTS', "index,follow");
        define('URL_SITE', "");
    }

    public function getTimeZone() {
        return self::$_conexao->getTimeZone();
    }

    public static function layout($_name, $_data = null) {
        return Layout::view($_name, $_data);
    }

    public static function conexao() {
        return self::$_conexao;
    }

    /**
     *
     * Método incluir automaticamente os arquivos de controllers no momento que for instanciar a classe.
     *
     *
     *
     * @access   public
     * @name     loader()
     * @since    0.1 ~ 0.2
     *
     * @param String $class Variável com o nome da classe
     */
    public static function loader($class) {
        /**
         * Caminho completo para o controller.
         * $var string
         */
        $class = null;
        $state = true;
        try {
            $modules = _MODULE_;
            if (!empty($modules)) {
                $file = ucfirst($class) . '.class.php';                                           //Formata o nome correspondendo com o controller que será incluído.
                $file = "Modules/" . _MODULE_ . "/Controllers/" . $file;                           //Caminho completo para o controller.

                if (file_exists($file))                                                             //Verifica se existe o controller na pasta.
                    require_once($file);                                                           //Inclui o arquivo antes de instanciar a classe.
                else
                    throw new Exception("[LOADER]:: FILENOTFOUND::: {$file}" . _MODULE_);                     //Se não for encontrado o arquivo gera um erro.
            }
        } catch (Exception $e) {
            echo "<script>alert('" . \Utils\Excecao::mensagem($e) . "')</script>";
        }
    }

    public function checaURL($_url) {
        $baseURL = array();
        $parameters = array();//Resultado dos parâmetros já formatados
        $lisPar = null;
        //Lista com todos os parâmetros sem tratamento passado por GET
        $uri = substr($_SERVER['REQUEST_URI'], 1);            //Retira do inicio a barra '/'

        try {
            //Verifica se os parâmetros estão sendo passado pela URL como GET
            if (strpos($_url, '?')) {
                $baseURL = parse_url($_url); //Faz parse da URL retornando um Array.
                parse_str($baseURL['query'], $urlArr); //Faz parse dos parâmetros que foi passado pela URL.

                $uri = $baseURL["path"];

                $parameters['_GET'] = $urlArr;  //Pega todos os valores passado por GET
            }


            if ($uri !== false) {
                //Verifica se existe parâmetro passado pela URL


                if (substr($uri, -1, 1) == '/') {
                    $lisPar = explode('/', substr($uri, 0, -1));
                } else if (substr($uri, 0, 1) == '/') {
                    $lisPar = explode('/', substr($uri, 1));
                } else {
                    $lisPar = explode('/', $uri);
                }



                $r = "";
                $indices = (sizeof($lisPar) >= 3 ? 3 : (sizeof($lisPar) == 2 ? 2 : 1));

                while(empty($r)) {

                    $rota = Array();
                    if ($indices > 0) {
                        for($i = 0; $i < $indices; $i++) {
                            $rota[] = $lisPar[$i];
                        }

                        $rota = implode("/", $rota);
                        $r = \Utils\Rotas::getRota($rota);

                        if ($r == null) {
                            $indices--;
                        }
                    } else {
                        break;
                    }

                }

                $parameters['_rota'] = $rota;
                for($i = $indices; $i < sizeof($lisPar); $i++) {
                    $parameters['_parameters'][] = $lisPar[$i];
                }

                $lisPar = explode("/", $r);

                foreach ($lisPar as $key=>$value) {
                    if ($key == 0) {
                        $parameters['_parameters']["_modules"] = $value;
                    } else if ($key==1) {
                        $parameters['_parameters']["_controller"] = $value;
                    } else if ($key==2) {
                        $parameters['_parameters']["_method"] = $value;
                    } else if ($key > 0) {
                        $parameters['_parameters'][] = $value;
                    }
                }

            }

            //$parameters['_GET'] = $_GET; //Pega todos os valores passado por GET
            $parameters['_POST'] = $_POST; //Pega todos os valores passado por POST
            $parameters['_FILE'] = $_FILES; //Pega todos os valores passado por FILES

            unset($_url, $baseURL, $lisPar, $uri, $key, $value);

            return $parameters;
        } catch (\Exception $e) {
            return \Utils\Excecao::mensagem($e);
        }
    }

    public function route($_parameters) {

        /*$name = 'log1.txt';


        $usr = Geral::getLogado();


        if (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], "newc.com.br") == false && strpos($_SERVER["HTTP_REFERER"], "/book") == false && strpos($_SERVER["HTTP_REFERER"], "/init") == false) {

            if (!in_array($_SERVER['REMOTE_ADDR'], Array("179.222.178.215")) ) {
                $content = $_SERVER["HTTP_REFERER"] ;
                $content .= "      " . $_SERVER['REMOTE_ADDR'];
                $content .= "      " . $_SERVER['QUERY_STRING'];
                if ($usr != null) {
                    $content .= "   USR = " . $usr->email;
                }
                $content .= "   POST = " . implode("|", $_POST);
                $content .= "   GET = " . implode("|", $_GET). "\n";
                $file = fopen($name, 'a');
                fwrite($file, $content);
                fclose($file);
            }
        }
       */


        $instance = null;                                                   //Guarda o objeto Class instanciado
        $controller = null;                                                 //Controller a ser chamado
        $module = null;                                                     //Módulo a ser instanciado
        $method = null;
        $state = false; //Flag inicia em FALSE
        try {
            $module = isset($_parameters['_parameters']['_modules']) ? $_parameters['_parameters']['_modules'] : null;
            $controller = isset($_parameters['_parameters']['_controller']) ? $_parameters['_parameters']['_controller'] : null;
            $method = isset($_parameters['_parameters']['_method']) ? $_parameters['_parameters']['_method'] : null;

            if (trim($controller) != '') {
                if (strpos($controller, '_') != false) {
                    $auxArray = explode('_', $controller);
                    $controller = '';
                    foreach ($auxArray as $aux) {
                        $controller.= ucfirst($aux) . '_';
                    }
                    $controller = substr($controller, 0, -1);
                }
            }




            //Verifica se o módulo foi passado pela URL
            if ($module == null || $module == 'index') {
                $module = 'trade';
            }
            if ($method == null) {
                $method = 'index';
            }

            $_parameters['_PATH'] = DIR_MODULES . $module . "/views";


            if ($module == 'error') {
                \Utils\Session::start(true);
                if ($controller == null || $controller == 'index') {
                    $controller = $module;

                }
                //Estas duas variáveis está na ordem de prioridade ao selecionar os modules sempre sendo como padrão
                $nameClassDefault = "Modules\\{$module}\\Controllers\\" . ucfirst($controller);


                if (file_exists(str_replace('\\', '/', "{$nameClassDefault}.class.php"))) {
                    define('_MODULE_', "{$module}");
                    $instance = new $nameClassDefault($_parameters);

                } else {
                    throw new \Exception("Não foi encontrado o módulo.", 100);
                }

                if (method_exists($instance, $method)) {

                    $instance->$method($_parameters);
                } else {
                    throw new \Exception("Método não existe.", 103);
                }
            } else {

                //Verifica se foi passado o controler por URL e se o valor é diferente de index
                if ($controller == null || $controller == 'index') {
                    $controller = 'book';
                }

                //Estas duas variáveis está na ordem de prioridade ao selecionar os Modules sempre sendo como padrão
                $nameClassDefault = "Modules/{$module}/Controllers/" . ucfirst($controller) .".class.php";

                \Utils\SessionHandler::initializeSession(strtolower($controller), strtolower($method));

                if (file_exists(str_replace('\\', '/', "{$nameClassDefault}"))) {

                    define('_MODULE_', "{$module}");

                    $nameClassInstancia = "\Modules\\" .$module . "\\Controllers\\" . ucfirst($controller);
                    //Carregar autoload do modulo
                    spl_autoload_register(function($nameClassDefault) {

                        $nameClassDefault = getcwd() . "/" . str_replace('\\', '/', "{$nameClassDefault}") .".class.php";

                        if (file_exists($nameClassDefault)){
                            require_once $nameClassDefault;
                        } else {
                            if(AMBIENTE == "producao"){

                                $mensagem = "Autoload Modules - Arquivo de classe não encontrado. - {$nameClassDefault} - " . " - _SERVER['QUERY_STRING']: " . $_SERVER['QUERY_STRING'] . " -  _SERVER['HTTP_REFERER']: " . $_SERVER["HTTP_REFERER"];
                                \Utils\Notificacao::notificar($mensagem, true, false, null, true);

                                throw new \Exception("Por favor, contate o suporte técnico. - Carregar Módulo", 500);
                            } else {
                                throw new \Exception("Autoload Modules - Arquivo de classe não encontrado. - {$nameClassDefault}", 500);
                            }
                        }
                    });

                    $instance = new $nameClassInstancia($_parameters);

                } else {
                    throw new \Exception("Não foi encontrado o arquivo do módulo .", 109);
                }

                //Verifica se o método foi passado por URL e se existe na classe instanciada
                if (method_exists($instance, $method)) {

                    $instance->$method($_parameters);
                } else {
                    throw new \Exception("Método não existe.", 103);
                }

            }
            if (is_object($instance)) {
                $state = true;
            }
            unset($_parameters, $controller, $module, $method, $instance);       //Limpa as variáveis da memória.
        } catch (\Exception $e) {
            /*echo $e->getFile() . " " . $e->getLine(). "<br>";
            echo $e->getTraceAsString() . "<br>";*/
            if(AMBIENTE == "desenvolvimento" || Geral::getCliente()->id == 15093064543895){
                exit(var_dump($e));
            }
            //Geral::redirect(URLBASE_CLIENT . 'error/error/index/' . $e->getCode());
            Geral::redirect(URLBASE_CLIENT . 'login');
        }
    }
}
