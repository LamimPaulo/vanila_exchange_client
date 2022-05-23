<?php

namespace Modules\api\Controllers;

class Roadmap {
    
    public function __construct() {
        header('Access-Control-Allow-Origin: *');
    }

    public function index() {
        try {
            $roadmapRn = new \Models\Modules\Cadastro\RoadmapRn();
            $lista = $roadmapRn->conexao->listar("publicado > 0", "posicao");

            $json["roadmap"] =  Array( );
            foreach ($lista as $roadmap) {
                $json["roadmap"][] = Array(
                    "posicao" => $roadmap->posicao,
                    "dia" => $roadmap->data->formatar("d"),
                    "data" => "{$roadmap->data->getNomeMes(false)} de {$roadmap->data->formatar("Y")}",
                    "titulo" => $roadmap->titulo,
                    "texto" => $roadmap->texto,
                    "concluido" => ($roadmap->concluido > 0),
                    "img" => URLBASE_CLIENT . PUBLIC_IMAGES . $roadmap->imagem
                );
            }
            $json["sucesso"] = true;
        } catch (\Exception $ex) {
            $json["sucesso"] = false;
        }
        print json_encode($json);
    }
    
    
    
    
}