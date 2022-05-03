<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ResgateEstabelecimentoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ResgateEstabelecimento());
        } else {
            $this->conexao = new GenericModel($adapter, new ResgateEstabelecimento());
        }
    }
    
    public function salvar(ResgateEstabelecimento &$resgateEstabelecimento) {
        $resgateEstabelecimento->id = 0;
        $resgateEstabelecimento->data = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (!$resgateEstabelecimento->idCliente > 0) {
            throw new \Exception("O cliente precisa ser informado");
        }
        
        if (!$resgateEstabelecimento->idEstabelecimento > 0) {
            throw new \Exception("O estabelecimento precisa ser informado");
        }
        
        if (!$resgateEstabelecimento->idMoeda > 0) {
            throw new \Exception("É necessário informar o id da moeda");
        }
        
        $tipos = Array(
            \Utils\Constantes::ENTRADA,
            \Utils\Constantes::SAIDA
        );
        
        if (!in_array($resgateEstabelecimento->tipo, $tipos)) {
            throw new \Exception("Tipo de movimento inválido");
        }
        
        unset($resgateEstabelecimento->moeda);
        unset($resgateEstabelecimento->cliente);
        unset($resgateEstabelecimento->estabelecimento);
        unset($resgateEstabelecimento->contaCorrenteBtc);
        unset($resgateEstabelecimento->contaCorrenteReais);
        
        $this->conexao->salvar($resgateEstabelecimento);
    }
    
    
    public function carregar(ResgateEstabelecimento &$resgateEstabelecimento, $carregar = true, $carregarContaCorrenteBtc = true, $carregarContaCorrenteReais = true, $carregarCliente = true, $carregarEstabelecimento = true) {
        
        if ($carregar) {
            $this->conexao->carregar($resgateEstabelecimento);
        }
        
        if ($carregarContaCorrenteBtc && $resgateEstabelecimento->idContaCorrenteBtc > 0) {
            $resgateEstabelecimento->contaCorrenteBtc = new ContaCorrenteBtc(Array("id" => $resgateEstabelecimento->idContaCorrenteBtc));
            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
            $contaCorrenteBtcRn->carregar($resgateEstabelecimento->contaCorrenteBtc, true, false, true);
        }
        
        if ($carregarContaCorrenteReais && $resgateEstabelecimento->idContaCorrenteReais > 0) {
            $resgateEstabelecimento->contaCorrenteReais = new ContaCorrenteReais(Array("id" => $resgateEstabelecimento->idContaCorrenteReais));
            $contaCorrenteReaisRn = new ContaCorrenteReaisRn($this->conexao->adapter, false);
            $contaCorrenteReaisRn->carregar($resgateEstabelecimento->contaCorrenteReais, true, false, true);
        }
        
        if ($carregarCliente && $resgateEstabelecimento->idCliente > 0) {
            $resgateEstabelecimento->cliente = new Cliente(Array("id" => $resgateEstabelecimento->idCliente));
            $clienteRn = new ClienteRn($this->conexao->adapter);
            $clienteRn->conexao->carregar($resgateEstabelecimento->cliente);
        }
        
        if ($carregarEstabelecimento && $resgateEstabelecimento->idEstabelecimento > 0) {
            $resgateEstabelecimento->estabelecimento = new Estabelecimento(Array("id" => $resgateEstabelecimento->idEstabelecimento));
            $estabelecimentoRn = new EstabelecimentoRn($this->conexao->adapter);
            $estabelecimentoRn->carregar($resgateEstabelecimento->estabelecimento, true, false, false);
        }
        
        
        if ($resgateEstabelecimento->idMoeda > 0) {
            $resgateEstabelecimento->moeda = new Moeda(Array("id" => $resgateEstabelecimento->idMoeda));
            $moedaRn = new MoedaRn($this->conexao->adapter);
            $moedaRn->carregar($resgateEstabelecimento->moeda);
        }
    }
    
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarContaCorrenteBtc = true, $carregarContaCorrenteReais = true, $carregarCliente = true, $carregarEstabelecimento = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        
        $lista = Array();
        
        foreach ($result as $resgateEstabelecimento) {
            $this->carregar($resgateEstabelecimento, false, $carregarContaCorrenteBtc, $carregarContaCorrenteReais, $carregarCliente, $carregarEstabelecimento);
            $lista[] = $resgateEstabelecimento;
        }
        
        return $lista;
    }
    
}

?>