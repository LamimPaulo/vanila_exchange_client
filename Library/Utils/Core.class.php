<?php

namespace Utils;

class Core {



  private static function connect_be($user, $method, $params)
  {
    // Path do socket
    $sfile = "/opt/newcash/newcash.s";
    // Verifica se o link para o socket existe
    if (!file_exists($sfile))
    {
        die("socket não existe<br>\n");
    }
    // Cria o socket
    $s = socket_create(AF_UNIX, SOCK_STREAM, 0);
    if ($s == FALSE)
    {
        die("Erro ao criar socket: " . socket_strerror(socket_last_error()));
    }
    // Conecta
    $conn = socket_connect($s, $sfile);
    if ($conn == FALSE)
    {
        die("Erro ao criar conexao: " . socket_strerror(socket_last_error()));
    }
    // Comunicação
    $res = socket_send($s, sprintf("BC|%s|%s|%s", $user, $method, $params), 13, 0);
    if ($res == FALSE)
    {
        die("Erro ao enviar dados.");
    }
    socket_close($s);
  }

}
