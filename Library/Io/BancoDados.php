<?php

namespace Io;

use Utils\Data;
use Zend\Db\Adapter\Adapter;


/**
 * Classe de Banco de dados
 */
class BancoDados extends Adapter {

    protected $driver;
    protected $username;
    protected $password;
    protected $dsn;


    protected $transactionStarted = false;

    /**
     * Armazena o valor do timezone da conexão
     */
    private $timezone = 'America/Sao_Paulo';

    /**
     * Construtor da classe
     *
     * @param $dadosConexao: Dados da conexão
     * @param timezone: Timezone da conexão
     */
    function __construct($bd = null) {

        $this->timezone = $_ENV["EnvTimeZone"];
        $this->url = $_ENV["EnvBdUrl"];
        $this->user = $_ENV["EnvBdUser"];
        $this->pass = $_ENV["EnvBdPass"];
        $this->name = $_ENV["EnvBdName"];

        $urlBdBook = $_ENV["EnvBdUrlBook"];
        $urlBdGrafico = $_ENV["EnvBdUrlGrafico"];

        $pdoParams = array(
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );



        switch ($bd){
            case null:
                $this->driver = "PDO_MYSQL";
                $this->username = $this->user;
                $this->password = $this->pass;
                $this->dsn = "mysql:host={$this->url};dbname={$this->name};charset=utf8;";
                break;
            case BDBOOK:
                //Banco de Dados Somente Leitura - Book
                $this->driver = "PDO_MYSQL";
                $this->username = $this->user;
                $this->password = $this->pass;
                $this->dsn = "mysql:host={$urlBdBook};dbname={$this->name};charset=utf8;";
                break;
            case BDGRAFICO:
                //Banco de Dados Somente Leitura - Grafico
                $this->driver = "PDO_MYSQL";
                $this->username = $this->user;
                $this->password = $this->pass;
                $this->dsn = "mysql:host={$urlBdGrafico};dbname={$this->name};charset=utf8;";
                break;
            default:
                $this->driver = "PDO_MYSQL";
                $this->username = $this->user;
                $this->password = $this->pass;
                $this->dsn = "mysql:host={$this->url};dbname={$this->name};charset=utf8;";
                break;
        }

        parent::__construct(Array("driver" => $this->driver, "username" => $this->username, "password" => $this->password, "dsn" => $this->dsn, "driver_oprions" => $pdoParams));
    }




    public function onTransaction() {
        return $this->transactionStarted;
    }

    /**
     * sequencia() Retorna o próximo valor da sequência
     *
     * @param  nomeSequencia: Nome da sequência do postgres
     * @return Integer: Valor da sequência passada
     */
    function sequencia($nomeSequencia) {
        $oResultSet = $this->query("SELECT NEXTVAL('{$nomeSequencia}') AS valor_sequencia")->execute();
        foreach ($oResultSet as $sequencia) {
            return $sequencia ['valor_sequencia'];
        }
    }

    /**
     * iniciar() Inicia uma transação
     */
    function iniciar() {
        $this->query("BEGIN;")->execute();
        $this->transactionStarted = true;
    }

    /**
     * finalizar() Finaliza e confirma uma transação
     */
    function finalizar() {
        $this->query("COMMIT;")->execute();
        $this->transactionStarted = false;
    }

    /**
     * cancelar() Cancela e desfaz uma transação
     */
    function cancelar() {
        $this->query("ROLLBACK;")->execute();
        $this->transactionStarted = false;
    }

    /**
     * dataHora() Busca e retorna a data e hora atual do banco de dados
     *
     * @return String: Valor da hora passada
     */
    function dataHoraAtual() {
        $oResultSet = $this->query("SELECT NOW() AS data_hora_atual")->execute();
        foreach ($oResultSet as $dataHoraAtual) {
            return new Data(substr($dataHoraAtual ['data_hora_atual'], 0, 19));
        }
    }

    public function getTimeZone() {
        return $this->timezone;
    }

}

?>
