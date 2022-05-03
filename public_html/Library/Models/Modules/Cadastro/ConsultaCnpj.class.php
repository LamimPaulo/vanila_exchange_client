<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ConsultaCnpj {

    /**
     *
     * @var String 
     */
    public $nomeFantasia;
    
    /**
     *
     * @var String 
     */
    public $nomeEmpresa;
    

    /**
     *
     * @var String 
     */
    public $atividadesSecundarias;

    /**
     *
     * @var String 
     */
    public $complemento;

    /**
     *
     * @var String 
     */
    public $municipio;

    /**
     *
     * @var String 
     */
    public $dataSituacaoEspecial;

    /**
     *
     * @var String 
     */
    public $email;

    /**
     *
     * @var String 
     */
    public $cnpj;

    /**
     *
     * @var String 
     */
    public $atividadePrincipal;

    /**
     *
     * @var String 
     */
    public $situacaoCadastral;

    /**
     *
     * @var String 
     */
    public $nomeResponsavel;

    /**
     *
     * @var String 
     */
    public $creditos;

    /**
     *
     * @var String 
     */
    public $dataSituacaoCadastral;

    /**
     *
     * @var String 
     */
    public $numero;

    /**
     *
     * @var String 
     */
    public $dataAbertura;

    /**
     *
     * @var String 
     */
    public $cpfResponsavel;

    /**
     *
     * @var String 
     */
    public $uf;

    /**
     *
     * @var String 
     */
    public $naturezaJuridica;

    /**
     *
     * @var String 
     */
    public $socios;

    /**
     *
     * @var String 
     */
    public $tipo;

    /**
     *
     * @var String 
     */
    public $logradouro;

    /**
     *
     * @var String 
     */
    public $motivoSituacaoCadastral;

    /**
     *
     * @var String 
     */
    public $tempoConsulta;

    /**
     *
     * @var String 
     */
    public $cep;

    /**
     *
     * @var String 
     */
    public $resultado;

    /**
     *
     * @var String 
     */
    public $resultadoTxt;

    /**
     *
     * @var String 
     */
    public $situacaoEspecial;

    /**
     *
     * @var String 
     */
    public $bairro;

    /**
     *
     * @var String 
     */
    public $capitalSocial;

    /**
     *
     * @var String 
     */
    public $telefone;
    
    /**
     *
     * @var Integer
     */
    public $idCliente;
    
    /**
     *
     * @var Data
     */
    public $dataCadastro;
    
    /**
     * Construtor da classe 
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function __construct($dados = null) {
        if (!is_null($dados)) {
            $this->exchangeArray($dados);
        }
    }

    /**
     * Função responsável por atribuir os dados do array no objeto
     *  
     * @param String $dados Array contendo os dados do objeto
     */
    public function exchangeArray($dados) {
        //Só atribuo os dados do array somente se eles existem
        $this->atividadePrincipal = ((isset($dados ['atividade_principal'])) ? ($dados ['atividade_principal']) : (null));
        $this->atividadesSecundarias = ((isset($dados ['atividades_secundarias'])) ? ($dados ['atividades_secundarias']) : (null));
        $this->bairro = ((isset($dados ['bairro'])) ? ($dados ['bairro']) : (null));
        $this->capitalSocial = ((isset($dados ['capital_social'])) ? ($dados ['capital_social']) : (null));
        $this->cep = ((isset($dados ['cep'])) ? ($dados ['cep']) : (null));
        $this->cnpj = ((isset($dados ['cnpj'])) ? ($dados ['cnpj']) : (null));
        $this->complemento = ((isset($dados ['complemento'])) ? ($dados ['complemento']) : (null));
        $this->cpfResponsavel = ((isset($dados ['cpf_responsavel'])) ? ($dados ['cpf_responsavel']) : (null));
        $this->creditos = ((isset($dados ['creditos'])) ? ($dados ['creditos']) : (null));
        $this->dataAbertura = ((isset($dados ['data_abertura'])) ? ($dados ['data_abertura']) : (null));
        $this->dataSituacaoCadastral = ((isset($dados ['data_situacao_cadastral'])) ? ($dados ['data_situacao_cadastral']) : (null));
        $this->dataSituacaoEspecial = ((isset($dados ['data_situacao_especial'])) ? ($dados ['data_situacao_especial']) : (null));
        $this->situacaoEspecial = ((isset($dados ['situacao_especial'])) ? ($dados ['situacao_especial']) : (null));
        $this->email = ((isset($dados ['email'])) ? ($dados ['email']) : (null));
        $this->logradouro = ((isset($dados ['logradouro'])) ? ($dados ['logradouro']) : (null));
        $this->motivoSituacaoCadastral = ((isset($dados ['motivo_situacao_cadastral'])) ? ($dados ['motivo_situacao_cadastral']) : (null));
        $this->municipio = ((isset($dados ['municipio'])) ? ($dados ['municipio']) : (null));
        $this->naturezaJuridica = ((isset($dados ['natureza_juridica'])) ? ($dados ['natureza_juridica']) : (null));
        $this->nomeEmpresa = ((isset($dados ['nome_empresa'])) ? ($dados ['nome_empresa']) : (null));
        $this->nomeFantasia = ((isset($dados ['nome_fantasia'])) ? ($dados ['nome_fantasia']) : (null));
        $this->nomeResponsavel = ((isset($dados ['nome_responsavel'])) ? ($dados ['nome_responsavel']) : (null));
        $this->resultado = ((isset($dados ['resultado'])) ? ($dados ['resultado']) : (null));
        $this->resultadoTxt = ((isset($dados ['resultado_txt'])) ? ($dados ['resultado_txt']) : (null));
        $this->situacaoCadastral = ((isset($dados ['situacao_cadastral'])) ? ($dados ['situacao_cadastral']) : (null));
        $this->socios = ((isset($dados ['socios'])) ? ($dados ['socios']) : (null));
        $this->telefone = ((isset($dados ['telefone'])) ? ($dados ['telefone']) : (null));
        $this->tempoConsulta = ((isset($dados ['tempo_consulta'])) ? ($dados ['tempo_consulta']) : (null));
        $this->tipo = ((isset($dados ['tipo'])) ? ($dados ['tipo']) : (null));
        $this->uf = ((isset($dados ['uf'])) ? ($dados ['uf']) : (null));
        $this->idCliente = ((isset($dados ['id_cliente'])) ? ($dados ['id_cliente']) : (null));
        $this->dataCadastro = ((isset($dados['data_cadastro'])) ? ($dados['data_cadastro'] instanceof \Utils\Data ? $dados['data_cadastro'] : 
            new \Utils\Data(substr($dados['data_cadastro'], 0, 19))) : (null));

    }
    
    public function getTable() {
        return "consultas_cnpj";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ConsultaCnpj();
    }


}

?>