<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Configuracao
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ConfiguracaoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    
    private static $configuracao;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Configuracao());
        } else {
            $this->conexao = new GenericModel($adapter, new Configuracao());
        }
    }
    
    public function salvar(Configuracao &$configuracao) {
        //exit(print_r($configuracao));
        
        if ($configuracao->id <= 0) {
            $configuracao->id = 1;
        }
        
        if (!is_numeric($configuracao->percentualCompra) || $configuracao->percentualCompra < 0) {
            throw new \Exception("Valor do percentual pago pelo compra inválido");
        }
        
        if (!is_numeric($configuracao->percentualDepositos) || $configuracao->percentualDepositos < 0) {
            throw new \Exception("Valor do percentual pago pelo depósito inválido");
        }
        
        if (!is_numeric($configuracao->percentualVenda) || $configuracao->percentualVenda < 0) {
            throw new \Exception("Valor do percentual pago pela venda inválido");
        }
        
        if (!is_numeric($configuracao->valorCartao) || $configuracao->valorCartao < 0) {
            throw new \Exception("Valor cobrado pela emissão do cartão nacional inválido");
        }
        if (!is_numeric($configuracao->valorCartaoEx) || $configuracao->valorCartaoEx < 0) {
            throw new \Exception("Valor cobrado pela emissão do cartão internacional inválido");
        }
        
        if (!is_numeric($configuracao->valorMensalidadeCartao) || $configuracao->valorMensalidadeCartao < 0) {
            throw new \Exception("Valor cobrado pela mensalidade do cartão inválido");
        }
        
        
        if (!is_numeric($configuracao->taxaTransferenciaInternaBtc) || $configuracao->taxaTransferenciaInternaBtc < 0) {
            throw new \Exception("O valor mínimo para resgate da comissão é inválido");
        }
        
        if (!is_numeric($configuracao->taxaTransferenciaInternaReais) || $configuracao->taxaTransferenciaInternaReais < 0) {
            throw new \Exception("O valor mínimo para resgate da comissão é inválido");
        }
        
        
        if (!is_numeric($configuracao->taxaDeposito) || $configuracao->taxaDeposito < 0) {
            throw new \Exception("A taxa de comissão para solicitação de depósitos é inválida");
        }
        
        if (!is_numeric($configuracao->taxaSaque) || $configuracao->taxaSaque < 0) {
            throw new \Exception("A taxa de comissão para solicitação de saques é inválida");
        }
        
        if (!is_numeric($configuracao->tarifaTed) || $configuracao->tarifaTed < 0) {
            throw new \Exception("A tarifa de TED é inválida");
        }
        
        
        if (!is_numeric($configuracao->qtdMinConfirmacoesTransacao) || $configuracao->qtdMinConfirmacoesTransacao < 0) {
            throw new \Exception("Quantidade mínima de confirmações de transações inválida");
        }
        
        if (!is_numeric($configuracao->qtdMaxConfirmacoesTransacao) || $configuracao->qtdMaxConfirmacoesTransacao < 0) {
            throw new \Exception("Quantidade máxima de confirmações de transações inválida");
        }
        
        
        if (!is_numeric($configuracao->comissaoConvite) || $configuracao->comissaoConvite < 0) {
            $configuracao->comissaoConvite = 0;
        }
        
        if (!is_numeric($configuracao->valorMaximoSaqueBtc) || $configuracao->valorMaximoSaqueBtc < 0) {
            $configuracao->valorMaximoSaqueBtc = 0;
        }
        
        if (!is_numeric($configuracao->taxaRedeBtc) || $configuracao->taxaRedeBtc < 0) {
            $configuracao->taxaRedeBtc = 0;
        }
        if (!is_numeric($configuracao->prazoHorasValidacaoDepositos) || $configuracao->prazoHorasValidacaoDepositos <= 0) {
            $configuracao->prazoHorasValidacaoDepositos = 0;
        }
        
        if (!is_numeric($configuracao->prazoHorasValidacaoSaques) || $configuracao->prazoHorasValidacaoSaques <= 0) {
            $configuracao->prazoHorasValidacaoSaques = 0;
        }
        
        unset($configuracao->prazoHorasValidacaoConta);
        unset($configuracao->prazoHorasAtendimento);
        unset($configuracao->forcarEnvioToken);
        $this->conexao->salvar($configuracao);
    }
    
    /**
     * 
     * @return \Models\Modules\Cadastro\Configuracao
     */
    public static function get() {

        if (self::$configuracao == null) {
            self::$configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn = new ConfiguracaoRn();
            $configuracaoRn->conexao->carregar(self::$configuracao);
        }
        return self::$configuracao;
    }



    
    
    public static function getMeioAutenticacao() {
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        return strtolower($configuracao->forcarEnvioToken);
    }
    
    public static function getTaxaTransferenciaCurrency(Cliente $cliente) {
        $configuracao = ConfiguracaoRn::get();
        $taxaTransferencia = ($cliente->considerarTaxaTransferenciaCurrency > 0 ? $cliente->taxaComissaoTransfenciaCurrency : $configuracao->taxaTransferenciaInternaBtc);
        return $taxaTransferencia;
    }
    
    
    public static function getTaxaServicos(Cliente $cliente, Configuracao $configuracao = null, $servico = "boletos") {
        if ($configuracao == null) {
            $configuracao = self::get();
        }
        
        $taxa = 0;
        
        switch ($servico) {
            case "boletos":


                break;
            case "remessas":


                break;
            case "depositos":


                break;
            case "saques":


                break;
        }
        
    }
    
    public function atualizarSaldoAtar($valor){
        
        $this->conexao->update(Array("atar_saldo" => $valor), Array("id" => 1));
        
    }
    
    
}

?>
