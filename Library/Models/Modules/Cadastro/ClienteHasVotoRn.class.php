<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade TaxaMoedaRn
 *
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasVotoRn {
    
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
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasVoto()); 
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasVoto()); 
        }
    }
    
    public function salvar(ClienteHasVoto  &$clienteHasVoto) {
        $this->conexao->adapter->iniciar();
        try {
            $clienteHasVoto->id = 0;
            $clienteHasVoto->data = new \Utils\Data(date("d/m/Y H:i:s"));
            if (!$clienteHasVoto->idCliente > 0) {
                throw new \Exception($this->idioma->getText("clienteHasVotoRn1"));
            }

            if (!$clienteHasVoto->idVotacaoListagem > 0) {
                throw new \Exception($this->idioma->getText("clienteHasVotoRn2"));
            }


            if (!is_numeric($clienteHasVoto->votos) || !$clienteHasVoto->votos > 0) {
                throw new \Exception($this->idioma->getText("clienteHasVotoRn3"));
            }
            
            $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
            
            $saldo = $contaCorrenteBtcRn->calcularSaldoConta(new Cliente(Array("id" => $clienteHasVoto->idCliente)), 33, false);
            
            if ($saldo < $clienteHasVoto->votos) {
                throw new \Exception($this->idioma->getText("clienteHasVotoRn4"));
            }
            
            $this->conexao->salvar($clienteHasVoto);
            
            $votacaoListagem = new VotacaoListagem(Array("id" => $clienteHasVoto->idVotacaoListagem));
            $votacaoListagemRn = new VotacaoListagemRn($this->conexao->adapter);
            $votacaoListagemRn->conexao->carregar($votacaoListagem);
            
            
            $contaCorrenteBtc = new ContaCorrenteBtc();
            $contaCorrenteBtc->id = 0;
            $contaCorrenteBtc->autorizada = 1;
            $contaCorrenteBtc->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
            $contaCorrenteBtc->descricao = "Votação {$votacaoListagem->nomeMoeda}";
            $contaCorrenteBtc->direcao = \Utils\Constantes::TRANF_INTERNA;
            $contaCorrenteBtc->enderecoBitcoin = "";
            $contaCorrenteBtc->enderecoEnvio = "";
            $contaCorrenteBtc->executada = 1;
            $contaCorrenteBtc->hash = "";
            $contaCorrenteBtc->idCliente = $clienteHasVoto->idCliente;
            $contaCorrenteBtc->idMoeda = 33;
            $contaCorrenteBtc->idReferenciado = null;
            $contaCorrenteBtc->orderBook = 0;
            $contaCorrenteBtc->origem = 6;
            $contaCorrenteBtc->seed = "";
            $contaCorrenteBtc->tipo = \Utils\Constantes::SAIDA;
            $contaCorrenteBtc->transferencia = 0;
            $contaCorrenteBtc->valor = $clienteHasVoto->votos;
            $contaCorrenteBtc->valorTaxa = 0;
            $contaCorrenteBtcRn->salvar($contaCorrenteBtc, NULL);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            
            if ($clienteHasVoto->id > 0) {
                $this->conexao->excluir($clienteHasVoto);
            }
            
            if (isset($contaCorrenteBtc) && $contaCorrenteBtc->id > 0) {
                $contaCorrenteBtcRn = new ContaCorrenteBtcRn($this->conexao->adapter, false);
                $contaCorrenteBtcRn->excluir($contaCorrenteBtc);
            }
            
            throw new \Exception(\Utils\Excecao::mensagem($ex));
        }
    }
    
    public function getQuantidadeVotos(VotacaoListagem $votacaoListagem) {
        
        $query = "SELECT SUM(votos) AS votos FROM cliente_has_voto WHERE  id_votacao_listagem = {$votacaoListagem->id};";
        $result = $this->conexao->adapter->query($query)->execute();
        $votos = 0;
        foreach ($result as $dados) {
            $votos = $dados["votos"];
        }
        
        return $votos;
    }
    
}

?>