<?php
/**
 * Classe para manipulação de pdf
 */
namespace Utils;

/**
 * Contém a classe por geração de PDF
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class PDF {

    /**
     * Objeto MPDF 
     * @var MPDF $mpdf
     */
    public $mpdf = null;

    /**
     * Conteúdo Html do PDF
     * @var string $html
     */
    public $html = null;

    /**
     * Conteúdo de folha de estilo
     * @var String 
     */
    public $style = null;
    
    /**
     * Título do arquivo
     * @var string $titulo
     */
    public $titulo = null;
    
    /**
     * Header do documento
     * @var String 
     */
    public $header = null;

    /**
     * Footer do documento
     * @var String 
     */
    public $footer = null;
    
    /**
     * Construtor da classe
     *  
     * @param String $titulo Título do Relatório
     * @param String $formato Formato da Página do PDF
     * @param String $orientacao Orientação da impressão (P = Portrait, L = Landscape)
     */
    public function __construct($titulo = '', $formato = 'A4', $orientacao = 'P') {
        $this->titulo = $titulo;
        $this->mpdf = new \mPDF('', $formato, 0, '', 10, 10, 10, 10, 3, 3, $orientacao);
    }

    /**
     * Função responsável pela geração dum cabeçalho padrão do arquivo PDF
     *  
     * @param String $titulo Título do Relatório
     * @param String $empresa Empresa do relatório
     * @param String $filtro Descrição do filtro do relatório
     * @param array $listaParametros Parâmetros adicionais. Ex: $listaParametros[] = array('rotulo' => 'Rótulo', 'valor' => 'Valor');
     */
    public function cabecalho(\Models\Modules\Cadastro\Empresa $empresa) {
        ob_start();
        ?>
        <div style="width: 100%; text-align: center; padding: 10px;">
            <br><br>
            <img src="<?php echo URLBASE_CLIENT . UPLOADS . $empresa->cabecalho ?>"  style="margin: 10px;" />
        </div>
        <?php
        $this->header .= ob_get_contents();
        ob_end_clean();
    }

        /**
         * Contém o conteúdo do relatório
         * @param String $html Conteúdo html do relatório
         */
        public function conteudo($html) {
            $this->html .= $html;
        }
        
        public function stylesheet ($stylesheet) {
            $this->style .= $stylesheet;
        }

        /**
         * Gera o rodapé do conteúdo html
         */
        public function rodape(\Models\Modules\Cadastro\Empresa $empresa) {
            ob_start();
            ?>
        <div style="width: 100%; text-align: center; padding: 10px;">
            <img src="<?php echo URLBASE_CLIENT . UPLOADS . $empresa->rodape ?>"  style="margin: 10px;" />
        </div>
        <?php
        $this->footer .= ob_get_contents();
        ob_end_clean();
    }

    /**
     * Gera o arquivo pdf
     *  
     * @param String $nome Nome do arquivo
     * @param String $formato Formato do arquivo (I = Browser, D = Download, F = Servidor, S = String)
     * @param boolean $imprimirTitulo Define se será impresso o título da página
     * @param boolean $numeroPaginas Define se será impresso o número de páginas no rodapé
     */
    public function gerar($nome = 'relatorio.pdf', $formato = 'D', $imprimirTitulo = true, $rodape = true, $numeroPaginas = true) {
        //Verifico se vai ser impresso o título
        $this->mpdf->margin_header = 20;
        
        if ($imprimirTitulo == true) {
            $this->mpdf->SetHTMLHeader($this->header);
        }   
        
        if ($rodape) {
            $this->mpdf->SetHTMLFooter($this->footer);
        }
        
        //Verifico se vai ser impresso o rodapé
        /*if ($numeroPaginas == true) {
            $this->mpdf->setFooter('{PAGENO}');
        }
*/
        //Algumas configurações do PDF
        $this->mpdf->SetDisplayMode('fullpage');
        
        $this->mpdf->margin_header = -10;
        $this->mpdf->mgt = -10;
        if (strlen($this->style) > 0) {
            $this->mpdf->WriteHTML($this->style, 1);
        }
        $this->mpdf->WriteHTML($this->html, 2);
        $this->mpdf->Output($nome, $formato);
        
    }

}
?>