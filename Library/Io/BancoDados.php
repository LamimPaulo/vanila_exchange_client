<?php

namespace Io;

use Utils\Data;
use Zend\Db\Adapter\Adapter;


/**
 * Classe de Banco de dados
 */
class BancoDados extends Adapter
{

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
     * @param $dadosConexao : Dados da conexão
     * @param timezone: Timezone da conexão
     */
    function __construct($bd = null)
    {


        $this->timezone = !empty( getenv("EnvTimeZone") ) ? getenv("EnvTimeZone") : DBZONE;
        $this->url = !empty(getenv("EnvBdUrl")) ? getenv("EnvBdUrl") :  BDHOST;
        $this->user = !empty(getenv("EnvBdUser")) ? getenv("EnvBdUser") :  BDUSER;
        $this->pass = !empty(getenv("EnvBdPass")) ? getenv("EnvBdPass") :  BDPASS;
        $this->name = !empty(getenv("EnvBdName")) ? getenv("EnvBdName") :   BDNAME;

        /*        $this->timezone = DBZONE;
                $this->url = BDHOST;
                $this->user = BDUSER;
                $this->pass = BDPASS;
                $this->name = BDNAME;*/


        $pdoParams = array(
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        );

        $this->driver = "PDO_MYSQL";
        $this->username = $this->user;
        $this->password = $this->pass;
        $this->dsn = "mysql:host={$this->url};dbname={$this->name};charset=utf8;";


        parent::__construct(array("driver" => $this->driver, "username" => $this->username, "password" => $this->password, "dsn" => $this->dsn, "driver_oprions" => $pdoParams));
    }


    public function onTransaction()
    {
        return $this->transactionStarted;
    }

    /**
     * sequencia() Retorna o próximo valor da sequência
     *
     * @param nomeSequencia: Nome da sequência do postgres
     * @return Integer: Valor da sequência passada
     */
    function sequencia($nomeSequencia)
    {
        $oResultSet = $this->query("SELECT NEXTVAL('{$nomeSequencia}') AS valor_sequencia")->execute();
        foreach ($oResultSet as $sequencia) {
            return $sequencia ['valor_sequencia'];
        }
    }

    /**
     * iniciar() Inicia uma transação
     */
    function iniciar()
    {
        $this->query("BEGIN;")->execute();
        $this->transactionStarted = true;
    }

    /**
     * finalizar() Finaliza e confirma uma transação
     */
    function finalizar()
    {
        $this->query("COMMIT;")->execute();
        $this->transactionStarted = false;
    }

    /**
     * cancelar() Cancela e desfaz uma transação
     */
    function cancelar()
    {
        $this->query("ROLLBACK;")->execute();
        $this->transactionStarted = false;
    }

    /**
     * dataHora() Busca e retorna a data e hora atual do banco de dados
     *
     * @return String: Valor da hora passada
     */
    function dataHoraAtual()
    {
        $oResultSet = $this->query("SELECT NOW() AS data_hora_atual")->execute();
        foreach ($oResultSet as $dataHoraAtual) {
            return new Data(substr($dataHoraAtual ['data_hora_atual'], 0, 19));
        }
    }

    public function getTimeZone()
    {
        return $this->timezone;
    }

}

?>
