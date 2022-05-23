<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade LimiteOperacional
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class LimiteOperacionalRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;

    
    public function __construct(\Io\BancoDados $adapter = null) {
         $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new LimiteOperacional());
        } else {
            $this->conexao = new GenericModel($adapter, new LimiteOperacional());
        }
    }
    
    
    public function carregar(LimiteOperacional &$limiteOperacional, $carregar = true, $carregarMoeda = true, $carregarCliente = true) {
        if ($carregar) {
            $this->conexao->carregar($limiteOperacional);
        }
        
        if ($carregarCliente && $limiteOperacional->idCliente > 0) {
            $limiteOperacional->cliente = new Cliente(Array("id" => $limiteOperacional->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($limiteOperacional->cliente);
        }
        
        if ($carregarMoeda && $limiteOperacional->idMoeda > 0) {
            $limiteOperacional->moeda = new Moeda(Array("id" => $limiteOperacional->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($limiteOperacional->moeda);
        }
    } 
}

?>