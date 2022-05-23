<?php

namespace Models\Modules\Cadastro;

use \Models\Modules\Model\GenericModel;
/**
 * Classe que contém as regras de negócio da entidade Estado
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Models_Modules
 * @subpackage Acesso
 */
class DispositivoMobileRn {
    
    const QTD_APARELHOS_SIMULTANEOS = 1;
    
    /**
     *
     * @var GenericModel 
     */
    public $conexao = null;
    public $idioma=null;
    
    public function __construct(\Io\BancoDados $adapter = null) {
        $this->idioma = new \Utils\PropertiesUtils("exception", 'IDIOMA');
        if ($adapter == null) {
            $this->conexao = new GenericModel(\Dduo::conexao(), new DispositivoMobile());
        } else {
            $this->conexao = new GenericModel($adapter, new DispositivoMobile());
        }
    }
    
    public function salvar(DispositivoMobile &$dispositivoMobile) {
        
        $dispositivoMobile->dataCadastro = new \Utils\Data(date("d/m/Y H:i:s"));
        
        $dispositivoMobile->id = 0;
        $dispositivoMobile->status = 1;
        
        if (empty($dispositivoMobile->marcaFabricante)) {
            throw new \Exception("É necessário informar a marca do fabricante.");
        }
        
        if (empty($dispositivoMobile->modelo)) {
            throw new \Exception("É necessário informar o modelo do aparelho.");
        }
        
        
        if (empty($dispositivoMobile->numeroSerial)) {
            throw new \Exception("É necessário informar o número serial do aparelho.");
        }
        
        if (empty($dispositivoMobile->sistemaOperacional)) {
            throw new \Exception("É necessário informar o sistema operacional do aparelho.");
        }
        
        if (empty($dispositivoMobile->versaoSo)) {
            throw new \Exception("É necessário informar a versão do sistema operacional do aparelho.");
        }
        
        if (!($dispositivoMobile->idCliente > 0)) {
            throw new \Exception("Identificação do cliente inválida.");
        }
        
        $this->validarQuantidadeAparelhos(new Cliente(Array("id"=>$dispositivoMobile->idCliente)));
        
        $this->conexao->salvar($dispositivoMobile);
    }
    
    
    public function getDispositivo(DispositivoMobile $dispositivoMobile, Cliente $cliente, $registrarNovo = true) {
        
        $where = new \Zend\Db\Sql\Where();
        
        $where->equalTo("marca_fabricante", $dispositivoMobile->marcaFabricante);
        $where->equalTo("modelo", $dispositivoMobile->modelo);
        $where->equalTo("numero_serial", $dispositivoMobile->numeroSerial);
        $where->equalTo("id_cliente", $cliente->id);
        
        $result = $this->conexao->select($where);
        
        if (sizeof($result) > 0) {
            return $result->current();
        }
        
        if ($registrarNovo) {
            $dispositivoMobile->idCliente = $cliente->id;
            $this->salvar($dispositivoMobile);
            
            return $dispositivoMobile;
        } else {
            return null;
        }
    }
    
    public function validarQuantidadeAparelhos(Cliente $cliente)  {
        
        $result = $this->conexao->listar("id_cliente = {$cliente->id} AND status = 1");
        if (sizeof($result) >= self::QTD_APARELHOS_SIMULTANEOS) {
            throw new \Exception("Você atingiu a quantidade máxima de aparelhos ativos. Por favor acesse o seu perfil na exchange e na aba Dispositivos Mobile desabilite o dispositivo ativo e tente novamente.");
        }
    }
    
    public function desativar(DispositivoMobile $dispositivoMobile, $admin = false) {
        
        try {
            
            try {
                $this->conexao->carregar($dispositivoMobile);
            } catch (\Exception $ex) {
                throw new \Exception("Dispositivo inválido ou não localizado");
            }
            
            $this->conexao->update(Array("status" => ($admin ? 2 : 0)), Array("id" => $dispositivoMobile->id));
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
        
    }
    
    public function ativar(DispositivoMobile $dispositivoMobile, $admin = false) {
        
        try {
            
            try {
                $this->conexao->carregar($dispositivoMobile);
            } catch (\Exception $ex) {
                throw new \Exception("Dispositivo inválido ou não localizado");
            }
            $this->validarQuantidadeAparelhos(new Cliente(Array("id"=>$dispositivoMobile->idCliente)));
            if (!$admin && $dispositivoMobile->status == 2) {
                throw new \Exception("O dispositivo encontra-se bloqueado no sistema. Por favor contate o suporte.");
            }
            
            $this->conexao->update(Array("status" => 1), Array("id" => $dispositivoMobile->id));
            
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
        
    }
    
    
    public function filtrar($idCliente = 0) {
        
        $where = Array();
        
        if ($idCliente > 0) {
            $where[] = " d.id_cliente = {$idCliente} ";
        }
        
        $sWhere = (sizeof($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        
        $query = " SELECT d.* FROM dispositivos_mobile d {$sWhere} ORDER BY status DESC, d.data_cadastro DESC;";
        $result = $this->conexao->adapter->query($query)->execute();
        
        $lista = Array();
        foreach ($result as $dados) {
            $dispositivoMobile  = new DispositivoMobile($dados);
            
            $lista[] = $dispositivoMobile;
        }
        
        return $lista;
    }
}

?>