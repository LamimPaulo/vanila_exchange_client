<?php

namespace Modules\ws\Controllers;

class Telegram {
    
    public function index($params) {
        try {
            $slug = \Utils\Get::get($params, 0, null);
            
            $sWhereSlug = "";
            if (!empty($slug)) {
                $sWhereSlug = " AND slug = '{$slug}' ";
            }
            
            $telegramMensagemAutomaticaRn = new \Models\Modules\Cadastro\TelegramMensagemAutomaticaRn();
            $telegramMensagensAutomaticas = $telegramMensagemAutomaticaRn->conexao->listar("ativo > 0 {$sWhereSlug} ", "id");
            
            foreach ($telegramMensagensAutomaticas as $telegramMensagemAutomatica) {
                
                if ($this->validarData($telegramMensagemAutomatica)) {
                    $this->executar($telegramMensagemAutomatica);
                }
                
            }
        } catch (\Exception $ex) {
            exit(print_r($ex));
        }
    }
    
    private function validarData(\Models\Modules\Cadastro\TelegramMensagemAutomatica $telegramMensagemAutomatica) {
        
        
        if ($telegramMensagemAutomatica->dataAtualizacao == null) {
            return true;
        } else {
            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            $dataProximaExecutacao = new \Utils\Data($telegramMensagemAutomatica->dataAtualizacao->formatar("d/m/Y H:i:s"));
            switch ($telegramMensagemAutomatica->periodicidade) {
                case "1H" :
                    $dataProximaExecutacao->somar(0, 0, 0, 1);
                    break;
                case "2H" :
                    $dataProximaExecutacao->somar(0, 0, 0, 2);
                    break;
                case "4H" :
                    $dataProximaExecutacao->somar(0, 0, 0, 4);
                    break;
                case "6H" :
                    $dataProximaExecutacao->somar(0, 0, 0, 6);
                    break;
                case "12H" :
                    $dataProximaExecutacao->somar(0, 0, 0, 12);
                    break;
                case "24H" :
                    $dataProximaExecutacao->somar(0, 0, 0, 24);
                    break;
            }
            
            if ($dataAtual->maiorIgual($dataProximaExecutacao)) {
                return true;
            }
        }
        return false;
    }
    
    private function executar(\Models\Modules\Cadastro\TelegramMensagemAutomatica $telegramMensagemAutomatica) {
        $conteudo = "";
        switch ($telegramMensagemAutomatica->id) {
            case 1:
            case 2:
            case 3:
                $conteudo = $this->icoInfo($telegramMensagemAutomatica);

                break;

            default:
                return null;
        }
        $telegramGrupoRn = new \Models\Modules\Cadastro\TelegramGrupoRn();
        $telegramBotRn = new \Models\Modules\Cadastro\TelegramBotRn();
        
        $telegramGrupo = new \Models\Modules\Cadastro\TelegramGrupo(Array("id" => $telegramMensagemAutomatica->idTelegramGrupo));
        $telegramBot = new \Models\Modules\Cadastro\TelegramBot(Array("id" => $telegramMensagemAutomatica->idTelegramBot));
        
        $telegramGrupoRn->conexao->carregar($telegramGrupo);
        $telegramBotRn->conexao->carregar($telegramBot);
        
        $this->sendMessage($conteudo, $telegramGrupo->codigo, $telegramBot->chave);
        
        if ($telegramGrupo->codigo != "-326637530") {
            $this->sendMessage($conteudo, "-326637530", $telegramBot->chave);
        }
        
        $telegramMensagemAutomaticaRn = new \Models\Modules\Cadastro\TelegramMensagemAutomaticaRn();
        $telegramMensagemAutomaticaRn->conexao->update(Array("data_atualizacao" => date("Y-m-d H:i:s")), Array("id" => $telegramMensagemAutomatica->id));
    }
    
    private function sendMessage($conteudo, $chatId, $key) {
        $parameters = [
            'text'       => $conteudo,
            'chat_id'    => $chatId,
            'parse_mode' => 'HTML'
        ];

        $url = "https://api.telegram.org/bot{$key}/sendMessage";
        $pacote =  $url . '?' . http_build_query($parameters);
        
        //echo "{$pacote} <br><br>";
        $execucao = file_get_contents($pacote);
    }
    
    
    private function icoInfo(\Models\Modules\Cadastro\TelegramMensagemAutomatica $telegramMensagemAutomatica) {
        $mensagem = html_entity_decode($telegramMensagemAutomatica->conteudo);
        
        
        $distribuicaoTokenRn = new \Models\Modules\ICO\DistribuicaoTokenRn();
        $clienteRn = new \Models\Modules\Cadastro\ClienteRn($distribuicaoTokenRn->conexao->adapter);
        $faseIcoRn = new \Models\Modules\ICO\FaseIcoRn($distribuicaoTokenRn->conexao->adapter);
        $clienteEnvolvidos = $clienteRn->getQuantidadeClientesCadastrados();
        $ticket = $distribuicaoTokenRn->getDadosTicketVendas(\Utils\Constantes::ID_ICO);
        
        $tokensDistribuidos = 0;
        $tokensVendidos = 0;
        $tokensBonificados = 0;
        $pessoasEnvolvidas = $clienteEnvolvidos;
        $ticketMedioNewc = $ticket["ticketMedioVolume"];
        $ticketMedioUSD = $ticket["ticketMedio"];
        
        $distribuicao = $distribuicaoTokenRn->getDadosVendaTokens(\Utils\Constantes::ID_ICO, 0, 0, null, null);
        
        foreach ($distribuicao as $idFase=>$dados) {
            $faseIco = new \Models\Modules\ICO\FaseIco(Array("id" => $idFase));
            $faseIcoRn->conexao->carregar($faseIco);
            
            $tokensDistribuidos += (double) $faseIco->tokensVendidos;
            
            $tokensVendidos += (double) $dados["tokensVendidos"];
            $tokensBonificados += (double) $dados["tokensBonificados"];
        }
        
        $mensagem = str_replace("{par2}", number_format($tokensDistribuidos, 8, ".", ""), $mensagem);
        $mensagem = str_replace("{par3}", number_format($tokensVendidos, 8, ".", ""), $mensagem);
        $mensagem = str_replace("{par4}", number_format($tokensBonificados, 8, ".", ""), $mensagem);
        $mensagem = str_replace("{par5}", $pessoasEnvolvidas, $mensagem);
        $mensagem = str_replace("{par6}", number_format($ticketMedioNewc, 8, ".", ""), $mensagem);
        $mensagem = str_replace("{par7}", number_format($ticketMedioUSD, 8, ".", ""), $mensagem);
        
        return $mensagem;
        
    }
    
}