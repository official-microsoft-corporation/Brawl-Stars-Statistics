<?php

class Transformer{
    public function elabora($dati){
        return [
            'success' => true,
            'data' => [
                'profile' => $dati['profilo'],
                'battle_stats' => $dati['battlelog'],
                'brawlers' => $dati['profilo']['brawlers'],
            ],
            'meta' => [ 
                // date('c') restituisce la data e ora corrente in formato ISO 8601
                'generated_at'    => date('c'),
                // Numero fisso di chiamate API fatte dall'Aggregator
                'api_calls_made'  => 2,
            ]
        ];
    }

}

?>