<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;

class InvestimentoContratosRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
     private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new InvestimentoContratos());
        } else {
            $this->conexao = new GenericModel($adapter, new InvestimentoContratos());
        }
    }
    
    public function salvar(InvestimentoContratos &$investimentoContrato) {
        try {
            $this->conexao->adapter->iniciar();
            
            if (!($investimentoContrato->idUsuario > 0)) {
                throw new \Exception("Usuário inválido.");
            }
            
            if (!($investimentoContrato->idMoeda > 0)) {
                throw new \Exception($this->idioma->getText("Moeda inválida."));
            }
            
            if (empty($investimentoContrato->lucroNc)) {
                throw new \Exception($this->idioma->getText("Taxa de lucro NC inválida."));
            }
            
            if (empty($investimentoContrato->lucroPoupanca)) {
                throw new \Exception($this->idioma->getText("Taxa de lucro Poupança inválida."));
            }
            
            if (empty($investimentoContrato->lucroTesouro)) {
                throw new \Exception($this->idioma->getText("Taxa de lucro Tesouro inválida."));
            }
            
            if (empty($investimentoContrato->lucroImovel)) {
                throw new \Exception($this->idioma->getText("Taxa de lucro Ímovel inválida."));
            }

            if (empty($investimentoContrato->descricao)) {
                throw new \Exception($this->idioma->getText("Descrição deve ser informada."));
            }

            if (empty($investimentoContrato->ativo)) {
                throw new \Exception($this->idioma->getText("Ativo deve ser informado."));
            }

            if (empty($investimentoContrato->dataCriacao)) {
                throw new \Exception($this->idioma->getText("Data Criação deve ser informada."));
            }
            
            if (empty($investimentoContrato->tempoMeses)) {
                throw new \Exception($this->idioma->getText("Tempo de contrato deve ser informado."));
            }
            
            $this->conexao->salvar($investimentoContrato);
            
            $this->conexao->adapter->finalizar();
        } catch(\Exception $e) {
            $this->conexao->adapter->cancelar();
            throw new \Exception(\Utils\Excecao::mensagem($e));
        }
    }
}

?>