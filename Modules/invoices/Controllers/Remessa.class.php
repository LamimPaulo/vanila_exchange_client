<?php

namespace Modules\invoices\Controllers;

class Remessa {
    
    private $codigoModulo = "cartoes";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    public function index($params) {
        try {
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("remessa_recarga_cartao", $params);
    }
    
    public function listar($params) {
        try {
            
            
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $result = $recargaCartaoRn->conexao->listar("status = '".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."'", "data_pedido");
            
            ob_start();
            ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col col-lg-1 text-center">
                        <strong>Recarga</strong>
                    </div>
                    <div class="col col-lg-1 text-center">
                        <strong>Invoice</strong>
                    </div>

                    <div class="col col-lg-1 text-center">
                        <strong>D. Nasc.</strong>
                    </div>

                    <div class="col col-lg-2 text-center">
                        <strong>Nome Completo</strong>
                    </div>

                    <div class="col col-lg-2 text-center">
                        <strong>CPF</strong>
                    </div>
                    <div class="col col-lg-2 text-center">
                        <strong>Email</strong>
                    </div>

                    <div class="col col-lg-2 text-center">
                        <strong>Num. Cartão</strong>
                    </div>

                    <div class="col col-lg-1 text-center">
                        <strong>Valor</strong>
                    </div>
                </div>
            </li>
            <?php
            if (sizeof($result) > 0) {
                foreach ($result as $recargaCartao) {
                    //$recargaCartao = new \Models\Modules\Cadastro\RecargaCartao();
                    $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao(Array("id" => $recargaCartao->idPedidoCartao));
                    $pedidoCartaoRn->carregar($pedidoCartao, true, true);
                    ?>
                    <li class="list-group-item" style="font-size: 10px;">
                        <div class="row">
                            <div class="col col-lg-1 text-center">
                                <?php echo $recargaCartao->id ?>
                            </div>
                            <div class="col col-lg-1 text-center">
                                <?php echo $recargaCartao->idInvoice ?>
                            </div>

                            <div class="col col-lg-1 text-center">
                                <?php echo ($pedidoCartao->cliente->dataNascimento != null ? $pedidoCartao->cliente->dataNascimento->formatar(\Utils\Data::FORMATO_PT_BR) : "") ?>
                            </div>
                            
                            <div class="col col-lg-2 text-center">
                                <?php echo $pedidoCartao->cliente->nome ?>
                            </div>
                            
                            <div class="col col-lg-2 text-center">
                                <?php echo $pedidoCartao->cliente->documento ?>
                            </div>
                            <div class="col col-lg-2 text-center">
                                <?php echo $pedidoCartao->cliente->email ?>
                            </div>
                            
                            <div class="col col-lg-2 text-center">
                                <?php echo $pedidoCartao->numeroCartao ?>
                            </div>
                            
                            <div class="col col-lg-1 text-center">
                                <?php echo number_format($recargaCartao->valorReal, 2, ',',".") ?>
                            </div>
                        </div>
                    </li>
                    <?php
                }
            } else {
                ?>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col col-lg-12">
                            Nenhuma recarga com pagamento confirmado
                        </div>
                    </div>
                </li>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function download($params) {
        try {
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
            
            // Criamos as colunas
            $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'REFERÊNCIA 1' )
                        ->setCellValue('B1', "REFERÊNCIA 2" )
                        ->setCellValue("C1", "D. NASCIMENTO" )
                        ->setCellValue("D1", "NOME COMPLETO" )
                        ->setCellValue("E1", "CPF" )
                        ->setCellValue("F1", "EMAIL" )
                        ->setCellValue("G1", "NUM CARTÃO" )
                        ->setCellValue("H1", "VALOR" );

            // Podemos configurar diferentes larguras paras as colunas como padrão
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $pedidoCartaoRn = new \Models\Modules\Cadastro\PedidoCartaoRn();
            $result = $recargaCartaoRn->conexao->listar("status = '".\Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO."'", "data_pedido");
            
            $linha = 2;
            foreach ($result as $recargaCartao) {
                //$recargaCartao = new \Models\Modules\Cadastro\RecargaCartao();
                $pedidoCartao = new \Models\Modules\Cadastro\PedidoCartao(Array("id" => $recargaCartao->idPedidoCartao));
                $pedidoCartaoRn->carregar($pedidoCartao, true, true);
                
                // Inserindo na posição exata do dado  (coluna, linha, dado);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(0, $linha, $recargaCartao->id);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $linha, $recargaCartao->idInvoice);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $linha, ($pedidoCartao->cliente->dataNascimento != null ? $pedidoCartao->cliente->dataNascimento->formatar(\Utils\Data::FORMATO_PT_BR) : ""));
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $linha, $pedidoCartao->cliente->nome);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $linha, $pedidoCartao->cliente->documento);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $linha, $pedidoCartao->cliente->email);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $linha, $pedidoCartao->numeroCartao);
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, $linha, number_format($recargaCartao->valorReal, 2, ',',"."));
                
                $linha++;
            }
            

            // Renomear o nome das planilha atual
            $objPHPExcel->getActiveSheet()->setTitle('Arquivo de Remessa');

            // Cabeçalho do arquivo para ele baixar
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="remessa.xls"');
            header('Cache-Control: max-age=0');
            // Se for o IE9, isso talvez seja necessário
            header('Cache-Control: max-age=1');

            // Acessandp o 'Writer' para poder salvar o arquivo
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            // Salva diretamente no output
            $objWriter->save('php://output'); 

        } catch (\Exception $ex) {
            
        }
    }
}