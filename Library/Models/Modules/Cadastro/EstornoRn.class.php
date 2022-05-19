<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 */
class EstornoRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new Estorno());
        } else {
            $this->conexao = new GenericModel($adapter, new Estorno());
        }
    }
    
    public function salvar(Estorno &$estorno) {
        try {
            
            if ($estorno->id > 0) {
                
                $aux = new Estorno(Array("id" => $estorno->id));
                $this->conexao->carregar($aux);
                
                $estorno->dataAbertura = $aux->dataAbertura;
                $estorno->dataCancelamento = $aux->dataCancelamento;
                $estorno->dataFinalizacao = $aux->dataFinalizacao;
                $estorno->agencia = $aux->agencia;
                $estorno->conta = $aux->conta;
                $estorno->cpfCnpj = $aux->cpfCnpj;
                $estorno->idBanco = $aux->idBanco;
                $estorno->idCliente = $aux->idCliente;
                $estorno->idDeposito = $aux->idDeposito;
                $estorno->idUsuarioAbertura = $aux->idUsuarioAbertura;
                $estorno->idUsuarioFinalinacao = $aux->idUsuarioFinalinacao;
                $estorno->motivoRejeicao = $aux->motivoRejeicao;
                $estorno->nomeTitular = $aux->nomeTitular;
                $estorno->status = $aux->status;
                $estorno->tipoConta = $aux->tipoConta;
                
            } else {
                $usuario = \Utils\Geral::getLogado();
                if (!$usuario instanceof Usuario) {
                    throw new \Exception($this->idioma->getText("vocePrecisaLogadoExecutarOperacao"));
                }
                
                $estorno->idUsuarioAbertura = $usuario->id;
                $estorno->dataAbertura = new \Utils\Data(date("d/m/Y H:i:s"));
                $estorno->dataCancelamento = null;
                $estorno->dataFinalizacao = null;
                $estorno->agencia = null;
                $estorno->conta = null;
                $estorno->cpfCnpj = null;
                $estorno->idBanco = null;
                $estorno->idUsuarioFinalizacao = null;
                $estorno->motivoRejeicao = "";
                $estorno->nomeTitular = null;
                $estorno->status = \Utils\Constantes::EXTORNO_PENDENTE;
                $estorno->tipoConta = null;
            }
            
            if (!($estorno->idCliente > 0)) {
                throw new \Exception($this->idioma->getText("informarIdentificacaoCliente"));
            }
            
            
            if (!($estorno->idDeposito > 0)) {
                throw new \Exception($this->idioma->getText("necessarioIdentificacaoDeposito"));
            }
            
            if (!is_numeric($estorno->valor) || $estorno->valor <= 0) {
                throw new \Exception($this->idioma->getText("necessarioIdentificacaoDeposito"));
            }
            
            
            if (!is_numeric($estorno->taxaTed) || $estorno->taxaTed < 0) {
                throw new \Exception($this->idioma->getText("taxaTedInvalida"));
            }
            
            
            if (!is_numeric($estorno->percentualTaxa) || $estorno->percentualTaxa < 0) {
                throw new \Exception($this->idioma->getText("percentualTaxaInvalido"));
            }
            
            
            if (!is_numeric($estorno->valorTaxa) || $estorno->valorTaxa < 0) {
                throw new \Exception($this->idioma->getText("valorTaxaInvalido"));
            }
            
            unset($estorno->usuarioAbertura);
            unset($estorno->usuarioFinalizacao);
            unset($estorno->banco);
            unset($estorno->cliente);
            unset($estorno->deposito);
            
            $this->conexao->salvar($estorno);
        } catch(\Exception $e) {
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
    
    public function filtrar(\Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $status = "T", $limit = "T", $filtro = null, $comDadosBancarios = false) {
        
        $where = Array();
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal"));
            }
            
            $where[] = " e.data_abertura BETWEEN '{$dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' AND '{$dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)}' ";
            
        }
        
        if ($status != "T") { 
            $where[] = " e.status = '{$status}' ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ( "
                    . " ( (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(c.email) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(e.agencia) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(e.conta) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(b.nome) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(e.nome_titular) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (CAST(e.id AS CHAR) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (CAST(e.id_deposito AS CHAR) LIKE LOWER('%{$filtro}%')) ) OR "
                    . " ( (LOWER(e.cpf_cnpj) LIKE LOWER('%{$filtro}%')) ) "
                    . " ) ";
        }
        
        if ($comDadosBancarios) {
            $where[] = " e.agencia IS NOT NULL ";
            $where[] = " e.nome_titular IS NOT NULL ";
            $where[] = " e.cpf_cnpj IS NOT NULL ";
            $where[] = " e.id_banco IS NOT NULL ";
            $where[] = " e.conta IS NOT NULL ";
            $where[] = " e.tipo_conta IS NOT NULL ";
        }
        
        $whereString = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $limitString = ($limit != "T" ? " LIMIT {$limit} " : "");
        $query = " SELECT e.* "
                . " FROM estornos e "
                . " INNER JOIN clientes c ON (c.id = e.id_cliente) "
                . " LEFT JOIN  bancos b ON (b.id = e.id_banco) "
                . " {$whereString} "
                . " ORDER BY e.data_abertura "
                . " {$limitString} ";
                
                
               
        $lista = Array();
        $result = $this->conexao->executeSql($query);
        foreach ($result as $dados) {
            $estorno = new Estorno($dados);
            $this->carregar($estorno, false, true, true, true, true, true);
            $lista[] = $estorno;
        }
        
        return $lista;
    }
    
    public function carregar(Estorno &$estorno, $carregar = true, $carregarUsuarioAbertura = true, $carregarUsuarioFinalizacao = true, $carregarBanco = true, $carregarCliente = true, $carregarDeposito = true) {
        if ($carregar) {
            $this->conexao->carregar($estorno);
        }
        
        if ($carregarUsuarioAbertura && $estorno->idUsuarioAbertura > 0) {
            $estorno->usuarioAbertura = new Usuario(Array("id" => $estorno->idUsuarioAbertura));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($estorno->usuarioAbertura);
        }
                
        if ($carregarUsuarioFinalizacao && $estorno->idUsuarioFinalizacao > 0) {
            $estorno->usuarioFinalizacao = new Usuario(Array("id" => $estorno->idUsuarioFinalizacao));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($estorno->usuarioFinalizacao);
        }
           
        if ($carregarBanco && $estorno->idBanco > 0) {
            $estorno->banco = new Banco(Array("id" => $estorno->idBanco));
            $bancoRn = new BancoRn();
            $bancoRn->conexao->carregar($estorno->banco);
        }
           
        if ($carregarCliente && $estorno->idCliente > 0) {
            $estorno->cliente = new Cliente(Array("id" => $estorno->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($estorno->cliente);
        }
           
        if ($carregarDeposito && $estorno->idDeposito > 0) {
            $estorno->deposito = new Deposito(Array("id" => $estorno->idDeposito));
            $depositoRn = new DepositoRn();
            $depositoRn->carregar($estorno->deposito, true, true, false, false);
        }
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarUsuarioAbertura = true, $carregarUsuarioFinalizacao = true, $carregarBanco = true, $carregarCliente = true, 
            $carregarDeposito = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        foreach ($result as $estorno) {
            $this->carregar($estorno, false, $carregarUsuarioAbertura, $carregarUsuarioFinalizacao, $carregarBanco, $carregarCliente, $carregarDeposito);
            $lista[] = $estorno;
        }
        
        return $lista;
    }
    
    public function iniciar(Deposito $deposito) {
        try {
            $this->conexao->adapter->iniciar();
            
            $depositoRn = new DepositoRn();
            try {
                $depositoRn->conexao->carregar($deposito);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("depositoInvalidoNaoEncontrado"));
            }



            $configuracao = new Configuracao(Array("id" => 1));
            $configuracaoRn = new ConfiguracaoRn();
            $configuracaoRn->conexao->carregar($configuracao);

            $taxa = number_format(($deposito->valorDepositado * ($configuracao->percentualEstornoDeposito / 100)), 2, ".", "");

            $estorno = new Estorno();
            $estorno->idCliente = $deposito->idCliente;
            $estorno->idDeposito = $deposito->id;
            $estorno->valor = number_format(($deposito->valorDepositado  - $taxa - $configuracao->tarifaTed), 2, ".", "");
            $estorno->valorTaxa = $taxa;
            $estorno->percentualTaxa = $configuracao->percentualEstornoDeposito;
            $estorno->taxaTed = $configuracao->tarifaTed;

            $this->salvar($estorno);
            $deposito->motivoCancelamento = $this->idioma->getText("procEstornoIniciado");
            $depositoRn->cancelar($deposito);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $ex) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($ex);
        }
    }
    
    public function cadastrarInformacoesBancarias(Estorno $estorno) {
        
        $e = new Estorno(Array("id" => $estorno->id));
        try {
            $this->conexao->carregar($e);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("estornoInvalidoNaoEncontrado"));
        }
        
        if ($e->status == \Utils\Constantes::EXTORNO_FINALIZADO) {
            throw new \Exception($this->idioma->getText("registroComoFinalizado"));
        }
        
        if ($e->status == \Utils\Constantes::EXTORNO_CANCELADO) {
            throw new \Exception($this->idioma->getText("registroComoCancelado"));
        }
        
        
        if ($e->status == \Utils\Constantes::EXTORNO_APROVADO) {
            throw new \Exception($this->idioma->getText("registroComoAprovado"));
        }
        
        if (!($estorno->idBanco > 0)) {
            throw new \Exception($this->idioma->getText("agenciaDeveInformada"));
        }
        
        if (empty($estorno->agencia)) {
            throw new \Exception($this->idioma->getText("agenciaDeveInformada"));
        }
        
        if (empty($estorno->conta)) {
            throw new \Exception($this->idioma->getText("contaDeveInformada"));
        }
        
        if (empty($estorno->nomeTitular)) {
            throw new \Exception($this->idioma->getText("titularDeveInformado"));
        }
        
        $tiposContas = Array(
            \Utils\Constantes::CONTA_CORRENTE,
            \Utils\Constantes::CONTA_POUPANCA
        );
        
        if (!in_array($estorno->tipoConta, $tiposContas)) {
            throw new \Exception($this->idioma->getText("tipoContaInvalido"));
        }
        
        $doc = str_replace(Array(".", "-", "/"), "", $estorno->cpfCnpj);
        if (strlen($doc) != 11 && strlen($doc) != 14) {
            throw new \Exception($this->idioma->getText("cpfCnpjInvalido"));
        }
        
        if (strlen($doc) == 11) {
            if (!\Utils\Validacao::cpf($doc)) { 
                throw new \Exception($this->idioma->getText("cpfInvalido"));
            }
        }
        if (strlen($doc) == 14) {
            if (!\Utils\Validacao::cnpj($doc)) { 
                throw new \Exception($this->idioma->getText("cnpjInvalido"));
            }
        }
        $this->conexao->update(
                Array(
                    "id_banco" => $estorno->idBanco,
                    "agencia" => $estorno->agencia,
                    "conta" => $estorno->conta,
                    "nome_titular" => $estorno->nomeTitular,
                    "tipo_conta" => $estorno->tipoConta,
                    "cpf_cnpj" => $estorno->cpfCnpj,
                    "status" => \Utils\Constantes::EXTORNO_PENDENTE
                ), 
                Array(
                    "id" => $estorno->id
                )
            );
        
    }
    
    
    public function rejeitar(Estorno &$estorno) {
        try {
            $this->conexao->adapter->iniciar();
            
            $e = new Estorno(Array("id" => $estorno->id));
            try {
                $this->conexao->carregar($e);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("estornoInvalidoNaoEncontrado"));
            }

            if ($e->status == \Utils\Constantes::EXTORNO_FINALIZADO) {
                throw new \Exception($this->idioma->getText("naoPossiveRejeitarRegistroFinalizado"));
            }

            if ($e->status == \Utils\Constantes::EXTORNO_CANCELADO) {
                throw new \Exception($this->idioma->getText("naoPossRejRegCancelado"));
            }

            if (empty($estorno->motivoRejeicao)) {
                throw new \Exception($this->idioma->getText("necInfMotRejDados"));
            }

            $this->conexao->update(
                    Array(
                        "status" => \Utils\Constantes::EXTORNO_REJEITADO,
                        "motivo_rejeicao" => $estorno->motivoRejeicao
                    ), 
                    Array(
                        "id" => $estorno->id
                    )
                );
            
            $notificacaoRn = new NotificacaoRn();
            $notificacao = new Notificacao();
            $notificacao->clientes = 0;
            $notificacao->usuarios = 0;
            $notificacao->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $valor = number_format($e->valor, 2, ",", ".");
            $texto = $this->idioma->getText("osDadosInformadosConsulteMensTopoTela");
            $texto = str_replace("{var1}",$e->id, $texto);
            $texto = str_replace("{var2}",$valor, $texto);
            $notificacao->html = $texto;
            
            $notificacao->tipo = "w";      
            $notificacaoRn->salvarNotificacao($notificacao, Array($e->idCliente), Array(), false, false);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($e);
        }
    }
    
    public function cancelarEstorno(Estorno &$estorno) {
        
        try { 
            $this->conexao->adapter->iniciar();
            $e = new Estorno(Array("id" => $estorno->id));
            try {
                $this->conexao->carregar($e);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("estornoInvalidoNaoEncontrado"));
            }
            if ($e->status == \Utils\Constantes::EXTORNO_FINALIZADO) {
                throw new \Exception($this->idioma->getText("naoPosCancelarRegFina"));
            }

            $this->conexao->update(
                    Array(
                        "status" => \Utils\Constantes::EXTORNO_CANCELADO,
                        "data_cancelamento" => date("d/m/Y H:i:s")
                    ),
                    Array(
                        "id" => $estorno->id
                    )
                );
           
            $notificacaoRn = new NotificacaoRn();
            $notificacao = new Notificacao();
            $notificacao->clientes = 0;
            $notificacao->usuarios = 0;
            $notificacao->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $valor = number_format($e->valor, 2, ",", ".");
            $texto1 = $this->idioma->getText("oProcEstornCancelado");
            $texto1 = str_replace("{var1}",$e->id, $texto1);
            $texto1 = str_replace("{var2}",$valor, $texto1);
            $notificacao->html = $texto1;
            $notificacao->tipo = "e";      
            $notificacaoRn->salvarNotificacao($notificacao, Array($e->idCliente), Array(), false, false);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($e);
        }
    }
    
    
    public function aprovarEstorno(Estorno &$estorno) {
        
        try { 
            $this->conexao->adapter->iniciar();
            
            $e = new Estorno(Array("id" => $estorno->id));
            try {
                $this->conexao->carregar($e);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("estornoInvalidoNaoEncontrado"));
            }


            if ($e->status == \Utils\Constantes::EXTORNO_FINALIZADO) {
                throw new \Exception($this->idioma->getText("naoPosAproRegFinal"));
            }
            if ($e->status == \Utils\Constantes::EXTORNO_APROVADO) {
                throw new \Exception($this->idioma->getText("naoPOsAprovRegAprovado"));
            }

            if ($e->status == \Utils\Constantes::EXTORNO_CANCELADO) {
                throw new \Exception($this->idioma->getText("naoPosAprovRegCancelado"));
            }

            $this->conexao->update(
                    Array(
                        "status" => \Utils\Constantes::EXTORNO_APROVADO
                    ),
                    Array(
                        "id" => $estorno->id
                    )
                );
            
            $notificacaoRn = new NotificacaoRn();
            $notificacao = new Notificacao();
            $notificacao->clientes = 0;
            $notificacao->usuarios = 0;
            $notificacao->data = new \Utils\Data(date("d/m/Y H:i:s"));
            $valor = number_format($e->valor, 2, ",", ".");
            $texto2 = $this->idioma->getText("dadosInformValidadosSucesso");
            $texto2 = str_replace("{var1}",$e->id, $texto2);
            $texto2 = str_replace("{var2}",$valor, $texto2);
            $notificacao->html = $texto2;
            $notificacao->tipo = "s";      
            $notificacaoRn->salvarNotificacao($notificacao, Array($e->idCliente), Array(), false, false);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($e);
        }
    }
    
    
    public function finalizarEstorno(Estorno &$estorno) {
        try { 
            $this->conexao->adapter->iniciar();
            $usuario = \Utils\Geral::getLogado();
            if (!$usuario instanceof Usuario) {
                throw new \Exception($this->idioma->getText("vocePrecisaLogadoOperacao"));
            }

            $e = new Estorno(Array("id" => $estorno->id));
            try {
                $this->conexao->carregar($e);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("estornoInvalidoNaoEncontrado"));
            }


            if ($e->status == \Utils\Constantes::EXTORNO_FINALIZADO) {
                throw new \Exception($this->idioma->getText("naoPosRegFinalizado"));
            }

            if ($e->status == \Utils\Constantes::EXTORNO_CANCELADO) {
                throw new \Exception($this->idioma->getText("naoPossFinalRegCan"));
            }

            if ($e->status != \Utils\Constantes::EXTORNO_APROVADO) {
                throw new \Exception($this->idioma->getText("regPrecEstarAprovado"));
            }

            $this->conexao->update(
                Array(
                    "status" => \Utils\Constantes::EXTORNO_FINALIZADO,
                    "data_finalizacao" => date("Y-m-d H:i:s"),
                    "id_usuario_finalizacao" => $usuario->id
                ),
                Array(
                    "id" => $estorno->id
                )
            );
        
            $notificacaoRn = new NotificacaoRn();
            $notificacao = new Notificacao();
            $notificacao->clientes = 0;
            $notificacao->usuarios = 0;
            $notificacao->data = new \Utils\Data(date("Y-m-d H:i:s"));
            $valor = number_format($e->valor, 2, ",", ".");
            $texto3 = $this->idioma->getText("enviarValorRefProcEstorno");
            $texto3 = str_replace("{var1}",$valor, $texto3);
            $texto3 = str_replace("{var2}",$e->id, $texto3);
            $notificacao->html = $texto3;
            $notificacao->tipo = "s";      
            $notificacaoRn->salvarNotificacao($notificacao, Array($e->idCliente), Array(), false, false);
            
            $this->conexao->adapter->finalizar();
        } catch (\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception($e);
        }
    }
    
    public function getByDeposito(Deposito $deposito) {
        $s = \Utils\Constantes::EXTORNO_CANCELADO;
        
        $result = $this->conexao->select("id_deposito = {$deposito->id} AND status != '{$s}' ", null, null, null);
        if (sizeof($result) > 0) {
            return $result->current();
        }
        return null;
    }
    
    
    public function listarByCliente(Cliente $cliente) {
        $s = \Utils\Constantes::EXTORNO_CANCELADO;
        //exit("id_cliente = {$cliente->id} AND situacao = '{$s}' ");
        
        $result = $this->conexao->select("id_cliente = {$cliente->id} AND status != '{$s}' ", null, null, null);
        
        return $result;
    }
    
    
    public function listarDepositosDadosPendentes(Cliente $cliente) {
        $pendente = \Utils\Constantes::EXTORNO_PENDENTE;
        $rejeitado = \Utils\Constantes::EXTORNO_REJEITADO;
        $query = " SELECT e.* "
                . "FROM estornos e "
                . " WHERE "
                . " id_cliente = {$cliente->id} AND"
                . " ("
                    . " (status = '{$pendente}' AND (id_banco IS NULL OR agencia IS NULL OR conta IS NULL OR tipo_conta IS NULL OR nome_titular IS NULL OR cpf_cnpj IS NULL) ) OR "
                    . " (status = '{$rejeitado}' ) "
                . " ) "
                . " ORDER BY data_abertura";
        //exit($query);
        $result = $this->conexao->executeSql($query);
        $lista = Array();
        foreach ($result as $dados) {
            $estorno = new Estorno($dados);
            $lista[] = $estorno;
        }
        
        return $lista;
    }
}

?>