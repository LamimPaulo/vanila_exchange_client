<?php

namespace Modules\configuracoes\Controllers;

class RecursosLicencas {
    
    private $codigoModulo = "configuracoes";
    
    public function __construct($params) {
        
        \Utils\Validacao::acesso($this->codigoModulo);
    }
    
    
    public function listar($params) {
        
        try {
            
            $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
            $recursos = $recursoLicencaRn->conexao->listar(NULL, "ordem", NULL, NULL);
            
            ob_start();
            if (sizeof($recursos) > 0) {
                foreach ($recursos as $recursoLicenca) {
                    $this->tableRecursoLicenca($recursoLicenca);
                }
            } else {
                ?>
                <tr>
                    <td class="text-center" colspan="4">
                    Nenhum recurso de licença cadastrado
                    </td>
                </tr>
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
    
    
    
    private function tableRecursoLicenca(\Models\Modules\Cadastro\RecursoLicenca $recursoLicenca) {
        ?>
        <tr>
            <td><?php echo $recursoLicenca->descricao ?></td>
            <td><?php echo $recursoLicenca->ordem ?></td>
            <td class="text-center">
                <button class="btn btn-success" onclick="cadastroRecurso('<?php echo \Utils\Criptografia::encriptyPostId($recursoLicenca->id)?>');">
                    <i class="fa fa-edit"></i> Editar
                </button>
            </td>
            <td class="text-center" >
                <button class="btn btn-danger"  onclick="modalExcluirRecurso('<?php echo \Utils\Criptografia::encriptyPostId($recursoLicenca->id)?>');">
                    <i class="fa fa-trash"></i> Excluir
                </button>
            </td>
        </tr>
        <?php
    }
    
    public function cadastrar($params) {
        try {
            
            $recursoLicenca = new \Models\Modules\Cadastro\RecursoLicenca();
            $recursoLicenca->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            if ($recursoLicenca->id > 0) {
                $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
                $recursoLicencaRn->conexao->carregar($recursoLicenca);
            }
            
            $recursoLicenca->id = \Utils\Criptografia::encriptyPostId($recursoLicenca->id);
            
            $json["recurso"] = $recursoLicenca;
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function salvar($params) {
        try {
            
            $recursoLicenca = new \Models\Modules\Cadastro\RecursoLicenca();
            $recursoLicenca->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $recursoLicenca->descricao = \Utils\Post::get($params, "descricao", null);
            $recursoLicenca->ordem = \Utils\Post::get($params, "ordem", 0);
            $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
            $recursoLicencaRn->salvar($recursoLicenca);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Recurso de Licença de software salvo com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    
    public function excluir($params) {
        try {
            
            $recursoLicenca = new \Models\Modules\Cadastro\RecursoLicenca();
            $recursoLicenca->id = \Utils\Post::getEncrypted($params, "codigo", 0);
            
            $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
            $recursoLicencaRn->excluir($recursoLicenca);
            
            $json["sucesso"] = true;
            $json["mensagem"] = "Recurso de Licença de software excluído com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    public function atribuir($params) {
        try {
            
            $licencaSoftwareHasRecurso = new \Models\Modules\Cadastro\LicencaSoftwareHasRecurso();
            $licencaSoftwareHasRecurso->idLicencaSoftware = \Utils\Post::getEncrypted($params, "licenca", 0);
            $licencaSoftwareHasRecurso->idRecursoLicenca = \Utils\Post::getEncrypted($params, "recurso", 0);
            
            $licencaSoftwareHasRecursoRn = new \Models\Modules\Cadastro\LicencaSoftwareHasRecursoRn();
            $licencaSoftwareHasRecursoRn->atribuir($licencaSoftwareHasRecurso);
            
            $licencaSoftware = new \Models\Modules\Cadastro\LicencaSoftware(Array("id" => $licencaSoftwareHasRecurso->idLicencaSoftware));
            $recursoLicenca = new \Models\Modules\Cadastro\RecursoLicenca(Array("id" => $licencaSoftwareHasRecurso->idRecursoLicenca));
            $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
            $recursoLicencaRn->conexao->carregar($recursoLicenca);
            
            ob_start();
            LicencasSoftware::trRecursoLicenca($licencaSoftware, $recursoLicenca, true);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Recurso de Licença de software excluído com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function remover($params) {
        try {
            
            $licencaSoftwareHasRecurso = new \Models\Modules\Cadastro\LicencaSoftwareHasRecurso();
            $licencaSoftwareHasRecurso->idLicencaSoftware = \Utils\Post::getEncrypted($params, "licenca", 0);
            $licencaSoftwareHasRecurso->idRecursoLicenca = \Utils\Post::getEncrypted($params, "recurso", 0);
            
            $licencaSoftwareHasRecursoRn = new \Models\Modules\Cadastro\LicencaSoftwareHasRecursoRn();
            $licencaSoftwareHasRecursoRn->remover($licencaSoftwareHasRecurso);
            
            $licencaSoftware = new \Models\Modules\Cadastro\LicencaSoftware(Array("id" => $licencaSoftwareHasRecurso->idLicencaSoftware));
            $recursoLicenca = new \Models\Modules\Cadastro\RecursoLicenca(Array("id" => $licencaSoftwareHasRecurso->idRecursoLicenca));
            $recursoLicencaRn = new \Models\Modules\Cadastro\RecursoLicencaRn();
            $recursoLicencaRn->conexao->carregar($recursoLicenca);
            
            ob_start();
            LicencasSoftware::trRecursoLicenca($licencaSoftware, $recursoLicenca, true);
            $html = ob_get_contents();
            ob_end_clean();
            
            $json["html"] = $html;
            $json["sucesso"] = true;
            $json["mensagem"] = "Recurso de Licença de software excluído com sucesso!";
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
}