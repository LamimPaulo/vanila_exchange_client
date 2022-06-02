<?php

namespace Utils;

class ValidarLimiteOperacional {
    
    public static function validar(\Models\Modules\Cadastro\Cliente $cliente, \Models\Modules\Cadastro\Moeda $moeda, $tipo = Constantes::SAIDA, $valor = 0, $validar = true) {
        
        $limiteCliente = null;
        $limitePadrao = null;
        $limite = null;
        $total = 0;
        $totalSaque = 0;
        $totalDeposito = 0;
        $queryMoedaSaque = "";
        $dataInicial = new Data(date("Y-m-d 08:00:00"));
        $dataInicial->subtrair(0, 1);
        $dataFinal = new Data(date("Y-m-t 08:00:00"));
        
        if($cliente->documentoVerificado != 1){
            $cliente->documentoVerificado = 0;
        }
        
        
        $limiteOperacionalRn = new \Models\Modules\Cadastro\LimiteOperacionalRn();
        
        if(!empty($moeda->idMoedaSaque) && $moeda->idMoedaSaque > 0){
            $queryMoedaSaque = " OR id_moeda = {$moeda->idMoedaSaque} AND ativo = 1 ";
        }
        $limites = $limiteOperacionalRn->conexao->listar(" id_moeda = {$moeda->id} AND ativo = 1 {$queryMoedaSaque} ");
       
        //Buscar o limite
        if(sizeof($limites) > 0) {
            
            //Identificar limite do cliente
            foreach ($limites as $limite){
                
                //Separar o limite do cliente
                if(!empty($limite->idCliente) && $limite->idCliente == $cliente->id){
                    $limiteCliente = $limite;
                } else {
                    if($limite->fase == $cliente->documentoVerificado && empty($limite->idCliente)){
                        $limitePadrao = $limite;
                    }
                }
            }
        }
        
        if(!empty($limiteCliente)){
           $limite = $limiteCliente;
        } else {
           $limite = $limitePadrao;
        }
        
        if(empty($limite)){
            return null;
        }
        
        //REAL
        if($moeda->id == 1){
            $contaCorrenteReaisRn = new \Models\Modules\Cadastro\ContaCorrenteReaisRn();
            
            $lista = $contaCorrenteReaisRn->filtrar($cliente->id, $dataInicial, $dataFinal, "T", null, "T", "T", false, false);
           
            if (sizeof($lista["lista"]) > 0) {
                foreach ($lista["lista"] as $op) {
                    
                    if ($op->tipo == Constantes::SAQUE) {
                        $totalSaque += $op->valor;
                    } else {
                        $totalDeposito += $op->valor;
                    }
                }
            }
            
            if ($tipo == Constantes::SAQUE) {
                $total = $totalSaque;
            } else {
                $total = $totalDeposito;
            }

            if($validar){
                self::verificar($limite, $tipo, $total, 0, $valor);
            } else {
                return self::dados($limite, $tipo, $total, 0, $valor);
            }
            
        //CRIPTOMOEDA         
        } else {
            
            $contaCorrenteBtcRn = new \Models\Modules\Cadastro\ContaCorrenteBtcRn();

            $lista = $contaCorrenteBtcRn->filtrar($cliente->id, $dataInicial, $dataFinal, "T", null, "S", $moeda->id, "T", false, false);
                        
            if (sizeof($lista["lista"]) > 0) {
                foreach ($lista["lista"] as $op) {
                    
                    if ($op->tipo == Constantes::SAQUE) {
                        if($op->autorizada != 2){
                            $totalSaque += ($op->valor - $op->valorTaxa);
                        }
                    } else {
                        $totalDeposito += ($op->valor - $op->valorTaxa);
                    }
                }
            }
            
            if (!empty($moeda->idMoedaSaque) && $moeda->idMoedaSaque > 0) {
                
                $lista = $contaCorrenteBtcRn->filtrar($cliente->id, $dataInicial, $dataFinal, "T", null, "S", $moeda->idMoedaSaque, "T", false, false);

                if (sizeof($lista["lista"]) > 0) {
                    foreach ($lista["lista"] as $op) {

                        if ($op->tipo == Constantes::SAQUE) {
                            if($op->autorizada != 2){
                                $totalSaque += ($op->valor - $op->valorTaxa);
                            }
                        } else {
                            $totalDeposito += ($op->valor - $op->valorTaxa);
                        }
                    }
                }
            }

            if ($tipo == Constantes::SAQUE) {
                $total = $totalSaque;
            } else {
                $total = $totalDeposito;
            }

            if($validar){
                self::verificar($limite, $tipo, $total, 0, $valor);
            } else {
                return self::dados($limite, $tipo, $total, 0, $valor);
            }
        }
    }
    
    
    private static function verificar($limite, $tipo, $totalMes, $totalDiario, $valor) {

        if (Constantes::SAQUE == $tipo) {

            //Verificar limite disponível
            $verificacaoMensal1 = $limite->saqueMensal - $totalMes;

            if ($verificacaoMensal1 < 0) {
                throw new \Exception("Limite mensal para saque não disponível. ");
            }

            $verificacaoMensal2 = $verificacaoMensal1 - $valor;
            
            if ($verificacaoMensal2 < 0) {
                throw new \Exception("Limite mensal para saque não disponível. ");
            }

//            $verificacaoDiario1 = $limite->saqueDiario - $totalDiario;
//
//            if ($verificacaoDiario1 < 0) {
//                throw new \Exception("Limite diário para saque não disponível.");
//            }
//
//            $verificacaoDiario2 = $verificacaoDiario1 - $valor;
//
//            if ($verificacaoDiario2 < 0) {
//                throw new \Exception("Limite diário para saque não disponível.");
//            }
            
        } else {
            
            //Verificar limite disponível
            $verificacaoMensal1 = $limite->depositoMensal - $totalMes;

            if ($verificacaoMensal1 < 0) {
                throw new \Exception("Limite mensal para depósito não disponível.");
            }

            $verificacaoMensal2 = $verificacaoMensal1 - $valor;

            if ($verificacaoMensal2 < 0) {
                throw new \Exception("Limite mensal para depósito não disponível.");
            }

//            $verificacaoDiario1 = $limite->depositoDiario - $totalDiario;
//
//            if ($verificacaoDiario1 <= 0) {
//                throw new \Exception("Limite diário para saque não disponível.");
//            }
//
//            $verificacaoDiario2 = $verificacaoDiario1 - $valor;
//
//            if ($verificacaoDiario2 <= 0) {
//                throw new \Exception("Limite diário para saque não disponível.");
//            }
        }
    }
    

