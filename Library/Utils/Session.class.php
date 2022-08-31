<?php
namespace Utils;
/**
 * Classe para controlar as sessão dos utilizadores.
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Session{

    
    /**
     * Método que chama outro método de iniciar uma sessão.
     * 
     */
    public function __construct() {
        self::start();
    }
    
    
    /**
     * Método que verifica se foi iniciado a sessão.
     * 
     * @access   public
     * @name     state()
     * @since    0.1 ~ 0.2
     * 
     * @return boolean $state Retorna true quando a sessão já se encontra iniciada
     */
    public static function state()
    {
        $state = false;
        try{
            if (session_id())
                $state = true;
            return $state;
        }catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    
    /**
     * Método que inicia a sessão.
     * 
     * @access   public
     * @name     start()
     * @since    0.1 ~ 0.2
     * 
     * @return boolean $state Retorna true se foi iniciado a sessão
     */
    public static function start($readAndClose = true) {
        $state = false;
        try {
            if (!session_id()) {  
                
                ini_set("session.gc_maxlifetime", 60*60*24);
                session_set_cookie_params((60 * 60 * 24)); // define o tempo da sessao para 24 horas
                
                $state = session_start([
                    'read_and_close' => $readAndClose,
                    'cookie_lifetime' => 86400
                ]);
            }
            
            return $state;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    
    
    /**
     * Método que recebe um Array que o seu conteúdo será convertido em variáveis de sessão.
     * 
     * <code>
     * 
     * $arr = Array( 'nome' => 'MeuNome', 'idade' => 21);
     * 
     * Session::setData($arr);
     * 
     * </code>
     * 
     * @access   public
     * @name     setData()
     * @since    0.1 ~ 0.2
     * 
     * @param Array $_datas Recebe um Array com os valores para setar nas variáveis de sessão.
     * @return boolean
     */
    public static function setData($_datas)
    {
        $state = false;
        try{
            if(is_array($_datas))
            {
                foreach($_datas as $key => $value)
                    $_SESSION[$key] = $value;
                $state = true;
            }
            
            return $state;
            unset($_datas, $_value, $key, $value);
        }catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    
    /**
     * Método que recupera o valor de uma variável de sessão.
     * Quando precisar usar uma variável que está guardade em sessão deve passar apenas o nome da variável.
     * 
     * Ex:
     * <code>
     * $nome = Session::getData('nome');
     * </code>
     * 
     * @access   public
     * @name     getData()
     * @since    0.1 ~ 0.2
     * 
     * @param string $key Valor da chave da variável de sessão.
     * @return all $state Retorna o valor da variável de sessão caso não exista retorna false.
     */
    public static function getData($key)
    {
        $state = false;
        try{
            if(isset($_SESSION[$key]))
                $state = $_SESSION[$key];
            return $state;
        }catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    public static function closeWrite() {
        session_write_close();
    }
    
    /**
     * Método que lista todas as variáveis de sessão guardadas.
     * 
     * @access   public
     * @name     listVar()
     * @since    0.1 ~ 0.2
     * 
     * @return Array Todas as variáveis que existe na sessão atual.
     */
    
    public static function listVar()
    {
        $state = false;
        try{
            if(isset($_SESSION) && !empty($_SESSION))
                $state = $_SESSION;

            return $state;
        }catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    /**
     * Método que apaga todas as variáveis de sessão que limpa toda a sessão.
     * 
     * @access   public
     * @name     close()
     * @since    0.1 ~ 0.2
     * 
     * @return type
     */
    public static function close() 
    {
        try{
            
            //Session::start(false);
            session_unset();
            session_destroy();
            Session::closeWrite();
            session_regenerate_id(true);
        }catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    

}
?>
