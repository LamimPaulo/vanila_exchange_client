<?php

namespace Modules\error\Controllers;

use Zend\Config\Reader\Xml;
use Utils\Layout;

/**
 * Classe de Erro HTTP
 *
 * @copyright Copyright (c) 2012 DDuo Sistemas Ltda (http://www.dduo.com.br)
 * @package Dduo
 */
class Error {

    public function index($params) {
        try {
            
            $codigo = \Utils\Get::get($params, 0, 700);
            
            $mensagem = $this->getErrorbyCodigo($codigo);
            
            $params["mensagem"] = $mensagem;
            $params["erro"] = $codigo;
            
            Layout::view('erros', $params);
        } catch (\Exception $e) {
            return \Utils\Excecao::mensagem($ex);
        }
    }

    
    
    private function getErrorbyCodigo($codigo) {
        
        switch ($codigo) {
            case "001": return "Erro sem parâmetro";
            case "100": return "Módulo inexistente";
            case "101": return "Controller inexistente.";
            case "102": return "Cliente inexistente.";
            case "103": return "Método inexistente";
            case "104": return "View inexistente.";
            case "105": return "Configuração dos dados do cliente não encontrada.";
            case "109": return "Não foi encontrada a rotina .";
            case "400": return "Erro de requisição, limpe o cache com o comando CTRL+F5 e tente novamente.";
            case "401": return "Acesso não autorizado.";
            case "403": return "Acesso negado.";
            case "404": return "Arquivo ou diretório não existe.";
            case "407": return "A conexão deve ser feita através de um proxy.";
            case "408": return "Conexão perdida, excedeu o tempo de espera.";
            case "410": return "Site não encontrado, DNS modificado";
            case "411": return "Sobrecarga no transporte de dados na conexão.";
            case "412": return "Sistema não oferece suporte a este protocolo.";
            case "413": return "Excedeu o limite de upload do servidor.";
            case "414": return "URL extremamente grande.";
            case "415": return "Serviço não existe instalado/configurado.";
            case "422": return "Recurso não pode ser processado.";
            case "500": return "Diretórios com privilégios de acesso inconsistentes.";
            case "501": return "Requisição não implementada no servidor.";
            case "502": return "Falha no proxy intermediário entre o Cliente e Servidor.";
            case "503": return "Serviço em manutenção.";
            case "506": return "Problemas de cache de servidor ou do usuário ou em módulos do Apache.";
            case "507": return "Espaço em disco ou memória insuficiente.";
            case "600": return "Aviso padrão.";
            case "601": return "Não possui permissão de acesso a rotina.";
            case "602": return "Não possui permissão para executar a ação.";
            case "106": return "Cliente impossibilitado de autenticar no sistema.";
            case "107": return "Sessão expirada.";
            case "108": return "Não possui permissão de acesso ao módulo.";
            case "901": return "Identificação do registro não informada.";
            default : return "Falha desconhecida.";
        }
        
    }
    
}

