<?php

require_once __DIR__ . '/Aggregator.php';
require_once __DIR__ . '/Transformer.php';

    class Router{
        public function gestisciRichiesta($metodo, $parti){
            if($metodo == 'GET'){
                $this->gestisciGet($parti);
            }
        }

        public function gestisciGet($parti){
        //controlla che l'URL sia nella forma /player/{tag} $parti[0] deve essere "player" e $parti[1] deve essere il tag
            if (isset($parti[0]) && $parti[0] === 'player' && isset($parti[1]) && $parti[1] !== '') {
                $tag = $parti[1];
                $this->gestisciPlayer($tag);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code'    => 'NOT_FOUND',
                        'message' => 'Endpoint non trovato. Usa /player/{tag}'
                    ]
                ]);
            }
        }

        private function gestisciPlayer($tag){

            // echo '<pre>';
            // var_dump($tag);
            // echo '</pre>';

            // Valida il formato del tag con una espressione regolare
            // Il tag deve contenere solo lettere e numeri, con # opzionale all'inizio
            // preg_match restituisce 1 se il pattern corrisponde, 0 altrimenti
            if (!preg_match('/^#?[A-Z0-9]+$/i', $tag)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code'    => 'INVALID_TAG_FORMAT',
                        'message' => 'Il tag non è valido',
                        'hint'    => 'Il tag deve iniziare con # e contenere solo lettere e numeri. Es: #ABC123'
                    ]
                ]);
                return;
            }

            //chiama l'Aggregator per raccogliere i dati dalle api di brawlstars
            $aggregator = new Aggregator();
            $dati = $aggregator->raccogliDati($tag);

            // echo '<pre>';
            // var_dump($dati);
            // echo '</pre>';

            //chiama il Transformer per elaborare i dati e costruire la risposta finale
            $transformer = new Transformer();
            $risultato = $transformer->elabora($dati);


            http_response_code(200);
            echo json_encode($risultato);
        }
    }

?>