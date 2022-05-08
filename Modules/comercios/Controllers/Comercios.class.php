<?php

namespace Modules\comercios\Controllers;

class Comercios {
    
    private  $codigoModulo = "comercios";
    
    function __construct($params) {
        \Utils\Validacao::acesso($this->codigoModulo);
        
        \Modules\principal\Controllers\Principal::validarAcessoCliente($params, false);
        
    }
    
    
    public function index($params) {
        try {
            
            $segmentoComercioRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
            $segmentos = $segmentoComercioRn->conexao->listar("ativo > 0", "nome", null, null);
            
            $estadoRn = new \Models\Modules\Cadastro\EstadoRn();
            $estados = $estadoRn->conexao->listar(null, "sigla", null, null);
            
            $params["estados"] = $estados;
            $params["segmentos"] = $segmentos;
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["sucesso"] = \Utils\Excecao::mensagem($ex);
        }
        
        \Utils\Layout::view("comercios", $params);
    }
    
    public function cadastro($params) {
        try {
            
            $comercio = new \Models\Modules\Cadastro\Comercio();
            $comercio->id = \Utils\Post::getEncrypted($params, "id", 0);
            
            if ($comercio->id > 0) {
                $comercioRn = new \Models\Modules\Cadastro\ComercioRn();
                $comercioRn->carregar($comercio, true, false, true, true);
            }
            
            $comercio->id = \Utils\Criptografia::encriptyPostId($comercio->id);
            $comercio->idSegmentoComercio = \Utils\Criptografia::encriptyPostId($comercio->idSegmentoComercio);
            
            if ($comercio->cidade != null) {
                $comercio->cidade->estado->id = (\Utils\Criptografia::encriptyPostId($comercio->cidade->estado->id));
            }
            
            $json["comercio"] = $comercio;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function listar($params) {
        try {
            
            
            $cliente = \Utils\Geral::getCliente();
            
            $comercioRn = new \Models\Modules\Cadastro\ComercioRn();
            $result = $comercioRn->listar("id_cliente = {$cliente->id}", "id", null, null, false, false, false);
           
            $lista = Array();
            foreach ($result as $comercio) {
                $comercio->id = \Utils\Criptografia::encriptyPostId($comercio->id);
                $lista[] = $comercio;
            }
            
            $json["comercios"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function salvar($params) {
        try {
            $comercio = new \Models\Modules\Cadastro\Comercio();
            
            $comercio->id = \Utils\Post::getEncrypted($params, "id", 0);
            $comercio->bairro = \Utils\Post::get($params,  "bairro", null);
            $comercio->cep = \Utils\Post::get($params,  "cep", null);
            $comercio->codigoCidade = \Utils\Post::get($params,  "codigoCidade", null);
            $comercio->coordenadas = \Utils\Post::get($params,  "coordenadas", null);
            $comercio->descricao = \Utils\Post::get($params,  "descricao",null);
            $comercio->endereco = \Utils\Post::get($params,  "endereco", null);
            $comercio->idSegmentoComercio = \Utils\Post::getEncrypted($params,  "idSeguimentoComercio", null);
            
            $comercio->numero = \Utils\Post::get($params,  "numero", null);
            $comercio->publico = \Utils\Post::get($params,  "publico", 0);
            
            $comercioRn = new \Models\Modules\Cadastro\ComercioRn();
            $comercioRn->salvar($comercio);
            
            
            $comercio->id = \Utils\Criptografia::encriptyPostId($comercio->id);
            $json["comercio"] = $comercio;
            $json["sucesso"] = true;
            $json["mensagem"] = "Dados salvos com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function excluir($params) {
        try {
            $comercio = new \Models\Modules\Cadastro\Comercio();
            $comercio->id = \Utils\Post::getEncrypted($params, "id", 0);
            $comercioRn = new \Models\Modules\Cadastro\ComercioRn();
            $comercioRn->conexao->excluir($comercio);
            
            
            $json["id"] = \Utils\Criptografia::encriptyPostId($comercio->id);
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function getCidadesByEstado($params) {
        try {
            
            $estado = new \Models\Modules\Cadastro\Estado();
            $estado->id = \Utils\Post::getEncrypted($params, "estado", 0);
            
            if ($estado->id > 0) {
                $cidadeRn = new \Models\Modules\Cadastro\CidadeRn();
                $result = $cidadeRn->conexao->listar("id_estado = {$estado->id}", "nome");
            } else {
                $result = Array();
            }
            ob_start();
            ?>
            <option value="0">Selecione uma cidade</option>
            <?php
            foreach ($result as $cidade) {
                ?>
                <option value="<?php echo $cidade->codigo ?>"><?php echo $cidade->nome ?></option>
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
    
    
    public function administrativo($params) {
        try {
            
            $segmentoComercioRn = new \Models\Modules\Cadastro\SeguimentoComercialRn();
            $segmentos = $segmentoComercioRn->conexao->listar("ativo > 0", "nome", null, null);
            
            
            $params["segmentos"] = $segmentos;
            
            $params["sucesso"] = true;
        } catch (\Exception $ex) {
            $params["sucesso"] = false;
            $params["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        \Utils\Layout::view("administrativo_comercios", $params);
    }
    
    
    public function administrativoListar($params) {
        try {
            
            $segmento = \Utils\Post::getEncrypted($params, "segmento", 0);
            $filtro = \Utils\Post::get($params, "filtro", null);
            
            $comercioRn = new \Models\Modules\Cadastro\ComercioRn();
            $comercios = $comercioRn->filtrar(0, $segmento, $filtro);
            
            $dados = $this->htmlListaComercioAdministrativo($comercios);
            
            $json["html"] = $dados["html"];
            $json["popover"] = $dados["popover"];
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function htmlListaComercioAdministrativo($lista) {
        $popover = Array();
        ob_start();
        
        if (sizeof($lista) > 0) {
            foreach ($lista as $comercio) {
                $popover[$comercio->id] = $this->htmlPopoverComercios($comercio);
                $this->htmlComercioAdministrativo($comercio);
            }
        } else {
            ?>
                <tr>
                    <td class="text-center" colspan="5" >Nenhum comércio cadastrado no sistema.</td>
                </tr>
            <?php
        }
        $html = ob_get_contents();
        ob_end_clean();
        
        return Array("html"=> $html, "popover" => $popover);
    }
    
    public function htmlComercioAdministrativo(\Models\Modules\Cadastro\Comercio $comercio) {
        
        ?>
        <tr id="comercio-<?php echo \Utils\Criptografia::encriptyPostId($comercio->id); ?>">
            <td><?php echo $comercio->descricao ?></td>
            <td><?php echo $comercio->cliente->nome ?></td>
            <td><?php echo $comercio->seguimentoComercial->nome ?></td>
            <td class='text-center'>
                <a tabindex="0" class="btn btn-xs btn-info popover-comercios" id="popover-comercios-<?php echo \Utils\Criptografia::encriptyPostId($comercio->id); ?>" role="button" data-controle='<?php echo $comercio->id?>' data-nome='<?php echo $comercio->descricao ?>' data-toggle="popover" data-trigger="focus" style="margin-left: 5px; ">
                    Detalhes
                </a>
            </td>
            <td class='text-center'>
                <button class="btn btn-xs btn-<?php echo ($comercio->ativo < 1 ? "danger" : "primary") ?>" type="button" 
                        onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($comercio->id); ?>');">
                    <i class="fa fa-<?php echo ($comercio->ativo < 1 ? "square-o" : "check") ?>"></i> 
                </button>
            </td>
        </tr>
        <?php
    }
    
    
    public function htmlPopoverComercios(\Models\Modules\Cadastro\Comercio $comercio) {
        ob_start();
        ?>
        <p>
            <strong>Cliente: </strong> <?php echo $comercio->cliente->nome ?> <br>
        </p>
        <p>
            <strong>Segmento: </strong> <?php echo $comercio->seguimentoComercial->nome ?> <br>
        </p>
        <p>
            <strong>Endereço: </strong> <?php echo $comercio->endereco ?> <br>
        </p>
        <p>
            <strong>Número: </strong> <?php echo $comercio->numero ?> <br>
        </p>
        <p>
            <strong>CEP: </strong> <?php echo $comercio->cep ?> <br>
        </p>
        <p>
            <strong>Bairro: </strong> <?php echo $comercio->bairro ?> <br>
        </p>
        <p>
            <strong>Cidade: </strong> <?php echo $comercio->cidade->nome ?> <br>
        </p>
        <p>
            <strong>Estado: </strong> <?php echo $comercio->cidade->estado->nome ?> <br>
        </p>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function alterarStatus($params) {
        try {
            $comercio = new \Models\Modules\Cadastro\Comercio();
            $comercio->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $comercioRn = new \Models\Modules\Cadastro\ComercioRn();
            $comercioRn->alterarStatusAtivo($comercio);
            
            ob_start();
            $this->htmlComercioAdministrativo($comercio);
            $html = ob_get_contents();
            ob_end_clean();
            
            $popover = Array();
            $popover[$comercio->id] = $this->htmlPopoverComercios($comercio);
            
            $json["popover"] = $popover;
            $json["codigo"] = \Utils\Criptografia::encriptyPostId($comercio->id);
            $json["html"] = $html;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}