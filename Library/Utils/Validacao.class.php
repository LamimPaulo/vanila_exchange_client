<?php
/**
 * Classe para validação de dados.
 */
namespace Utils;
/***
 * Contém as validações de dados genéricos no sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Util
 */
class Validacao {

    
    /**
     * Valida endereco de saque
     *
     * @param string $carteira
     * @param Moeda $moeda
     *
     * @return boolean carteira false ou true
     */
    public static function validaCarteira($carteira, $moeda) {
        
        $validade = false;
        
        //Verifica caracteres especiais
        if (!preg_match('/[^a-zA-Z\d]/', $carteira)) {
           
            $validade = true;
            if (strlen($carteira) > 30) {
                $validade = true;
                
                switch ($moeda->id) {
                    case 2: //Bitcoin           
                        $first = Array("1", "3", "B"); 
                        if(in_array(strtoupper(substr($carteira, 0, 1)), $first)){                          
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;
                        
                    case 4: //Litecoin           
                        $first = Array("L", "M", "3"); 
                        if(in_array(strtoupper(substr($carteira, 0, 1)), $first)){                          
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;
                        
                    case 23: //BBK           
                        $first = Array("B");
                        if (in_array(strtoupper(substr($carteira, 0, 1)), $first)) {
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;
                        
                    case 5: //Dash
                        $first = Array("X", "7");
                        if(in_array(strtoupper(substr($carteira, 0, 1)), $first)){                          
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;    
                        
                    case 19: //LQX
                        if(strtoupper(substr($carteira, 0, 1)) == "L"){
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;
                    case 18: //Lunes
                        if(strtoupper(substr($carteira, 0, 1)) == "3"){
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;
                    case 7: //DOGE
                        if(strtoupper(substr($carteira, 0, 1)) == "D"){
                            $validade = true;
                        } else {
                            $validade = false;
                        }
                        break;

                    case 3: //ETH - Tokens
                    case 13:    
                    case 14:
                    case 15:
                    case 16:
                    case 17: 
                    case 21:
                    case 30:
                    case 32:
                    case 28:
                    case 27:
                    case 26:
                    case 28:
                    case 31:
                    case 33:
                    case 34:
                    case 35:
                    case 36:
                    case 37:
                    case 38:
                    case 39:
                    case 40:
                    case 41:
                    case 42:
                    case 43:
                    case 46:
                    case 47:
                    case 48:
                    case 49:
                        if (strtoupper(substr($carteira, 0, 2)) == "0X") {
                            if (strlen($carteira) == 42) {
                                $validade = true;
                            } else {
                                $validade = false;
                            }
                        } else {
                            $validade = false;
                        }
                        break;
                    
                    default :
                        $validade = true;
                }
            } else {
                $validade = false;
            }
        }
        
        return $validade;
    }
    
   /**
     * email Valida o email 
     *
     * @param string $email Valor do email
     * @return boolean Email válido ou não
     */
    public static function email($email) {
        $conta = "^[a-zA-Z0-9\._-]+@";
        $domino = "[a-zA-Z0-9\._-]+\.";
        $extensao = "([a-zA-Z]{2,4})$";
        $pattern = $conta . $domino . $extensao;
        return preg_match("/{$pattern}/", $email);
    }
    
    public static function verificarNomeCompleto($string) {
        $retorno = true;
        if(is_numeric(filter_var($string, FILTER_SANITIZE_NUMBER_INT))){
            $retorno = false;
        }
        if(!preg_match("/[A-Z][a-z]* [A-Z][a-z]*/", $string)){
            $retorno = false;
        }        
        if (!preg_match('/^[a-zA-Z0-9]+/', $string)) {
            $retorno = false;
        }
        return $retorno;
    }

    /**
     * cpf Valida o CPF
     *
     * @param string $cpf Valor do CPF com ou sem formatação 
     * @return boolean CPF válido ou não
     */
    public static function cpf($cpf) {
        $cpf = str_replace(array("-", "."), "", $cpf);
        if ($cpf != '') {
            if (is_numeric($cpf)) {
                $cpf = preg_replace("/[^0-9]/", "", $cpf);
                $digitoUm = 0;
                $digitoDois = 0;

                for ($i = 0, $x = 10; $i <= 8; $i++, $x--) {
                    $digitoUm += $cpf [$i] * $x;
                }
                for ($i = 0, $x = 11; $i <= 9; $i++, $x--) {
                    if (str_repeat($i, 11) == $cpf) {
                        return false;
                    }
                    $digitoDois += $cpf [$i] * $x;
                }

                $calculoUm = (($digitoUm % 11) < 2) ? 0 : 11 - ($digitoUm % 11);
                $calculoDois = (($digitoDois % 11) < 2) ? 0 : 11 - ($digitoDois % 11);
                if ($calculoUm != $cpf [9] || $calculoDois != $cpf [10]) {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
   /**
    * cnpj Valida o CNPJ
    *
    * @param string $cnpj Valor do CNPJ com ou sem formatação
    * @return boolean CNPJ válido ou não
    *   
    */
    public static function cnpj($cnpj) {
        $cnpj = str_pad(str_replace(array('.', '-', '/'), '', $cnpj), 14, '0', STR_PAD_LEFT);
        if (strlen($cnpj) != 14) {
            return false;
        } else {
            for ($t = 12; $t < 14; $t++) {
                for ($d = 0, $p = $t - 7, $c = 0; $c < $t; $c++) {
                    $d += $cnpj{$c} * $p;
                    $p = ($p < 3) ? 9 : --$p;
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cnpj{$c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }
    
    /**
    * Remove acentos e caracteres especiais.
    *
    * @param string com caracteres especiais e espaços
    * @return string limpa
    *   
    */
    public static function limparString($string, $espaco = true) {
        if($string !== mb_convert_encoding(mb_convert_encoding($string, 'UTF-32', 'UTF-8'), 'UTF-8', 'UTF-32'))
            $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
        $string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');
        $string = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i', '\1', $string);
        $string = html_entity_decode($string, ENT_NOQUOTES, 'UTF-8');
        $string = preg_replace(array('`[^a-z0-9]`i','`[-]+`'), ' ', $string);
        $string = preg_replace('/( ){2,}/', '$1', $string);
        if($espaco){
          $string = strtoupper(trim($string));
          $string = str_replace(" ", "", $string);  
        }        
        return $string;
    }
    
    
    /**
     * Efetua as validações de permissão de acesso as rotinas e a sessão 
     * 
     * @param string $codigoModulo Código do módulo que será acessado
     * @return string String com os valores
     */
    public static function acesso($codigoModulo) {
        $idioma = new \Utils\PropertiesUtils("utils", 'IDIOMA');
        //Verificação da permissão de acesso
        $excecao = null;
        $json["resultado"] = array('sucesso' => false);
        //exit(print_r($_SESSION));
        
        $redirecionado = false;
        
        if (!Geral::isLogado()) {
            $excecao = new \Exception($idioma->getText("sessaoExpirada"));
            $json['mensagem'] = $idioma->getText("sessaoExpirada");
            $json['redirect'] = true;
            $json['url'] = URLBASE_CLIENT . Rotas::R_LOGIN;
        } else {
            if (!Geral::isAutenticado()) {
                $redirecionado = true;
                Geral::redirect(URLBASE_CLIENT . Rotas::R_TWOFACTORAUTH);
            } else {
                
                
                $moduloRn = new \Models\Modules\Acesso\ModuloRn();
                
                if (!$moduloRn->validarAcesso($codigoModulo)) {
                    $excecao =  new \Exception($idioma->getText("naoPossuiPermissao"), 108);
                    $json['mensagem'] = $idioma->getText("naoPossuiPermissao");
                }
                
            }
            if (Geral::isCliente()) {
                if (!$redirecionado) {
                    $cliente = Geral::getCliente();
                    
                    $documentoSistemaRn = new \Models\Modules\Cadastro\DocumentoSistemaRn();
                    $clienteHaDocumentoSistemaRn = new \Models\Modules\Cadastro\ClienteHasDocumentoSistemaRn();

                    $codigos = $documentoSistemaRn->getCodigos(true);

                        foreach ($codigos as $cod) {
                            $doc = $documentoSistemaRn->getDocumentoSistema($cod);
                            if ($doc != null) {
                                $aceite = $clienteHaDocumentoSistemaRn->getAceiteCliente($cliente, $doc);
                                if ($aceite == null ) {
                                    $redirecionado = true;
                                    Geral::redirect(URLBASE_CLIENT . Rotas::R_DOCS_ACEITACAO);
                                }
                            }
                        }   
                 
                    if (!$redirecionado) {
                        $notificacaoMoedaHasLidoRn = new \Models\Modules\Cadastro\NotificacaoMoedaHasLidoRn();
                        $result = $notificacaoMoedaHasLidoRn->notificacoesNaoLidas($cliente);
                        if (sizeof($result) > 0) {
                            Geral::redirect(URLBASE_CLIENT . Rotas::R_NOTIFICACAO_MOEDA_CLIENTE);
                        }
                    }
                    
                    if (!$redirecionado) {
                        //if ($cliente->id == 15093064543895 || $cliente->id == 15093064543892 || $cliente->id == 15093064555961 || $cliente->id == 15093064544017) {
                            
                            $marketingImagemHasLidoRn = new \Models\Modules\Cadastro\MarketingImagemHasLidoRn();
                            $marketingImagemHasLido = new \Models\Modules\Cadastro\MarketingImagemHasLido();
                            $marketingImagemRn = new \Models\Modules\Cadastro\MarketingImagemRn();
                            $marketingImagem = new \Models\Modules\Cadastro\MarketingImagem();
                            
                            $result = $marketingImagemRn->conexao->listar(" ativo = 1 AND date(data_inicial) <= curdate() AND date(data_final) >= curdate() ");

                            if (sizeof($result) > 0) {
                               
                                $notificacao = $result->current();
                                if (!$redirecionado) {
                                    $marketingImagem->id = $notificacao->id;
                                    $marketingImagemRn->conexao->carregar($marketingImagem);
                                    
                                    $qtdVisualizacao = $marketingImagemHasLidoRn->conexao->listar("id_notificacao = {$marketingImagem->id} AND id_cliente = {$cliente->id}", "data_leitura DESC");

                                    if (sizeof($qtdVisualizacao) < $marketingImagem->qtdMaxVisualizacao) {
                                        
                                        if (sizeof($qtdVisualizacao) < 1) {
                                         
                                            Geral::redirect(URLBASE_CLIENT . Rotas::R_MARKETING_IMAGEM_ONLY_VIEW . "/" . Criptografia::encriptyPostId($marketingImagem->id));
                                                                                    
                                        } else {
                                             
                                            $marketingImagemHasLido->id = $qtdVisualizacao->current()->id;

                                            $marketingImagemHasLidoRn->conexao->carregar($marketingImagemHasLido);

                                            $marketingImagemHasLido->dataLeitura->somar(0, 0, $marketingImagem->intervalo);

                                            $dataAtual = new Data(date("Y-m-d H:i:s"));
                                            
                                            if ($dataAtual->maior($marketingImagemHasLido->dataLeitura)) {
                                                Geral::redirect(URLBASE_CLIENT . Rotas::R_MARKETING_IMAGEM_ONLY_VIEW . "/" . Criptografia::encriptyPostId($marketingImagem->id));
                                            }
                                        }
                                    }
                                }
                            }
                       // }
                    }
                }
            }
            
        }
        
        if (Geral::isCliente()) {
            $cliente = Geral::getCliente();
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clienteRn->conexao->carregar($cliente);
            
            if ($cliente->status != 1) {
                $excecao =  new \Exception($idioma->getText("cadastroSuspenso"), 108);
                $json['mensagem'] = $idioma->getText("cadastroSuspenso");
                Session::close();
                Geral::redirect(URLBASE_CLIENT . Rotas::R_LOGOUT);
                $redirecionado = true;
            }
            
            
        } else if (Geral::isUsuario()) {
            $usuario = Geral::getLogado();
            $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
            $usuarioRn->conexao->carregar($usuario);
            
            if ($usuario->ativo < 1) {
                $excecao =  new \Exception($idioma->getText("cadastroSuspenso"), 108);
                $json['mensagem'] = $idioma->getText("cadastroSuspenso");
                Session::close();
                Geral::redirect(URLBASE_CLIENT . Rotas::R_LOGOUT);
                $redirecionado = true;
            } 
            
        }
        
         // verifico se a já não existe o redirecionamento para a autenticação em duas etapas
        if (!$redirecionado) {
            //Vericação se é uma requisição ajax, ou requisição do arquivo do formulário
            if ($excecao != null) {
                if (Geral::isAjax()) {
                    die(json_encode($json));
                } else {
                    print json_encode($json);
                    Geral::redirect(URLBASE_CLIENT . Rotas::R_LOGIN);
                }
            }
        }
        
        
    }
    
    

}

?>
