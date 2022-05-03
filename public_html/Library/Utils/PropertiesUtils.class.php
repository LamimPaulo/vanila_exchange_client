<?php

namespace Utils;

class PropertiesUtils {
    
    private $idiomaPadrao = "pt-BR";
    private $lang = "";
    private $arquivo = "";
    private $idioma = "";
    
    private $dados = Array( );
    private $dadosDefault = Array( );
    
    public function __construct($arquivo, $idioma="pt-BR") {
        $this->arquivo = $arquivo;
        $this->idioma = $idioma;
        
        $l = explode("-", $idioma);
        $this->lang = $l[0];
        $this->openFile();
    }
    
    public function getLang() {
        return (empty($this->lang) ? "pt" : $this->lang);
    }
    
    public function has($key) {
        return (isset($this->dados[$key]) || isset ($this->dadosDefault[$key]));
    }
    
    public function getText($key) {
        
        if (isset($this->dados[$key])) {
            return $this->dados[$key];
        } else if (isset ($this->dadosDefault[$key])) {
            return $this->dadosDefault[$key];
        }
        return "undefined";
    }
    
    public function hasKey($key) {
        return isset($this->dados[$key]) || isset($this->dadosDefault[$key]);
    }
    
    private function openFile() {
        
        $file = "./resources/idiomas/{$this->arquivo}-{$this->idioma}.lang";
        $defaultFile = "./resources/idiomas/{$this->arquivo}-{$this->idiomaPadrao}.lang";
        
        $this->dados = Array();
        $this->dadosDefault = Array();        
        
         if (!file_exists($defaultFile)) {
            throw new \Exception("Arquivo de idioma não encontrado");
        }
        
        if (file_exists($file)) {
            
            $this->loadfile($file);
        }
        
        $this->loadfileDefault($defaultFile);
        
    }
    
    private function loadfileDefault($file) {
        $f = fopen ($file, 'r');
        while(!feof($f)) {
            $linha = str_replace(Array("\r", "\n"), "", fgets($f));
            
            if (substr($linha, 0, 1) != "#") {
                
                $chave = substr($linha, 0, strpos($linha, "="));
                $conteudo = substr($linha, strpos($linha, "=")+1);
                
                $this->dadosDefault[$chave] = $conteudo;
            }
            
        }
        fclose($f);
    }
    
    private function loadfile($file) {
        $f = fopen ($file, 'r');
        
        while(!feof($f)) {
            
            $linha = str_replace(Array("\r", "\n"), "", fgets($f));
            
            if (substr($linha, 0, 1) != "#") {
                
                $chave = substr($linha, 0, strpos($linha, "="));
                $conteudo = substr($linha, strpos($linha, "=")+1);
                
                $this->dados[$chave] = $conteudo;
            }
            
        }
        fclose($f);
    }
}