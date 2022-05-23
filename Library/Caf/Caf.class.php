<?php

namespace Caf;

class Caf
{

    protected $token;

    public function __construct($token_ = null)
    {
        $this->token = $token_;

    }

    private function requestSCaf($method, $url_, $params = [])
    {
        try {
            $url = sprintf('https://api.combateafraude.com%s?token=%s', $url_, $this->token);
            $headers = [
                "Content-Type: application/json"
            ];

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => $headers,
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                return [
                    'sucesso' => false,
                    'info' => $info ?? 'Sem informacoes adicionais',
                    'mensagem' => $err,
                    'params' => $params
                ];
            } else {
                return [
                    'sucesso' => true,
                    'message' => 'Processado com sucesso com sucesso.',
                    'data' => json_decode($response, true)
                ];
            }

        } catch (\Exception $e) {
            print_r($e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    public function createLinkOnboard($params)
    {
        try {

            unset($params['email']);

            if (!isset($params['federal_document'])) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'CPF/CNPJ não informado'
                ];
            }

            if (!isset($params['nome'])) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Nome não foi informado'
                ];
            }


            return $this->requestSCaf('POST', '/reports/62387b4aca6efb00097d54f1/onboarding-ext', $params ?? []);

        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'mensagem' => sprintf('Falha geral | %s ', $e->getMessage())
            ];
        }

    }


}
