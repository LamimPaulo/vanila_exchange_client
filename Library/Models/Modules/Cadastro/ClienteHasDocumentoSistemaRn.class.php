<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade ClienteHasDocumentoSistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasDocumentoSistemaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasDocumentoSistema());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasDocumentoSistema());
        }
    }
    
    public function salvar(ClienteHasDocumentoSistema &$clienteHasDocumentoSistema) {
        
        
        
        if (!$clienteHasDocumentoSistema->idCliente > 0) {
            throw new \Exception($this->idioma->getText("clienteInvalido"));
        }
        
        if (!$clienteHasDocumentoSistema->idDocumentoSistema > 0) {
            throw new \Exception($this->idioma->getText("documentoInvalido"));
        }
        
        $result = $this->conexao->listar("id_cliente = {$clienteHasDocumentoSistema->idCliente} AND id_documento_sistema = {$clienteHasDocumentoSistema->idDocumentoSistema}");
        if (sizeof($result) > 0) {
            throw new \Exception($this->idioma->getText("clienteAceitouDocumento"));
        }
        
        $clienteHasDocumentoSistema->dataAceitacao = new \Utils\Data(date("d/m/Y H:i:s"));
        
        $this->conexao->salvar($clienteHasDocumentoSistema);
    }
    
    
    public function getAceiteCliente(Cliente $cliente, DocumentoSistema $documento) {
        
        if ($cliente == null || $cliente->id <= 0) {
            throw new \Exception($this->idioma->getText("clienteInvalido"));
        }
        
        if ($documento == null || $documento->id <= 0) {
            throw new \Exception($this->idioma->getText("codigoInvalido"));
        }
        
        $documentoSistemaRn = new DocumentoSistemaRn();
        $documentoUltimaVersao = $documentoSistemaRn->getDocumentoSistema($documento->codigo);
        
        $result = $this->conexao->listar("id_documento_sistema = '{$documentoUltimaVersao->id}' AND id_cliente = {$cliente->id}", null, null, 1);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
}

?>