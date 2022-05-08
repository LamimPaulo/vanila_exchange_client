<?php

namespace Utils;

class ValidarSeguranca {
    
    public static function validar(\Models\Modules\Cadastro\Cliente $cliente) {
        
        $configuracao = \Models\Modules\Cadastro\ConfiguracaoRn::get();
        
        
        //KYC
        if ($cliente->documentoVerificado != 1) {
            throw new \Exception("Por favor, faça seu KYC no menu MEU PERFIL para habilitar o saque.");
        }
        
        
        //2FA
        if (!empty($configuracao->horasUpdateTwofa) && $configuracao->horasUpdateTwofa > 0) {
            $dataAtual = new Data(date("Y-m-d H:i:s"));
            $dataAtual->subtrair(0, 0, 0, $configuracao->horasUpdateTwofa);

            if (!empty($cliente->dataUpdateTwofa)) {
                if ($cliente->dataUpdateTwofa->maior($dataAtual)) {
                    $diferenca = $dataAtual->diferenca($cliente->dataUpdateTwofa);

                    $stringData = " {$diferenca->d} dia(s), {$diferenca->h} hora(s) e {$diferenca->i} minuto(s)";

                    throw new \Exception("Seu 2FA foi alterado recentemente. Por favor, aguarde o período de {$stringData} para continuar.");
                }
            }
        }
        
        //Senha
        if (!empty($configuracao->horasUpdateSenha) && $configuracao->horasUpdateSenha > 0) {
            $dataAtual = new Data(date("Y-m-d H:i:s"));
            $dataAtual->subtrair(0, 0, 0, $configuracao->horasUpdateSenha);

            if (!empty($cliente->dataUpdateSenha)) {
                if ($cliente->dataUpdateSenha->maior($dataAtual)) {
                    $diferenca = $dataAtual->diferenca($cliente->dataUpdateSenha);

                    $stringData = " {$diferenca->d} dia(s), {$diferenca->h} hora(s) e {$diferenca->i} minuto(s)";

                    throw new \Exception("Sua senha foi alterada recentemente. Por favor, aguarde o período de {$stringData} para continuar.");
                }
            }
        }

        //PIN
        if(!empty($configuracao->horasUpdatePin) && $configuracao->horasUpdatePin > 0){
            $dataAtual = new Data(date("Y-m-d H:i:s"));
            $dataAtual->subtrair(0, 0, 0, $configuracao->horasUpdatePin);
            
            if (!empty($cliente->dataUpdatePin)) {
                if ($cliente->dataUpdatePin->maior($dataAtual)) {
                    $diferenca = $dataAtual->diferenca($cliente->dataUpdatePin);

                    $stringData = " {$diferenca->d} dia(s), {$diferenca->h} hora(s) e {$diferenca->i} minuto(s)";

                    throw new \Exception("Seu PIN foi alterado recentemente. Por favor, aguarde o período de {$stringData} para continuar.");
                }
            }
        }
        
        //Frase de segurança
        if (!empty($configuracao->horasUpdateFraseSeguranca) && $configuracao->horasUpdateFraseSeguranca > 0) {
            $dataAtual = new Data(date("Y-m-d H:i:s"));
            $dataAtual->subtrair(0, 0, 0, $configuracao->horasUpdateFraseSeguranca);

            if (!empty($cliente->dataUpdateFraseSeguranca)) {
                if ($cliente->dataUpdateFraseSeguranca->maior($dataAtual)) {
                    $diferenca = $dataAtual->diferenca($cliente->dataUpdateFraseSeguranca);

                    $stringData = " {$diferenca->d} dia(s), {$diferenca->h} hora(s) e {$diferenca->i} minuto(s)";

                    throw new \Exception("Sua frase de segurança foi alterada recentemente. Por favor, aguarde o período de {$stringData} para continuar.");
                }
            }
        }
    }
}
