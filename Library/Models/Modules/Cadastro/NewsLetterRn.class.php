<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class NewsLetterRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new NewsLetter());
        } else {
            $this->conexao = new GenericModel($adapter, new NewsLetter());
        }
    }
    
    public function salvar(NewsLetter &$newsLetter, $ico = false) {
        if ($newsLetter->id > 0) {
            $aux = new NewsLetter(Array("id" => $newsLetter->id));
            $this->conexao->carregar($aux);
            
            $newsLetter->ativo = $aux->ativo;
        } else {
            $newsLetter->ativo = 1;
        }
        
        if (empty($newsLetter->nome)) {
            //throw new \Exception($this->idioma->getText("necessarioNomeNews"));
        }
        
        
        if (empty($newsLetter->email)) {
            throw new \Exception($this->idioma->getText("necessarioEmailNews"));
        }
        
        if (!\Utils\Validacao::email($newsLetter->email)) {
            throw new \Exception($this->idioma->getText("emailInvalido"));
        }
        
        $result = $this->conexao->listar(" email = '{$newsLetter->email}' AND id = {$newsLetter->id} ");
        if (sizeof($result) > 0) {
            throw new \Exception($this->idioma->getText("emailCadstradoNews"));
        }
        
        $this->conexao->salvar($newsLetter);
        
        
        try {
            
            if ($ico) {
                \EmailIco\ConfirmacaoNewsletter::send($newsLetter);
                $cliente = new Cliente(Array("id" => 0, "nome" => $newsLetter->nome, "email" => $newsLetter->email));
                \Lahar\Cadastro::novo($cliente, "newsletter_token");
            } else {
                \Email\ConfirmacaoNewsletter::send($newsLetter);
                $cliente = new Cliente(Array("id" => 0, "nome" => $newsLetter->nome, "email" => $newsLetter->email));
                \Lahar\Cadastro::novo($cliente, "newsletter");
            }
        } catch (\Exception $ex) {

        }
    }
    
    
    public function desativar(NewsLetter &$newsletter) {
        try {
            $this->conexao->carregar($newsletter);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("newsNaoLocalizada"));
        }
        
        $newsletter->ativo = 0;
        $this->conexao->update(Array("ativo" => $newsletter->ativo), Array("id" => $newsletter->id));
    }
    
    public function desativarEmail(NewsLetter &$newsletter) {
        
        
        if (empty($newsletter->email)) {
            throw new \Exception($this->idioma->getText("necessarioEmailNews"));
        }
        
        if (!\Utils\Validacao::email($newsletter->email)) {
            throw new \Exception($this->idioma->getText("emailInvalido"));
        }
        $result = $this->conexao->listar(" email = '{$newsletter->email}' ");
        if (sizeof($result) > 0) {
            $newsletter = $result->current();
            
            $this->conexao->update(Array("ativo" => 0), Array("id" => $newsletter->id));
        } else {
            throw new \Exception($this->idioma->getText("emailNaoCadastradoSistema"));
        }
    }
    
    
    public function ativar(NewsLetter &$newsletter) {
        try {
            $this->conexao->carregar($newsletter);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("newsNaoLocalizada"));
        }
        
        $newsletter->ativo = 1;
        $this->conexao->update(Array("ativo" => $newsletter->ativo), Array("id" => $newsletter->id));
    }
}

?>