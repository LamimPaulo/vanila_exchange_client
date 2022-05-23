<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 *
 */
class KycRn
{

    /**
     *
     * @var GenericModel
     */
    public $conexao = null;

    public function __construct(\Io\BancoDados $adapter = null)
    {

        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Kyc());

        } else {
            $this->conexao = new GenericModel($adapter, new Kyc());
        }
    }


    public function save(Kyc &$kyc)
    {

        try {
            $kyc->created_at = new \Utils\Data(date("d/m/Y H:i:s"));
            $this->conexao->salvar($kyc);

        } catch (\Exception $ex) {
            exit(sprintf('Erro -> %s', $ex->getMessage()));
        }

    }

    public function carregar($params)
    {
        try {

            $result = $this->conexao->select($params);
            if (sizeof($result) > 0) {
                return  $result->current();
            } else {
                throw new \Exception($this->idioma->getText("cidadeNaoLocalizada"));
            }


        } catch (\Exception $ex) {
            exit(sprintf('Erro -> %s', $ex->getMessage()));
        }
    }


}

?>
