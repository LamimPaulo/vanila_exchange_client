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
class EmailManagerRn {

    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new EmailManager());
        } else {
            $this->conexao = new GenericModel($adapter, new EmailManager());
        }
    }
    
    public static function get($idEmail) {

        $emailManager = new EmailManager(Array("id" => $idEmail));
        $emailManagerRn = new EmailManagerRn();
        $emailManagerRn->conexao->carregar($emailManager);

        return $emailManager;
    }

}

?>