    private static function dados($limite, $tipo, $totalMes, $totalDiario) {

        $dados = Array();
        
        if (Constantes::SAQUE == $tipo) {
            //Verificar limite disponível mensal
            $verificacao1 = $limite->saqueMensal - $totalMes;

            $dados["limiteMensal"] = $limite->saqueMensal;
            
            if ($verificacao1 <= 0) {
                $dados["limiteDisponivelMensal"] = 0;
            } else {
                $dados["limiteDisponivelMensal"] = $verificacao1;
            }

//            //Verificar limite disponível diario
//            $verificacao2 = $limite->saqueDiario - $totalDiario;
//
//            if ($verificacao2 <= 0) {
//                $dados["limiteDisponivelDiario"] = 0;
//            } else {
//                $dados["limiteDisponivelDiario"] = $verificacao2;
//            }
            
        } else {
            
            //Verificar limite disponível mensal
            $verificacao1 = $limite->depositoMensal - $totalMes;
            
            $dados["limiteMensal"] = $limite->depositoMensal;

            if ($verificacao1 <= 0) {
                $dados["limiteDisponivelMensal"] = 0;
            } else {
                $dados["limiteDisponivelMensal"] = $verificacao1;
            }

            //Verificar limite disponível diario
//            $verificacao2 = $limite->depositoDiario - $totalDiario;
//
//            if ($verificacao2 <= 0) {
//                $dados["limiteDisponivelDiario"] = 0;
//            } else {
//                $dados["limiteDisponivelDiario"] = $verificacao2;
//            }
        }
        
        return $dados;
    }

}
