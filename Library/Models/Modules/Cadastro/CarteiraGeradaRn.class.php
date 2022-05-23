<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;


class CarteiraGeradaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new CarteiraGerada());
        } else {
            $this->conexao = new GenericModel($adapter, new CarteiraGerada());
        }
    }
    
    public function salvar(CarteiraGerada &$carteiraGerada) {
        
        $carteiraGerada->id = 0;
        if (empty($carteiraGerada->address)) {
            throw new \Exception($this->idioma->getText("necessarioInformarEndereco"));
        }
        
        if ($carteiraGerada->idMoeda < 2) {
            throw new \Exception($this->idioma->getText("indentificacaoDeveInformada"));
        }
        
        $result = $this->conexao->listar(" address = '{$carteiraGerada->address}' ");
        if (sizeof($result) > 0) {
            //throw new \Exception($this->idioma->getText("enderecoCadastrado"), 1001);
            $carteiraGerada = $result->current();
        } else {
            $carteiraGerada->dataCriacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $carteiraGerada->utilizada = 0;
            $carteiraGerada->inutilizada = 0;

            $this->conexao->salvar($carteiraGerada);
        }
    }
    
    public function contarRegistros($idMoeda) {
        
        $qtdClientes = 0;
        $qtdEstabelecimentos = 0;
        $qtdPdvs = 0;
        $carteirasLivres = 0;
        $carteirasClientes = 0;
        
        $queryClientes = " SELECT COUNT(*) AS clientes FROM clientes";
        $resultClientes = $this->conexao->executeSql($queryClientes);
        foreach ($resultClientes as $dadosClientes) {
            $qtdClientes = (isset($dadosClientes["clientes"]) ? $dadosClientes["clientes"] : 0);
        }
        
        $queryEstabelecimentos = " SELECT COUNT(*) AS estabelecimentos FROM estabelecimentos";
        $resultEstabelecimentos = $this->conexao->executeSql($queryEstabelecimentos);
        foreach ($resultEstabelecimentos as $dadosEstabelecimentos) {
            $qtdEstabelecimentos = (isset($dadosEstabelecimentos["estabelecimentos"]) ? $dadosEstabelecimentos["estabelecimentos"] : 0);
        }
        
        $queryPdvs = " SELECT COUNT(*) AS pdvs FROM pontos_pdv ";
        $resultPdvs = $this->conexao->executeSql($queryPdvs);
        foreach ($resultPdvs as $dadosPdvs) {
            $qtdPdvs = (isset($dadosPdvs["pdvs"]) ? $dadosPdvs["pdvs"] : 0);
        }
        
        $queryCarteiras = " SELECT COUNT(*) AS carteiras  FROM  carteiras_geradas WHERE utilizada < 1 AND inutilizada  < 1 AND id_moeda = {$idMoeda}";
        $resultCarteiras = $this->conexao->executeSql($queryCarteiras);
        foreach ($resultCarteiras as $dadosCarteiras) {
            $carteirasLivres = (isset($dadosCarteiras["carteiras"]) ? $dadosCarteiras["carteiras"] : 0);
        }
        
        $queryCarteirasClientes = " SELECT COUNT(*) AS carteiras  FROM  carteiras_clientes WHERE id_moeda = {$idMoeda} AND inutilizada < 1 ";
        $resultCarteirasClientes = $this->conexao->executeSql($queryCarteirasClientes);
        foreach ($resultCarteirasClientes as $dadosCarteirasClientes) {
            $carteirasClientes = (isset($dadosCarteirasClientes["carteiras"]) ? $dadosCarteirasClientes["carteiras"] : 0);
        }
        
        return Array("clientes" => $qtdClientes, "estabelecimentos" => $qtdEstabelecimentos, "pdvs" => $qtdPdvs, "carteirasLivres" => $carteirasLivres, "carteirasClientes" => $carteirasClientes);
    }
    
/**
     * 
     * @param type $idMoeda
     * @return CarteiraGerada
     * @throws \Exception
     */
    public function getWallet($idMoeda) {
        
        $cod1 = rand(1, 1000);
        $cod2 = rand(1, 1000);
        $codigo = sha1("{$cod1}-{$cod2}");
        
        $this->conexao->adapter->query("UPDATE carteiras_geradas SET codigo = '{$codigo}' WHERE utilizada = 0 AND inutilizada = 0 AND id_moeda = {$idMoeda} AND codigo IS NULL LIMIT 1; ")->execute();
        
        $result = $this->conexao->listar("codigo = '{$codigo}' ", "id");
        
        if (sizeof($result) > 0) {
            $carteira = $result->current();
            $this->conexao->update(Array("utilizada" => 1), Array("id" => $carteira->id));
            return $carteira;
        } else {
            throw new \Exception($this->idioma->getText("naoPossivelGerarCarteira"));
        }
    }

    public function contarCarteiras($idMoeda) {

        $gerarCarteira = false;
        $qtd = 0;
        if(is_numeric($idMoeda)){
            $queryCarteiras = " SELECT COUNT(*) AS carteiras  FROM  carteiras_geradas WHERE utilizada < 1 AND inutilizada  < 1 AND id_moeda = {$idMoeda}";
            $resultCarteiras = $this->conexao->executeSql($queryCarteiras);
            if (sizeof($resultCarteiras) > 0) {
                $carteirasLivres = $resultCarteiras->current();
                $carteirasLivres = (isset($dadosCarteiras["carteiras"]) ? $dadosCarteiras["carteiras"] : 0);
            }

            $taxaMoedaRn = new TaxaMoedaRn();
            $taxaMoeda = $taxaMoedaRn->getByMoeda($idMoeda);

            if($carteirasLivres < $taxaMoeda->poolSize){
                $gerarCarteira = true;

                $qtd = $taxaMoeda->poolSize - $carteirasLivres;
            }
        }
        return Array("id_moeda" => $idMoeda,
            "gerar_carteiras" => $gerarCarteira,
            "qtd" => $qtd);
    }
}

?>