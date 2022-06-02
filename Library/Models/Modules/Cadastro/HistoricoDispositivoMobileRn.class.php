<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Estado
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class HistoricoDispositivoMobileRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new HistoricoDispositivoMobile());
        } else {
            $this->conexao = new GenericModel($adapter, new HistoricoDispositivoMobile());
        }
    }
    
    public function salvar(HistoricoDispositivoMobile &$historicoDispositivoMobile) {
        
        $historicoDispositivoMobile->id = 0;
        $historicoDispositivoMobile->data = new \Utils\Data(date("d/m/Y H:i:s"));
        
        if (empty($historicoDispositivoMobile->descricao)) {
            throw new \Exception("A descrição do log deve ser informada.");
        }
        
        if (!$historicoDispositivoMobile->idDispositivoMobile > 0) {
            throw new \Exception("A descrição do log deve ser informada.");
        }
        
        $this->conexao->salvar($historicoDispositivoMobile);
    }
    
    public static function registrar(DispositivoMobile $dispositivoMobile, $descricao) {
        $historicoDispositivoMobile = new HistoricoDispositivoMobile();
        $historicoDispositivoMobile->descricao = $descricao;
        $historicoDispositivoMobile->idDispositivoMobile = $dispositivoMobile->id;
        
        $historicoDispositivoMobileRn = new HistoricoDispositivoMobileRn();
        $historicoDispositivoMobileRn->salvar($historicoDispositivoMobile);
        
    }
    
}

?>