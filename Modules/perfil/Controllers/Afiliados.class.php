<?php

namespace Modules\perfil\Controllers;

class Afiliados {
    
    public function listar($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            $data = \Utils\Post::get($params, "data", "todos");
            $saldo = \Utils\Post::getBoolean($params, "saldo", TRUE);
            $pesquisa = \Utils\Post::get($params, "pesquisa", null);
            
            
            switch ($data) {
                case "dia":                        
                    $dataInicial = new \Utils\Data(date("d/m/Y 00:00:00"));
                    $dataFinal = new \Utils\Data(date("d/m/Y 23:59:59"));
                    break;
                case "semana":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 0, 6);
                    break;
                case "mes":
                    $dataInicial = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataFinal = new \Utils\Data(date("d/m/Y H:i:s"));
                    $dataInicial->subtrair(0, 1);
                    break;                
            }
            
            $where = Array();
            
            $where[] = "id_referencia = {$cliente->id}";

            
            if (!empty($pesquisa)) {
                $where[] = " LOWER(nome) LIKE LOWER('%{$pesquisa}%') ";
            }
            
            if ($data !== "todos") {
                $where[] = " data_cadastro BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}'  ";
            }
            
            $stringWhere = (sizeof($where) > 0 ? implode(" AND ", $where) : null);
            
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $referenciados = $clienteRn->conexao->listar($stringWhere, "data_cadastro DESC", null, null);
            
            $dados = $this->htmlLista($referenciados, $cliente, $saldo);
            
            $json["indicacao"] = $dados["indicacao"];
            $json["comissao"] = $dados["comissao"];
            $json["html"] = $dados["html"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }

        print json_encode($json);
        
    }
    
    
    public function htmlLista($referenciados, $cliente, $saldo) {
        $retorno = Array();
        $comissaoReferencia = 0;
        $comissaoIndicacao = 0;
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $referenciaVerificada = false;
        $ccRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
        $valor = "";
        
        ob_start();
        
        if (sizeof($referenciados) > 0) {                        
            foreach ($referenciados as $referencia) {
                //$referenciaVerificada = $clienteRn->clienteVerificado($referencia);
                //if($referenciaVerificada){
                    //$valor = $ccRn->saldoClienteByReferenciado($cliente->id, $referencia->id);   
                    //$comissaoReferencia += $valor;
                    
                    //$comissaoIndicacao++;
                   
                    //if($saldo){
                      //  if($valor > 0){
                            //$this->htmlReferencia($referencia, $valor); 
                       // }
                   // } else {                        
                        $this->htmlReferencia($referencia, $valor); 
                    //}                    
              // }
            }
     
        } else {
            ?>
            <tr>
                <td class='text-center' colspan='3'></td>
            </tr>
            <?php
        }    
        
        $retorno["html"] = ob_get_contents();
        ob_end_clean();
        $retorno["comissao"] = "R$ " . number_format($comissaoReferencia, 2, ",", ".");
        $retorno["indicacao"] = "R$ " . number_format($comissaoIndicacao, 2, ",", ".");
        
        return $retorno;
    }
    
    
    public function htmlReferencia(\Models\Modules\Cadastro\Cliente $cliente, $valor) {        
        ?>
            <tr>
                <td class="text-left"><?php echo $cliente->nome ?></td>
                <td class='text-center'><?php echo ($cliente->dataCadastro != null ? $cliente->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR) : "") ?></td>                 
            </tr>
        
        <?php
    }
    

    
    
}
