<?php
require_once __DIR__ . '/Aggregator.php';
require_once __DIR__ . '/Transformer.php';

class Router {

    public function gestisciRichiesta($metodo, $parti) {

        if ($metodo == 'GET') {
            // return: restituisce il risultato a index.php
            // che ci farà echo json_encode()
            // NON si fa echo qui dentro
            return $this->gestisciGet($parti);
        }

        // Metodo non supportato
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
        // funziona sia con /player/TAG che con /BrawlStars/player/TAG
        $indicePlayer = array_search('player', $parti);

        if ($indicePlayer !== false && isset($parti[$indicePlayer + 1]) && $parti[$indicePlayer + 1] !== '') {

            $tag = $parti[$indicePlayer + 1];
            // return: passa il risultato su fino a index.php
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

        // Valida il tag con regex
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

        // Chiama l'Aggregator per raccogliere i dati grezzi
        $aggregator = new Aggregator();
        $dati = $aggregator->raccogliDati($tag);

        // Chiama il Transformer per elaborare i dati
        $transformer = new Transformer();
        $risultato = $transformer->elabora($dati);

        http_response_code(200);
        // return: restituisce l'array a index.php
        // NON si fa echo qui, lo farà solo index.php
        return $risultato;
    }
}
?>