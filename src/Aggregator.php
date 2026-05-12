<?php

require_once __DIR__ . '/../config/config.php';

class Aggregator{
    public function raccogliDati($tag){
    
    $profilo    = $this->chiamaAPI('/players/%23' . $tag);
    $battlelog  = $this->chiamaAPI('/players/%23' . $tag . '/battlelog');

    // Restituisce i dati grezzi al Router che li passerà al Transformer
    return [
        'profilo'   => $profilo,
        'battlelog' => $battlelog,
    ];
    
    }

    private function chiamaAPI($endpoint){
        //prende l'url base per le richieste dal file config
        $url = BRAWLSTARS_BASE_URL . $endpoint;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        //returntransfer: restituisce la risposta come stringa invece di stamparla
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        //mette la chiave nell header prendendola dal file config
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . BRAWLSTARS_API_KEY]);
        
        $risposta = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);  

        //gestione errori in base al codice HTTP ricevuto
        if ($risposta === false) {
            throw new Exception('BRAWLSTARS_UNAVAILABLE', 503);
        }

        if ($httpCode === 404) {
            throw new Exception('PLAYER_NOT_FOUND', 404);
        }

        if ($httpCode === 429) {
            throw new Exception('RATE_LIMIT_EXCEEDED', 429);
        }

        if ($httpCode === 403) {
            throw new Exception('INVALID_API_KEY', 503);
        }

        if ($httpCode >= 500) {
            throw new Exception('BRAWLSTARS_UNAVAILABLE', 503);
        }

        //json decode con true converte il json in array associativo php
        return json_decode($risposta, true);
    }
}
?>