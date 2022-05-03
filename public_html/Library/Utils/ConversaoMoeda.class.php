<?php

namespace Utils;

class ConversaoMoeda {
    
    
    
    public static function conversao($idMoedaOrigem, $idMoedaConversao, $idCliente, $valor, $taxa) {
       
        $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();
        $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
        $contaCorrenteEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresaRn();
        $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();
        
        $moeda = \Models\Modules\Cadastro\MoedaRn::get($idMoedaConversao);
        $moedaOrigem = \Models\Modules\Cadastro\MoedaRn::get($idMoedaOrigem);
        
        $taxaFinal = $taxa / 100  * ($valor * $moedaOrigem->valorConversao);
        $valorFinal = ($valor * $moedaOrigem->valorConversao) - $taxaFinal;

        //1 - Fazer debito na conta do cliente
            $contaCorrenteBtcDebito = new \Models\Modules\Cadastro\ContaCorrenteBtc();
            $contaCorrenteBtcDebito->id = 0;
            $contaCorrenteBtcDebito->autorizada = 1;
            $contaCorrenteBtcDebito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcDebito->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcDebito->enderecoBitcoin = "";
            $contaCorrenteBtcDebito->hash = "";
            $contaCorrenteBtcDebito->descricao = "Mover saldo para " . $moeda->nome;
            $contaCorrenteBtcDebito->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtcDebito->enderecoEnvio = "";
            $contaCorrenteBtcDebito->executada = 1;
            $contaCorrenteBtcDebito->origem = 17;
            $contaCorrenteBtcDebito->idCliente = $idCliente;
            $contaCorrenteBtcDebito->idMoeda = $idMoedaOrigem;
            $contaCorrenteBtcDebito->tipo = Constantes::SAIDA;
            $contaCorrenteBtcDebito->transferencia = 0;
            $contaCorrenteBtcDebito->valor = number_format($valor, 8, ".", "");;
            $contaCorrenteBtcDebito->valorTaxa = 0;
            $contaCorrenteBtcDebito->orderBook = 0;
        
            $contaCorrenteBtcRn->gerarContaCorrente($contaCorrenteBtcDebito);
            
        //2 - Creditar moeda debitada na conta da empresa            
            $contaCorrenteBtcEmpresaCredito = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresaCredito->id = 0;
            $contaCorrenteBtcEmpresaCredito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresaCredito->descricao = "Conversao de saldo do cliente " . $idCliente . " - {$moedaOrigem->simbolo} para {$moeda->simbolo}";
            $contaCorrenteBtcEmpresaCredito->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtcEmpresaCredito->valor = number_format($valor, 8, ".", "");
            $contaCorrenteBtcEmpresaCredito->transferencia = 1;
            $contaCorrenteBtcEmpresaCredito->idMoeda = $idMoedaOrigem;

            $contaCorrenteEmpresaRn->gerarContaCorrente($contaCorrenteBtcEmpresaCredito);
        
        //3 - Fazer debito da moeda conversão na conta da empresa
        if($moeda->id == 1){
            
            $contaCorrenteReaisEmpresaDebito = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresaDebito->id = 0;
            $contaCorrenteReaisEmpresaDebito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresaDebito->descricao = "Conversao de saldo do cliente " . $idCliente . " - {$moedaOrigem->simbolo} para {$moeda->simbolo}";
            $contaCorrenteReaisEmpresaDebito->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteReaisEmpresaDebito->valor = number_format(($valor * $moedaOrigem->valorConversao), 4, ".", "");
            $contaCorrenteReaisEmpresaDebito->transferencia = 1;

            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresaDebito);
            
            //Creditar Taxa
            $contaCorrenteReaisEmpresaTaxa= new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresaTaxa->id = 0;
            $contaCorrenteReaisEmpresaTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteReaisEmpresaTaxa->descricao = "Taxa de conversao de saldo do cliente " . $idCliente . " - {$moedaOrigem->simbolo} para {$moeda->simbolo}";
            $contaCorrenteReaisEmpresaTaxa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisEmpresaTaxa->valor = number_format($taxaFinal, 2, ".", "");
            $contaCorrenteReaisEmpresaTaxa->transferencia = 1;

            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresaTaxa);
            
        } else {
            
            $contaCorrenteBtcEmpresaDebito = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresaDebito->id = 0;
            $contaCorrenteBtcEmpresaDebito->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresaDebito->descricao = "Conversao de saldo do cliente " . $idCliente . " - {$moedaOrigem->simbolo} para {$moeda->simbolo}";
            $contaCorrenteBtcEmpresaDebito->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtcEmpresaDebito->valor = number_format(($valor * $moedaOrigem->valorConversao), 8, ".", "");
            $contaCorrenteBtcEmpresaDebito->transferencia = 1;
            $contaCorrenteBtcEmpresaDebito->idMoeda = $moeda->id;

            $contaCorrenteEmpresaRn->gerarContaCorrente($contaCorrenteBtcEmpresaDebito);
            
            
            //Creditar Taxa
            $contaCorrenteBtcEmpresaTaxa = new \Models\Modules\Cadastro\ContaCorrenteBtcEmpresa();
            $contaCorrenteBtcEmpresaTaxa->id = 0;
            $contaCorrenteBtcEmpresaTaxa->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtcEmpresaTaxa->descricao = "Taxa de conversao de saldo do cliente " . $idCliente . " - {$moedaOrigem->simbolo} para {$moeda->simbolo}";
            $contaCorrenteBtcEmpresaTaxa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteBtcEmpresaTaxa->valor = number_format($taxaFinal, 8, ".", "");
            $contaCorrenteBtcEmpresaTaxa->transferencia = 1;
            $contaCorrenteBtcEmpresaTaxa->idMoeda = $moeda->id;

            $contaCorrenteEmpresaRn->gerarContaCorrente($contaCorrenteBtcEmpresaTaxa);
            
            
        } 
        
        //4 - Fazer crédito na conta do cliente da moeda conversão
        
        
        
        //Reais
        if($moeda->id == 1){
            
            $contaCorrenteTo = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteTo->id = 0;
            $contaCorrenteTo->idCliente = $idCliente;
            $contaCorrenteTo->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteTo->descricao = "Conversao de saldo da moeda " . $moedaOrigem->nome;
            $contaCorrenteTo->tipo = Constantes::ENTRADA;
            $contaCorrenteTo->valor = number_format($valorFinal, 2, ".", "") ;
            $contaCorrenteTo->transferencia = 1;
            $contaCorrenteTo->origem = 11;
                        
            $contaCorrenteReaisRn->gerarContaCorrente($contaCorrenteTo);
            
        } else {
            
            //Criptomoeda
            $contaCorrenteBtc = new \Models\Modules\Cadastro\ContaCorrenteBtc();
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->autorizada = 1;
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->enderecoBitcoin = "";
            $contaCorrenteBtc->hash = "";
            $contaCorrenteBtc->descricao = "Conversao de saldo da moeda " . $moedaOrigem->nome;
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtc->enderecoEnvio = "";
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->origem = 17;
            $contaCorrenteBtc->idCliente = $idCliente;
            $contaCorrenteBtc->idMoeda = $moeda->id;
            $contaCorrenteBtc->tipo = Constantes::ENTRADA;
            $contaCorrenteBtc->transferencia = 0;
            $contaCorrenteBtc->valor = number_format($valorFinal, 8, ".", "");
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtc->orderBook = 0;

            $contaCorrenteBtcRn->gerarContaCorrente($contaCorrenteBtc);
        }
        
    }
}
