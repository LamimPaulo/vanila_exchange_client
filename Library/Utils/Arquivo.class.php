<?php 
/**
 * Classe para manipulaÃ§Ã£o de arquivos.
 */
namespace Utils;

/**
 * ContÃ©m os dados do arquivo.
 */

class Arquivo {
    
    /**
     * Largura de saÃ­da em px da imagem.
     * @var int 
     */
    public $largura;
    
    /**
     * Altura de saÃ­da em px da imagem.
     * @var int 
     */
    public $altura;
    
    /**
     * Nome do arquivo original.
     * @var String 
     */
    private $nome;
    
    /**
     * Nome do arquivo de saida.
     * @var String 
     */
    public $nome_saida;
    
    /**
     * DiretÃ³rio temporÃ¡rio do arquivo.
     * @var String 
     */
    private $diretorio_temp;
    
    /**
     * DiretÃ³rio de saÃ­da do arquivo para upload.
     * @var String 
     */
    public $diretorio_saida;
    
    /**
     * Tipo do arquivo (IMG, VID, DOC, PDF, ZIP, RAR).
     * @var String 
     */
    private $tipo;
    
    /**
     * Tamanho em bytes do arquivo.
     * @var int 
     */
    private $tamanho;
    
    /**
     * ExtensÃ£o do arquivo.
     * @var String 
     */
    private $extensao;
    
    
    /**
     * Indica se o arquivo foi upado no servidor.
     * @var Boolean
     */
    
    public $upado = false;
    
    
    private $publicDirectory = false;
    
    /**
     * 
     * @param type $arquivo
     * @throws \Exception
     */
    
    public $idioma = null;
    
    public function __construct ($arquivo) {
        $this->idioma = new \Utils\PropertiesUtils("utils", IDIOMA);
        if (is_array($arquivo)) {
            //exit(print_r($arquivo));
            if (!$arquivo["error"]) {
                $this->diretorio_temp = $arquivo["tmp_name"];
                $this->nome = $arquivo["name"];
                $this->tipo = (substr($arquivo["name"], (strpos($arquivo["name"], ".") + 1)));
                $this->tamanho = $arquivo["size"];
                
                $a = explode(".", $arquivo["name"]);
                $this->extensao = $a[sizeof($a) - 1];
                $nomeSemAcento = Texto::removerAcentos($arquivo["name"]);
                $this->nome_saida = str_replace(" ", "", $nomeSemAcento); 
            } else {
                throw new \Exception($this->idioma->getText("erroEnvioArquivo"));
            }
        } else {
            throw new \Exception ($this->idioma->getText("arqInvalido"));
        }
    }
    
    public function setPublicDirectory($publicDirectory = true) {
        $this->publicDirectory = $publicDirectory;
    }
    
    public function getTamanho() {
        return $this->tamanho;
    }
    
    public function getDiretorioTemp() {
        return $this->diretorio_temp;
    }
    
    public function getNome() {
        return $this->nome;
    }
    
    public function getExtensao() {
        return $this->extensao;
    }
    
    public function getTipo() {
        return $this->tipo;
    }
    
    /**
     * Analiza a extensão do arquivo e determina o tipo dele (IMG ou DOC)
     * 
     * Extensões aceitas jpg, jpeg, png, gif, txt, pdf, doc, docx, ppt, xls
     * 
     * @param Array $file array com os dados do arquivo
     * @return $tipo 
     * @throws \Exception Se a extensão do arquivo não for uma das especificadas 
     */
    public function obtemExtensaoArquivo () {
        
        $tipo = "";
        switch (strtolower($this->extensao)) {
            
            case "jpeg" : $tipo = "IMG";
                         break;
            case "jpg" : $tipo = "IMG";
                         break;
            case "png" : $tipo = "IMG";
                         break;
            //case "gif" : $tipo = "IMG";
                         //break;
            case "txt" : $tipo = "DOC";
                         break;
            case "pdf" : $tipo = "PDF";
                         break;
            case "xps" : $tipo = "DOC";
                         break;
            case "doc" : $tipo = "DOC";
                         break;
            case "docx" : $tipo = "DOC";
                         break;
            case "ppt" : $tipo = "DOC";
                         break;
            case "xls" : $tipo = "DOC";
                         break;
            case "rar" : $tipo = "RAR";
                break;
            case "zip" : $tipo = "ZIP";
                break;
            default: $tipo = null;
            
        }
        
        if (!$tipo)
            throw new \Exception($this->idioma->getText("arqNaoSuportado"));
        
        return $tipo;
    }
    
    
    function uploadArquivo($cliente = null , $modulo = null) {
        //exit($this->tamanho . "");
        // Limita o tamanho máximo do arquivo a 3MB
        if ($this->tamanho > (10*1024*1024)) {
            throw new \Exception($this->idioma->getText("tamanhoMaximoArq"));
        }
        
        try {
            // Se for uma imagem, o controle é redirecionado para a classe de imagens.
            if ($this->obtemExtensaoArquivo() == "IMG") {
                Imagem::enviaImagem($this, $cliente, $modulo, $this->publicDirectory);
            } else {
                
                if (!$this->publicDirectory) {
                    $this->diretorio_saida = UPLOADS;
                } else {
                    $this->diretorio_saida = PUBLIC_IMAGES;
                }
                
                if (!$this->publicDirectory) {
                    if ($cliente != null && $cliente->id > 0) {
                        $this->diretorio_saida .= "{$cliente->id}/";
                        if (!file_exists($this->diretorio_saida)) {
                            mkdir($this->diretorio_saida);
                        }
                    }
                }
                
                if ($modulo != null && !empty($modulo)) {
                    $this->diretorio_saida .= "{$modulo}/";
                    if (!file_exists($this->diretorio_saida)) {
                        mkdir($this->diretorio_saida);
                    }
                }

                if (file_exists($this->diretorio_saida . $this->nome_saida)) {
                    $valida = false;
                    $i = 1;
                    while (!$valida) {

                        $novoNome = substr($this->nome_saida, 0, strpos($this->nome_saida, ".")) . "_" . $i . "." . $this->getExtensao();
                        if (!file_exists($this->diretorio_saida . $novoNome)) {
                            $valida = true;
                            $this->nome_saida =  $novoNome;
                        }

                        $i++;
                    }
                }
                
                move_uploaded_file($this->diretorio_temp, $this->diretorio_saida . $this->nome_saida);
                
                if (file_exists($this->diretorio_saida . $this->nome_saida)) {
                    $this->upado = true;
                } else {
                    throw new \Exception ($this->idioma->getText("ocorreramFalhas"));
                }
                
                if ($this->publicDirectory) { 
                    $this->nome_saida = $this->diretorio_saida . $this->nome_saida;
                } else {
                    $this->nome_saida = str_replace(UPLOADS, "", $this->diretorio_saida) . $this->nome_saida;
                }
            }
        } catch (\Exception $erro) {
            throw new \Exception (Excecao::mensagem($erro));
        }
            
    }
    
}

?>
