<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Auth
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ConsultaCpfRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ConsultaCpf());
        } else {
            $this->conexao = new GenericModel($adapter, new ConsultaCpf());
        }
    }
    
    public function salvar(ConsultaCpf &$consultaCpf) {
        
        try {
            $this->conexao->adapter->iniciar();
            $consultaCpf->cpf = str_replace(Array(".", "-"), Array(""), $consultaCpf->cpf);
            
            if (strlen($consultaCpf->cpf) != 11) {
                throw new \Exception("CPF inválido");
            }
            
            $result = $this->conexao->listar("cpf = '{$consultaCpf->cpf}'", "cpf", NULL, 1);
            if (sizeof($result)) {
                
                $this->conexao->update(
                        Array(
                            'ano_obito' => $consultaCpf->anoObito,
                            'bairro' => $consultaCpf->bairro,
                            'cep' => $consultaCpf->cep,
                            'cidade' => $consultaCpf->cidade,
                            'codigo_controle' => $consultaCpf->codigoControle,
                            'complemento' => $consultaCpf->complemento,
                            'creditos' => $consultaCpf->creditos,
                            'data_consulta' => $consultaCpf->dataConsulta,
                            'data_inscricao' => (strlen($consultaCpf->dataInscricao) == 10 ? $consultaCpf->dataInscricao : "00:00:0000"),
                            'data_nascimento' => (strlen($consultaCpf->dataNascimento) == 10 ? $consultaCpf->dataNascimento : "00:00:0000"),
                            'digito_verificador' => $consultaCpf->digitoVerificador,
                            'genero' => $consultaCpf->genero,
                            'hora_consulta' => $consultaCpf->horaConsulta,
                            'logradouro' => $consultaCpf->logradouro,
                            'msg_obito' => $consultaCpf->msgObito,
                            'nome_mae' => $consultaCpf->nomeMae,
                            'numero' => $consultaCpf->numero,
                            'resultado' => $consultaCpf->resultado,
                            'resultado_txt' => $consultaCpf->resultadoTxt,
                            'situacao' => $consultaCpf->situacao,
                            'tempo_consulta' => $consultaCpf->tempoConsulta,
                            'titular' => $consultaCpf->titular,
                            'uf' => $consultaCpf->uf,
                            'datahora' => date("Y-m-d H:i:s")
                        ),
                        Array(
                            'cpf' => $consultaCpf->cpf,
                        )
                    );
                
            } else {
                
                $this->conexao->insert(
                        Array(
                            'ano_obito' => $consultaCpf->anoObito,
                            'bairro' => $consultaCpf->bairro,
                            'cep' => $consultaCpf->cep,
                            'cidade' => $consultaCpf->cidade,
                            'cpf' => $consultaCpf->cpf,
                            'codigo_controle' => $consultaCpf->codigoControle,
                            'complemento' => $consultaCpf->complemento,
                            'creditos' => $consultaCpf->creditos,
                            'data_consulta' => $consultaCpf->dataConsulta,
                            'data_inscricao' => (strlen($consultaCpf->dataInscricao) == 10 ? $consultaCpf->dataInscricao : "00:00:0000"),
                            'data_nascimento' => (strlen($consultaCpf->dataNascimento) == 10 ? $consultaCpf->dataNascimento : "00:00:0000"),
                            'digito_verificador' => $consultaCpf->digitoVerificador,
                            'genero' => $consultaCpf->genero,
                            'hora_consulta' => $consultaCpf->horaConsulta,
                            'logradouro' => $consultaCpf->logradouro,
                            'msg_obito' => $consultaCpf->msgObito,
                            'nome_mae' => $consultaCpf->nomeMae,
                            'numero' => $consultaCpf->numero,
                            'resultado' => $consultaCpf->resultado,
                            'resultado_txt' => $consultaCpf->resultadoTxt,
                            'situacao' => $consultaCpf->situacao,
                            'tempo_consulta' => $consultaCpf->tempoConsulta,
                            'titular' => $consultaCpf->titular,
                            'uf' => $consultaCpf->uf,
                            'datahora' => date("Y-m-d H:i:s")
                        )
                    );
                
            }
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function getByCpf($cpf) {
        $cpf = str_replace(Array(".", "-"), "", $cpf);
        $result = $this->conexao->select(Array(
            "cpf" => $cpf
        ));
        
        if ($result) {
            return $result->current();
        }
        return null;
    }
    
}

?>