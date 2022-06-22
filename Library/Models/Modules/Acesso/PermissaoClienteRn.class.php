<?php
namespace Models\Modules\Acesso;
use \Models\Modules\Model\GenericModel;

class PermissaoClienteRn {
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;

    public function __construct(\Io\BancoDados $adapter = null) {
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new PermissaoCliente());
        } else {
            $this->conexao = new GenericModel($adapter, new PermissaoCliente());
        }
    }

    public function salvar(\Models\Modules\Cadastro\Cliente $cliente, $permissoes) {
        $this->conexao->delete("id_cliente = {$cliente->id}");
        if (sizeof($permissoes) > 0) {
            foreach ($permissoes as $idRotinaHasAcao) {
                $permissaoCliente = new PermissaoCliente();
                $permissaoCliente->idRotinaHasAcao = $idRotinaHasAcao;
                $permissaoCliente->idCliente = $cliente->id;

                $this->conexao->salvar($permissaoCliente);
            }
        }
    }

    public function addToCliente($idRotinaHasAcao, $idCliente) {

        $result = $this->conexao->select(Array("id_cliente" => $idCliente, "id_rotina_has_acao" => $idRotinaHasAcao));
        if (!sizeof($result) > 0) {

            $permissaoCliente = new PermissaoCliente();
            $permissaoCliente->idCliente = $idCliente;
            $permissaoCliente->idRotinaHasAcao = $idRotinaHasAcao;
            $permissaoCliente->id = 0;

            $this->conexao->salvar($permissaoCliente);
        }
    }

    public function addToAllClient($idRotinaHasAcao) {

        $clienteRn = new \Models\Modules\Cadastro\ClienteRn();
        $clientes = $clienteRn->conexao->listar(null, "id", NULL, NULL);
        foreach ($clientes as $cliente) {
            $this->addToCliente($idRotinaHasAcao, $cliente->id);
        }
    }

    public static function getPermissoesCliente(\Models\Modules\Cadastro\Cliente $cliente) {
        $permissaoClienteRn = new PermissaoClienteRn();
        $query = "
                SELECT
                m.id AS id_modulo,
                m.nome AS nome_modulo,
                m.codigo AS codigo_modulo,
                m.ordem AS ordem_modulo,
                m.url AS url_modulo,
                m.novidade AS novidade_modulo,
                m.contem_rotinas,

                r.id AS id_rotina,
                r.nome AS nome_rotina,
                r.icone AS icone_rotina,
                r.ordem AS ordem_rotina,
                r.codigo AS codigo_rotina,
                r.url AS url_rotina,
                r.novidade As novidade_rotina

                FROM modulos m
                INNER JOIN modulos_has_acoes mha ON (mha.id_modulo = m.id)
                INNER JOIN permissoes_modulos_clientes pmc ON (mha.id = pmc.id_modulo_has_acao)

                LEFT JOIN rotinas r ON (
                r.id_modulo = m.id AND r.id IN
                        (
                        SELECT r2.id FROM rotinas r2
                        INNER JOIN rotinas_has_acoes rha ON (r2.id = rha.id_rotina)
                        INNER JOIN permissoes_clientes pc ON (rha.id = pc.id_rotina_has_acao)
                        WHERE r2.ativo  > 0 AND r2.tipo IN ('C', 'UC') AND rha.id_acao = 1  AND rha.tipo IN ('C', 'UC')  AND pc.id_cliente = {$cliente->id}
                        )
                )

                WHERE
                m.visivel_cliente > 0 AND
                m.ativo > 0 AND
                mha.id_acao = 1 AND
                m.tipo IN ('C', 'UC') AND
                mha.tipo IN ('C', 'UC') AND
                pmc.id_cliente = {$cliente->id}
                ORDER BY m.ordem , r.ordem
                ";

        $dados = $permissaoClienteRn->conexao->adapter->query($query)->execute();
        $permissoes = Array();
        foreach ($dados as $d) {
            if ($d["contem_rotinas"] < 1 || !empty($d["nome_rotina"])) {

                if (!isset($permissoes[$d["codigo_modulo"]])) {
                    $permissoes[$d["codigo_modulo"]] = Array(
                        "codigo" => $d["codigo_modulo"],
                        "id" => $d["id_modulo"],
                        "nome" => $d["nome_modulo"],
                        "ordem" => $d["ordem_modulo"],
                        "url" => $d["url_modulo"],
                        "novidade" => $d["novidade_modulo"],
                        "rotinas" => Array()
                    );
                }

                if ($d["contem_rotinas"] > 0) {
                    $permissoes[$d["codigo_modulo"]]["rotinas"][] = Array(
                        "codigo"=> $d["codigo_rotina"], 
                        "id" => $d["id_rotina"],
                        "nome" => $d["nome_rotina"],
                        "icone" => $d["icone_rotina"],
                        "ordem" => $d["ordem_rotina"],
                        "url" => $d["url_rotina"],
                        "novidade" => $d["novidade_rotina"],
                    ) ;
                }
            }
        }
        return $permissoes;
    }
}

?>