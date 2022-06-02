<?php
/**
 * Classe para gerencia de planilhas eletronicas
 */
namespace Utils;

/**
 * Contém a classe do Excel
 *
 * @copyright Copyright (c) 2013 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Excel {

    /**
     * Objeto PHPExcel
     * @var \PHPExcel 
     */
    public $phpExcel;

    /**
     * Armazena o número da linha do último registro incluído na planilha
     * @var int 
     */
    public $linha;

    /**
     * Construtor da classe
     */
    public function __construct() {
        $this->phpExcel = new \PHPExcel();
    }

    /**
     * Função responsável pela geração dum cabeçalho padrão do arquivo PDF
     *  
     * @param String $titulo Título do relatório
     * @param String $largura Letra da coluna até o qual o cabeçalho ocupará apartir da letra A
     * @param String $empresa Empresa do relatório
     * @param String $filtro Descriçao do filtro do relatório
     * @param array $listaParametros Parâmetros adicionais. Ex: $listaParametros[] = array('rotulo' => 'Rótulo', 'valor' => 'Valor');
     */
    public function cabecalho($titulo, $empresa, $filtro, $largura = "G", $cor = "CCCCCC", $listaParametros = array()) {
        $this->phpExcel->setActiveSheetIndex(0);
        $sheet = $this->phpExcel->getActiveSheet();
        $sheet->SetCellValue('A1', $titulo);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->SetCellValue('A2', "Empresa: {$empresa}");
        $styleCabecalho = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'font' => array(
                'name' => 'Arial',
                'size' => '16',
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => $cor),
                'endcolor' => array('rgb' => $cor)
            )
        );

        $styleCelulas = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => array('rgb' => $cor),
                'endcolor' => array('rgb' => $cor)
            )
        );

        $sheet->SetCellValue('A3', "Filtro: {$filtro}");

        $this->estilo("A1:{$largura}1", $styleCabecalho);
        $listaCelulasPreencherBordas = array("A2:{$largura}2", "A3:{$largura}3");

        $posicaoLinha = 4;
        foreach ($listaParametros as $aux) {
            $sheet->SetCellValue("A{$posicaoLinha}", "{$aux['valor']}: {$aux['rotulo']}");
            $listaCelulasPreencherBordas[] = "A{$posicaoLinha}:{$largura}{$posicaoLinha}";
            $posicaoLinha++;
        }
        $this->linha = $posicaoLinha;
        $this->estilo($listaCelulasPreencherBordas, $styleCelulas);
    }

    /**
     * Salva o arquivo XLS no servidor
     *  
     * @param String $celula Célula que vai ser preenchido o valor
     * @param String $valor Valor da célula
     * @param String $mergir Célula que será unida a célula do valor
     * @param array() $estilo Estilo da célula (bordas, cor, fonte, etc)
     */
    public function celula($celula, $valor, $mergir = null, $estilo = array()) {
        //Células que serão aplicados os estilos/mergidas
        $celulasEstilo = ($mergir == null) ? ($celula) : ($celula . ":" . $mergir);
        //Seleciono a planilha ativa
        $this->phpExcel->setActiveSheetIndex(0);
        $sheet = $this->phpExcel->getActiveSheet();
        //Aplico o estilo
        $this->estilo($celulasEstilo, $estilo);
        //Aplico o valor
        $sheet->SetCellValue($celula, $valor);
    }

    /**
     * Define o estilo das celulas
     *  
     * @param String/array $celulas Células que serão coloridas
     * @param array $arrayStyle Array com o estilo
     */
    public function estilo($celulas, $arrayStyle) {
        try {
            $sheet = $this->phpExcel->getActiveSheet();
            //Verifico se foi passado apenas um intervalo de células
            if (is_string($celulas)) {
                $styleFiltro = $sheet->getStyle($celulas);
                $styleFiltro->applyFromArray($arrayStyle);
                //Só efetuo a unção das células se foi passado como parâmetro um range de células
                if (strstr($celulas, ':')) {
                    $sheet->mergeCells($celulas);
                }
            }
            //Ou se foi passado um array de células
            if (is_array($celulas)) {
                foreach ($celulas as $celula) {
                    $styleFiltro = $sheet->getStyle($celula);
                    $styleFiltro->applyFromArray($arrayStyle);
                    //Só efetuo a unção das células se foi passado como parâmetro um range de células
                    if (strstr($celula, ':')) {
                        $sheet->mergeCells($celula);
                    }
                }
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Força o download do arquivo xls
     */
    public function download() {
        $phpExcelGravar = new \PHPExcel_Writer_Excel5($this->phpExcel);
        header('Content-type: application/vnd.ms-excel');
        $phpExcelGravar->save('php://output');
    }

    /**
     * Salva o arquivo XLS no servidor
     *  
     * @param String $nome Nome do arquivo gerado no servidor
     */
    public function salvar($nome = 'arquivo.xls') {
        //Gravação do arquivo no servidor
        $phpExcelGravar = new \PHPExcel_Writer_Excel5($this->phpExcel);
        $phpExcelGravar->save($nome);
    }

}

?>
