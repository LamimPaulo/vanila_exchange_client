<?php
namespace Modules\referencia\Controllers;

use GuzzleHttp\Client;

class Referencia {
    private $codigoModulo = "referencia";
    private $idioma = null;

    public function __construct() {
        if(\Utils\Geral::isUsuario()){
            \Utils\Geral::redirect(URLBASE_CLIENT . \Utils\Rotas::R_DASHBOARD);
        }
        \Utils\Validacao::acesso($this->codigoModulo);
        $this->idioma = new \Utils\PropertiesUtils("extrato", IDIOMA);
    }

    public function buyPlan($params) {
        $client = new Client();
        $cliente = \Utils\Geral::getCliente();
        
        $plan_id = \Utils\Post::get($params, "plan_id", null);
        
        if(true){

            if(AMBIENTE != "producao"){
                $url = 'https://sandbox.coinage.trade/api/priv/buyplan';
            }else{
                $url = 'https://coinage.trade/api/priv/buyplan';
            }
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSH_COMPRESSION, true);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url
            ]);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode(['plan_id' => $plan_id]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: Application/json',
                'Content-type: Application/json',
                'usr: '.$cliente->id,
            ]);
            $result2 = curl_exec($ch);
            // print(json_decode($result2));
            curl_close($ch);
        }
        
        print $result2;
        // return $result2;
        // \Utils\Layout::view("index_referencia2", $params);
    }

    public function index($params) {
        $client = new Client();
        $cliente = \Utils\Geral::getCliente();
        $moedaRn = new \Models\Modules\Cadastro\MoedaRn(); 
        $moedas = $moedaRn->listar(" id = 1 OR ativo = 1 AND (visualizar_deposito = 1 OR visualizar_saque = 1)", "nome ASC");

        if(true){
            if(AMBIENTE != "producao"){
                $url = 'https://sandbox.coinage.trade/api/priv/buyplan';
            }else{
                $url = 'https://coinage.trade/api/priv/buyplan';
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSH_COMPRESSION, true);
            curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_URL => $url
            ]);
            $result = curl_exec($ch);
            $plans = json_decode($result);

            curl_close($ch);
        }
        if(true){
            if(AMBIENTE != "producao"){
                $url = 'https://sandbox.coinage.trade/api/priv/buyplan';
            }else{
                $url = 'https://coinage.trade/api/priv/buyplan';
            }
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSH_COMPRESSION, true);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url
            ]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: Application/json',
                'Content-type: Application/json',
                'usr: '.$cliente->id,
            ]);
            $result2 = curl_exec($ch);
            $current_plan = json_decode($result2);

            curl_close($ch);
        }

        // exit(print_r($current_plan));


        $params["moedas"] = $moedas;
        $params["plans"] = $plans->data;
        $params["current_plan"] = $current_plan->data;

        \Utils\Layout::view("index_referencia2", $params);
    }

    // public function index($params) {
    //     $moedaRn = new \Models\Modules\Cadastro\MoedaRn(); 
    //     $moedas = $moedaRn->listar(" id = 1 OR ativo = 1 AND (visualizar_deposito = 1 OR visualizar_saque = 1)", "nome ASC");

    //     $params["moedas"] = $moedas;

    //     \Utils\Layout::view("index_referencia", $params);
    // }

    public function listarReferencia($params) {
        try {

            $cliente = \Utils\Geral::getCliente();
            $dataInicial = \Utils\Post::getData($params, "dataInicial", null, "00:00:00");
            $dataFinal = \Utils\Post::getData($params, "dataFinal", null, "23:59:59");
            $idMoeda = \Utils\Post::getEncrypted($params, "moeda", null);
            $limite = \Utils\Post::get($params, "registros", null);
            $idReferencia = \Utils\Post::getEncrypted($params, "referencia", null);

            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

            if ($limite == "T") {
                $limite = null;
            }

            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception("Data inicial não pode ser maior que a data final.");
            }

            if (empty($idMoeda)) {
                throw new \Exception("Moeda precisa ser selecionada.");
            }

            $referencia = "";
            if (!empty($idReferencia)) {
                $referencia = " id_referenciado = {$idMoeda} AND ";
            }

            $moeda = \Models\Modules\Cadastro\MoedaRn::get($idMoeda);
            $referencias = $clienteRn->getClientesReferencias($cliente);

            $lista = Array();
            $listaNomeData = Array();
            $deposito = 0;
            $saque = 0;
            $compraVenda = 0;
            foreach ($referencias as $referencia) {
                $referenciaAux = (object)null;
                $gravar = false;

                $arrayNomeCliente = explode(" ", $referencia->nome);
                $clienteNome = $arrayNomeCliente[0] . ' ' . $arrayNomeCliente[1];
                $clienteNome = mb_convert_case($clienteNome, MB_CASE_LOWER, "UTF-8");
                $nome = ucwords($clienteNome);

                if($cliente->id == 15093064572782){
                    $referenciaAux->nomeReferencia = $referencia->email; 
                } else {
                    $referenciaAux->nomeReferencia = $nome; 
                }

                $referenciaAux->dataCadastro = $referencia->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR);

                $listaNomeData[] = $referenciaAux;

                $referenciaAux->valorDeposito = number_format(0, $moeda->casasDecimais, ".", "");
                $referenciaAux->valorSaque = number_format(0, $moeda->casasDecimais, ".", "");
                $referenciaAux->valorCompraVenda = number_format(0, $moeda->casasDecimais, ".", "");

                $valorMoeda = $contaCorrenteBtcRn->calcularSaldoReferencia($cliente->id, $idMoeda, $referencia->id, $dataInicial, $dataFinal, "2");

                if (sizeof($valorMoeda) > 0) {
                    foreach ($valorMoeda as $dados) {
                        switch ($dados["origem"]) {
                            case 2:
                                $gravar = $dados["valor"] > 0 ? true : false;
                                $referenciaAux->valorCompraVenda = number_format($dados["valor"], $moeda->casasDecimais, ".", "");
                                break;
                            case 15:
                                $gravar = $dados["valor"] > 0 ? true : false;
                                $referenciaAux->valorDeposito = number_format($dados["valor"], $moeda->casasDecimais, ".", "");
                                break;
                            case 16:
                                $gravar = $dados["valor"] > 0 ? true : false;
                                $referenciaAux->valorSaque = number_format($dados["valor"], $moeda->casasDecimais, ".", "");
                                break;
                            default:
                                $gravar = false;
                                break;
                        }
                    }
                }

                $deposito += $referenciaAux->valorDeposito;
                $saque += $referenciaAux->valorSaque;
                $compraVenda += $referenciaAux->valorCompraVenda;

                $referenciaAux->total = number_format(($referenciaAux->valorCompraVenda + $referenciaAux->valorSaque + $referenciaAux->valorDeposito), $moeda->casasDecimais, ".", "");

                if ($gravar) {
                    $lista[] = $referenciaAux;
                }
            }

            if(empty(sizeof($lista))){
                $referenciaAux = (object)null;
                $referenciaAux->nomeReferencia = "-";
                $referenciaAux->dataCadastro = "-";
                $referenciaAux->valorDeposito = "-";
                $referenciaAux->valorSaque = "-";
                $referenciaAux->valorCompraVenda = "-";
                $referenciaAux->total = "-";
                $lista[] = $referenciaAux;
            }

            $json["moedaImg"] = IMAGES . "currencies/" . $moeda->icone;
            $json["moedaNome"] = $moeda->nome;
            $json["qtdReferencias"] = sizeof($referencias);
            $json["totalDepositos"] = number_format($deposito, $moeda->casasDecimais, ".", "");
            $json["totalCompraVenda"] = number_format($compraVenda, $moeda->casasDecimais, ".", "");
            $json["totalSaques"] = number_format($saque, $moeda->casasDecimais, ".", "");
            $json["total"] = number_format(($deposito + $saque + $compraVenda), $moeda->casasDecimais, ".", "");

            $json["indicados"] = $listaNomeData;
            $json["dados"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}