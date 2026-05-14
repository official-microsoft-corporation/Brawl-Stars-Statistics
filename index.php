<?php

// INCLUDE FILE

require_once __DIR__ . '/config/config.php';

require_once __DIR__ . '/src/Router.php';


// HEADER JSON, la risposta sarà json
header('Content-Type: application/json');

// METODO HTTP
$metodo = $_SERVER['REQUEST_METHOD'];

// LETTURA PERCORSO
$percorso = '';
//nell' url q è valorizzato al tag del giocatore
if (isset($_GET['q'])) {
    //trim pulisce il percorso togliendo / alla fine o inizio
    $percorso = trim($_GET['q'], '/');
}

//parsing del percorso usando / come separatore in array
$parti = explode('/', $percorso);


//crea oggetto della classe router
$router = new Router();

try {

    // salva il risultato restituito dal Router
    $risultato = $router->gestisciRichiesta($metodo, $parti);

    //trasforma array php in json e lo invia al client
    //json pretty print era solo in fase di sviluppo per rendere il json leggibile(con a capo, tab, spazi)
    //json unescaped unicode traduce i codici unicode in caratteri
    echo json_encode( $risultato, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
catch (Exception $e) {

    http_response_code(503);

    echo json_encode([

        'success' => false,

        'error' => [

            'code' =>
                $e->getCode(),

            'message' =>
                $e->getMessage()
        ]

    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>