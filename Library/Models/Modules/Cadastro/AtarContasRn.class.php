<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

/**
 * 
 *
 * @author willianchiquetto
 */
class AtarContasRn {
    
     /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new AtarContas());
        } else {
            $this->conexao = new GenericModel($adapter, new AtarContas());
        }
        $this->idioma = new \Utils\PropertiesUtils("exception", IDIOMA);
    }
    
    public function salvarTransacao(AtarContas &$atarContas) {
        
        if ($atarContas->id > 0) {
            $aux = new AtarContas(Array("id" => $atarContas->id));
            $this->conexao->carregar($aux);
            $atarContas->id = $aux->id;
            $atarContas->dataCadastro = $aux->dataCadastro;
            $atarContas->idCliente = $aux->idCliente;
            $atarContas->idClienteAtar = $aux->idClienteAtar;
            $atarContas->tipo = $aux->tipo;
            $atarContas->valor = $aux->valor;
        } 
        
        if ($atarContas->tipo == \Utils\Constantes::ENTRADA) {
          
            $atarClientesRn = new AtarClientesRn();
            $atarClientes = $atarClientesRn->encontrarCliente($atarContas, true);
            
            $atarClientes = $atarClientes->current();
           
            if(!empty($atarClientes)){
                
                $atarContas->idCliente = $atarClientes->idCliente;
                
            } else {
                //Devolver dinheiro para cliente Atar
                throw new \Exception("Cliente não identificado.");
                //$this->devolverCredito($atarContas);
            }
            
        } else {
            if (empty($atarContas->idCliente)) {
                throw new \Exception("Cliente precisa ser preenchido.");
            }
        }

        if (empty($atarContas->documentAtar)) {
            throw new \Exception("Documento precisa ser preenchido.");
        }
        
        if (empty($atarContas->valor) || $atarContas->valor <= 0) {
            throw new \Exception("Valor precisa ser válido.");
        }
        
        if (empty($atarContas->valorCreditado) || $atarContas->valorCreditado <= 0) {
            throw new \Exception("Valor precisa ser válido.");
        }

        if (empty($atarContas->dataCadastro)) {
            throw new \Exception("Data de cadastro vazia.");
        }
        
        if (empty($atarContas->tipo)) {
            throw new \Exception("Tipo deve ser inserido.");
        }
        
        $documento = \Utils\Validacao::limparString($atarContas->documentAtar);

        if (\Utils\Validacao::cpf($documento)) {
            $atarContas->documentAtar = $documento;
        } else if (\Utils\Validacao::cnpj($documento)) {
            $atarContas->documentAtar = $documento;
        } else {
            throw new \Exception("Documento informado não aceito.");
        }

        $response = null;
        $saldoAtar = null;
        
        if($atarContas->tipo == \Utils\Constantes::SAIDA){
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $saldo = $contaCorrenteReaisRn->calcularSaldoConta(new Cliente(Array("id" => $atarContas->idCliente)), false, true);
            
            if ($saldo < $atarContas->valor) {
                throw new \Exception("Saldo insuficiente.");
            }            
            
            ClienteHasCreditoRn::validar(new Cliente(Array("id" => $atarContas->idCliente)));
            
            $response = $this->inserirTransacao($atarContas);
            
            if (is_numeric($response)) {

                $mensagem = "";
                
                switch ($response) {
                    case 400:
                        $mensagem = "Parâmetros errados.";
                        break;

                    case 401:
                        $mensagem = "Não autorizado.";
                        break;
                    
                    case 402:
                        $mensagem = "Fundos insuficientes.";
                        break;

                    case 403:
                        $mensagem = "Conta encerrada, bloqueada ou não possui escopo de permissão.";
                        break;

                    case 404:
                        $mensagem = "Entidade alvo não encontrada.";
                        break;

                    case 405:
                        $mensagem = "Método não permitido.";
                        break;

                    case 406:
                        $mensagem = "Conta do usuário bloqueada ou cancelada, ou você não pode pagar você mesmo.";
                        break;

                    case 412:
                        $mensagem = "Usuário alvo não pôde receber a transação.";
                        break;

                    case 500:
                        $mensagem = "Erro interno de processamento.";
                        break;
                    
                    case 504:
                        $mensagem = "Erro interno de processamento.";
                        break;
                    
                    default:
                        $mensagem = "Muitas requests ou IP não está em whitelist.";
                        break;
                }
                
                \Utils\Notificacao::notificar($mensagem . " ID Cliente: " . $atarContas->idCliente, true, false, null, true);
                
                throw new \Exception("Por favor, verifique se a conta de destino está ativa/normalizada para receber o saque.");
                
            } else {
                
                $atarContas->retorno = json_encode($response);

                $atarContas->idTransacao = $response->id;

                $saldoAtar = number_format(($response->balanceAfter / 100), 2, ".", "");
                
            }
        }
        
        if(!empty($atarContas->idTransacao)){
            
            $atarContas->dataConfirmacao = new \Utils\Data(date("Y-m-d H:i:s"));
            $atarContas->confirmado = 1;
            
            $this->conexao->salvar($atarContas);
            $this->gerarContaCorrente($atarContas);
            
            //Atualiza saldo Atar
            $configuracaoRn = new ConfiguracaoRn();
            if($atarContas->tipo == \Utils\Constantes::SAIDA){
                $configuracaoRn->atualizarSaldoAtar($saldoAtar);
            } else {
                $atarApi = new \Atar\AtarApi();
                $saldo = $atarApi->consultarSaldo();
                
                $saldoAtar = number_format(($saldo->amount / 100), 2, ".", "");
                $configuracaoRn->atualizarSaldoAtar($saldoAtar);
            }
            
            
        } else {
            
            $atarContas->dataConfirmacao = new \Utils\Data(date("Y-m-d H:i:s"));
            $atarContas->confirmado = 0;
            
            throw new \Exception("Não foi possível registrar a transação. Verifique o documento de destino.");
        }
       
    }
    
    public function confirmarTransacao(AtarContas &$atarContas) {
        try {            
            $this->conexao->update(Array("confirmado" => $atarContas->ativo, "data_confirmacao" => $atarContas->dataConfirmacao, 
                "retorno" => $atarContas->retorno), Array("id" => $atarContas->id));
        } catch (\Exception $ex) {
            throw new \Exception("Confirmação de transação Atar não realizada.");
        }
    }
    
    private function inserirTransacao(AtarContas &$atarContas){
        
        $atarApi = new \Atar\AtarApi();
        $response = $atarApi->transferenciaInterna($atarContas->valorCreditado, $atarContas->documentAtar);
        
        return $response;
    }
    
    private function devolverCredito(AtarContas &$atarContas) {
        try{
            $dados = json_decode($atarContas->retorno);
            
            $atarContas->idClienteAtar = $dados->from->atarId;
            $atarContas->documentAtar = $dados->from->entity->document;
            $atarContas->valor = number_format($dados->amount / 100, 2, ".", ""); //Recebe em centavos;
            
            $atarApi = new \Atar\AtarApi();
            $atarApi->transferenciaInterna($atarContas->valor, $atarContas->documentAtar);
            
        } catch (Exception $ex) {
            throw new \Exception("Falha para devolver o crédito.");
        }
    }
    
    private function gerarContaCorrente(AtarContas &$atarContas) {
        try {
            
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            $contaCorrenteReaisEmpresaRn = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresaRn();

            if($atarContas->tipo == \Utils\Constantes::ENTRADA){
                $valor = $atarContas->valorCreditado;
            } else {                
                $valor = $atarContas->valor;
            }
            
            $contaCorrenteTo = new \Models\Modules\Cadastro\ContaCorrenteReais();
            $contaCorrenteTo->id = 0;
            $contaCorrenteTo->idCliente = $atarContas->idCliente;
            $contaCorrenteTo->data = new \Utils\Data(date("Y-m-d H:i:s"));
            $contaCorrenteTo->descricao = "Atar - ID {$atarContas->id}";
            $contaCorrenteTo->tipo = $atarContas->tipo;
            $contaCorrenteTo->valor = number_format($valor, 2, ".", "");
            $contaCorrenteTo->transferencia = 1;
            $contaCorrenteTo->origem = 10;
            $contaCorrenteReaisRn->gerarContaCorrente($contaCorrenteTo);
            
            //Credito ou Debito
            $contaCorrenteReaisEmpresa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresa->id = 0;
            $contaCorrenteReaisEmpresa->data = new \Utils\Data(date("Y-m-d H:i:s"));
            $contaCorrenteReaisEmpresa->descricao = "Transferencia Atar - ID {$atarContas->id}";
            $contaCorrenteReaisEmpresa->tipo = $atarContas->tipo == \Utils\Constantes::ENTRADA ? \Utils\Constantes::ENTRADA : \Utils\Constantes::SAIDA;
            $contaCorrenteReaisEmpresa->valor = number_format($atarContas->valorCreditado, 2, ".", "");
            $contaCorrenteReaisEmpresa->transferencia = 1;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresa);
            
            // Credito de Taxas e Tarifas
            $contaCorrenteReaisEmpresaTaxa = new \Models\Modules\Cadastro\ContaCorrenteReaisEmpresa();
            $contaCorrenteReaisEmpresaTaxa->id = 0;
            $contaCorrenteReaisEmpresaTaxa->data = new \Utils\Data(date("Y-m-d H:i:s"));
            $contaCorrenteReaisEmpresaTaxa->descricao = "Taxa e Tarifa Atar - ID {$atarContas->id}";
            $contaCorrenteReaisEmpresaTaxa->tipo = \Utils\Constantes::ENTRADA;
            $contaCorrenteReaisEmpresaTaxa->valor = number_format($atarContas->taxa + $atarContas->tarifa, 2, ".", "");
            $contaCorrenteReaisEmpresaTaxa->transferencia = 1;
            $contaCorrenteReaisEmpresaRn->salvar($contaCorrenteReaisEmpresaTaxa);
            
            //Notificar Cliente
            $clienteNotificacao = new Cliente(Array("id" => $atarContas->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($clienteNotificacao);

            if($atarContas->tipo == \Utils\Constantes::ENTRADA){
                
                $dados["valor"] = number_format($atarContas->valor, 2, ",", ".");
                $dados["status"] = "Confirmado";
                $dados["moeda_nome"] = "Real";
                
                \LambdaAWS\LambdaNotificacao::notificar($clienteNotificacao, true, 13, false, $dados);
                
            } else {                
                
                $dados["valor"] = number_format($atarContas->valor, 2, ",", ".");
                $dados["status"] =  "Confirmado";
                $dados["taxa"] = number_format($atarContas->taxa + $atarContas->tarifa, 2, ",", ".");
                $dados["creditado"] = number_format($atarContas->valorCreditado, 2, ",", ".");
                $dados["banco_nome"] = "Atar";
                $dados["moeda_nome"] = "Real";

                \LambdaAWS\LambdaNotificacao::notificar($clienteNotificacao, true, 15, false, $dados);
            }

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
    
    public function filtrar($idCliente = null, \Utils\Data $dataInicial = null, \Utils\Data $dataFinal = null, $tipo = NULL,
            $confirmado = null, $filtro = null, $limit = "T") {
        
        $where = Array();
        
        /*if (\Utils\Geral::isCliente()) {
            $idCliente = \Utils\Geral::getCliente()->id;
        }*/
        
        if ($idCliente > 0) {
            $where[] = " a.id_cliente = {$idCliente} ";
        }
        
        if (isset($dataInicial->data) && $dataInicial->data != null && isset($dataFinal->data) && $dataFinal->data != null) {
            if ($dataInicial->maior($dataFinal)) {
                throw new \Exception($this->idioma->getText("dataIniciarMaiorDataFinal")  );
            }
            
            $sDataInicial = $dataInicial->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            $sDataFinal = $dataFinal->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO);
            
            $where[] = " a.data_cadastro BETWEEN '{$sDataInicial}' AND '{$sDataFinal}' ";
        }
        
        if($tipo == \Utils\Constantes::ENTRADA){
            $where[] = "a.tipo = 'E' ";
        } else if($tipo == \Utils\Constantes::SAIDA){
            $where[] = "a.tipo = 'S' ";
        }

        if ($confirmado != null) {
            $where[] = " a.confirmado = 1 ";
        }
        
        if (!empty($filtro)) {
            $where[] = " ("                    
                    . " (a.valor LIKE '%{$filtro}%') OR "
                    . " (a.id LIKE '%{$filtro}%') OR "  
                    . " (a.taxa LIKE '%{$filtro}%') OR "                     
                    . " (LOWER(tarifa) LIKE LOWER('%{$filtro}%')) "
                    . ") ";
        }
        
        $limitString = "";
        if ($limit != "T") {
            $limitString = " limit {$limit}";
        }
        
        $where = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $query = " SELECT a.* FROM atar_contas a "
                . " INNER JOIN clientes c ON (c.id = a.id_cliente) "                
                . " {$where} "
                . " ORDER BY a.data_cadastro DESC"
                . " {$limitString};";
                
        $result = $this->conexao->adapter->query($query)->execute();
        $lista = Array();
        
        foreach ($result as $dados) {
            $conta = new AtarContas($dados);
            $this->conexao->carregar($conta);
            $lista[] = $conta;
        }
        return $lista;
    }
    
    
    public function verificarTransacao($id) {
        try {
            if (!empty($id)) {
                
                $query = "SELECT * FROM atar_contas WHERE id_transacao = '{$id}' ";

                $dados = $this->conexao->adapter->query($query)->execute();
                if (sizeof($dados) > 0) {
                    $transacao = $dados->current();
                    
                    if($transacao["id_transacao"] === $id){
                        return true;
                    } else {
                       return false; 
                    }
                    return true;
                } else {
                    return false;
                }
            } else {
                throw new \Exception("Transação ID não pode ser nula.");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Falha para encontrar a transação.");
        }
    }
    
    public function saldoAtarDisponivel(Configuracao $configuracao) {
        try {
            
            $saldoAtar = $configuracao->atarSaldo * ($configuracao->atarPorcenSaldoSaque / 100);
            
            if ($configuracao->atarMaxSaque > 0) {
                $valorSaques = 0;
                $dataInicio = new \Utils\Data(date("Y-m-d H:i:s"));
                $dataInicio->subtrair(0, 0, 1);
                $dataFinal = new \Utils\Data(date("Y-m-d H:i:s"));
                
                $saques = $this->filtrar(null, $dataInicio, $dataFinal, "S", true);

                foreach ($saques as $saque){
                    $valorSaques = $valorSaques + $saque->valor;
                }
                
                $saldoAtar = $saldoAtar - $valorSaques;
            }
            
            return $saldoAtar > 0 ? $saldoAtar : 0;
            
        } catch (\Exception $ex) {
            throw new \Exception("Saque Atar não disponível.");
        }
    }

}
