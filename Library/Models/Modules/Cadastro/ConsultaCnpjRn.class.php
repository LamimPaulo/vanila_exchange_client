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
class ConsultaCnpjRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ConsultaCnpj());
        } else {
            $this->conexao = new GenericModel($adapter, new ConsultaCnpj());
        }
    }
    
    public function salvar(ConsultaCnpj &$consultaCnpj) {
        
        try {
            $this->conexao->adapter->iniciar();
            $consultaCnpj->cnpj = str_replace(Array(".", "-", "/"), Array(""), $consultaCnpj->cnpj);
            
            if (strlen($consultaCnpj->cnpj) != 14) {
                throw new \Exception("CNPJ inválido");
            }
            
            $usuario = \Utils\Geral::getLogado();
            if ($usuario == null || !$usuario instanceof Usuario) {
                throw new \Exception("Você não tem permissão para executar essa ação");
            }
            
            $consultaCnpj->idUsuario = $usuario->id;
            $consultaCnpj->usuario = $usuario;
            $consultaCnpj->datahora = new \Utils\Data(date("d/m/Y H:i:s"));
            
            $result = $this->conexao->listar("cnpj = '{$consultaCnpj->cnpj}'", "cnpj", NULL, 1);
            if (sizeof($result)) {
                
                $this->conexao->update(
                        Array(
                            'nome_fantasia' => $consultaCnpj->nomeFantasia,
                            'nome_empresa' => $consultaCnpj->nomeEmpresa,
                            'atividades_secundarias' => $consultaCnpj->atividadesSecundarias,
                            'complemento' => $consultaCnpj->complemento,
                            'municipio' => $consultaCnpj->municipio,
                            'data_situacao_especial' => $consultaCnpj->dataSituacaoEspecial,
                            'email' => $consultaCnpj->email,
                            'atividade_principal' => $consultaCnpj->atividadePrincipal,
                            'situacao_cadastral' => $consultaCnpj->situacaoCadastral,
                            'nome_responsavel' => $consultaCnpj->nomeResponsavel,
                            'creditos' => $consultaCnpj->creditos,
                            'data_situacao_cadastral' => $consultaCnpj->dataSituacaoCadastral,
                            'numero' => $consultaCnpj->numero,
                            'data_abertura' => $consultaCnpj->dataAbertura,
                            'cpf_responsavel' => $consultaCnpj->cpfResponsavel,
                            'uf' => $consultaCnpj->uf,
                            'natureza_juridica' => $consultaCnpj->naturezaJuridica,
                            'socios' => $consultaCnpj->socios,
                            'tipo' => $consultaCnpj->tipo,
                            'logradouro' => $consultaCnpj->logradouro,
                            'motivo_situacao_cadastral' => $consultaCnpj->motivoSituacaoCadastral,
                            'tempo_consulta' => $consultaCnpj->tempoConsulta,
                            'cep' => $consultaCnpj->cep,
                            'resultado' => $consultaCnpj->resultado,
                            'datahora' => date("Y-m-d H:i:s"),
                            'resultado_txt' => $consultaCnpj->resultadoTxt,
                            'situacao_especial' => $consultaCnpj->situacaoEspecial,
                            'bairro' => $consultaCnpj->bairro,
                            'capital_social' => $consultaCnpj->capitalSocial,
                            'telefone' => $consultaCnpj->telefone,
                            'id_usuario' => $usuario->id
                        ),
                        Array(
                            'cnpj' => $consultaCnpj->cnpj,
                        )
                    );
                
            } else {
                
                $this->conexao->insert(
                        Array(
                            'cnpj' => $consultaCnpj->cnpj,
                            'nome_fantasia' => $consultaCnpj->nomeFantasia,
                            'nome_empresa' => $consultaCnpj->nomeEmpresa,
                            'atividades_secundarias' => $consultaCnpj->atividadesSecundarias,
                            'complemento' => $consultaCnpj->complemento,
                            'municipio' => $consultaCnpj->municipio,
                            'data_situacao_especial' => $consultaCnpj->dataSituacaoEspecial,
                            'email' => $consultaCnpj->email,
                            'atividade_principal' => $consultaCnpj->atividadePrincipal,
                            'situacao_cadastral' => $consultaCnpj->situacaoCadastral,
                            'nome_responsavel' => $consultaCnpj->nomeResponsavel,
                            'creditos' => $consultaCnpj->creditos,
                            'data_situacao_cadastral' => $consultaCnpj->dataSituacaoCadastral,
                            'numero' => $consultaCnpj->numero,
                            'data_abertura' => $consultaCnpj->dataAbertura,
                            'cpf_responsavel' => $consultaCnpj->cpfResponsavel,
                            'uf' => $consultaCnpj->uf,
                            'natureza_juridica' => $consultaCnpj->naturezaJuridica,
                            'socios' => $consultaCnpj->socios,
                            'tipo' => $consultaCnpj->tipo,
                            'logradouro' => $consultaCnpj->logradouro,
                            'motivo_situacao_cadastral' => $consultaCnpj->motivoSituacaoCadastral,
                            'tempo_consulta' => $consultaCnpj->tempoConsulta,
                            'cep' => $consultaCnpj->cep,
                            'resultado' => $consultaCnpj->resultado,
                            'datahora' => date("Y-m-d H:i:s"),
                            'resultado_txt' => $consultaCnpj->resultadoTxt,
                            'situacao_especial' => $consultaCnpj->situacaoEspecial,
                            'bairro' => $consultaCnpj->bairro,
                            'capital_social' => $consultaCnpj->capitalSocial,
                            'telefone' => $consultaCnpj->telefone,
                            'id_usuario' => $usuario->id
                        )
                    );
                
            }
            
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    public function salvarArray($dados) {
        
        try {
            
            
            $consultaCnpj = new ConsultaCnpj();
            $cliente = \Utils\Geral::getCliente();
            
            $consultaCnpj->cnpj = \Utils\Validacao::limparString($dados["Cnpj"]);
            $consultaCnpj->nomeFantasia = $dados["NomeFantasia"];
            $consultaCnpj->nomeEmpresa = $dados["NomeEmpresa"];
            $consultaCnpj->complemento = $dados["Complemento"];
            $consultaCnpj->municipio = $dados["Municipio"];
            $consultaCnpj->email = $dados["E-mail"];
            $consultaCnpj->atividadePrincipal = $dados["AtividadePrincipal"] ;
            $consultaCnpj->situacaoCadastral = $dados["SituacaoCadastral"];
            $consultaCnpj->nomeResponsavel = $dados["NomeResponsavel"];
            $consultaCnpj->dataSituacaoCadastral = $dados["DataSituacaoCadastral"];
            $consultaCnpj->numero = $dados["Numero"];
            $consultaCnpj->dataAbertura = $dados["DataAbertura"];
            $consultaCnpj->cpfResponsavel = \Utils\Validacao::limparString($dados["CpfResponsavel"]);
            $consultaCnpj->uf = $dados["UF"];
            $consultaCnpj->naturezaJuridica = $dados["NaturezaJuridica"];
            $consultaCnpj->tipo = $dados["Tipo"];
            $consultaCnpj->logradouro = $dados["Logradouro"];
            $consultaCnpj->motivoSituacaoCadastral = $dados["MotivoSituacaoCadastral"];
            $consultaCnpj->cep = $dados["Cep"];
            $consultaCnpj->resultadoTxt = $dados["response"];
            $consultaCnpj->situacaoEspecial = $dados["SituacaoEspecial"];
            $consultaCnpj->bairro = $dados["Bairro"];
            $consultaCnpj->capitalSocial = $dados["CapitalSocial"];
            $consultaCnpj->telefone = $dados["Telefone"];
            $consultaCnpj->dataCadastro =  date("Y-m-d H:i:s");            
            $consultaCnpj->idCliente = $cliente->id;
            
            
            $this->conexao->salvar($consultaCnpj);
            
        } catch(\Exception $e) {
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    
    public function getByCnpj($cnpj) {
        $cnpj = \Utils\Validacao::limparString($cnpj);
        $result = $this->conexao->select(Array(
            "cnpj" => $cnpj
        ));
        
        if ($result) {
            $consultaCnpj = $result->current();
            /*if ($consultaCnpj->idUsuario > 0) {
                $consultaCnpj->usuario  =new Usuario(Array("id" => $consultaCnpj->idUsuario));
                $usuarioRn = new UsuarioRn();
                $usuarioRn->conexao->carregar($consultaCnpj->usuario);
            }*/
            return $consultaCnpj;
        }
        return null;
    }
    
}

?>