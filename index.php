<?php
    //include i file
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/src/Router.php';

    //imposta la risposta come JSON
    header("Content-Type: application/json");

    //legge il metodo HTTP della richiesta (GET, POST, ecc.)
    $metodo = $_SERVER['REQUEST_METHOD'];

    //index controlla se q e' settato, nel caso scrive in percorso quello che e' dentro q che sarebbe il percorso ricevuto dal client
    $percorso = '';
    if(isset($_GET['q'])) {
        $percorso = trim($_GET['q'], '/');
    }

    //parse del percorse usa / come separatore
    $parti = explode('/', $percorso);

    // Avvia il router e gli passa metodo e parti dell'URL
    $router = new Router();

    try {
        $router->gestisciRichiesta($metodo, $parti);
    } catch (Exception $e) {
        //gestione errori del server
        http_response_code(503);
        echo json_encode([
            'success' => false,
            'error' => [
                'code'    => $e->getMessage(),
                'message' => 'errore interno del server'
            ]
        ]);
    }
?>