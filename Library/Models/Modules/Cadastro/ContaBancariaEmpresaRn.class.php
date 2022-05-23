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
class ContaBancariaEmpresaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContaBancariaEmpresa());
        } else {
            $this->conexao = new GenericModel($adapter, new ContaBancariaEmpresa());
        }
    }
    
    public function salvar(ContaBancariaEmpresa &$contaBancariaEmpresa) {
        
        if ($contaBancariaEmpresa->id > 0) {
            $aux = new ContaBancariaEmpresa(Array("id" => $contaBancariaEmpresa->id));
            $this->conexao->carregar($aux);
            
            $contaBancariaEmpresa->ativo = $aux->ativo;
        } else {
            $contaBancariaEmpresa->ativo = 1;
        }
        
        if (empty($contaBancariaEmpresa->titular)) {
            throw new \Exception($this->idioma->getText("necessarioTitular"));
        }
        if (empty($contaBancariaEmpresa->cnpj)) {
            throw new \Exception($this->idioma->getText("necessarioCNPJ"));
        }
        if (empty($contaBancariaEmpresa->agencia)) {
            throw new \Exception($this->idioma->getText("necessarioAgencia"));
        }
        if (empty($contaBancariaEmpresa->conta)) {
            throw new \Exception($this->idioma->getText("necessarioConta"));
        }
        
        if (!$contaBancariaEmpresa->idBanco > 0) {
            throw new \Exception($this->idioma->getText("necessarioBanco"));
        }
        
        $tiposConta = Array(
            \Utils\Constantes::CONTA_CORRENTE,
            \Utils\Constantes::CONTA_POUPANCA
        );
        
        if (!in_array($contaBancariaEmpresa->tipoConta, $tiposConta)) {
            throw new \Exception($this->idioma->getText("tipoContaInvalido"));
        }
        
        unset($contaBancariaEmpresa->banco);
        $this->conexao->salvar($contaBancariaEmpresa);
        
    }
    
    
    public function carregar(ContaBancariaEmpresa &$contaBancariaEmpresa, $carregar = true, $carregarBanco = true) {
        if ($carregar) {
            $this->conexao->carregar($contaBancariaEmpresa);
        }
        
        if ($carregarBanco && $contaBancariaEmpresa->idBanco > 0) {
            $bancoRn = new BancoRn();
            $contaBancariaEmpresa->banco = new Banco(Array("id" => $contaBancariaEmpresa->idBanco));
            $bancoRn->conexao->carregar($contaBancariaEmpresa->banco);
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarBanco = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $contaBancariaEmpresa) {
            $this->carregar($contaBancariaEmpresa, false, $carregarBanco);
            $lista[] = $contaBancariaEmpresa;
        }
        return $lista;
    }
    
    public function alterarStatusAtivo(ContaBancariaEmpresa &$contaBancariaEmpresa) {
        try {
            $this->conexao->carregar($contaBancariaEmpresa);
        } catch (Exception $ex) {
            throw new \Exception($this->idioma->getText("contaNaoLocalizada"));
        }
        
        $contaBancariaEmpresa->ativo = ($contaBancariaEmpresa->ativo > 0 ? 0 : 1);
        $this->conexao->update(Array("ativo" => $contaBancariaEmpresa->ativo), Array("id" => $contaBancariaEmpresa->id));
    }
    
    public function getIdsBancosEmpresa() {
        
        $query = "SELECT b.id FROM bancos b INNER JOIN contas_bancarias_empresa c ON (c.id_banco = b.id) GROUP BY id;";
        $result = $this->conexao->adapter->query($query)->execute();
        $array = Array();
        foreach ($result as $d) {
            $array[] = $d["id"];
        }
        return $array;
    }
}

?>