<?php

namespace Modules\configuracoes\Controllers;

class LicencasSoftware {
    
    private $codigoModulo = "configuracoes";
    
    public function __construct($params) {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    public function index($params) {
        
        
        \Utils\Layout::view("index_licencas_software", $params);
    }
    
    public function listar($params) {
        
        try {
            
            $licencaSoftwareRn = new \Models\Modules\Cadastro\LicencaSoftwareRn();
            $licencas = $licencaSoftwareRn->conexao->listar(NULL, "ordem", NULL, NULL);
            
            ob_start();
            if (sizeof($licencas) > 0) {
                foreach ($licencas as $licenca) {
                    $this->tableLicencaSoftware($licenca);
                }
            } else {
                ?>
                <div class="col col-xs-12 text-center">
                    Nenhuma licença cadastrada
                </div>
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
    
    
    
    private function tableLicencaSoftware(\Models\Modules\Cadastro\LicencaSoftware $licencaSoftware) {
        
        $licencaHasRecursoRn = new \Models\Modules\Cadastro\LicencaSoftwareHasRecursoRn();
        $recursosLicenca = $licencaHasRecursoRn->conexao->listar("id_licenca_software = {$licencaSoftware->id}");
        $array = Array();
        foreach ($recursosLicenca as $licencaHasRecurso) {
            $array[] = $licencaHasRecurso->idRecursoLicenca;
        }
        
        $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
        $recursos = $recursoLicencaRn->conexao->listar(NULL, "ordem", null, null);
        
        ?>
        <div class="col-sm-4">
            <table class="table table-striped table-hover table-condensed table-bordered">
                <tbody>
                    <tr>
                        <td style="font-size: 18px;color: #00cf7a; text-align: center;">
                            <label for="perfilVendedorComissionado">
                                <?php echo $licencaSoftware->nome ?>
                            </label>
                        </td>
                        <td class="text-center">
                            <input type="radio" name="tipoPerfil" id="perfil-<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id) ?>" value="<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id) ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <td >
                            <strong>Investimento</strong>
                        </td>
                        <td class="text-center">
                            R$ <?php echo number_format($licencaSoftware->preco, 2, ",", ".") ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <td >
                            <strong>Duração da Licença</strong>
                        </td>
                        <td class="text-center">
                            <?php echo $licencaSoftware->mesesDuracao ?> (meses)
                        </td>
                    </tr>
                    
                    <tr>
                        <td >
                            <strong>Tempo de liberação de depósitos e saques</strong>
                        </td>
                        <td class="text-center">
                            <?php echo $licencaSoftware->tempoLiberacaoDepositosSaques ?> (horas)
                        </td>
                    </tr>
                    
                    
                    <tr>
                        <td >
                            <strong>Comissão (Saque e Depósito de Reais, Remessa de dinheiro e boleto)</strong>
                        </td>
                        <td class="text-center">
                            <?php echo number_format($licencaSoftware->comissao, 2, ",", ".") ?>%
                        </td>
                    </tr>
                    
                    <?php 
                    
                        foreach ($recursos as $recursoLicenca) {
                            
                            self::trRecursoLicenca($licencaSoftware, $recursoLicenca, (in_array($recursoLicenca->id, $array)));
                        }
                    ?>
                    
                    
                    <tr>
                        <td colspan="2" class="text-center" >
                            <button type="button" class="btn btn-success pull-left" onclick="cadastroLicenca('<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id)?>')">
                                <i class="fa fa-edit"></i>
                            </button>
                            
                            <button type="button" class="btn btn-<?php echo ($licencaSoftware->ativo > 0 ? "primary" : "danger") ?>" onclick="alterarStatusAtivo('<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id)?>')">
                                <i class="fa fa-<?php echo ($licencaSoftware->ativo > 0 ? "check" : "square-o") ?>"></i>
                            </button>
                            
                            <button type="button" class="btn btn-danger pull-right" onclick="modalExcluirLicenca('<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id)?>')">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    
    public static function trRecursoLicenca(\Models\Modules\Cadastro\LicencaSoftware $licencaSoftware, \Models\Modules\Cadastro\RecursoLicenca $recursoLicenca, $habilitado) {
        
        ?>
        <tr id='recurso-<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id) ?>-<?php echo \Utils\Criptografia::encriptyPostId($recursoLicenca->id)  ?>'>
            <td>
                <strong>
                <?php echo $recursoLicenca->descricao ?>
                </strong>
            </td>
            <td class="text-center">
                <?php if ($habilitado) { ?>
                <a href="javascript:removerRecurso('<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id) ?>', '<?php echo \Utils\Criptografia::encriptyPostId($recursoLicenca->id)  ?>');">
                    <i class="fa fa-check-circle-o fa-2x" style="color: #00cf7a" aria-hidden="true"></i>
                </a>
                <?php } else { ?>
                <a href="javascript:atribuirRecurso('<?php echo \Utils\Criptografia::encriptyPostId($licencaSoftware->id) ?>', '<?php echo \Utils\Criptografia::encriptyPostId($recursoLicenca->id)  ?>');">
                    <i class="fa fa-times-circle  fa-2x" style="color: #ff1e1e" aria-hidden="true"></i>
                </a>
                <?php } ?>
            </td>
        </tr>
        <?php 
    }
    
    
    public function cadastrar($params) {
        try {
            
            $licencaSoftware = new \Models\Modules\Cadastro\LicencaSoftware();
            $licencaSoftware->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($licencaSoftware->id > 0) {
                $licencaSoftwareRn = new \Models\Modules\Cadastro\LicencaSoftwareRn();
                $licencaSoftwareRn->conexao->carregar($licencaSoftware);
            }
            
            $licencaSoftware->id = \Utils\Criptografia::encriptyPostId($licencaSoftware->id);
            $licencaSoftware->comissao = number_format($licencaSoftware->comissao, 2, ",", "");
            $licencaSoftware->preco = number_format($licencaSoftware->preco, 2, ",", "");
            
            $json["licenca"] = $licencaSoftware;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            
            $licencaSoftware = new \Models\Modules\Cadastro\LicencaSoftware();
            $licencaSoftware->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $licencaSoftware->comissao = \Utils\Post::getNumeric($params, "comissao", 0);
            $licencaSoftware->descricao = \Utils\Post::get($params, "descricao", null);
            $licencaSoftware->mesesDuracao = \Utils\Post::get($params, "mesesDuracao", null);
            $licencaSoftware->nome = \Utils\Post::get($params, "nome", null);
            $licencaSoftware->ordem = \Utils\Post::get($params, "ordem", 0);
            $licencaSoftware->preco = \Utils\Post::getNumeric($params, "preco", 0);
            $licencaSoftware->tempoLiberacaoDepositosSaques = \Utils\Post::get($params, "tempoLiberacaoDepositosSaques", null);
            $licencaSoftwareRn = new \Models\Modules\Cadastro\LicencaSoftwareRn();
            $licencaSoftwareRn->salvar($licencaSoftware);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Licença de software salva com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function excluir($params) {
        try {
            
            $licencaSoftware = new \Models\Modules\Cadastro\LicencaSoftware();
            $licencaSoftware->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $licencaSoftwareRn = new \Models\Modules\Cadastro\LicencaSoftwareRn();
            $licencaSoftwareRn->excluir($licencaSoftware);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Licença de software excluída com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function alterarStatus($params) {
        try {
            
            $licencaSoftware = new \Models\Modules\Cadastro\LicencaSoftware();
            $licencaSoftware->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $licencaSoftwareRn = new \Models\Modules\Cadastro\LicencaSoftwareRn();
            $licencaSoftwareRn->status($licencaSoftware);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Licença de software excluída com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}

