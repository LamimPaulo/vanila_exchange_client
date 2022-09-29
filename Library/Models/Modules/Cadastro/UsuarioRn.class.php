<?php

namespace Models\Modules\Cadastro;

use LambdaAWS\LambdaNotificacao;
use \Models\Modules\Model\GenericModel;
use Utils\Mail;

/**
 * Classe que contém as regras de negócio da entidade Usuario
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class UsuarioRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Usuario());
        } else {
            $this->conexao = new GenericModel($adapter, new Usuario());
        }
        
    }
    
    public function salvar(Usuario &$usuario, $confirmacaoSenha, $permissoesRotinas = Array(), $permissoesModulos = Array()) {
        
        if (!empty($usuario->senha) && empty($confirmacaoSenha)) {
            throw new \Exception($this->idioma->getText("confirmaSenhaErr"));
        }
        if (empty($usuario->senha) && !empty($confirmacaoSenha)) {
            throw new \Exception($this->idioma->getText("informadaSenhaErr"));
        }
        
        if ($usuario->senha != $confirmacaoSenha) {
            throw new \Exception($this->idioma->getText("confirmacaoSenhaIguaisErr"));
        }
        
        if ($usuario->id > 0) {
            $aux = new Usuario(Array("id" => $usuario->id));
            $this->conexao->carregar($aux);
            
            $usuario->ativo = $aux->ativo;
            $usuario->observacoes = $aux->observacoes;
            $usuario->googleAuthAtivado = $aux->googleAuthAtivado;
            $usuario->googleAuthSecret = $aux->googleAuthSecret;
            $usuario->codAppAdvisor = $aux->codAppAdvisor;
            
            if (!empty($usuario->senha)) {
                $usuario->senha = sha1($usuario->senha.\Utils\Constantes::SEED_SENHA);
            } else {
                $usuario->senha = $aux->senha;
            }
            $usuario->dataCadastro = $aux->dataCadastro;
            $usuario->permiteAlteracao = $aux->permiteAlteracao;
            
            
            if (empty($usuario->foto)) {
                $usuario->foto = $aux->foto;
            }
            
            if ($aux->permiteAlteracao < 1) {
                throw new \Exception($this->idioma->getText("usuarioAlteracoesErr"));
            }
            
        } else {
            if (empty($usuario->senha)) {
                throw new \Exception($this->idioma->getText("informarSenhaErr"));
            }
            $usuario->senha = sha1($usuario->senha.\Utils\Constantes::SEED_SENHA);
            $usuario->ativo = 1;
            $usuario->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $usuario->permiteAlteracao = 1;
            
            
            $usuario->googleAuthAtivado = 0;
            $usuario->googleAuthSecret = null;
        }
        
        if ($usuario->tipo != \Utils\Constantes::ADMINISTRADOR && $usuario->tipo != \Utils\Constantes::VENDEDOR) {
            throw new \Exception($this->idioma->getText("usuarioInvalidoErr"));
        }
        
        if (empty($usuario->nome)) {
            throw new \Exception($this->idioma->getText("nomeUserInformadoErr"));
        }
        
        if (empty($usuario->email)) {
            throw new \Exception($this->idioma->getText("emailPrecisaIndformadoErr"));
        } 
        
        $result = $this->conexao->listar("email = '{$usuario->email}' AND id != {$usuario->id}");
        if (sizeof($result) > 0) {
            throw new \Exception($this->idioma->getText("emailJaCadastradoErr"));
        }
        
        $cel = str_replace(Array("(", ")", " ", "-"), "", $usuario->celular);
        if (strlen($cel) != 10 && strlen($cel) != 11) {
            throw new \Exception($this->idioma->getText("celularInvalido"));
        }
        
        if ($usuario->twoFactorAuth > 0 && empty($usuario->celular)) {
            throw new \Exception($this->idioma->getText("login2FAErr"));
        }
        
        $tiposAuth = Array(
            \Utils\Constantes::TIPO_AUTH_EMAIL,
            \Utils\Constantes::TIPO_AUTH_SMS
        );
        
        if (!in_array($usuario->tipoAutenticacao, $tiposAuth)) {
            $usuario->tipoAutenticacao = \Utils\Constantes::TIPO_AUTH_EMAIL;
        }
        
        $usuario->twoFactorAuth = 1;
        
        try {
            $usuario->observacoes = \Utils\Criptografia::encriptyPostId($usuario->observacoes);
            $this->conexao->salvar($usuario);
            
            $permissaoUsuarioRn = new \Models\Modules\Acesso\PermissaoUsuarioRn();
            $permissaoUsuarioRn->salvar($usuario, $permissoesRotinas);
            
            $permissaoModuloUsuarioRn = new \Models\Modules\Acesso\PermissaoModuloUsuarioRn();
            $permissaoModuloUsuarioRn->salvar($usuario, $permissoesModulos);
            
            if (\Utils\Geral::getLogado()->id == $usuario->id) {
                \Utils\Geral::setUsuario($usuario);
            }
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    /**
     * Função resposável por efetuar o login no sistema
     * @param \Models\Modules\Cadastro\Usuario $usuario
     * @throws \Exception
     */
    public function logar(Usuario &$usuario) {
        // Valida se o email foi informado
        if (empty($usuario->email)) {
            throw new \Exception($this->idioma->getText("loginEmailErr"));
        }
        // Valida se a senha foi informada
        if (empty($usuario->senha)) {
            throw new \Exception($this->idioma->getText("loginSenhaErr"));
        }

        $usuario->senha = sha1($usuario->senha.\Utils\Constantes::SEED_SENHA);

        $configuracaoRn = new ConfiguracaoRn();
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn->conexao->carregar($configuracao);

        if ($configuracao->statusLoginSistema < 1) {
            throw new \Exception($this->idioma->getText("sistemaIndisponivelErr"));
        }

        $clienteRn = new ClienteRn();
        $result = $clienteRn->conexao->listar("email = '" . $usuario->email . "' and senha = '" . $usuario->senha . "'" , null, null, 1);

        $logado = null;
        $auth = new \Models\Modules\Cadastro\Auth();
        if (sizeof($result) > 0) {

            $cliente = $result->current();

            $pagarIco = ($cliente->emailConfirmado < 1);
            $cliente->quantidadeTentativasLogin++;
            if ($cliente->quantidadeTentativasLogin >= 5) {
                $cliente->bloquearLogin = 1;

                $observacoesCliente = new ObservacaoCliente();
                $observacoesCliente->idCliente = $cliente->id;
                $observacoesCliente->observacoes = "Login bloqueado após {$cliente->quantidadeTentativasLogin} tentativas inválidas.";
                $observacaoClienteRn = new ObservacaoClienteRn();
                $observacaoClienteRn->salvar($observacoesCliente);
            } else {
                $cliente->bloquearLogin = 0;
            }

            $clienteRn->conexao->update(
                Array(
                    "quantidade_tentativas_login" => $cliente->quantidadeTentativasLogin, 
                    "bloquear_login" => $cliente->bloquearLogin
                ),
                Array("id" => $cliente->id)
            );

            if ($cliente->senha != $usuario->senha) {
                throw new \Exception($this->idioma->getText("senhaInvalidaErr"));
            }

            $clienteRn->conexao->update(
                Array(
                    "quantidade_tentativas_login" => 0,
                    "bloquear_login" => 0,
                    "data_ultimo_login" => date("Y-m-d H:i:s")
                ),
                Array("id" => $cliente->id)
            );

            if ($cliente->status == \Utils\Constantes::CLIENTE_AGUARDANDO) {
                throw new \Exception($this->idioma->getText("cadastroEmAnaliseErr"));
            }
            if ($cliente->status == \Utils\Constantes::CLIENTE_NEGADO) {
                throw new \Exception($this->idioma->getText("cadastroRecusadoErr"));
            }

            $clienteRn->conexao->update(Array("email_confirmado" => 1), Array("id" => $cliente->id));

            $auth->idCliente = $cliente->id;
            \Utils\Geral::setLogado(null, $cliente);

            $logado = $cliente;
            if ($pagarIco) {
                $clienteRn->pagarAirDropPromocaoICONEWC($cliente,  'Bônus de Cadastro', true);
            }

        } else {
            throw new \Exception($this->idioma->getText("loginESenhaInvalidosErr"));
        }
        
        $moduloRn = new \Models\Modules\Acesso\ModuloRn($this->conexao->adapter);
        $rotinaRn = new \Models\Modules\Acesso\RotinaRn($this->conexao->adapter);
        $listaModulos = $moduloRn->getModulosByPermissao($logado);
        $listaRotinas = $rotinaRn->getRotinas($logado);
        
        \Utils\Geral::setMenu($listaModulos, $listaRotinas);
        
        \Models\Modules\Acesso\LoginSistemaRn::registrar();
        $authRn = new \Models\Modules\Cadastro\AuthRn();
        $authRn->salvar($auth);
        
    }
    
    public function getByEmail($email) {
        if (!empty($email)) {
            $result = $this->conexao->listar("email = '{$email}'", null, null, 1);
            if (sizeof($result) > 0) {
                return $result->current();
            }
        }
        return null;
    }
    
        public function getByCpf($cpf) {
        if (!empty($cpf)) {
            $result = $this->conexao->listar("cpf = '{$cpf}'", null, null, 1);
            if (sizeof($result) > 0) {
                return $result->current();
            }
        }
        return null;
    }
    
    public function recuperar(Usuario $usuario) {
        
        $clienteRn = new ClienteRn();
        
        $where = new \Zend\Db\Sql\Where();

        $where->equalTo("email", $usuario->email);
        $result = $clienteRn->conexao->listar($where);

        if (sizeof($result) > 0) {
            $cliente = $result->current();

            if (intval($cliente->status) != 1) {
                throw new \Exception($this->idioma->getText("problemaContaErr"));
            }


            if (!$cliente->emailConfirmado > 0) {
                //throw new \Exception("O seu e-mail precisa ser confirmado para efetuar a troca de senha.");
            }

            $time = time();
            $hash = sha1("@NeWc{$cliente->nome}PaSsWoRd{$time}ReCoVeR");
            $validade = new \Utils\Data(date("d/m/Y H:i:s"));
            $validade->somar(0, 0, 0, 24);

            $cliente->hashRecuperacaoSenha = $hash;
            #$cliente->quantidadeTentativasRecuperacao;

            if ($cliente->quantidadeTentativasRecuperacao >= 10) {
                $cliente->bloquearRecuperacaoSenha = 1;
                $observacoesCliente = new ObservacaoCliente();
                $observacoesCliente->idCliente = $cliente->id;
                $observacoesCliente->observacoes = "Recuperação de senha bloqueada após {$cliente->quantidadeTentativasRecuperacao} tentativas inválidas.";
                $observacaoClienteRn = new ObservacaoClienteRn();
                $observacaoClienteRn->salvar($observacoesCliente);
            } else {
                $cliente->bloquearRecuperacaoSenha = 0;
            }

            $dados = Array(
                "hash_recuperacao_senha" => $cliente->hashRecuperacaoSenha, 
                "validade_hash_recuperacao_senha" => $validade->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO), 
                "quantidade_tentativas_recuperacao" => $cliente->quantidadeTentativasRecuperacao,
                "bloquear_recuperacao_senha" => $cliente->bloquearRecuperacaoSenha,
                "data_ultima_tentativa_recuperar" => date("Y-m-d H:i:s")
            );

            $clienteRn->conexao->update(
                    $dados, 
                    Array("id" => $cliente->id)
                );

            $listaEnvio = Array(
                Array("nome" => $cliente->nome, "email" => $cliente->email)
            );

            $conteudo = Array(
                "Hash" => $hash
            );

            $conteudo = Mail::template($conteudo, "Recuperar Senha", "Hash",$cliente->nome);

            $mail = new \Utils\Mail(BrandRn::getBrand()->nome, "Recuperar Senha", $conteudo, $listaEnvio);
            $mail->send();

        } else {
            throw new \Exception($this->idioma->getText("nenhumUserCPFinformado"), 99);
        }
    }
    
    
    public function excluir(Usuario &$usuario) {
        try {
            $this->conexao->adapter->iniciar();
            $this->conexao->carregar($usuario);

            
            $clienteRn = new ClienteRn($this->conexao->adapter);
            $clientes = $clienteRn->conexao->listar("id_usuario = {$usuario->id}");
            
            if (sizeof($clientes) > 0) {
            $texto1 = $this->idioma->getText("userNaoPodeExcluido");
            $texto1 = str_replace("{var1}",sizeof($clientes), $texto1);
                throw new \Exception($texto1);
            }
            
            $this->conexao->excluir($usuario);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    public function alterarStatusAtivo(Usuario &$usuario) {
        $this->conexao->carregar($usuario);
        $usuario->ativo = $usuario->ativo ? 0 : 1;
        $this->conexao->update(Array("ativo" => $usuario->ativo), Array("id" => $usuario->id));
    }
    
    
    
    public function alterarSenha(Usuario $usuario, $confirmacao, $senhaAtual) {
        
        try {
            $u = new Usuario(Array("id" => $usuario->id));
            $this->conexao->carregar($u);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("userNaoLocalizadoErr"));
        }
        
        if ($u->senha !== sha1($senhaAtual . \Utils\Constantes::SEED_SENHA)) {
            throw new \Exception($this->idioma->getText("senhaAtualInvalidErr"));
        }
        
        if (empty($usuario->senha)) {
            throw new \Exception($this->idioma->getText("informarSenhaErr"));
        }
        
        if (empty($confirmacao)) {
            throw new \Exception($this->idioma->getText("necessConfirmaSenhaErr"));
        }
        
        if ($usuario->senha != $confirmacao) {
            throw new \Exception($this->idioma->getText("senhaConfirmacaoErr"));
        }
        
        $usuario->senha = sha1($usuario->senha . \Utils\Constantes::SEED_SENHA);
        
        $this->conexao->update(Array("senha" => $usuario->senha), Array("id" => $usuario->id));
        
        if (\Utils\Geral::isUsuario()) {
            $usuarioLogado = \Utils\Geral::getLogado();
            if ($usuario->id == $usuarioLogado->id) {
                if (\Utils\Geral::isCliente()) {
                    \Utils\Geral::setLogado($usuario, \Utils\Geral::getCliente());
                } else {
                    \Utils\Geral::setLogado($usuario, null);
                }
            }
        }
        
    }
}

?>
