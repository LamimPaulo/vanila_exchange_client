<?php

namespace Models\Modules\Acesso;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class LoginSistemaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LoginSistema());
        } else {
            $this->conexao = new GenericModel($adapter, new LoginSistema());
        }
    }
    
    
    public static function registrar() {
        
        try {
            
            $loginSistemaRn = new LoginSistemaRn();
            
            $cliente = \Utils\Geral::getCliente();
            $usuario = \Utils\Geral::getLogado();
            
            $loginSistema = new LoginSistema();
            $loginSistema->id = 0;
            $loginSistema->dataHora = new \Utils\Data(date("d/m/Y H:i:s"));
            
            if ($cliente != null && $cliente instanceof \Models\Modules\Cadastro\Cliente) {
                $loginSistema->idCliente = $cliente->id;
            }
            
            if ($usuario != null && $usuario instanceof \Models\Modules\Cadastro\Usuario) {
                $loginSistema->idUsuario = $usuario->id;
            }
            
            $loginSistema->ip = "{$_SERVER["REMOTE_ADDR"]}";
            $loginSistema->origem = "{$_SERVER["HTTP_REFERER"]}";
            $loginSistema->queryString = "{$_SERVER["QUERY_STRINGe"]}";
            $loginSistema->webkit = "{$_SERVER["HTTP_USER_AGENT"]}";
            
            $loginSistemaRn->conexao->salvar($loginSistema);
            
        } catch (\Exception $ex) {
            //throw new \Exception($ex);
        }
        
    }
    
    
    public static function getUltimoLogin(\Models\Modules\Cadastro\Cliente $cliente) {
        
        $loginsSistemaRn = new LoginSistemaRn();
        $result = $loginsSistemaRn->conexao->listar("id_cliente = {$cliente->id} ", "data_hora DESC", null, null);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public static function calcularTempoLogado(\Models\Modules\Cadastro\Cliente $cliente) {
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $clienteRn->conexao->carregar($cliente);
        
        if ($cliente->dataUltimoLogin != null && $cliente->ultimaAtividade != null) {
            $dif = $cliente->ultimaAtividade->diferenca($cliente->dataUltimoLogin);
            
            $time = 0;
            
            $temp = Array();
            $short = Array();
            if ($dif->y > 0) {
                if ($dif->y > 1) {
                    $temp[] = "{$dif->y} anos";
                } else {
                    $temp[] = "{$dif->y} ano";
                }
                
                $short[] = "{$dif->y}a";
                
                $time += (60 * 60 * 24 * 365 * $dif->y);
            }
            
            if ($dif->m > 0) {
                if ($dif->m > 1) {
                    $temp[] = "{$dif->m} meses";
                } else {
                    $temp[] = "{$dif->m} mês";
                }
                $short[] = "{$dif->m}m";
                
                $time += (60 * 60 * 24 * 30 * $dif->m);
            }
            
            if ($dif->d > 0) {
                if ($dif->d > 1) {
                    $temp[] = "{$dif->d} dias";
                } else {
                    $temp[] = "{$dif->d} dia";
                }
                $short[] = "{$dif->d}d";
                $time += (60 * 60 * 24 * $dif->d);
            }
            
            if ($dif->h > 0) {
                if ($dif->h > 1) {
                    $temp[] = "{$dif->h} horas";
                } else {
                    $temp[] = "{$dif->h} hora";
                }
                $short[] = "{$dif->h}h";
                
                $time += (60 * 60  * $dif->h);
            }
            
            if ($dif->i > 0) {
                if ($dif->i > 1) {
                    $temp[] = "{$dif->i} minutos";
                } else {
                    $temp[] = "{$dif->i} minuto";
                }
                $short[] = "{$dif->i}m";
                
                $time += (60  * $dif->i);
            }
            
            if ($dif->s > 0) {
                if ($dif->s > 1) {
                    $temp[] = "{$dif->s} segundos";
                } else {
                    $temp[] = "{$dif->s} segundo";
                }
                $short[] = "{$dif->y}s";
                
                $time += ($dif->s);
            }
            
            $long = implode(" ", $temp);
            $short = implode(" ", $short);
            
            return Array("stringLong" => $long,"stringShort" => $short , "seconds" => $time);
        }
        
        return Array("stringLong" => "","stringShort" => "", "seconds" => 0);
    }
    
}

?>