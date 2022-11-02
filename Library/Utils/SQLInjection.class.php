<?php

namespace Utils;

class SQLInjection {
    
    public static function clean($value, $scape= true) {
        if(is_array($value)) 
            return array_map(__METHOD__, $value); 

        
        if ($scape) { 
            if(!empty($value) && is_string($value)) { 
                $value = str_replace(
                    array('||','|',' * ', ';', '<', '>', '!',  '--' , '/*' , '='  , ' and ', ' AND ', ' or ' , ' OR ', '\\'  , "\0" , "\n" , "\r"  , " '"   , '"'  , "\x1a", "' " ), 
                    array(''   ,''   ,''   , '' , '' , '' , '' ,  ''    ,  ''  , '\\=', ''     , ''     , '\\or' , '\\OR', '\\\\', '\\0', '\\n', '\\r' ,  " \\'", '\\"', '\\Z',  "\\' "  ), 
                $value);
            } 
        }
        
        $palavrasReservadas = Array(
            ' HREF ',
            ' SCRIPT',
            ' ALERT( ',
            ' CONSOLE.',
            ' SELECT ',
            ' UPDATE ',
            ' WHERE ',
            ' LEFT ',
            ' NOT ',
            ' LIKE ',
            ' DROP ',
            ' ALTER ',
            ' INSERT ',
            ' DELETE ',
            ' JOIN ',
            ' INNER ',
            ' TRUNCATE ',
            ' CREATE ' ,
            ' DELIMITER ',
            ' CASE ',
            ' THEN ',
            ' NULL ',
            'CHR(',
            ' CAST ',
            ' CAST(',
            ' UNION ',
            ' END) ',
            ' END ',
            ' declare ',
            'master',
            ' exec ',
            ' SLEEP( ',
            ' SLEEP ',
            'CONCAT',
            'ELT(',
            'BOOLEAN',
            'VARCHAR',
            'INTEGER',
            'EXP(',
            'JSON_KEYS',
            'EXTRACT',
            'UPDATEXML',
            'ROW(',
            'GROUP BY',
            'COLUMN(',
            'NUMERIC',
            'INFORMATION_SCHEMA',
            'MAKE_SET',   
            'DEC(',
            'HEX(',
            'BINARY',
            'BINARY(',
            'EXTRACTVALUE',
            'EXTRACTVALUE(',
            'XMLTYPE',
            'XMLTYPE(',
            'SYSTEM',
            'MASTER',
            'UTF-8',            
            'DUAL',
            'VERSION',            
            'DOCTYPE',
            'REPLACE',
            'RAWTOHEX(',
            'RAWTOHEX',
            'CHR(',
            'ELSE',
            'DATABASE',
            'MSSQL',
            'MSSQLI',
            'INDEX',
            'SUGAR',
            'VERSION',
            'JSON_',
            'DRIVER',
            'EXTRACT',
            'CONCAT',
            'LOAD_FILE',
            'CHR(',
            'CHAR(',
            'NULL',
            'DUMPFILE',
            'ENCODE',
            'SELECT',
            
        );
        
        foreach ($palavrasReservadas as $palavra) {
            if (is_numeric(strpos(strtoupper($value), strtoupper($palavra))) || (is_numeric($value) && $value < 0)) {
                
                $value = Criptografia::encriptyPostId($value);
                
                $usuarioRn = new \Models\Modules\Cadastro\UsuarioRn();
                $logado = Geral::getLogado();
                
                $enviarSms = false; 
                if (!isset($_SESSION["SQLI"][$logado->email]) ) {
                    $enviarSms = true;
                    $_SESSION["SQLI"][$logado->email] = new Data(date("d/m/Y H:i:s"));
                }
                
                $dataUltimaIdentificacaoUsuario = $_SESSION["SQLI"][$logado->email];
                $dataAtual = new Data(date("d/m/Y H:i:s"));
                
                $dif = $dataAtual->diferenca($dataUltimaIdentificacaoUsuario) ;
                
                if ($dif->i > 30 || $dif->h > 0 || $dif->days > 0) {
                    $enviarSms = true;
                    unset($_SESSION["SQLI"][$logado->email]);
                }
                
                
                
                if ($logado instanceof \Models\Modules\Cadastro\Cliente) {
                    $clienteRn = new \Models\Modules\Cadastro\ClienteRn();

                    
                    // $clienteRn->conexao->update(
                    //     Array(
                    //         "status" => 2,
                    //         "analise_cliente" => 1,
                    //         "id_analise_cliente_adm" => 1483022582, //Renato User
                    //         "anotacoes" => " {$logado->observacoes} \n Cliente bloqueado automaticamente por ser detectada SQL Injection "
                    //     ), 
                    //     Array(
                    //         "id" => $logado->id
                    //     )
                    // ); //TODO uncoment to be safer
                         
                    $observacaoCliente = new \Models\Modules\Cadastro\ObservacaoCliente();
                    $observacaoCliente->idCliente = $logado->id;
                    $observacaoCliente->observacoes = "SQL INJECTION detectado: {$value} - POST = " . implode("|", $_POST) . " - GET = " . implode("|", $_GET) . " - _SERVER['QUERY_STRING']: " . $_SERVER['QUERY_STRING'] . " -  _SERVER['HTTP_REFERER']: " . $_SERVER["HTTP_REFERER"];
                    
                    $observacaoClienteRn = new \Models\Modules\Cadastro\ObservacaoClienteRn();
                    $observacaoClienteRn->salvar($observacaoCliente);
                    
                    
                    
                } else {
                    $meliante = new \Models\Modules\Cadastro\Usuario(Array("id" => $logado->id));
                    $data = new Data(date("d/m/Y H:i:s"));
                    $meliante->observacoes .= " \n\n SQL INJECTION em {$data->formatar(Data::FORMATO_PT_BR_TIMESTAMP_LONGO)} ";
                    $usuarioRn->conexao->update(Array("observacoes" => $meliante->observacoes, "ativo" => 0), Array("id" => $meliante->id));
                }
                
                if ($enviarSms) {
                    
                    
                    if ($logado instanceof \Models\Modules\Cadastro\Usuario) {
                        $ident = " USR {$logado->id}";
                    } else if ($logado instanceof \Models\Modules\Cadastro\Cliente){
                        $ident = " CLI {$logado->id}";
                    } else {
                        $ident = " NO AUTH";
                    }

                    $msg = "BROKER-SQLINJEC: {$ident}-" .  $_SERVER['QUERY_STRING'];
                    
                    Notificacao::notificar($msg, true, true);
                    
                }

                
                $name = 'sqli.txt';

                $content = date("d/m/Y H:i:s") . " ";
                $content .= $_SERVER["HTTP_REFERER"] ;
                $content .= "      " . $_SERVER['QUERY_STRING'];
                if ($logado != null) {
                    $content .= "   USR = " . $logado->email;
                }
                $content .= "   POST = " . implode("|", $_POST);
                $content .= "   GET = " . implode("|", $_GET);
                $content .= "   SQL = {$value}\n";
                $file = fopen($name, 'a');
                fwrite($file, $content);
                fclose($file);
                
                
                
                //\Utils\Geral::setAutenticado(false);
                
                //\Cloudflare\ZoneFirewallAccessRule::block($_SERVER["REMOTE_ADDR"], "Bloqueio automático devido a inserção de conteudo suspeito de SQLInjection.");
                //throw new \Exception("Cliente inválido.");
            }
        }
       
        return $value;
    }
    
}
