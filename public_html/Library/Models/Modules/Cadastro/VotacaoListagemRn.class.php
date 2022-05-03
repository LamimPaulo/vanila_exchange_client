<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade TaxaMoedaRn
 *
 * @package Models_Modules
 * @subpackage Acesso
 */
class VotacaoListagemRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    /**
     *
     * @var \Utils\PropertiesUtils 
     */
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new VotacaoListagem()); 
        } else {
            $this->conexao = new GenericModel($adapter, new VotacaoListagem()); 
        }
    }
    
    public function salvar(VotacaoListagem  &$votacaoListagem, $redesSociais = Array()) {
        $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter);
        
        $this->conexao->adapter->iniciar();
        
        try {
            if ($votacaoListagem->id > 0) {

                $aux = new VotacaoListagem(Array("id" => $votacaoListagem->id));
                $this->conexao->carregar($aux);

                $votacaoListagem->idCliente = $aux->idCliente;
                $votacaoListagem->dataCadastro = $aux->dataCadastro;
                $votacaoListagem->votosNecessarios = $aux->votosNecessarios;
                $votacaoListagem->votosMaxPorCliente = $aux->votosMaxPorCliente;
                $votacaoListagem->ativo = $aux->ativo;
                $votacaoListagem->aprovado = $aux->aprovado;

                if (empty($votacaoListagem->logo)) {
                    $votacaoListagem->logo = $aux->logo;
                }

            } else {

                if (!$votacaoListagem->idCliente > 0) {
                    throw new \Exception($this->idioma->getText("votacaoListagemRn1"));
                }

                $votacaoListagem->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
                $votacaoListagem->votosNecessarios = 100000;
                $votacaoListagem->votosMaxPorCliente = 0;
                $votacaoListagem->ativo = 1;
                $votacaoListagem->aprovado = 0;
            }

            $cliente = new Cliente(Array("id" => $votacaoListagem->idCliente));
            $saldoNC = $contaCorrenteBtcRn->calcularSaldoConta($cliente, 33, false);

            if ($saldoNC < \Utils\Constantes::QTD_MIN_TOKENS_CADASTRO_MOEDA) {
                $msg = $this->idioma->getText("votacaoListagemRn15");
                $msg = str_replace("{var1}", \Utils\Constantes::QTD_MIN_TOKENS_CADASTRO_MOEDA, $msg);
                $msg = str_replace("{var2}", $saldoNC, $msg);
                throw new \Exception($msg);
            }

            if (empty($votacaoListagem->descricao)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn2"));
            }

            if (empty($votacaoListagem->logo)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn3"));
            }

            if (!($votacaoListagem->casasDecimais > 0)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn4"));
            }

            if (empty($votacaoListagem->email)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn5"));
            }

            if (empty($votacaoListagem->responsavel)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn6"));
            }

            if (empty($votacaoListagem->sigla)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn7"));
            }

            if (strlen($votacaoListagem->sigla) > 5) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn8"));
            }

            if (empty($votacaoListagem->nomeMoeda)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn9"));
            }

            if (empty($votacaoListagem->site)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn13"));
            }

            if (empty($votacaoListagem->linkWhitepapper)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn14"));
            }
            
            if (empty($votacaoListagem->descricaoComunidade)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn16"));
            }
            
            if (empty($votacaoListagem->marketcap)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn17"));
            }

            $this->conexao->salvar($votacaoListagem);
            
            if (is_array($redesSociais) && sizeof($redesSociais) > 0) {
                $votacaoListagemHasComunidadeRn = new VotacaoListagemHasComunidadeRn();
                foreach ($redesSociais as $votacaoListagemHasComunidade) {
                    $votacaoListagemHasComunidade->idVotacaoListagem = $votacaoListagem->id;
                    $votacaoListagemHasComunidadeRn->salvar($votacaoListagemHasComunidade);
                }
            }
            
            $this->conexao->adapter->finalizar();
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    
    public function getListagensPorOrdemVotos() {
        $where = Array();
        if (\Utils\Geral::isUsuario()) {
            $where[] = "aprovado < 2";
        } else {
            $where[] = "aprovado = 1";
        }
        $sWhere = " WHERE " . implode(" AND ", $where);
        
        
        $query = "SELECT "
                . " l.*, "
                . " (SELECT SUM(votos) FROM cliente_has_voto where id_votacao_listagem = l.id) AS votos "
                . " FROM votacao_listagem l "
                . " {$sWhere} "
                . " ORDER BY votos";
           
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $dados) {
            
            $votacaoListagem = new VotacaoListagem($dados);
            
            $lista[] = Array (
                "votacao" => $votacaoListagem,
                "votosAdquiridos" => $dados["votos"]
            );
        }
        
        return $lista;
    }
    
    public function aprovar(VotacaoListagem $votacaoListagem, \Utils\Data $dataInicial, \Utils\Data $dataFinal) {
        try {
            
            if (!($dataInicial instanceof \Utils\Data ) || $dataInicial->data == null) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn10"));
            }
            if (!($dataFinal instanceof \Utils\Data ) || $dataFinal->data == null) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn11"));
            }
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("votacaoListagemRn12"));
            }
            
            $this->conexao->carregar($votacaoListagem);
            
            $this->conexao->update(
                    Array(
                        "data_inicial" => $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "data_final" => $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "ativo" => 1,
                        "aprovado" => 1
                    ), 
                    Array(
                        "id" => $votacaoListagem->id
                    )
                );
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        } 
    }
    
    public function negar(VotacaoListagem $votacaoListagem) {
        try {
            
            $this->conexao->carregar($votacaoListagem);
            
            $this->conexao->update(
                    Array(
                        "ativo" => 0,
                        "aprovado" => 2
                    ), 
                    Array(
                        "id" => $votacaoListagem->id
                    )
                );
            
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        } 
    }
    
}

?>