<?php

namespace Modules\cofre\Controllers;

class InvestimentoContrato {
    
    private $codigoModulo = "cofre";
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("cofre", IDIOMA);
        \Utils\Validacao::acesso($this->codigoModulo);
        
    }
    
    public function listarInvestimentoContratos($params) {
        try {            
            $contratosRn = new \Models\Modules\Cadastro\InvestimentoContratosRn();
            $dados = Array();
            $result = $contratosRn->conexao->listar("ativo = 1", "tempo_meses ASC");
            
            
            ob_start();
            ?>
            <tr>
                <td class="text-center"><strong><?php echo $this->idioma->getText("tempo") ?></strong></td>            
                <?php
                $aux = 0;
                foreach ($result as $contrato){ 
                    $dados[] = $contrato ?> 
                    
                <td class="text-center" onclick="tempoSlider('<?php echo $aux++ ?>')" style="cursor: pointer;"><?php echo $contrato->tempoMeses ?></td>
              <?php
              $meses[] = $contrato->tempoMeses;
              $lucroNc[] = $contrato->lucroNc;
              $lucroPoup[] = $contrato->lucroPoupanca;
              $lucroImov[] = $contrato->lucroImovel;
              $lucroTesouro[] = $contrato->lucroTesouro;
              
                } ?>
            </tr>
            <tr>
                <td class="text-center"><strong><?php echo $this->idioma->getText("lucro") ?></strong></td> 
                <?php
                $aux = 0;
                foreach ($dados as $contrato){ ?>            
                <td class="text-center" onclick="tempoSlider('<?php echo $aux++ ?>')" style="cursor: pointer;"><?php echo $contrato->lucroNc ?>%</td>
                <?php } ?>
            </tr>
            <?php
            
            $html = ob_get_contents();
            ob_end_clean();
            
            
            
            $json["lucroPoup"] = $lucroPoup;
            $json["lucroImo"] = $lucroImov;
            $json["lucroTes"] = $lucroTesouro;
            $json["meses"] = $meses;
            $json["lucro"] = $lucroNc;
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}