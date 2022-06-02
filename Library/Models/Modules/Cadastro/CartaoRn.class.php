<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Banco
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class CartaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Cartao());
        } else {
            $this->conexao = new GenericModel($adapter, new Cartao());
        }
    }
    
    public function salvar(Cartao &$cartao, $confirmacaoSenha) {
        
        if (!empty($cartao->senha)) {
            if (empty($confirmacaoSenha)) {
                throw new \Exception("É necessário confirmar a senha do cartão");
            }
            
            if ($cartao->senha != $confirmacaoSenha) {
                throw new \Exception("Confirmação de senha inválida");
            }
        }
        
        if ($cartao->id > 0){
            
            $aux = new Cartao(Array("id" => $cartao->id));
            $this->conexao->carregar($aux);
            
            if (empty($cartao->senha)) {
                $cartao->senha = $aux->senha;
            }
            
            $cartao->ativo = $aux->ativo ? 1 : 0;
            $cartao->idPedidoCartao = $aux->idPedidoCartao;
        } else {
            
            if (empty($cartao->senha)) {
                throw new \Exception("A senha deve ser informada");
            }
            
            $cartao->ativo = 0;
            $cartao->idPedidoCartao = null;
        }
        
        
        if (strlen($cartao->numero) != 19) {
            throw new \Exception("Número do cartão inválido");
        }
        
        $result = $this->conexao->listar("numero = '{$cartao->numero}' AND id != {$cartao->id}", null, null, 1);
        if (sizeof($result) > 0) {
            throw new \Exception("O cartão já foi cadastrado no sistema");
        }
        
        if (strlen($cartao->validade) != 5) {
            throw new \Exception("Validade do cartão inválida");
        }
        $av = explode("/", $cartao->validade);
        if (sizeof($av) != 2) {
            throw new \Exception("Validade do cartão inválida");
        }
        
        
        if (intval($av[0])< 1 || intval($av[0]) > 12) {
            throw new \Exception("Mês de validado do cartão inválido");
        }
        
        if (intval("20".$av[0])< intval(date("y"))) {
            throw new \Exception("O ano de validade do cartão não pode ser inferior ao ano atual");
        }
        
        $bandeiras = Array(
            \Utils\Constantes::CARTAO_MASTER,
            \Utils\Constantes::CARTAO_VISA
        );
        
        if (!in_array($cartao->bandeira, $bandeiras)) {
            throw new \Exception("A bandeira do cartão deve ser informada");
        }
        
        unset($cartao->pedidoCartao);
        $this->conexao->salvar($cartao);
    }
    
    public function filtrar($filtro, $status, $comCliente, $bandeira) {
        
        $where = Array();
        
        if (!empty($filtro)) {
            $where[] = " ( LOWER(c.numero) LIKE LOWER('%{$filtro}%') "
                    . " OR LOWER(cl.nome) LIKE LOWER('%{$filtro}%') "
                    . " OR LOWER(cl.email) LIKE LOWER('%{$filtro}%') "
                    . "  ) ";
        }
        
        if ($status != 'T') {
            $where[] = " c.ativo = ".($status == "A" ? "'t'" : "'f'")." ";
        }
        
        if ($comCliente != "T") {
            $where[] = " p.id_cliente ".($comCliente == "S" ? " IS NOT NULL " : " IS NULL ")." ";
        }
        
        if ($bandeira != 'T') {
            $where[] = " c.bandeira = '{$bandeira}' ";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT c.* "
                . " FROM cartoes c "
                . " LEFT JOIN pedidos_cartoes p ON (c.id_pedido_cartao = p.id) "
                . " LEFT JOIN clientes cl ON (p.id_cliente = cl.id) "
                . " {$where} "
                . " ORDER BY c.numero ";
        //exit($query);
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $cartao = new Cartao($dados);
            $this->carregar($cartao, false, true);
            $lista[] = $cartao;
        }
        
        return $lista;
    }
    
    public function carregar(Cartao &$cartao, $carregar = true, $carregarPedidoCartao = true) {
        if ($carregar) {
            $this->conexao->carregar($cartao);
        }
        
        if ($cartao->idPedidoCartao > 0 && $carregarPedidoCartao) {
            $cartao->pedidoCartao = new PedidoCartao(Array("id" => $cartao->idPedidoCartao));
            $pedidoCartaoRn = new PedidoCartaoRn();
            $pedidoCartaoRn->carregar($cartao->pedidoCartao, true, true);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarPedidoCartao = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $cartao) {
            $this->carregar($cartao, false, $carregarPedidoCartao);
            $lista[] = $cartao;
        }
        return $lista;
    }
    
    
    public function atribuirPedidoCartao(Cartao $cartao) {
        
        if (!$cartao->id > 0) {
            throw new \Exception("A identificação do cartão deve ser informada");
        }
        
        if (!$cartao->idPedidoCartao > 0) {
            throw new \Exception("A identificação do pedido deve ser informada");
        }
        
        $this->conexao->update(
                Array(
                    "id_pedido_cartao" => $cartao->idPedidoCartao
                ), 
                Array(
                    "id" => $cartao->id
                )
            );
        
    }
    
    
    public function excluir(Cartao &$cartao) {
        
        try {
            
            $this->conexao->excluir($cartao);
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
    public function ativar(Cartao &$cartao) {
        
        try {
            
            $this->conexao->carregar($cartao);
            $cartao->ativo = 1;
            
            $this->conexao->update(Array("ativo" => 1), Array("id" => $cartao->id));
            
            if ($cartao->idPedidoCartao > 0) {
                $pedidoCartaoRn = new PedidoCartaoRn();
                $pedidoCartaoRn->conexao->update(Array("ativo" => 1), Array("id" => $cartao->idPedidoCartao));
            }
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
    }
    
    
    public function getByNumero($numero) {
        $result = $this->conexao->select(Array("numero" => $numero));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    
}

?>