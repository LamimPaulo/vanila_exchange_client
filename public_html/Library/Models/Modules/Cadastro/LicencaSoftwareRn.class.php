<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LicencaSoftwareRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LicencaSoftware());
        } else {
            $this->conexao = new GenericModel($adapter, new LicencaSoftware());
        }
    }
    
    public function salvar(LicencaSoftware &$licencaSoftware) {
        
        if ($licencaSoftware->id > 0) {
            $aux = new LicencaSoftware(Array("id" => $licencaSoftware->id));
            $this->conexao->carregar($aux);
            
            $licencaSoftware->ativo = $aux->ativo;
        } else {
            $licencaSoftware->ativo = 1;
        }
        
        if (empty($licencaSoftware->nome)) {
            throw new \Exception("Nome da licença inválida");
        }
        
        if (!$licencaSoftware->preco) {
            throw new \Exception("Preço da licença inválida");
        }
        
        if (!$licencaSoftware->comissao) {
            throw new \Exception("Comissão da licença inválida");
        }
        
        if (!$licencaSoftware->mesesDuracao > 0) {
            throw new \Exception("Quantidade de meses de duração do contrato");
        }
        
        if ($licencaSoftware->ordem <= 0) {
            throw new \Exception("Ordem da licença inválida");
        }
        
        if (empty($licencaSoftware->tempoLiberacaoDepositosSaques)) {
            throw new \Exception("Tempo de liberação de depósitos e saques");
        }
        
        
        $this->conexao->salvar($licencaSoftware);
    }
    
    public function excluir(LicencaSoftware &$licencaSoftware) {
        
        try {
            $this->conexao->carregar($licencaSoftware);
        } catch (Exception $ex) {
            throw new \Exception("Liença não encontrada");
        }
        
        $licencaSoftwareHasRecursoRn = new LicencaSoftwareHasRecursoRn();
        $licencaSoftwareHasRecursoRn->conexao->delete(" id_licenca_software = {$licencaSoftware->id} ");
        
        
        $this->conexao->excluir($licencaSoftware);
    }
    
    
    public function status(LicencaSoftware &$licencaSoftware) {
        try {
            $this->conexao->carregar($licencaSoftware);
        } catch (Exception $ex) {
            throw new \Exception("Licença não encontrada");
        }
        
        $licencaSoftware->ativo = ($licencaSoftware->ativo > 0 ? 0 : 1);
        $this->conexao->update(Array("ativo" => $licencaSoftware->ativo), Array("id" => $licencaSoftware->id));
    }
    
}

?>