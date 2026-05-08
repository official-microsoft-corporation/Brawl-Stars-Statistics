<?php

// =====================================
// INCLUDE FILE
// =====================================
require_once __DIR__ . '/config/config.php';

require_once __DIR__ . '/src/Router.php';


// =====================================
// HEADER JSON
// =====================================
header('Content-Type: application/json');


// =====================================
// METODO HTTP
// =====================================
$metodo = $_SERVER['REQUEST_METHOD'];


// =====================================
// LETTURA PERCORSO
// =====================================
$percorso = '';

if (isset($_GET['q'])) {

    $percorso = trim($_GET['q'], '/');
}


// =====================================
// DIVIDE IL PERCORSO
// =====================================
$parti = explode('/', $percorso);


// =====================================
// ROUTER
// =====================================
$router = new Router();

try {

    // IMPORTANTE:
    // salva il risultato restituito dal Router
    $risultato = $router->gestisciRichiesta(
        $metodo,
        $parti
    );

    // invia il JSON al client
    echo json_encode(
        $risultato,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

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