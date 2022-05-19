<?php
namespace Modules\invoices\Controllers;

class Retorno {
    
    private $codigoModulo = "cartoes";
    
    public function __construct(&$params) {
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    public function upload($params) {
        
        try {
           
            $file = $params["_FILE"];
            $tipoArquivo = \Utils\Post::get($params, "tipo", "Excel5");
            
            if (!isset($file["dados"]) || $file["dados"]["error"] > 0) {
                throw new \Exception("Arquivo não recebido");
            }
            
            
            $novoArquivo = UPLOADS . "xls_" . date("Ymd_His") . ".xls";
            move_uploaded_file($file['dados']['tmp_name'], $novoArquivo);

            $object =  \PHPExcel_IOFactory::load($novoArquivo);
            
            $sheetData = $object->getActiveSheet()->toArray(null,true,true,true);
            $recargaCartaoRn = new \Models\Modules\Cadastro\RecargaCartaoRn();
            $header = true;
            foreach ($sheetData as $data) {
                if (!$header) {
                    
                    try {
                        
                        $idRecarga = $data["A"];
                        $idInvoice = $data["B"];
                        $status = ($data["I"] == 1 ? \Utils\Constantes::STATUS_RECARGA_CARTAO_FINALIZADO : \Utils\Constantes::STATUS_RECARGA_CARTAO_PAGO);
                        
                        $recargaCartaoRn->conexao->update(
                                Array("status" => $status), 
                                Array(
                                    "id" => $idRecarga,
                                    "id_invoice" => $idInvoice
                                )
                            );
                        
                        
                    } catch (\Exception $ex) {
                        
                    }
                    
                } else {
                    $header = false;
                }
            }
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Dados atualizados com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
        
    }
}
?>