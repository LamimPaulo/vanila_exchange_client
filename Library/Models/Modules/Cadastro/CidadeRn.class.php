<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Cidade
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class CidadeRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Cidade());
        } else {
            $this->conexao = new GenericModel($adapter, new Cidade());
        }
    }
    
    public function salvar(Cidade &$cidade) {
        if (strlen($cidade->nome) <= 0) {
            throw new \Exception($this->idioma->getText("necessarioInformarCidade"));
        }
        
        if (strlen($cidade->codigo) != 7) {
            throw new \Exception($this->idioma->getText("codigoCidadeInvalido"));
        }
        
        if (!$cidade->idEstado > 0) {
            throw new \Exception($this->idioma->getText("necessarioInformarEstado"));
        }
        unset($cidade->estado);
        $this->conexao->salvar($cidade);
    }
    
    public function carregar(Cidade &$cidade, $carregar = true, $carregarEstado = true) {
        if ($carregar) {
            $result = $this->conexao->select(
                        Array("codigo" => $cidade->codigo)
                    );
            if (sizeof($result) > 0) {
                $cidade = $result->current();
            } else {
                throw new \Exception($this->idioma->getText("cidadeNaoLocalizada"));
            }
        }
        
        if ($carregarEstado && $cidade->idEstado > 0) {
            $cidade->estado = new Estado(Array("id" => $cidade->idEstado));
            $estadoRn = new EstadoRn();
            $estadoRn->conexao->carregar($cidade->estado);
        }
        
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarEstado = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $cidade) {
            $this->carregar($cidade, false, $carregarEstado);
            $lista[] = $cidade;
        }
        return $lista;
    }
    
    
    public function getCidadesBySiglaEstado($sigla, $carregarEstado = true) {
        $query = "SELECT c.* FROM cidades c INNER JOIN estados e ON (c.id_estado = e.id) WHERE e.sigla = '{$sigla}' ORDER BY c.nome;";
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        foreach ($result as $dados) {
            $cidade = new Cidade($dados);
            if ($carregarEstado) {
                $this->carregar($cidade, false, true);
            }
            $lista[] = $cidade;
        }
        return $lista;
    }
    
    public static function get($idCidade) {
        $cidade = new Cidade(Array("id" => $idCidade));
        $cidadeRn = new CidadeRn();
        $cidadeRn->carregar($cidade);
        return $cidade;
    }
}

?>