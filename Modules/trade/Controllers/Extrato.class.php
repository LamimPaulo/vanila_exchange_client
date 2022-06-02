<?php

namespace Modules\trade\Controllers;

class Extrato {
    
    private $codigoModulo = "trade";
    private $idioma = null;
    
    public function __construct() {
                
        if(\Utils\Geral::isUsuario()){
           \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("extrato", IDIOMA);
    }
    
    public function index($params) {

        \Utils\Layout::view("index_extrato", $params);
    }
    
    
    public function listarMinhasOrdens($params) {
        try {
            
            $data = \Utils\Post::get($params, "data", "todos");
            $tipo = \Utils\Post::get($params, "tipo", "T");
            $limite = \Utils\Post::get($params, "limite", 10);
            
            $cliente = \Utils\Geral::getCliente();
            $paridade = \Modules\principal\Controllers\Principal::getParity();

            $orderBookRn = new \Models\Modules\Cadastro\OrderBookRn();
            $cancelada = "N";
            
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
                case "todos":
                    $dataInicial = null;
                    $dataFinal = null;
                    break;
            }
            
            switch ($tipo) {
                case "compra":                        
                    $tipo = \Utils\Constantes::ORDEM_COMPRA;
                    $cancelada = "T";
                    break;
                case "venda":                        
                    $tipo = \Utils\Constantes::ORDEM_VENDA;
                    $cancelada = "T";
                    break;                                
                case "todos":
                    $tipo = "T";
                    $cancelada = "T";
                    break;
            }
           
            if($limite == "todos"){
                $limite = 0;
            }
            
            if($tipo == "todos"){
                $tipo = "T";
            }
            
            $lista = $orderBookRn->getExtrato($paridade->id, $dataInicial, $dataFinal, $tipo, "M", $cancelada, $limite, $cliente->id);   
            
            $json["html"] = $this->htmlMinhasOrdens($lista, $paridade);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function htmlMinhasOrdens($lista, $paridade) {
        ob_start();
        if (sizeof($lista) > 0) {
            foreach ($lista as $ordem) {
                $this->htmlItemMinhasOrdens($ordem, $paridade);
            }
        } else {
            ?>
            <tr class="my-extrato-order-item">
                <td class=" text-center" colspan="10">
                    <?php echo $this->idioma->getText("nenhumaOrdemExibida") ?>
                </td>
            </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    private function htmlItemMinhasOrdens(\Models\Modules\Cadastro\OrderBook $ordem, $paridade) {
        
        if (empty($paridade->casasDecimaisMoedaTrade)) {
            $casasDecimaisMoedaTrade = $paridade->moedaTrade->casasDecimais;
        } else {
            $casasDecimaisMoedaTrade = $paridade->casasDecimaisMoedaTrade;
        }
        
        /*if ($ordem->executada == 0) {
            $color = "color: #666666;";
        } else {*/
            $color = ($ordem->tipo == \Utils\Constantes::ORDEM_COMPRA ? "color: #1ab394;" : "color: #ff1e1e;");
        /*}*/
        
        ?>
            
        <tr style="<?php echo $color?>" class="my-extrato-order-item" >
            <td style="vertical-align: middle; padding-left: 25px;" class="text-left">
                <img src="<?php echo IMAGES ?>currencies/<?php echo $paridade->moedaBook->icone ?>" style="max-width: 16px; max-height: 16px;" />
                <?php echo $paridade->symbol ?>
             </td>
            <td class="text-center"><?php echo $ordem->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO)?></td>
            <td class="text-center"><?php echo $paridade->moedaTrade->simbolo ?> <?php echo number_format($ordem->valorCotacao, $casasDecimaisMoedaTrade, ",", ".")?></td>
            <td class="text-center"><?php echo number_format($ordem->volumeCurrency, $paridade->moedaBook->casasDecimais, ".", "")?></td>
            <td class="text-center"><?php echo $paridade->moedaTrade->simbolo ?> <?php echo number_format(($ordem->valorCotacao * $ordem->volumeCurrency), $casasDecimaisMoedaTrade, ",", ".")?></td>
            <td class="text-center"><?php echo number_format(($ordem->volumeExecutado), $paridade->moedaBook->casasDecimais, ".", "")?></td>
            <td class="text-center"><?php echo $paridade->moedaTrade->simbolo ?> <?php echo number_format(($ordem->valorCotacao * $ordem->volumeExecutado), $casasDecimaisMoedaTrade, ",", ".")?></td>
            <td class="text-center"><?php            
            if($ordem->executada > 0){
                echo $this->idioma->getText("executadaC");
                } else if($ordem->cancelada == 0 && $ordem->executada == 0){
                        echo $this->idioma->getText("pendenteC");                        
                    } else if ($ordem->cancelada == 1) {
                        echo $this->idioma->getText("canceladaC");
                    }?></td>
        </tr>
        <?php
        
    }
}