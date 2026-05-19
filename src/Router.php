<?php
require_once __DIR__ . '/Aggregator.php';
require_once __DIR__ . '/Transformer.php';

class Router {

    public function gestisciRichiesta($metodo, $parti) {

        if ($metodo == 'GET') {
            //  restituisce il risultato a index.php che fara echo json_encode()
            return $this->gestisciGet($parti);
        }

        // gestione metodo non supportato
        http_response_code(405);
        return [
            'success' => false,
            'error' => [
                'code'    => 'METHOD_NOT_ALLOWED',
                'message' => 'Metodo HTTP non supportato'
            ]
        ];
    }

    public function gestisciGet($parti) {
        // array_search cerca "player" nell'array delle parti
        $indicePlayer = array_search('player', $parti);

        $tag = $parti[$indicePlayer + 1];
        return $this->gestisciPlayer($tag);
        
    }

    private function gestisciPlayer($tag) {

        // valida il tag (può contenere solo lettere e numeri)
        if (!preg_match('/^[A-Z0-9]+$/i', $tag)) {
            
            http_response_code(400);

            return [
                'success' => false,
                'error' => [
                    'code'    => 'INVALID_TAG_FORMAT',
                    'message' => 'Il tag non è valido',
                    'hint'    => 'Il tag deve contenere solo lettere e numeri'
                ]
            ];
        }

        //chiama l'Aggregator per raccogliere i dati 
        $aggregator = new Aggregator();
        $dati = $aggregator->raccogliDati($tag);

        //chiama il Transformer per elaborare i dati
        $transformer = new Transformer();
        $risultato = $transformer->elabora($dati);

        //success
        http_response_code(200);
        
        return $risultato;
    }
}
?>