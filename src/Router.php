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

        if ($indicePlayer !== false && isset($parti[$indicePlayer + 1]) && $parti[$indicePlayer + 1] !== '') {

            $tag = $parti[$indicePlayer + 1];
            return $this->gestisciPlayer($tag);

        } else {

            http_response_code(404);
            return [
                'success' => false,
                'error' => [
                    'code'    => 'NOT_FOUND',
                    'message' => 'Endpoint non trovato. Usa /player/{tag}'
                ]
            ];
        }
    }

    private function gestisciPlayer($tag) {

        // Valida il tag (può contenere solo lettere e numeri)
        if (!preg_match('/^#?[A-Z0-9]+$/i', $tag)) {
            http_response_code(400);
            return [
                'success' => false,
                'error' => [
                    'code'    => 'INVALID_TAG_FORMAT',
                    'message' => 'Il tag non è valido',
                    'hint'    => 'Il tag deve contenere solo lettere e numeri. Es: ABC123'
                ]
            ];
        }

        // Chiama l'Aggregator per raccogliere i dati 
        $aggregator = new Aggregator();
        $dati = $aggregator->raccogliDati($tag);

        // Chiama il Transformer per elaborare i dati
        $transformer = new Transformer();
        $risultato = $transformer->elabora($dati);

        //success
        http_response_code(200);
        
        return $risultato;
    }
}
?>