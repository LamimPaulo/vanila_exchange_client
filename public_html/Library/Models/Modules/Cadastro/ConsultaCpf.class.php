<?php

namespace Models\Modules\Cadastro;


/**
 * Mantém os dados das cidades do sistema
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ConsultaCpf {

    /**
     *
     * @var String 
     */
    public $creditos;
    
    
    /**
     *
     * @var String 
     */
    public $uf;
    
    
    /**
     *
     * @var String 
     */
    public $tempoConsulta;
    
    
    /**
     *
     * @var String 
     */
    public $dataConsulta;
    
    
    /**
     *
     * @var String 
     */
    public $titular;
    
    
    /**
     *
     * @var String 
     */
    public $cpf;
    
    
    /**
     *
     * @var String 
     */
    public $numero;
    
    
    /**
     *
     * @var String 
     */
    public $situacao;
    
    
    /**
     *
     * @var String 
     */
    public $genero;
    
    
    /**
     *
     * @var String 
     */
    public $cep;
    
    
    /**
     *
     * @var String 
     */
    public $dataInscricao;
    
    
    /**
     *
     * @var String 
     */
    public $horaConsulta;
    
    
    /**
     *
     * @var String 
     */
    public $codigoControle;
    
    
    /**
     *
     * @var String 
     */
    public $nomeMae;
    
    
    /**
     *
     * @var String 
     */
    public $complemento;
    
    
    /**
     *
     * @var String 
     */
    public $cidade;
    
    
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
    public $digitoVerificador;
    
    
    /**
     *
     * @var String 
     */
    public $dataNascimento;
    
    
    /**
     *
     * @var String 
     */
    public $anoObito;
    
    
    /**
     *
     * @var String 
     */
    public $bairro;
    
    
    /**
     *
     * @var String 
     */
    public $msgObito;
    
    
    /**
     *
     * @var String 
     */
    public $logradouro;
    
    
    /**
     *
     * @var \Utils\Data 
     */
    public $datahora;
    
    
    
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
        $this->anoObito = ((isset($dados ['ano_obito'])) ? ($dados ['ano_obito']) : (null));
        $this->bairro = ((isset($dados ['bairro'])) ? ($dados ['bairro']) : (null));
        $this->cep = ((isset($dados ['cep'])) ? ($dados ['cep']) : (null));
        $this->cidade = ((isset($dados ['cidade'])) ? ($dados ['cidade']) : (null));
        $this->codigoControle = ((isset($dados ['codigo_controle'])) ? ($dados ['codigo_controle']) : (null));
        $this->complemento = ((isset($dados ['complemento'])) ? ($dados ['complemento']) : (null));
        $this->cpf = ((isset($dados ['cpf'])) ? ($dados ['cpf']) : (null));
        $this->creditos = ((isset($dados ['creditos'])) ? ($dados ['creditos']) : (null));
        $this->dataConsulta = ((isset($dados ['data_consulta'])) ? ($dados ['data_consulta']) : (null));
        $this->dataInscricao = ((isset($dados ['data_inscricao'])) ? ($dados ['data_inscricao']) : (null));
        $this->dataNascimento = ((isset($dados ['data_nascimento'])) ? ($dados ['data_nascimento']) : (null));
        $this->digitoVerificador = ((isset($dados ['digito_verificador'])) ? ($dados ['digito_verificador']) : (null));
        $this->genero = ((isset($dados ['genero'])) ? ($dados ['genero']) : (null));
        $this->horaConsulta = ((isset($dados ['hora_consulta'])) ? ($dados ['hora_consulta']) : (null));
        $this->logradouro = ((isset($dados ['logradouro'])) ? ($dados ['logradouro']) : (null));
        $this->msgObito = ((isset($dados ['msg_obito'])) ? ($dados ['msg_obito']) : (null));
        $this->nomeMae = ((isset($dados ['nome_mae'])) ? ($dados ['nome_mae']) : (null));
        $this->numero = ((isset($dados ['numero'])) ? ($dados ['numero']) : (null));
        $this->resultado = ((isset($dados ['resultado'])) ? ($dados ['resultado']) : (null));
        $this->resultadoTxt = ((isset($dados ['resultado_txt'])) ? ($dados ['resultado_txt']) : (null));
        $this->situacao = ((isset($dados ['situacao'])) ? ($dados ['situacao']) : (null));
        $this->tempoConsulta = ((isset($dados ['tempo_consulta'])) ? ($dados ['tempo_consulta']) : (null));
        $this->titular = ((isset($dados ['titular'])) ? ($dados ['titular']) : (null));
        $this->uf = ((isset($dados ['uf'])) ? ($dados ['uf']) : (null));
        $this->datahora = ((isset($dados['datahora'])) ? ($dados['datahora'] instanceof \Utils\Data ? $dados['datahora'] : 
            new \Utils\Data(substr($dados['datahora'], 0, 19))) : (null));
    }
    
    public function getTable() {
        return "consultas_cpf";
    }
    
    public function getSequence() {
        return null;
    }
    
    public function getInstance() {
        return new ConsultaCpf();
    }


}

?>