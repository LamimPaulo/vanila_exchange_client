<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade MovimentacaoMes
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class MovimentacaoMesRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new MovimentacaoMes());
        } else {
            $this->conexao = new GenericModel($adapter, new MovimentacaoMes());
        }
    }
    
    public function salvar(MovimentacaoMes &$movimentacaoMes) {
        
        if (!$movimentacaoMes->idCliente > 0) {
            throw new \Exception($this->idioma->getText("clienteNaoInformado"));
        }
        
        if (strlen($movimentacaoMes->periodoRef) != 7) {
            throw new \Exception($this->idioma->getText("periodoReferenciaInvalido") . $movimentacaoMes->periodoRef.".");
        }
        
        if (!is_numeric($movimentacaoMes->deposito) || !$movimentacaoMes->deposito > 0) {
            $movimentacaoMes->deposito = 0;
        }
        if (!is_numeric($movimentacaoMes->compra) || !$movimentacaoMes->compra > 0) {
            $movimentacaoMes->compra = 0;
        }
        if (!is_numeric($movimentacaoMes->saque) || !$movimentacaoMes->saque > 0) {
            $movimentacaoMes->saque = 0;
        }
        if (!is_numeric($movimentacaoMes->venda) || !$movimentacaoMes->venda > 0) {
            $movimentacaoMes->venda = 0;
        }
        
        $movimentacoes = $this->conexao->listar("id_cliente = {$movimentacaoMes->idCliente} AND periodo_ref = '$movimentacaoMes->periodoRef'");
        
        //exit(number_format($movimentacaoMes->venda, 8, ".", ""));
        if (sizeof($movimentacoes) > 0) {
            $this->conexao->update(Array(
                "compra" => $movimentacaoMes->compra,
                "deposito" => $movimentacaoMes->deposito,
                "saque" => $movimentacaoMes->saque,
                "venda" => $movimentacaoMes->venda
            ),
            Array(
                "id_cliente" => $movimentacaoMes->idCliente,
                "periodo_ref" => $movimentacaoMes->periodoRef
            ));
        } else {
            $this->conexao->insert(Array(
                "compra" => $movimentacaoMes->compra,
                "deposito" => $movimentacaoMes->deposito,
                "saque" => $movimentacaoMes->saque,
                "venda" => $movimentacaoMes->venda,
                "id_cliente" => $movimentacaoMes->idCliente,
                "periodo_ref" => $movimentacaoMes->periodoRef,
                "pago" => 0,
                "valor_pago" => 0,
                "btc_pago" => 0
            ));
        }
        
    }
    
    
    public function marcarComoPago($periodo, $idCliente, $valorPago, $btcPago) {
        $this->conexao->update(Array(
            "pago" => 1,
            "valor_pago" => $valorPago,
            "btc_pago" => $btcPago
        ),
        Array(
            "id_cliente" => $idCliente,
            "periodo_ref" => $periodo
        ));
    }
    
    
    
    public function listarSomaComissoesUsuarios($periodoRef, array $idsUsuarios = Array()) {
        $queryUsuarios = (sizeof($idsUsuarios) > 0 ? " AND u.id IN (".implode(",", $idsUsuarios).")" : "" );
        $query = "SELECT "
            . "u.id, "
            . "u.nome, "
            . "u.data_expiracao, "
            . "u.email, "
            . "m.periodo_ref, "
            /*. "SUM((m.saque * (".\Utils\Constantes::getPercentualSaque()." / 100) * (c.comissao / 100))) AS comissao_saque, "
            . "SUM((m.compra * (".\Utils\Constantes::getPercentualCompra()." / 100) * (c.comissao / 100))) AS comissao_compra, "
            . "SUM((m.deposito * (".\Utils\Constantes::getPercentualDeposito()." / 100) * (c.comissao / 100))) AS comissao_deposito, "
            . "SUM((m.venda * (".\Utils\Constantes::getPercentualVenda()." / 100) * (c.comissao / 100))) AS comissao_venda, "*/
            . "SUM((m.saque  * (c.comissao / 100))) AS comissao_saque, "
            . "SUM((m.compra * (c.comissao / 100))) AS comissao_compra, "
            . "SUM((m.deposito * (c.comissao / 100))) AS comissao_deposito, "
            . "SUM((m.venda * (c.comissao / 100))) AS comissao_venda, "
            . "SUM(m.saque) AS saque, "
            . "SUM(m.compra) AS compra, "
            . "SUM(m.deposito) AS deposito, "
            . "SUM(m.valor_pago) AS valor_pago, "
            . "SUM(m.btc_pago) AS btc_pago, "
            . "SUM(m.venda) AS venda "
            . "FROM usuarios u INNER JOIN clientes c ON (u.id = c.id_usuario) "
            . "INNER JOIN movimentacoes_mes m ON (c.id = m.id_cliente) "
            . "WHERE u.tipo = '".\Utils\Constantes::VENDEDOR."' AND m.periodo_ref = '{$periodoRef}' AND c.status = 1 "
            . " {$queryUsuarios} "    
            . "GROUP BY u.id, u.nome, u.email, m.periodo_ref";
        
        //exit($query);
            
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $dados) {
            $lista[] = $dados;
        }
        return $lista;
    }
    
    
    public function getMesesReferenciaCadastrados(array $idsUsuarios = Array(), $decrescente = false) {
        $queryUsuarios = (sizeof($idsUsuarios) > 0 ? " AND c.id_usuario IN (".implode(",", $idsUsuarios).")" : "" );
        $query = "SELECT "
                    ." m.periodo_ref "
                    ." FROM movimentacoes_mes m  "
                    ." INNER JOIN clientes c ON (c.id = m.id_cliente) "
                    ." GROUP BY m.periodo_ref";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $dados) {
            $ref = explode("/", $dados["periodo_ref"]);
            $lista["{$ref[1]}-{$ref[0]}"] = $dados["periodo_ref"];
        }
        
        if ($decrescente) {
            krsort($lista);
        }
        
        return $lista;
    }
}

?>