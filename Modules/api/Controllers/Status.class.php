<?php

namespace Modules\api\Controllers;

class Status {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    public function index() {
        $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
        $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        $configuracaoSite = \Models\Modules\Cadastro\ConfiguracaoSiteRn::get();
        
        $exibirMensagem = true;
        
        if (empty($configuracao->alertaSite)) {
            $exibirMensagem = false;
        } else if (isset($configuracao->dataFinalAlertaSite->data) && $configuracao->dataFinalAlertaSite->data == null) { 
            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            if ($configuracao->dataFinalAlertaSite->menor($dataAtual)) {
                $exibirMensagem = false;
            }
        } 
        
        $mensagem = null;
        if ($configuracao->exibirMensagemSite > 0 && $exibirMensagem) {
            $mensagem = Array(
                "tipo" => $configuracao->tipoAlertaSite,
                "mensagem" => $configuracao->alertaSite
            );
        }

        $dados["cacentro"] = Array(
                /* Call to Action Centro */
                "imagemCAcentro" => URLBASE_CLIENT . UPLOADS . $configuracaoSite->imagemCacentro,
                "textoCAcentro" => $configuracaoSite->textoCacentro,
                "tituloCAcentro" => $configuracaoSite->tituloCacentro,
                "labelCABotao" => $configuracaoSite->labelBotaoCacentro,
                "showCelularCAcentro" => ($configuracaoSite->showCelularCacentro > 0),
                "showbotaoCAcentro" => ($configuracaoSite->showBotaoCacentro > 0),
                "urlbotaoCAcentro" => $configuracaoSite->urlBotaoCacentro,
                "showCAcentro" => ($configuracaoSite->showCacentro > 0)
            );


        $dados["cabase"] = Array(
                /* Call to Action Base */
                "imagemCAbase" => URLBASE_CLIENT . UPLOADS . $configuracaoSite->imagemCabase,
                "textoCAbase" => $configuracaoSite->textoCabase,
                "tituloCAbase" => $configuracaoSite->tituloCabase,
                "labelCABotao" => $configuracaoSite->labelBotaoCabase,
                "showCelularCAbase" => ($configuracaoSite->showCelularCabase > 0),
                "showbotaoCAbase" => ($configuracaoSite->showBotaoCabase > 0),
                "urlbotaoCAbase" => $configuracaoSite->urlBotaoCabase,
                "showCAbase" => ($configuracaoSite->showCabase > 0)
            );
        
        $dados["alerta"] =  Array(
            "sucesso" => true,
            "statusNovosCadastros" => ($configuracao->statusNovosCadastros > 0),
            "mensagem" => $mensagem,
            "statusLogin" => ($configuracao->statusLoginSistema > 0)


        );
        
        print json_encode($dados);
    }
    
    
}