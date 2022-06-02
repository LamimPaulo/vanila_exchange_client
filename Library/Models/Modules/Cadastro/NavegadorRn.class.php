<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * Description of Notificação
 *
 * @author willianchiquetto
 */
class NavegadorRn { 
    
     /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Navegador());
        } else {
            $this->conexao = new GenericModel($adapter, new Navegador());
        }
    }
    
    public static function registrarLog($cliente = null) {

        if($cliente == null){
            $cliente = \Utils\Geral::getCliente();
        }
        
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        if (strpos($ip, ',') !== false) {
            $ip = substr($ip, 0, strpos($ip, ','));
        }

       $browser = self::VerificaNavegadorSO();
//        $location = \Utils\Geolocation::locate($ip);

        $navegador = new Navegador();
        $navegador->ipUltimoAcesso = $ip;
        $navegador->idCliente = $cliente->id;
        
//        if (isset($location->city)) {
//            $navegador->localizacao = ("{$location->city} - {$location->region_code}, {$location->country_name}");
//        } else {
//            $navegador->localizacao = "";
//        }
        
        //$navegador->rayId = $_SERVER['HTTP_CF_RAY'];
        $navegador->idSession = \Utils\Criptografia::encriptyPostId(session_id());
        $navegador->navegador = $browser["name"];
        $navegador->sistemaOperacional = $browser["platform"];
        
        //$navegadorRn = new NavegadorRn();
        //$navegador = $navegadorRn->salvar($navegador);
        
        return $navegador;
    }

    public function salvar(Navegador &$navegador) {
        
        $navegador->id = 0;
        $navegador->dataAcesso = new \Utils\Data(date("d/m/Y H:i:s"));
        $navegador->ativo = 1;
        
        $this->conexao->salvar($navegador);
        
        return $navegador;
        
    }
    
    public function navegadorByCliente($idCliente, $limit = 30){
        
        $query = "SELECT * FROM navegadores WHERE id_cliente = {$idCliente} ORDER BY data_acesso DESC limit {$limit};";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        return $result;
    }
    
    public function alterarStatus(Navegador &$navegador) {
        try {
            $this->conexao->carregar($navegador);
        } catch (\Exception $ex) {
            throw new \Exception("Navegador não encontrado.");
        }        
        $navegador->ativo = $navegador->ativo == 1 ? 0 : 1;
        $this->conexao->update(Array("ativo" => $navegador->ativo), Array("id" => $navegador->id));
    }
    
    public function revogarAcesso(Navegador &$navegador) {     
        $navegador->revogado = 1;
        $navegador->dataRevogado = date("Y-m-d H:i:s");
        $this->conexao->update(Array("revogado" => $navegador->revogado, "data_revogado" => $navegador->dataRevogado), Array("id" => $navegador->id));
    }
    
    public function ultimoAcessoCliente($idCliente){
        
        $ultimoAcesso = "";
        
        $query = "SELECT * FROM navegadores WHERE id_cliente = {$idCliente} order by data_acesso DESC LIMIT 1;";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        if (sizeof($result) > 0) {
            foreach ($result as $dados) {
                $ultimoAcesso = $dados;
            }
            
            return $ultimoAcesso;
        } else {
            return null;
        }
    }
    
    
    private static function VerificaNavegadorSO() {
        
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'Linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'Windows';
        }


        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        elseif(preg_match('/Firefox/i',$u_agent))
        {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        }
        elseif(preg_match('/Chrome/i',$u_agent))
        {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        }
        elseif(preg_match('/AppleWebKit/i',$u_agent))
        {
            $bname = 'AppleWebKit';
            $ub = "Opera";
        }
        elseif(preg_match('/Safari/i',$u_agent))
        {
            $bname = 'Apple Safari';
            $ub = "Safari";
        }

        elseif(preg_match('/Netscape/i',$u_agent))
        {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
        }


        $i = count($matches['browser']);
        if ($i != 1) {
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        $browser = array(
                'userAgent' => $u_agent,
                'name'      => $bname,
                'version'   => $version,
                'platform'  => $platform,
                'pattern'    => $pattern
        );

        return $browser;
    }

}
