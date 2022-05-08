<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 * 
 */
class SaldoClienteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new SaldoCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new SaldoCliente());
        }
    }
    
    
    public static function existe($idCliente, $idMoeda) {
        $saldoClienteRn = new SaldoClienteRn();
        
        $result = $saldoClienteRn->conexao->select("id_cliente = {$idCliente} AND id_moeda = {$idMoeda} ");
        return sizeof($result) > 0;
    }
    
    public static function criar($idCliente, $idMoeda) {
        if (!$idCliente > 0) {
            throw new \Exception("A identificação do cliente precisa ser informada");
        }
        
        
        if (!$idMoeda > 0) {
            throw new \Exception("A identificação da moeda precisa ser informada");
        }
        
        $saldoClienteRn = new SaldoClienteRn();
        $saldoClienteRn->conexao->insert(
            Array(
                "id_cliente" => $idCliente,
                "id_moeda" => $idMoeda,
                "saldo" => 0,
                "saldo_bloqueado" => 0,
                "data_ultima_atualizacao" => date("Y-m-d H:i:s")
            )
        );
    }
    
    public static function creditar($valor, $idCliente, $idMoeda) {
        $saldoClienteRn = new SaldoClienteRn();
  
        if (!is_numeric($valor) || !$valor > 0) {
            throw new \Exception("O valor precisa ser maior que zero");
        }
        
        if (!self::existe($idCliente, $idMoeda)) {
            self::criar($idCliente, $idMoeda);
        }
        
        $v = number_format($valor, 25, ".", "");
        $update = "UPDATE saldos_clientes SET saldo = saldo + {$v} WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda};";
        $saldoClienteRn->conexao->adapter->query($update)->execute();
        
    }
    
    public static function debitar($valor, $idCliente, $idMoeda) {
        if (!is_numeric($valor) || !$valor > 0) {
            throw new \Exception("O valor precisa ser maior que zero");
        }
        
        if (!self::existe($idCliente, $idMoeda)) {
            self::criar($idCliente, $idMoeda);
        }
        
        $v = number_format($valor, 25, ".", "");
        $update = "UPDATE saldos_clientes SET saldo = saldo - {$v} WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda};";
        
        $saldoClienteRn = new SaldoClienteRn();
        $saldoClienteRn->conexao->adapter->query($update)->execute();
        
    }
    
    
    public static function creditarSaldos($valor, $valorBloqueado, $idCliente, $idMoeda) {
        if (!is_numeric($valor) || !$valor > 0) {
            throw new \Exception("O valor precisa ser maior que zero");
        }
        
        if (!self::existe($idCliente, $idMoeda)) {
            self::criar($idCliente, $idMoeda);
        }
        
        $v = number_format($valor, 25, ".", "");
        $vb = number_format($valorBloqueado, 25, ".", "");
        $update = "UPDATE saldos_clientes SET saldo = saldo + {$v}, saldo_bloqueado = saldo_bloqueado + {$vb} WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda};";
        
        $saldoClienteRn = new SaldoClienteRn();
        $saldoClienteRn->conexao->adapter->query($update)->execute();
        
    }
    
    public static function debitarSaldos($valor, $valorBloqueado, $idCliente, $idMoeda) {
        if (!is_numeric($valor) || !$valor > 0) {
            throw new \Exception("O valor precisa ser maior que zero");
        }
        
        if (!self::existe($idCliente, $idMoeda)) {
            self::criar($idCliente, $idMoeda);
        }
        
        $v = number_format($valor, 25, ".", "");
        $vb = number_format($valorBloqueado, 25, ".", "");
        $update = "UPDATE saldos_clientes SET saldo = saldo - {$v}, saldo_bloqueado = saldo_bloqueado - {$vb} WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda};";
        
        $saldoClienteRn = new SaldoClienteRn();
        $saldoClienteRn->conexao->adapter->query($update)->execute();
        
    }
    
    public static function creditarBloqueado($valor, $idCliente, $idMoeda) {
        if (!is_numeric($valor) || !$valor > 0) {
            throw new \Exception("O valor precisa ser maior que zero");
        }
        
        if (!self::existe($idCliente, $idMoeda)) {
            self::criar($idCliente, $idMoeda);
        }
        
        $v = number_format($valor, 25, ".", "");
        $update = "UPDATE saldos_clientes SET saldo_bloqueado = saldo_bloqueado + {$v} WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda};";
        
        $saldoClienteRn = new SaldoClienteRn();
        $saldoClienteRn->conexao->adapter->query($update)->execute();
        
    }
    
    public static function debitarBloqueado($valor, $idCliente, $idMoeda) {
        if (!is_numeric($valor) || !$valor > 0) {
            throw new \Exception("O valor precisa ser maior que zero");
        }
        
        if (!self::existe($idCliente, $idMoeda)) {
            self::criar($idCliente, $idMoeda);
        }
        
        $v = number_format($valor, 25, ".", "");
        $update = "UPDATE saldos_clientes SET saldo_bloqueado = saldo_bloqueado - {$v} WHERE id_cliente = {$idCliente} AND id_moeda = {$idMoeda};";
        
        $saldoClienteRn = new SaldoClienteRn();
        $saldoClienteRn->conexao->adapter->query($update)->execute();
        
    }
    
    public static function getSaldo($idCliente, $idMoeda = 0, $bloqueado = false, $desconsiderarCredito = false) {
        $sWhereMoeda = "";
        
        if ($idMoeda > 0) {
            $sWhereMoeda = " AND m.id = {$idMoeda} ";
        }
        
        $query = "SELECT 
                    m.simbolo,
                    m.id,
                    m.nome,
                    (s.saldo - s.saldo_bloqueado) AS saldo,
                    s.saldo_bloqueado AS bloqueado
                    FROM  moedas m  LEFT JOIN saldos_clientes s ON (s.id_moeda = m.id)
                    WHERE id_cliente = {$idCliente} $sWhereMoeda;";
        
        $saldoClienteRn = new SaldoClienteRn();            
        $dados = $saldoClienteRn->conexao->adapter->query($query)->execute();
        
        
        
        if ($idMoeda > 0) {
            foreach ($dados as $d) {
                
                $credito = 0;
                if (!$desconsiderarCredito) {
                    $clienteHasCredito = ClienteHasCreditoRn::get($idCliente, $d["id"], true);

                    if ($clienteHasCredito != null && $clienteHasCredito->ativo > 0) {
                        $credito = number_format($clienteHasCredito->volumeCredito, 8, ".", "");
                    }
                }
                
                if ($bloqueado) {
                    return Array(
                        "id" => $d["id"], 
                        "nome" => $d["nome"], 
                        "simbolo" => $d["simbolo"], 
                        "saldo" => number_format($d["saldo"] + $credito, 25, ".", ""), 
                        "bloqueado" => number_format($d["bloqueado"], 25, ".", "")
                    );
                } else {
                    return number_format($d["saldo"]+$credito, 25, ".", "");
                }
            }
        } else {
            $lista = Array();
            foreach ($dados as $d) {
                
                $credito = 0;
                if (!$desconsiderarCredito) {
                    $clienteHasCredito = ClienteHasCreditoRn::get($idCliente, $d["id"], true);

                    if ($clienteHasCredito != null && $clienteHasCredito->ativo > 0) {
                        $credito = number_format($clienteHasCredito->volumeCredito, 8, ".", "");
                    }
                }
                
                $s= Array(
                    "id" => $d["id"], 
                    "nome" => $d["nome"], 
                    "simbolo" => $d["simbolo"], 
                    "saldo" => number_format($d["saldo"] + $credito, 25, ".", "")
                );
                
                if ($bloqueado) {
                    $s["bloqueado"] = number_format($d["bloqueado"], 25, ".", "");
                }
                
                $lista[] = $s;
            }
            return $lista;
        }
        
        
        
    }
    
}

?>