<?php

namespace Modules\privado\Controllers;

class HTTPResponseCode {
    
    /**
     * Indica que o processamento foi realizado corretamente e o retorno poderá ser consultado no corpo do HTTP Response
     * @var String
     */
    static $CODE200 = "200"; 
    
    
    /**
     * Indica que o recurso foi criado com sucesso, deverá existir o header Location: indicando a URI do novo recurso
     * @var String
     */
    static $CODE201 = "201"; 
    
    
    /**
     * Indica que o processamento será assíncrono, portanto, além do header Location, deverá retornar o conteúdo com um atributo status
     * @var String
     */
    static $CODE202 = "202"; 
    
    
    /**
     * Indica que o recurso foi alterado ou excluído com sucesso
     * @var String
     */
    static $CODE204 = "204"; 
    
    
    /**
     * Exceções de negócio
     * @var String
     */
    static $CODE422 = "422"; 
    
    
    /**
     * Requisição Mal Formada
     * @var String
     */
    static $CODE400 = "400"; 
    
    
    /**
     * Requisição Requer Autenticação
     * @var String
     */
    static $CODE401 = "401"; 
    
    
    /**
     * Requisição Negada
     * @var String
     */
    static $CODE403 = "403"; 
    
    
    /**
     * Recurso não Encontrado
     * @var String
     */
    static $CODE404 = "404"; 
    
    
    /**
     * Método não Permitido
     * @var String
     */
    static $CODE405 = "405"; 
    
    
    /**
     * Tempo esgotado para a requisição
     * @var String
     */
    static $CODE408 = "408"; 
    
    
    /**
     * Requisição excede o tamanho máximo permitido
     * @var String
     */
    static $CODE413 = "413"; 
    
    
    /**
     * Tipo de mídia inválida (falta de informar o content-type correto, ver JSON)
     * @var String
     */
    static $CODE415 = "415"; 
    
    
    /**
     * Requisição excede a quantidade máxima de chamadas permitidas à API
     * @var String
     */
    static $CODE429 = "429"; 
    
    
    /**
     * Erro de servidor
     * @var String
     */
    static $CODE500 = "500"; 
}