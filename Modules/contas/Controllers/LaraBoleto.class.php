<?php

namespace Modules\contas\Controllers;

use Throwable;

class LaraBoleto {

    private $idioma;

    public function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("deposito", IDIOMA);
    }

    public function gerarBoleto($params) {
        $deposito = null;
        try {
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $cliente = \Utils\Geral::getCliente();

            $deposito->id = \Utils\Post::getEncrypted($params, "deposito", 0);
            try {
                $depositoRn->carregar($deposito, true, false, false, true);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("depositoInvalidoOuNaoEncontrado"));
            }

            if(!$deposito->cliente->endereco || !$deposito->cliente->numero || !$deposito->cliente->bairro || !$deposito->cliente->cep || !$deposito->cliente->cidade){
                throw new \Exception('É necessario ter um endereço completo cadastrado.');
            }

            if(!$deposito->cliente->celular){
                throw new \Exception('É necessario ter um numero de telefone cadastrado.');
            }

            $object = (object)null;
            $object->customer = $deposito->cliente;
            $object->value = $deposito->valorDepositado;
            $object->deposit_id = $deposito->id;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://hub.infinitypay.inf.br/api/create",
                // CURLOPT_URL => "http://127.0.0.1:8000/api/create",
                CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_FAILONERROR => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($object),
                CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "Content-Type: x`application/json"
                ),
            ));

        $response = curl_exec($curl);
        $response = json_decode($response);

        $err = curl_error($curl);
        if($err){
            throw new \Exception($err);
        }
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if($httpcode != 200){
            throw new \Exception('Tente novamente mais tarde!');
        }
            $configuracoes = \Models\Modules\Cadastro\ConfiguracaoRn::get();

            $valorCreditar = $deposito->valorDepositado - ($deposito->valorDepositado * ($configuracoes->taxaDepositoBoleto / 100)) - $configuracoes->tarifaDepositoBoleto;

            $json["status"] = "Aguardando pagamento";
            $json["mensagemLabel"] = "QRCode Gerado! pague-o dentro do tempo determinado.";
            // $json["vencimento"] = $deposito->dataVencimentoGateway->formatar(\Utils\Data::FORMATO_PT_BR);
            // $json["valor"] = "R$ " . number_format($deposito->valorDepositado, 2, ",", ".");
            // $json["link"] = $deposito->linkGateway;
            $json["valorCreditar"] = "R$ " . number_format($valorCreditar, 2, ",", ".");
            $json["comissao"] = number_format($configuracoes->taxaDepositoBoleto, 2, ",", ".") . "%";
            $json["taxa"] = "R$ " . number_format($configuracoes->tarifaDepositoBoleto, 2, ",", ".");

            $json["valor"] = "R$ ".$deposito->valorDepositado;
            // $json['timer'] = $response->data->transaction->calendario->expiracao;
            $json['timer'] = 3600;
            $json['qr'] = $response->data->payment->qrcode;
            $json['qr_img'] = "<img src='".$response->data->payment->imagemQrcode."' />";
            $json["mensagem"] = "QRCode Gerado! pague-o dentro do tempo determinado.";
            $json["sucesso"] = true;

        }  catch (\Exception $ex) {
            $this->deletaDeposito($deposito);
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function deletaDeposito(\Models\Modules\Cadastro\Deposito &$deposito) {
    
        try{            
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            $depositoRn->conexao->excluir($deposito);
        } catch (Exception $ex) {
            throw new \Exception("Falha para gerar o boleto.");
        }        
    }
    
    public function token($params) {
        try {
            $authRn = new \Models\Modules\Cadastro\AuthRn();
            $cliente = \Utils\Geral::getCliente();
            $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $valor = \Utils\Post::getNumeric($params, "valor", 0);
           
            $nomeCliente = \Utils\Validacao::limparString($cliente->nome, false);

            if (strlen($nomeCliente) < 8) {
                throw new \Exception("Nome do cliente inválido. Atualize seu nome completo no menu Meu Perfil, aba Meus Dados.");
            }
            
            if(!\Utils\Validacao::verificarNomeCompleto($nomeCliente)){
                throw new \Exception("Nome inválido. Atualize seu nome no menu Meu Perfil, aba Meus Dados.");
            }

            if($valor > $configuracao->valorMaxBoleto){
                throw new \Exception("Valor não permitido.");
            }
            
            $auth = new \Models\Modules\Cadastro\Auth();
            $auth->idCliente = $cliente->id;
            $authRn->salvar($auth);


            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_EMAIL) {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoEmail1") . " " . $cliente->email . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_SMS) {
                $json["mensagem"] = $this->idioma->getText("foiEnviadoSMS1") . " " . $cliente->celular . " " . $this->idioma->getText("porFavorInsiraToken1");
            }

            if ($cliente->tipoAutenticacao == \Utils\Constantes::TIPO_AUTH_GOOGLE) {
                $json["mensagem"] = $this->idioma->getText("useGoogle1");
            }
            
            $valorCreditar = $valor - ($valor * ($configuracao->taxaDepositoBoleto / 100)) - $configuracao->tarifaDepositoBoleto;
            
            $json["creditarBoleto"] = "R$ " . number_format($valorCreditar, 2, ",", ".");
            $json["totalBoleto"] = "R$ " . number_format($valor, 2, ",", ".");
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}