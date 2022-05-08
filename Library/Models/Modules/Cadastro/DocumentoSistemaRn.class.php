<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade DocumentoSistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class DocumentoSistemaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new DocumentoSistema());
        } else {
            $this->conexao = new GenericModel($adapter, new DocumentoSistema());
        }
    }
    
    public function salvar(DocumentoSistema &$documentoSistema) {
        
        if ($documentoSistema->id > 0) { 
            $aux = new DocumentoSistema(Array("id" => $documentoSistema->id));
            $this->conexao->carregar($aux);
            
            $documentoSistema->dataCriacao = $aux->dataCriacao;
        } else {
            $documentoSistema->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
        }
        
        if (empty($documentoSistema->descricao)) {
            throw new \Exception($this->idioma->getText("descricaoInvalida"));
        }
        
        if (empty($documentoSistema->codigo)) {
            throw new \Exception($this->idioma->getText("codigoInvalido"));
        }
        
        if (empty($documentoSistema->link)) {
            throw new \Exception($this->idioma->getText("linkInvalida"));
        }
        
        if (empty($documentoSistema->versao)) {
            throw new \Exception($this->idioma->getText("versaoInvalida"));
        }
        
        $this->conexao->salvar($documentoSistema);
    }
    
    public function getCodigos($ativo = true) {
        
        $where = "";
        if($ativo){
            $where = " WHERE ativo = 1 ";
        }
        
        $query = "SELECT codigo FROM documentos_sistemas {$where} GROUP BY codigo ORDER BY codigo;";
        $result = $this->conexao->adapter->query($query)->execute();
        $codigos = Array();
        foreach ($result as $dados) {
            $codigos[] = $dados["codigo"];
        }
        return $codigos;
    }
    
    public function getDocumentoSistema($codigo) {
        if (empty($codigo)) {
            throw new \Exception($this->idioma->getText("codigoInvalido"));
        }
        $result = $this->conexao->listar("codigo = '{$codigo}'", "versao DESC", null, 1);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
}

?>