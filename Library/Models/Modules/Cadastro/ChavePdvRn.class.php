<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade ChavePdv
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ChavePdvRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ChavePdv());
        } else {
            $this->conexao = new GenericModel($adapter, new ChavePdv());
        }
    }
    
    
    public function salvar(ChavePdv &$chavePdv) {
        
        try {
            $this->conexao->adapter->iniciar();
            
            if (!$chavePdv->idPontoPdv > 0) {
                throw new \Exception("A identificação do ponto deve ser informada");
            }
            
            $this->conexao->update(
                    Array(
                        "ativo" => 0
                    ),
                    Array(
                        "id_ponto_pdv" => $chavePdv->idPontoPdv
                    )
                );
            
            $chavePdv->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $chavePdv->ativo = 1;
            $this->conexao->salvar($chavePdv);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    
    
    public function desativar(ChavePdv &$chavePdv) {
        
        try {
            $this->conexao->carregar($chavePdv);
        } catch (\Exception $ex) {
            throw new \Exception("Chave não localizada no sistema ");
        }
        
        $chavePdv->ativo = 0;
        
        $this->conexao->update(
                Array("ativo" => $chavePdv->ativo), 
                Array("id" => $chavePdv->id)
                );
        
    }
    
    
    public function getByPontoPdv(PontoPdv $pontoPdv) {
        $result = $this->conexao->listar("id_ponto_pdv = {$pontoPdv->id} AND ativo > 0", "id", NULL, 1);
        
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
}
?>