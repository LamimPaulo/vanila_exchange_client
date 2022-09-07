<?php

namespace Models\Modules\Model;

use Io\BancoDados;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;
use Utils\Data;
use Utils\Conversao;

/**
 * Módulo generico
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class GenericModel extends AbstractTableGateway {

    /**
     * Contém o schema da tabela
     * @var String 
     */
    protected $schema = 'public';
    
    /**
     * Contém o nome da tabela
     * @var String 
     */
    protected $table = null;

    /**
     * Contém o valor da sequência que está vinculada a chave primária da tabela
     * @var String 
     */
    protected $sequence = null;


    /**
     * Construtor da classe 
     *  
     * @param BancoDados $bancoDados Contém a conexão com o banco de dados
     */
    public function __construct (BancoDados $bancoDados, $object) {
        
        $this->adapter = $bancoDados;
        //Fix que obriga a execução das querys no schema desejado
        //$this->adapter->query("SET search_path TO {$this->schema}")->execute();
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype($object->getInstance());
        $this->table = $object->getTable();
        $this->sequence = $object->getSequence();
        $this->initialize();
        
    }
    
    /**
     * Gera o array com os dados do objeto para uso do framework 
     * @param type $object
     * @return type
     */
    private function objectToArray($object) {
        if (is_object($object)) {
            $array = get_object_vars($object);
            $arrayToReturn = Array();
            foreach ($array as $key => $value) {
                $novaChave = "";
                for($i = 0; $i < strlen($key); $i++) {
                    
                    $ascii = ord(substr($key, $i, $i+1));
                    
                    if ($ascii >= 65 && $ascii <= 90) {
                        $novaChave .= "_";
                        $novaChave .= strtolower(chr($ascii));
                    } else {
                        $novaChave .= chr($ascii);
                    }
                    
                }
                $arrayToReturn[$novaChave] = ($value instanceof Data ? $value->formatar(Data::FORMATO_ISO_TIMESTAMP_LONGO) : $value);
            }
            return $arrayToReturn;
        } else {
            return array();
        }
        
    }
    

    /**
     * Salva os dados do objeto no banco de dados
     *  
     */
    public function salvar(&$object) {
        $close = false;
        if (!$this->adapter->onTransaction()) {
            $this->adapter->iniciar();
            $close = true;
        }
        $sql = "";
        try {
            //Preencho o array de inclusão dos dados do perfil
            $dados = $this->objectToArray($object);

            $logAcesso = new \Models\Modules\Cadastro\LogAcesso();
            
            //Verifico se o objeto já existe no banco de dados
            if (!($object->id > 0)) {
                //Se não existe, incluo o objeto na base de dados
                unset($dados['id']);
                //exit(print_r($dados));
                $this->insert($dados);
                
                $object->id = $this->getLastInsertValue();
                
            
                $logAcesso->acao = "CADASTRAR NOVO";
            } else {
                
                $this->update($dados, array(
                    'id' => $dados["id"]
                ));
                $logAcesso->acao = "ALTERAR";
            }
            
            
            
            if (!($object instanceof \Models\Modules\Cadastro\LogAcesso)) { 
                $logAcesso->jsonDados = json_encode($object);
                $logAcesso->tabela = $this->table;
                $logAcesso->idRegistro = $object->id;
                $logAcessoRn = new \Models\Modules\Cadastro\LogAcessoRn();
                $logAcessoRn->registrarLog($logAcesso);
            }
            
            if ($close) {
                $this->adapter->finalizar();
            }
        } catch (\Exception $e) {
            $this->adapter->cancelar();
            $message = \Utils\Excecao::mensagem($e);
            
            //exit(print_r($e));
            throw new \Exception($message . $sql);
        }
    }
    
    

    /**
     * Carrega os dados do objeto de acordo com a ID
     *  
     */
    public function carregar(&$object) {
        //A única validação é a verificação do preenchimento da ID
        if (($object->id > 0)) {
            $rowset = $this->select(array(
                'id' => $object->id
                    ));
            $object = $rowset->current();
            if (!$object) {
                throw new \Exception("Objeto não encontrado.");
            }
        } else {
            throw new \Exception("A ID do objeto deve estar preenchida.");
        }
    }
    
    

    /**
     * Efetua a pesquisa e retorna a lista de objetos do tipo Perfil
     *  
     * @param Where $where Objeto Where com todos os filtros necessários
     * @param Array $order Array contendo os ítens usados na ordenação dos perfis
     * @param int $offset Offset de perfis na listagem
     * @param int $limit Limite de perfis na listagem
     */
    public function listar($where = null, $order = null, $offset = null, $limit = null) {
        $oResultSet = $this->select(function (Select $select) use ($where, $order, $offset, $limit ) {

                    if (isset($where)) {
                        $select->where($where);
                    }

                    if (isset($order)) {
                        $select->order($order);
                    }

                    if (isset($offset)) {
                        $select->offset($offset);
                    }

                    if (isset($limit)) {
                        $select->limit($limit);
                    }
                });
        return $oResultSet;
    }

    /**
     * Exclui o objeto do banco de dados
     *  
     * @param Perfil $perfil Objeto do tipo perfil com a ID preenchida
     */
    public function excluir(&$object) {
        //Verificação da existência do objeto no banco de dados
        $this->carregar($object);
        
        $this->delete(array(
            'id' => $object->id
        ));
        
        $logAcesso = new \Models\Modules\Cadastro\LogAcesso();
        $logAcesso->acao = "EXCLUIR";
        $logAcesso->jsonDados = json_encode($object);
        $logAcesso->tabela = $this->table;
        $logAcesso->idRegistro = $object->id;
        $logAcessoRn = new \Models\Modules\Cadastro\LogAcessoRn();
        //$logAcessoRn->registrarLog($logAcesso);
        
    }
    
    /**
     * Executa o sql passado por parâmetro e retorna um array com o resultado
     * @param type $sql
     */
    public function executeSql($sql) {
        
        try{  
              
            $query = $this->adapter->query($sql)->execute();


      
        }catch(\Exception $e){

             $this->adapter->cancelar();
             $message = \Utils\Excecao::mensagem($e);   
             //exit(print_r($e));         
             throw new \Exception($message . $sql);
        }      

        return $query;                    
        
    }
    
    #created André 02/07/2019
    public function query($sql){

        try{  
      
            $this->adapter->iniciar();
                
            $response =$this->adapter->query($sql)->execute();

            $query = array(
                'query'    => $response->getAffectedRows(),
                'exception'=> null
            );

            $this->adapter->finalizar();


      
      }catch(\Exception $e){

         $this->adapter->cancelar();
         $message = \Utils\Excecao::mensagem($e);   
         //exit(print_r($e));         
         throw new \Exception($message . $sql);
      }                  
        


        return $query;

    }    

}

?>
