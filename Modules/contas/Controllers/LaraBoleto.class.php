<?php

namespace Modules\contas\Controllers;

class LaraBoleto {

    private $idioma;

    public function __construct($params) {
        $this->idioma = new \Utils\PropertiesUtils("deposito", IDIOMA);
    }

    public function gerarBoleto($params) {
        $deposito = null;
        try {
            // $authRn = new \Models\Modules\Cadastro\AuthRn();
            // $configuracoes = \Models\Modules\Cadastro\ConfiguracaoRn::get();
            $deposito = new \Models\Modules\Cadastro\Deposito();
            $depositoRn = new \Models\Modules\Cadastro\DepositoRn();
            // $laraBoleto = new \BoletosLara\BoletosLara();
            $cliente = \Utils\Geral::getCliente();

            // $token = \Utils\Post::get($params, "token", null);
            // $pin = \Utils\Post::get($params, "pin", null);
            $deposito->id = \Utils\Post::getEncrypted($params, "deposito", 0);
            // exit(print_r($deposito->id));
            try {
                $depositoRn->carregar($deposito, true, false, false, true);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("depositoInvalidoOuNaoEncontrado"));
            }

            $nomeCliente = \Utils\Validacao::limparString($deposito->cliente->nome, false);

            // if (empty($token)) {
            //     throw new \Exception($this->idioma->getText("tokenInvalido"));
            // }

            // if (empty($pin)) {
            //     throw new \Exception($this->idioma->getText("pinInvalido"));
            // }

            // if ($deposito->cliente->pin != $pin) {
            //     throw new \Exception($this->idioma->getText("pinInvalido"));
            // }

            // $authRn->validar($token, $cliente);

            if (strlen($nomeCliente) < 8) {
                throw new \Exception("Nome do cliente inválido. Atualize seu nome completo no menu Meu Perfil, aba Meus Dados.");
            }

            if(!\Utils\Validacao::verificarNomeCompleto($nomeCliente)){
                throw new \Exception("Nome inválido. Atualize seu nome no menu Meu Perfil, aba Meus Dados.");
            }


            $object = (object)null;
            $object->document = $deposito->cliente->documento;
            $object->name = $nomeCliente;
            $object->value = $deposito->valorDepositado;
            $object->deposit_id = $deposito->id;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://hub.infinitypay.inf.br/api/create",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($object),
                CURLOPT_HTTPHEADER => array(
                    // "Authorization: Bearer {$this->token}",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "Content-Type: x`application/json"
                ),
            ));

        $response = curl_exec($curl);
        $response = json_decode($response);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);

        if($err){
            throw new \Exception($err);
        }
        if($httpcode != 200){
            throw new \Exception($response);
            // throw new \Exception('Tente novamente mais tarde!');
        }

            $valorCreditar = $deposito->valorDepositado - ($deposito->valorDepositado * ($configuracoes->taxaDepositoBoleto / 100)) - $configuracoes->tarifaDepositoBoleto;

            $json["status"] = "Aguardando pagamento";
            $json["mensagemLabel"] = "QRCode Gerado! pague-o dentro do tempo determinado.";
            // $json["vencimento"] = $deposito->dataVencimentoGateway->formatar(\Utils\Data::FORMATO_PT_BR);
            // $json["valor"] = "R$ " . number_format($deposito->valorDepositado, 2, ",", ".");
            // $json["link"] = $deposito->linkGateway;
            $json["valorCreditar"] = "R$ " . number_format($valorCreditar, 2, ",", ".");
            $json["comissao"] = number_format($configuracoes->taxaDepositoBoleto, 2, ",", ".") . "%";
            $json["taxa"] = "R$ " . number_format($configuracoes->tarifaDepositoBoleto, 2, ",", ".");


            $json["valor"] = "R$ ".$response->data->transaction->valor->original;
            $json['timer'] = $response->data->transaction->calendario->expiracao;
            $json['qr'] = $response->data->payment->qrcode;
            $json['qr_img'] = "<img src='".$response->data->payment->imagemQrcode."' />";
            $json["mensagem"] = "Aguarde. QRCode Gerado! pague-o dentro do tempo determinado.";
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