<?php
namespace Modules\site\Controllers;
class Cadastro {
    
    public function index($params) {  
        
        \Utils\Session::close();
        try {
            $referencia = "";
            $cpf = "";
            $dataNascimento = "";
            
            $email = "";
            
            
            if (\Utils\Post::get($params, "cpf") && \Utils\Post::get($params, "dataNascimento") && \Utils\Post::get($params, "email")) {
                $referencia = \Utils\Post::get($params, "referencia");
                $cpf = \Utils\Post::get($params, "cpf");
                $dataNascimento = (strlen(\Utils\Post::get($params, "dataNascimento", "")));
                $email = \Utils\Post::get($params, "email");
            } else {
                $referencia = \Utils\Get::get($params, 0, 0);
                
            }
            
            
            $params["referencia"] = $referencia;
            
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $params["configuracao"] = $configuracao;
            
            $params["cpf"] = $cpf;
            $params["dataNascimento"] = $dataNascimento;
            $params["email"] = $email;
            
        } catch (\Exception $ex) {

        }
        \Utils\Layout::view("cadastro", $params);
    }

    public function getCidades($params) {
        try {
            $estado = new \Models\Modules\Cadastro\Estado(Array("id" => \Utils\Post::getEncrypted($params, "idEstado", 0)));
            
            try {
                $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
                $estadoRn->conexao->carregar($estado);
            } catch (\Exception $ex) {
                throw new \Exception("Estado não encontrado");
            }
            
            $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
            $cidades = $cidadeRn->conexao->listar("id_estado = {$estado->id}", "nome");
            
            ob_start();
            foreach ($cidades as $cidade) {
                ?>
                <option value="<?php echo $cidade->codigo?>"><?php echo $cidade->nome ?></option>    
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function cpf($params) {
        
        try {
            $nome = $this->consultarCpf($params);
            
            $json["nome"] = $nome;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }


    public function consultarCpf($params) {
        
        $cpf = \Utils\Post::getDoc($params, "cpf", "");
        $dataNascimento = \Utils\Post::getData($params, "nascimento", null);
        $dataNascimento = $dataNascimento->formatar(\Utils\Data::FORMATO_PT_BR);

        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $result = $clienteRn->conexao->listar("documento = '{$cpf}' ");

        if (sizeof($result) > 0) {
            throw new \Exception("O CPF informado já está cadastrado");
        }

        /*$consultaCpfRn = new \Models\Modules\Cadastro\ConsultaCpfRn();
        $consultaCpf = $consultaCpfRn->getByCpf($cpf);
        if ($consultaCpf != null) {
            if ($consultaCpf->anoObito > 0) {
                throw new \Exception("Desculpe. Parece que você faleceu em {$consultaCpf->anoObito}. Nossos pêsames");
            }
        }*/

        /*if (AMBIENTE == "producao") { // esse trecho evita que consulta de CPF sejam feitas em Homologação. Para testar a consulta em homologação deve-se comentar este if

            $dados = \Modules\services\Controllers\Consulta::cpf($cpf);

            if ($dataNascimento != $dados["dataNascimento"]) {
                throw new \Exception("Data de nascimento inválida");
            }

            $dtNascimento = new \Utils\Data($dataNascimento . " 00:00:00");
            $dataAtual = new \Utils\Data(date("d/m/Y H:i:s"));
            $diff = $dataAtual->diferenca($dtNascimento);


            if ($diff->y < 18) {
                throw new \Exception("É necessário possuir idade igual ou superior a 18 anos");
            }

            if ($dados["anoObito"] > 0) {
                throw new \Exception("Desculpe. Parece que você faleceu em {$dados["anoObito"]}. Nossos pêsames");
            }


            //$dados["nome"] = "";
            $nome = $dados["nome"];
        } else {
            if (AMBIENTE == "producao") { 
                $nome = "";
            } else {
                $nome = "Cliente de Teste";
            }
        }*/
        
        return $nome;
    }
    
    public function forcaSenha($params) {
        try {
            $senha = \Utils\Post::get($params, "senha", "");
            
            $forca = \Utils\Senha::forca($senha);
            
            $json["forca"] = $forca;
            $json["percentual"] = ($forca = 0 ? 0 : ($forca < 4 ? 3 : ($forca < 6 ? 6 : 10)));
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }    
            
}

