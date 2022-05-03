<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade TaxaMoedaRn
 *
 * @package Models_Modules
 * @subpackage Acesso
 */
class TaxaMoedaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new TaxaMoeda()); 
        } else {
            $this->conexao = new GenericModel($adapter, new TaxaMoeda()); 
        }
    }
    
    public function salvar(TaxaMoeda  &$taxaMoeda) {
        
        if (!$taxaMoeda->idMoeda > 0) {
            throw new \Exception("Moeda inválida");
        }
        
        if (!$taxaMoeda->taxaTransferencia > 0) {
            $taxaMoeda->taxaTransferencia = 0;
        }
        
        if (!$taxaMoeda->valorMaxSaqueSemConfirmacao > 0) {
            $taxaMoeda->valorMaxSaqueSemConfirmacao = 0;
        }
        
        
        if (!$taxaMoeda->volumeMinimoNegociacao > 0) {
            $taxaMoeda->volumeMinimoNegociacao = 0;
        }
        
        
        if (!$taxaMoeda->taxaRede > 0) {
            $taxaMoeda->taxaRede = 0;
        }
        
        if (!$taxaMoeda->minConfirmacoes > 0) {
            $taxaMoeda->minConfirmacoes = 0;
        }
        
        if (!$taxaMoeda->maxConfirmacoes > 0) {
            $taxaMoeda->maxConfirmacoes = 0;
        }
        unset($taxaMoeda->moeda);
        $this->conexao->salvar($taxaMoeda);
    }
    
    
    public function getByMoeda($idMoeda) {
        // Valida se o email foi informado
        if (!($idMoeda > 0)) {
            throw new \Exception("Moeda inválida.");
        }
        
        $result = $this->conexao->select(Array("id_moeda" => $idMoeda));
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        return null;
    }
    
    
    public function carregar(TaxaMoeda &$taxaMoeda, $carregar = true, $carregarMoeda = true) {
        if ($carregar) {
            $this->conexao->carregar($taxaMoeda);
        }
        
        if ($carregarMoeda && $taxaMoeda->idMoeda > 0) {
            $taxaMoeda->moeda = new Moeda(Array("id" => $taxaMoeda->idMoeda));
            $moedaRn = new MoedaRn();
            $moedaRn->conexao->carregar($taxaMoeda->moeda);            
        }
    }
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarMoeda = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $taxaMoeda) {
            $this->carregar($taxaMoeda, false, $carregarMoeda);
            $lista[] = $taxaMoeda;
        }
        return $lista;
    }
    
    public function salvarTaxas($array) {
        try {
            $this->conexao->adapter->iniciar();
            $moedaRn = new MoedaRn($this->conexao->adapter);
            
            foreach ($array as $taxa) {
                $taxa["idMoeda"] = \Utils\Criptografia::decriptyPostId($taxa["idMoeda"]);
                $taxaMoeda = $this->getByMoeda($taxa["idMoeda"]);
                
                if ($taxaMoeda == null) {
                    $taxaMoeda = new TaxaMoeda(Array("id_moeda" => $taxa["idMoeda"]));
                }
                
                $taxaMoeda->taxaTransferencia = str_replace(",", ".", $taxa["taxaTransferencia"]);
                $taxaMoeda->valorMaxSaqueSemConfirmacao = str_replace(",", ".", $taxa["valorMaxSaqueSemConfirmacao"]);
                $taxaMoeda->volumeMinimoNegociacao = str_replace(",", ".", $taxa["volumeMinimoNegociacao"]);
                $taxaMoeda->taxaRede = str_replace(",", ".", $taxa["taxaRede"]);
                $taxaMoeda->minConfirmacoes = $taxa["minConfirmacoes"];
                $taxaMoeda->maxConfirmacoes = $taxa["maxConfirmacoes"];
                
                $taxaMoeda->taxaCompraDireta = str_replace(",", ".", $taxa["taxaCompraDireta"]);
                $taxaMoeda->taxaCompraIndireta = str_replace(",", ".", $taxa["taxaCompraIndireta"]);
                $taxaMoeda->taxaVendaDireta = str_replace(",", ".", $taxa["taxaVendaDireta"]);
                $taxaMoeda->taxaVendaIndireta = str_replace(",", ".", $taxa["taxaVendaIndireta"]);
                $this->salvar($taxaMoeda);
                
                $moeda = new Moeda(Array("id" => $taxa["idMoeda"]));
                $moeda->qtdMaximaCarteiras = $taxa["qtdCarteiras"];
                $moedaRn->update($moeda);
            }
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
        
        return null;
    }
    
    public function taxasMoedasAtivas(){
        
        $moedaRn = new MoedaRn();
        
        $query = "  SELECT taxa_transferencia as taxaTrans,  min_confirmacoes as minConfirm, id_moeda_taxa as moedaTaxa, taxa_moeda_transferencia as taxaMoedaTransferencia,
                    moedas.id as moedaId, moedas.nome as nome, moedas.icone as icone, moedas.simbolo as sigla
                    FROM taxas_moedas
                    LEFT JOIN moedas on taxas_moedas.id_moeda = moedas.id
                    WHERE moedas.ativo = 1 AND moedas.status_mercado = 1 ORDER BY moedas.nome ASC; ";
        
        $result = $this->conexao->adapter->query($query)->execute();
        
        
        $lista = Array();
        $listaAux = Array();
        $listaRetorno = Array();
        
        if(sizeof($result) > 0){
            foreach ($result as $taxas){
                $lista[] = $taxas;
                $listaAux[] = $taxas;
            }
        }
        
        if(sizeof($lista) > 0){
            foreach ($lista as $taxas){
                
                if(!empty($taxas["moedaTaxa"])){
                   
                    foreach($listaAux as $taxaAux){
                        if($taxas["moedaTaxa"] == $taxaAux["moedaId"]){
                            $taxas["simboloMoedaTaxa"] = $taxaAux["sigla"];
                            
                            if(empty($taxas["taxaMoedaTransferencia"])){
                                $taxas["taxaMoedaTaxa"] = $taxaAux["taxaTrans"];
                            }
                        }
                    }
                }
                
                $listaRetorno[] = $taxas;
            }
        }

        //exit(print_r($listaRetorno));
        return $listaRetorno;        
    }
}

?>