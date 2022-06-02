<?php

namespace Models\Modules\Graficos;

class GraficoGanhosReferenciasRn {
    
    public function graficoGanhosMensalCliente(\Models\Modules\Cadastro\Cliente $cliente, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $idsClientes = Array(), $idMoeda = 1) {
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("A data inicial não pode ser maior que a data final");
            }
            
            $where[] = " cc.data BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
        }
        
        if (sizeof($idsClientes) > 0) {
            $idsClientes = implode(",", $idsClientes);
            $where[] = " cc.id_referenciado IN (".$idsClientes.") ";
        }
        
        $where[] = " cc.id_cliente = {$cliente->id} ";
        
        if ($idMoeda > 1) {
            $where[] = " cc.id_moeda = {$idMoeda} ";
            $origensComissao = implode(",", \Utils\Constantes::ORIGENS_COMISSAO_BTC);
            $where[] = " origem IN ({$origensComissao}) ";
        } else {
            $origensComissao = implode(",", \Utils\Constantes::ORIGENS_COMISSAO_REAIS);
            $where[] = "( cc.comissao_licenciado > 0 OR cc.comissao_convidado > 0 OR origem IN ({$origensComissao}) )";
        }
        
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode( " AND ", $where) : "");
        
        $query = "SELECT "
                . " SUM(cc.valor) AS valor, "
                . " EXTRACT(MONTH FROM cc.data_cadastro) AS month, "
                . " EXTRACT(YEAR FROM cc.data_cadastro) As year, "
                . " CONCAT(EXTRACT(MONTH FROM cc.data_cadastro),  '/', EXTRACT(YEAR FROM cc.data_cadastro)) AS period "
                . " FROM ".($idMoeda == 1 ? "conta_corrente_reais" : "conta_corrente_btc")." cc  "
                . " {$whereString} "
                . " GROUP BY period, month, year "
                . " ORDER BY period;";
                
        
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->adapter->query($query)->execute();
        $dados = Array();
        foreach ($result as $d) {
            
            $data = new \Utils\Data("01/" . ($d["month"] > 9 ? $d["month"] : "0{$d["month"]}") . "/" . $d["year"] . " 00:00:00");
            
            $dados[] = Array(
                "valor" => $d["valor"],
                "mes" => $d["month"],
                "ano" => $d["year"],
                "periodo" => $d["periodo"],
                "nomeMes" => $data->getNomeMes(false),
                "nomePeriodo" => $data->getNomeMes() . "/{$d["year"]}"
            );
            
        }
        
        
        return $dados;
    }
    
}