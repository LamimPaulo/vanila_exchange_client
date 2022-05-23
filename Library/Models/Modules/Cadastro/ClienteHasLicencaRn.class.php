<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * 
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class ClienteHasLicencaRn {
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    private $idioma = null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new ClienteHasLicenca());
        } else {
            $this->conexao = new GenericModel($adapter, new ClienteHasLicenca());
        }
    }
    
    public function salvar(ClienteHasLicenca &$clienteHasLicenca) {
        
        $configuracao = new Configuracao(Array("id" => 1));
        $configuracaoRn = new ConfiguracaoRn();
        $configuracaoRn->conexao->carregar($configuracao);
        
        if ($configuracao->statusUpgradePerfil < 1) {
            throw new \Exception($this->idioma->getText("solicitacoesUpgradelicenca"));
        }
        
        if ($clienteHasLicenca->id > 0) {
            $aux = new ClienteHasLicenca( Array("id" => $clienteHasLicenca->id) );
            $this->conexao->carregar($aux);
            
            $clienteHasLicenca->dataAdesao = $aux->dataAdesao;
            $clienteHasLicenca->dataAprovacao = $aux->dataAprovacao;
            $clienteHasLicenca->dataNegacao = $aux->dataVencimento;
            $clienteHasLicenca->motivoNegacao = $aux->motivoNegacao;
            $clienteHasLicenca->idUsuario = $aux->idUsuario;
            $clienteHasLicenca->preco = $aux->preco;
            $clienteHasLicenca->situacao = $aux->situacao;
            $clienteHasLicenca->bloqueada = $aux->bloqueada;
        } else {
            $clienteHasLicenca->dataAdesao = new \Utils\Data(date("d/m/Y H:i:s"));
            $clienteHasLicenca->dataAprovacao = null;
            $clienteHasLicenca->dataNegacao = null;
            $clienteHasLicenca->motivoNegacao = "";
            $clienteHasLicenca->idUsuario = null;
            $clienteHasLicenca->situacao = \Utils\Constantes::LICENCA_PENDENTE;
            $clienteHasLicenca->bloqueada = 0;
            
            $licencaSoftware = new LicencaSoftware(Array("id" => $clienteHasLicenca->idLicencaSoftware));
            $licencaSoftwareRn = new LicencaSoftwareRn();
            try {
                $licencaSoftwareRn->conexao->carregar($licencaSoftware);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("licencainvalida"));
            }
            
            $clienteHasLicenca->preco = $licencaSoftware->preco;
        }
        
        if ($clienteHasLicenca->idCliente <= 0) {
            throw new \Exception($this->idioma->getText("identiClienteInvalida"));
        }
        
        
        unset($clienteHasLicenca->usuario);
        unset($clienteHasLicenca->cliente);
        unset($clienteHasLicenca->licencaSoftware);
        
        $this->conexao->salvar($clienteHasLicenca);
    }
    
    public function carregarLicencaCliente(Cliente $cliente) {
        
        $data = date("Y-m-d H:i:s");
        $s = \Utils\Constantes::LICENCA_APROVADO;
        $result = $this->conexao->listar(" id_cliente = {$cliente->id} AND data_vencimento >= '{$data}' AND situacao = '{$s}'", "data_vencimento DESC", null, null);
        
        if (sizeof($result) > 0) {
            $clienteHasLicenca = $result->current();
            $this->carregar($clienteHasLicenca, false, false, true);
            return $clienteHasLicenca;
        } 
        
        return null;
    }
    
    public function carregarSolicitacaoLicencaCliente(Cliente $cliente) {
        $s = \Utils\Constantes::LICENCA_PENDENTE;
        $result = $this->conexao->listar(" id_cliente = {$cliente->id}  AND situacao = '{$s}'", "id DESC", null, null);
        
        if (sizeof($result) > 0) {
            $clienteHasLicenca = $result->current();
            $this->carregar($clienteHasLicenca, false, false, true);
            return $clienteHasLicenca;
        } 
        
        return null;
    }
    
    
    public static function carregarByCliente(Cliente $cliente) {
        $clienteHasLicencaRn = new ClienteHasLicencaRn();
        return $clienteHasLicencaRn->carregarLicencaCliente($cliente);
    }
    
    public function carregar(ClienteHasLicenca &$clienteHasLicenca, $carregar = true, $carregarCliente = true, $carregarLicencaSoftware = true, $usuario = true) {
        
        if ($carregar) {
            $this->conexao->carregar($clienteHasLicenca);
        }
        
        if ($carregarCliente && $clienteHasLicenca->idCliente > 0) {
            $clienteHasLicenca->cliente = new Cliente(Array("id" => $clienteHasLicenca->idCliente));
            $clienteRn = new ClienteRn();
            $clienteRn->conexao->carregar($clienteHasLicenca->cliente);
        }
        
        if ($carregarLicencaSoftware && $clienteHasLicenca->idLicencaSoftware > 0) {
            $clienteHasLicenca->licencaSoftware = new LicencaSoftware(Array("id" => $clienteHasLicenca->idLicencaSoftware));
            $licencaSoftwareRn = new LicencaSoftwareRn();
            $licencaSoftwareRn->conexao->carregar($clienteHasLicenca->licencaSoftware);
        }
        
        if ($usuario && $clienteHasLicenca->idUsuario) {
            $clienteHasLicenca->usuario = new Usuario(Array("id" => $clienteHasLicenca->idUsuario));
            $usuarioRn = new UsuarioRn();
            $usuarioRn->conexao->carregar($clienteHasLicenca->usuario);
        }
        
    }
    
    
    public function listar($where = null, $order = null, $offset = null, $limit = null, $carregarCliente = true, $carregarLicencaSoftware = true, $usuario = true) {
        $result = $this->conexao->listar($where, $order, $offset, $limit);
        $lista = Array();
        
        foreach ($result as $dados) {
            $clienteHasLicenca = new ClienteHasLicenca($dados);
            $this->carregar($clienteHasLicenca, false, $carregarCliente, $carregarLicencaSoftware, $usuario);
            $lista[] = $clienteHasLicenca;
        }
        
        return $lista;
    }
    
    public function aprovar(ClienteHasLicenca &$clienteHasLicenca) {
        
        try {
            try {
                $this->conexao->carregar($clienteHasLicenca);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("licencainvalida"));
            }

            $usuario = \Utils\Geral::getLogado();

            if (!($usuario instanceof Usuario)) {
                throw new \Exception($this->idioma->getText("voceNaoTemPermirealOper"));
            }

            $licencaSoftware = new LicencaSoftware(Array("id" => $clienteHasLicenca->idLicencaSoftware));
            $licencaSoftwareRn = new LicencaSoftwareRn();
            try {
                $licencaSoftwareRn->conexao->carregar($licencaSoftware);
            } catch (\Exception $ex) {
                throw new \Exception($this->idioma->getText("licencainvalida"));
            }

            $clienteHasLicenca->dataAprovacao = new \Utils\Data(date("d/m/Y H:i:s"));
            $clienteHasLicenca->idUsuario = $usuario->id;
            $clienteHasLicenca->situacao = \Utils\Constantes::LICENCA_APROVADO;
            $clienteHasLicenca->dataVencimento = new \Utils\Data(date("d/m/Y H:i:s"));
            $clienteHasLicenca->dataVencimento->somar(0, $licencaSoftware->mesesDuracao);

            $this->conexao->update(
                    Array(
                        "data_aprovacao" => $clienteHasLicenca->dataAprovacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                        "id_usuario" => $clienteHasLicenca->idUsuario,
                        "situacao" => $clienteHasLicenca->situacao,
                        "data_vencimento" => $clienteHasLicenca->dataVencimento->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO)
                    ),
                    Array(
                        "id" => $clienteHasLicenca->id
                    )
                );


            // zero todas as outras solicitacoes pendentes
            $queryPendentes = "UPDATE " . $clienteHasLicenca->getTable() . " "
                    . " SET "
                    . " situacao = '". \Utils\Constantes::LICENCA_NEGADA ."',"
                    . " data_negacao = '".date("Y-m-d H:i:s")."',"
                    . " id_usuario = {$usuario->id} "
                    . " WHERE situacao = '".\Utils\Constantes::LICENCA_PENDENTE."' AND "
                    . " id_cliente = {$clienteHasLicenca->id} AND "
                    . " id != {$clienteHasLicenca->id}  ";

            $this->conexao->executeSql($queryPendentes);


            // zero todas as outras solicitacoes aprovadas
            $queryAprovadas = "UPDATE " . $clienteHasLicenca->getTable() . " "
                    . " SET "
                    . " data_vencimento = '".date("Y-m-d H:i:s")."'"
                    . " WHERE situacao = '".\Utils\Constantes::LICENCA_APROVADO."' AND "
                    . " id_cliente = {$clienteHasLicenca->id} AND "
                    . " id != {$clienteHasLicenca->id}  ";

            $this->conexao->executeSql($queryAprovadas);
            
            $this->carregar($clienteHasLicenca, false, true, true);
            
            try {
                \Email\UpgradePerfilAprovado::send($clienteHasLicenca);
            } catch (\Exception $ex) {

            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
        
    }
    
    
    
    public function negar(ClienteHasLicenca &$clienteHasLicenca, $motivo = "") {
        try {
            $this->conexao->carregar($clienteHasLicenca);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("licencainvalida"));
        }
        
        $usuario = \Utils\Geral::getLogado();
        
        if (!($usuario instanceof Usuario)) {
            throw new \Exception($this->idioma->getText("voceNaoTemPermirealOper"));
        }
        
        
        $clienteHasLicenca->dataNegacao = new \Utils\Data(date("d/m/Y H:i:s"));
        $clienteHasLicenca->idUsuario = $usuario->id;
        $clienteHasLicenca->situacao = \Utils\Constantes::LICENCA_NEGADA;
        $clienteHasLicenca->motivoNegacao = $motivo;
        
        $this->conexao->update(
                Array(
                    "data_negacao" => $clienteHasLicenca->dataNegacao->formatar(\Utils\Data::FORMATO_ISO_TIMESTAMP_LONGO),
                    "id_usuario" => $clienteHasLicenca->idUsuario,
                    "situacao" => $clienteHasLicenca->situacao,
                    "motivo_negacao" => $clienteHasLicenca->motivoNegacao
                ),
                Array(
                    "id" => $clienteHasLicenca->id
                )
            );
        
        $this->carregar($clienteHasLicenca, true, true, true);
        
        try {
            \Email\UpgradePerfilNegado::send($clienteHasLicenca);
        } catch (Exception $ex) {

        }
    }
    
    
    
    public function filtrarSolicitacoes($filtro = null) {
        
        $where = Array();
        
        $s = \Utils\Constantes::LICENCA_PENDENTE;
        $where[] = " chl.situacao = '{$s}' ";

        if (!empty($filtro)) {
            $where[] = " ("
                    . " (LOWER(c.nome) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.email) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.celular) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.telefone) LIKE LOWER('%{$filtro}%')) OR "
                    . " (LOWER(c.documento) LIKE LOWER('%{$filtro}%')) "
                    . ") ";
        }

        $whereString = (sizeof($where) > 0  ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT chl.* "
                . " FROM clientes_has_licencas chl  "
                . " INNER JOIN clientes c ON (chl.id_cliente = c.id) "
                . " {$whereString} "
                . " ORDER BY chl.data_adesao DESC, c.nome ";
        
        $result = $this->conexao->executeSql($query);
        
        $lista = Array();
        foreach ($result as $dados) {
            $clienteHasLicenca = new ClienteHasLicenca($dados);
            $this->carregar($clienteHasLicenca, false, true, true);
            $lista[] = $clienteHasLicenca;
        }
        return $lista;
    }
    
    
    public function getQuantidadeFranquiasPorStatus() {
        
        $data = date("Y-m-d");
        $aprovada = \Utils\Constantes::LICENCA_APROVADO;
        
        $queryAprovadas = " SELECT COUNT(*) as qtd"
                . " FROM clientes_has_licencas"
                . " WHERE data_vencimento >= '{$data} 23:59:59' "
                . " AND  situacao = '{$aprovada}';";
                
        $pendente = \Utils\Constantes::LICENCA_PENDENTE;
        
        $queryPendentes = " SELECT COUNT(*) as qtd "
                . " FROM clientes_has_licencas"
                . " WHERE situacao = '{$pendente}';";
        
                
                
        
        $resultAprovadas = $this->conexao->executeSql($queryAprovadas);
        $resultPendentes = $this->conexao->executeSql($queryPendentes);
        
        
        $aprovadas = 0;
        if (sizeof($resultAprovadas) > 0) {
            foreach ($resultAprovadas as $dados) {
                $aprovadas = $dados["qtd"];
            }
        }
          
        
        $pendentes = 0;
        if (sizeof($resultPendentes) > 0) {
            foreach ($resultPendentes as $dados) {
                $pendentes = $dados["qtd"];
            }
        }
        
        return Array("aprovadas" => $aprovadas, "solicitacoes" => $pendentes);
        
    }
    
    
    public function alterarStatusLicenca(ClienteHasLicenca $clienteHasLicenca) {
        try {
            $this->conexao->carregar($clienteHasLicenca);
        } catch (\Exception $ex) {
            throw new \Exception($this->idioma->getText("licencaNaoEncontrada"));
        }
        
        $clienteHasLicenca->bloqueada = ($clienteHasLicenca->bloqueada > 0 ? 0 : 1);
        $this->conexao->update(Array("bloqueada" => $clienteHasLicenca->bloqueada), Array("id" => $clienteHasLicenca->id));
        
    }
}

?>