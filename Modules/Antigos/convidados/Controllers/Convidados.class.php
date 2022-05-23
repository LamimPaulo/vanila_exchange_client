<?php

namespace Modules\convidados\Controllers;

class Convidados {
    
    private $codigoModulo = "convites";
    private $idioma = null;
    
    public function __construct() {
        $this->idioma = new \Utils\PropertiesUtils("convidados", 'IDIOMA');
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        try {
            $configuracao = new \Models\Modules\Cadastro\Configuracao(Array("id" => 1));
            $configuracaoRn = new \Models\Modules\Cadastro\ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);
            
            $params["configuracao"] = $configuracao;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("convidados", $params);
    }
    
    
    public function enviar($params) {
        try {
            $email = \Utils\Post::get($params, "email", null);
            
            if (!\Utils\Validacao::email($email)) {
                throw new \Exception($this->idioma->getText("emailCadastradoSistema"));
            }
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $result = $clienteRn->conexao->listar("email = '{$email}'", NULL, NULL, NULL);
            if (sizeof($result) > 0) {
                throw new \Exception($this->idioma->getText("emailCadastradoSistema"));
            }
            
            $cliente = \Utils\Geral::getCliente();
            \Email\EmailConvite::send($cliente, $email);
            
            $clienteConvidado = new \Models\Modules\Cadastro\ClienteConvidado();
            $clienteConvidado->idCliente = $cliente->id;
            $clienteConvidado->email = $email;
            
            $clienteConvidadoRn = new \Models\Modules\Cadastro\ClienteConvidadoRn();
            $clienteConvidadoRn->salvar($clienteConvidado);
            
            $json["sucesso"] = true;
            $json["mensagem"] = $this->idioma->getText("conviteEnviadoSucesso");
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function listar($params) {
        try {
            $cliente = \Utils\Geral::getCliente();
            
            $data = new \Utils\Data(date("d/m/Y H:i:s"));
            $data->subtrair(0, 0, 180);
            
            $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
            $clientes = $clienteRn->conexao->listar("id_cliente_convite = {$cliente->id}  AND data_cadastro >= '{$data->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ", "nome");
            
            $clienteConvidadoRn = new \Models\Modules\Cadastro\ClienteConvidadoRn();
            
            $total = 0;
            $qtdClientes = 0;
            
            ob_start();
            if (sizeof($clientes) > 0) {
                foreach ($clientes as $c) {
                    $clienteConvidado =  $clienteConvidadoRn->get($cliente, $c->email);
                    $total += ($clienteConvidado != null ? $clienteConvidado->comissao : 0);
                    $qtdClientes++;
                    ?>
                    <tr>
                        <td><?php echo $c->nome ?></td>
                        <td><?php echo $c->email ?></td>
                        <td class="text-center"><?php echo $c->dataCadastro->formatar(\Utils\Data::FORMATO_PT_BR) ?></td>
                        <td class="text-center"><?php echo $c->dataCadastro->formatar(\Utils\Data::FORMATO_HORA) ?></td>
                        <td class="text-center"><?php echo ($clienteConvidado != null ? $clienteConvidado->movimento : "") ?></td>
                        <td class="text-center">R$ <?php echo number_format(($clienteConvidado != null ? $clienteConvidado->comissao : 0), 2, ",", ".") ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="6" class='text-center'><?php echo $this->idioma->getText("nenhumConvidado") ?></td>
                </tr>
                <?php
            }
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["total"] = number_format($total, 2, ',', ".");
            $json["clientes"] = $qtdClientes;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
}