<?php

namespace Modules\pdfs\Controllers;

class PDFComprovanteInvoice {
    
    public function gerar($cliente, $idInvoice, $numeroPedido, $dataPedido) {
        
        try {
            
            $orders = new \BitcoinToYou\Orders();
            $order = $orders->getInvoice($idInvoice);
            
            $params = Array(
                "cliente" => $cliente,
                "order" => $order,
                "numeroPedido" => $numeroPedido,
                "dataPedido" => $dataPedido
            );
            
            ob_start();
            \Utils\Layout::append("pdfs/pdf_comprovante_invoice", $params);
            $html = ob_get_contents();
            ob_end_clean();
            
            $pdf = new \Utils\PDF("Recarga de Cartão - Comprovante", "A4", "P");
            $pdf->conteudo($html);
            $pdf->gerar("comprovante.pdf", "D", false, false, false);
            
        } catch (\Exception $ex) {
            ?>
            <html>
                <head>
                    <title>Erro</title>
                </head>
                <body>
                    <h1><?php echo \Utils\Excecao::mensagem($ex)?></h1>
                </body>
            </html>
            <?php
        }
        
    }
    
}