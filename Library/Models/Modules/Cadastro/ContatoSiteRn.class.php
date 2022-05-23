<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class ContatoSiteRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
     private $idioma = null;
     
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ContatoSite());
        } else {
            $this->conexao = new GenericModel($adapter, new ContatoSite());
        }
    }
    
    public function salvar(ContatoSite &$contatoSite) {
        $contatoSite->id = 0;
        $contatoSite->dataEnvio = new \Utils\Data(date("d/m/Y H:i:s"));
        if (empty($contatoSite->nome)) {
            throw new \Exception($this->idioma->getText("nomeDeveInformado"));
        }
        
        if (empty($contatoSite->email)) {
            throw new \Exception($this->idioma->getText("emailDeveInformado"));
        }
        
        if (!\Utils\Validacao::email($contatoSite->email)) {
            throw new \Exception($this->idioma->getText("emailInvalido"));
        }
        
        if (empty($contatoSite->telefone)) {
            throw new \Exception($this->idioma->getText("telefoneDeveInformado"));
        }
        if (empty($contatoSite->departamento)) {
            throw new \Exception($this->idioma->getText("departamentoDeveInformado"));
        }
        
        if (empty($contatoSite->mensagem)) {
            throw new \Exception($this->idioma->getText("mensagemDeveInformada"));
        }
        
        
        $this->conexao->salvar($contatoSite);
        
        try {
            $cliente = new Cliente(Array("id" => 0, "nome" => $contatoSite->nome, "email" => $contatoSite->email));
            
            if ($contatoSite->departamento == "NEWC ICO") {
                \EmailIco\ContatoSite::send($contatoSite);
                \EmailIco\RespostaAutomaticaContato::send($contatoSite);
                \Lahar\Cadastro::novo($cliente, "contato_token");
            } else {
                \Email\ContatoSite::send($contatoSite);
                \Email\RespostaAutomaticaContato::send($contatoSite);
                \Lahar\Cadastro::novo($cliente, "contato_site");
            }
            
        } catch (\Exception $ex) {

        }
    }
    
}

?>