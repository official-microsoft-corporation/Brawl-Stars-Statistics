<?php

require_once __DIR__ . '/../config/config.php';

class Aggregator{

    public function raccogliDati($tag){
    
        /* %23 è come viene codificato il #
        # ha un significato speciale negli URL, tutto quello 
        che lo segue viene ignorato e non inviato al server
        l'API di brawlstars lo codifica in %23 (URL encoding RFC 3986) */

        $profilo    = $this->chiamaAPI('/players/%23' . $tag);
        $battlelog  = $this->chiamaAPI('/players/%23' . $tag . '/battlelog');

        return [
            'profilo'   => $profilo,
            'battlelog' => $battlelog,
        ];
    
    }

    private function chiamaAPI($endpoint){
        //prende l'url base per le richieste dal file config
        $url = BRAWLSTARS_BASE_URL . $endpoint;

        //inizializza oggetto cURL
        $curl = curl_init();

        //imposta URL
        curl_setopt($curl, CURLOPT_URL, $url);

        //risposta viene restituita come stringa e non stampata
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //mette la chiave nell header prendendola dal file config
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . BRAWLSTARS_API_KEY]);
        
        $risposta = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);  

        // gestione errori in base al codice HTTP ricevuto
        if ($risposta === false) {
            throw new Exception('BRAWLSTARS_UNAVAILABLE', 503);
        }

        switch ($httpCode) {
            case 404:
                throw new Exception('PLAYER_NOT_FOUND', 404);

            case 429:
                throw new Exception('RATE_LIMIT_EXCEEDED', 429);

            case 403:
                throw new Exception('INVALID_API_KEY', 503);

            default:
                if ($httpCode >= 500) {
                    throw new Exception('BRAWLSTARS_UNAVAILABLE', 503);
                }
                break;
        }

        // json decode con true converte il json in array php
        return json_decode($risposta, true);

    }
}
?>