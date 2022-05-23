<?php
namespace Modules\api\Controllers;

class Pdv {
    
    private $sandbox = false;
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }
    
    private function autenticar($params) {
        $token = \Utils\Post::get($params, "token", NULL);
        $tokenRn = new \Models\Modules\Cadastro\TokenApiRn();
        $tokenRn->validar($token);
        
        $cliente = $tokenRn->getClienteByToken($token);
        return $cliente;
    }
    
    private function getPdvByKey($chave, \Models\Modules\Cadastro\Cliente $cliente) {
        $pontoPdvRn = new \Models\Modules\Cadastro\PontoPdvRn();
        $dados = $pontoPdvRn->getByChave($chave, true);
        
        $pontoPdv = $dados["ponto"];
        
        if ($pontoPdv == null) {
            throw new \Exception("Pdv não localizado com a chave informada");
        }
        
        if ($cliente->id != $pontoPdv->estabelecimento->idCliente) {
            throw new \Exception("Você não tem permissão para acessar este estabelecimento");
        }
        
        $this->sandbox =  $dados["homologacao"];
        
        return $pontoPdv;
    }
     
    public function index($params) {
        try {
            $cliente = $this->autenticar($params);
            
            $chave = \Utils\Post::get($params, "chave", null);
            
            $pontoPdv = $this->getPdvByKey($chave, $cliente);
           
            $idInvoice = \Utils\Post::get($params, "invoice", NULL);
            if ($idInvoice > 0) {
                $token = \Utils\Post::get($params, "token", NULL);
                $json["invoice"] = $this->getInvoice($idInvoice, $cliente, $token);
            } else {
                $json["invoice"] = $this->newInvoice($params, $pontoPdv);
                $json["sucesso"] = "Invoice criada com sucesso";
            }
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    
    public function listar($params) {
        try {
            
            $cliente = $this->autenticar($params);
            
            $chave = \Utils\Post::get($params, "chave", null);
            $email = \Utils\Post::get($params, "email", null);
            $status = \Utils\Post::get($params, "status", null);
            $dataInicial = \Utils\Post::getData($params, "dtinicial", null);
            $dataFinal = \Utils\Post::getData($params, "dtfinal", null);
            
            $pontoPdv = $this->getPdvByKey($chave, $cliente);
            
            $invoicePdvRn = new \Models\Modules\Cadastro\InvoicePdvRn();
            $result = $invoicePdvRn->filtrar($chave, $email, $status, $dataInicial, $dataFinal);
            
            $lista = Array();
            foreach ($result as $invoicePdv) {
                $lista[] = $this->getArrayInvoice($invoicePdv);
            }
            
            $json["invoices"] = $lista;
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        print json_encode($json);
    }
    
    private function getInvoice($idInvoice, \Models\Modules\Cadastro\Cliente $cliente, $token) {
        try {
            $invoicePdv = new \Models\Modules\Cadastro\InvoicePdv(Array("id" => $idInvoice));
            $invoicePdvRn = new \Models\Modules\Cadastro\InvoicePdvRn();
            $invoicePdvRn->carregar($invoicePdv, true, true, true);
            
            if ($cliente->id !== $invoicePdv->pontoPdv->estabelecimento->idCliente) {
                throw new \Exception("Você não tem permissão para acessar os dados da invoice selecionada");
            }
            
            //$invoicePdvRn->atualizarStatus($invoicePdv, $token);
            
            return $this->getArrayInvoice($invoicePdv);
            
        } catch (\Exception $ex) {
            throw new \Exception("Invoice não localizada no sistema");
        }
    }
    
    private function newInvoice($params, \Models\Modules\Cadastro\PontoPdv $pontoPdv) {
        try {
            $invoicePdv = new \Models\Modules\Cadastro\InvoicePdv();
            
            $invoicePdv->callback = \Utils\Post::get($params, "callback", null);
            $invoicePdv->celular = \Utils\Post::get($params, "celular", null);
            $invoicePdv->email = \Utils\Post::get($params, "email", null);
            $invoicePdv->idPontoPdv = $pontoPdv->id;
            $invoicePdv->valorBrl = \Utils\Post::getNumeric($params, "valor", null);
            $invoicePdv->cotacaoBtcBrl = \Utils\Post::getNumeric($params, "cotacao", null);
            //$moeda = \Utils\Post::get($params, "moeda", null);
            $moeda = "btc";
            $exchange = \Utils\Post::get($params, "exchange", null);
            $invoicePdvRn = new \Models\Modules\Cadastro\InvoicePdvRn();
            $invoicePdvRn->novaInvoice($invoicePdv, $pontoPdv, $moeda, $this->sandbox, $exchange);
            
            return $this->getArrayInvoice($invoicePdv);
        } catch (\Exception $ex) {
            throw new \Exception(\Utils\Excecao::mensagem($ex), $ex->getCode());
        }
    }
    
    
    private function getArrayInvoice(\Models\Modules\Cadastro\InvoicePdv $invoicePdv) {
        if ($invoicePdv != null) {
            return Array(
                    "invoice" => $invoicePdv->id,
                    "callback" => $invoicePdv->callback,
                    "celular" => $invoicePdv->celular,
                    "email" => $invoicePdv->email,
                    "endereco" => $invoicePdv->enderecoCarteira,
                    "cotacao" => $invoicePdv->cotacaoMoedaBtc,
                    "datacriacao" => ($invoicePdv->dataCriacao != null ? $invoicePdv->dataCriacao->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : null),
                    "datadeposito" => ($invoicePdv->dataDeposito != null ? $invoicePdv->dataDeposito->formatar(\Utils\Data::FORMATO_PT_BR_TIMESTAMP_LONGO) : null),
                    "moeda" => $invoicePdv->moeda->simbolo,
                    "pdv" => $invoicePdv->pontoPdv->descricao,
                    "estabelecimento" => $invoicePdv->pontoPdv->estabelecimento->nome,
                    "status" => $invoicePdv->status,
                    "valorbrl" => number_format($invoicePdv->valorBrl, 2, ".", ""),
                    "valorbtc" => number_format($invoicePdv->valorBtc, $invoicePdv->moeda->casasDecimais, ".", ""),
                    "saldoRecebido" => number_format($invoicePdv->saldoRecebido, $invoicePdv->moeda->casasDecimais, ".", "")
                );
        } else {
            return null;
        }
    }
    
    
    
    public function pay($params) {
        
        try {
            
            $cliente = $this->autenticar($params);
            $chave = \Utils\Post::get($params, "chave", NULL);
            
            $volume = \Utils\Post::getNumeric($params, "volume", 0);
            $carteira = \Utils\Post::get($params, "carteira", 1);
            
            $this->getPdvByKey($chave, $cliente);
            
            if (!$this->sandbox) {
                throw new \Exception("Método disponível somente para invoices Sandbox");
            }
            
            $invoicePdvRn = new \Models\Modules\Cadastro\InvoicePdvRn();
            $invoicePdvRn->adicionarFundosSandbox($volume, $carteira);
            
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
            $json["mensagem"] = \Utils\Excecao::mensagem($ex);
        }
        
        print json_encode($json);
    }
}
?>
