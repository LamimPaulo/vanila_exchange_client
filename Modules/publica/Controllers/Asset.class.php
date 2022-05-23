<?php

namespace Modules\publica\Controllers;
use Predis\Client;
class Asset {
    private $method = null;
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    public function assets(){
        $httpResponse = new HttpResult();
        $storage_assets = new Client(array('database' => '0', 'host' => getenv("RedisHost"), 'port' => 6379, 'password' => getenv("RedisPass")));

        if ($storage_assets->exists('ASSETS')) {
            $assets[] = $storage_assets->hgetall('ASSETS');
            $httpResponse->addBody(null, json_decode($assets[0][0]));
            $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
        }else{
             try {
            $moedaRn = new \Models\Modules\Cadastro\MoedaRn();
            $moedas = $moedaRn->listar(" ativo = 1 AND mostrar_asset = 1 ", "nome ASC");
            $taxaMoedaRn = new \Models\Modules\Cadastro\TaxaMoedaRn();
            $data = new \Utils\Data(date("Y-m-d H:i:s"));
            $assets = Array();

            foreach ($moedas as $moeda) {
                $taxaMoeda = $taxaMoedaRn->getByMoeda($moeda->id);

                $mensagemManutencao = Array();
                $msgManutencao = "";

                if($moeda->ativo == 1 && $moeda->statusMercado == 0){
                    if($moeda->statusSaque == 0){
                        $mensagemManutencao[] =  "Withdraw temporarily suspended";
                    }
                    if($moeda->statusDeposito == 0){
                        $mensagemManutencao[] =  "Deposit temporarily suspended";
                    }
                    if($moeda->statusMercado == 0){
                        $mensagemManutencao[] = "Market temporarily suspended";
                    }

                    $msgManutencao = implode(" - ", $mensagemManutencao);

                }

                $asset = Array(
                          "timestamp" => strtotime($data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)),
                          "name"=> $moeda->simbolo,
                          "nameLong"=>  $moeda->nome,
                          "minConfirmation"=> number_format($taxaMoeda->minConfirmacoes, 0, ".", ""),
                          "withdrawTxFee"=> number_format($taxaMoeda->taxaTransferencia, $moeda->casasDecimais, ".", ""),
                          "withdrawTxFeePercent"=> 0,
                          "systemProtocol"=> $moeda->coinType,
                          "isActive"=> $moeda->ativo == 1 ? true : false,
                          "maintenanceMode"=> ($moeda->ativo == 1) && ($moeda->statusMercado == 0) ? true : false,
                          "maintenanceMessage"=> $msgManutencao,
                          "formatPrefix"=> "",
                          "formatSufix"=> "",
                          "decimalSeparator"=> ".",
                          "thousandSeparator"=> "",
                          "decimalPlaces"=> $moeda->casasDecimais,
                          "currency"=> $moeda->simbolo,
                          "currencyLong"=> $moeda->nome,
                          "coinType"=> $moeda->coinType,
                          "canWithdraw"=> $moeda->statusSaque == 1 ? true : false,
                          "canDeposit"=> $moeda->statusDeposito == 1 ? true : false,
                          "minWithdraw"=> "",
                          "maxWithdraw "=>"" ,
                          "makerFee"=> number_format($taxaMoeda->taxaVendaIndireta, $moeda->casasDecimais, ".", ""),
                          "takerFee"=> number_format($taxaMoeda->taxaCompraDireta, $moeda->casasDecimais, ".", ""),
                          "backgroundColor" =>$moeda->mainColor,
                          "fontColor" =>$moeda->corFonte,
                          "imageData" => URL_IMAGE . $moeda->icone
                    );

               $assets[] = $asset;
               $asset = null;


            }
                 $storage_assets->hmset('ASSETS', array(json_encode($assets)));
                 $storage_assets->expire('ASSETS',60);


                 if($storage_assets->exists('ASSETS')){
                     $markets[] = $storage_assets->hgetall('ASSETS');
                     $httpResponse->addBody(null, json_decode($markets[0][0]));
                     $httpResponse->setSuccessful(HTTPResponseCode::$CODE200);
                 }else {
                     throw new \Exception("Markets is not available.", 400);
                 }
             } catch (\Exception $ex) {
                 $httpResponse->setSuccessful($ex->getCode(), \Utils\Excecao::mensagem($ex));
             }
        }
        $httpResponse->printResult();
    }
}