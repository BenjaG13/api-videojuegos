<?php


use App\Controllers\ControllerGenero;
use App\Controllers\ControllerJuego;
use App\Controllers\ControllerPlataforma;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Response as SlimResponse;

require __DIR__ . '/vendor/autoload.php';



$app = AppFactory::create();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");


$app->addErrorMiddleware(true, true, true)->setErrorHandler(HttpNotFoundException::class, function ($request, $response) {
    $errorResponse = new SlimResponse();
    $errorResponse = $errorResponse
    ->withHeader('Content-Type', 'application/json')
    ->withStatus(404);
$errorResponse->getBody()->write(json_encode(['error' => '404 Not Found: La url no existe']));
return $errorResponse;
});

   // RUTAS GENEROS
   $app->get('/generos', [ControllerGenero::class, 'getGeneros']);  //  D
   $app->post('/generos', [ControllerGenero::class, 'postNuevoGenero']); //  A
   $app->put('/generos/{id}', [ControllerGenero::class, 'putActualizarGenero']); //  B
   $app->delete('/generos/{id}', [ControllerGenero::class, 'deleteEliminarGenero']); //  C
   

   // RUTAS PLATAFORMAS
   $app->post('/plataformas', [ControllerPlataforma::class, 'postNuevaPlataforma']); //  E
   $app->put('/plataformas/{id}', [ControllerPlataforma::class, 'putActualizarPlataforma']); //  F
   $app->delete('/plataformas/{id}', [ControllerPlataforma::class, 'deleteEliminarPlataforma']); //  G 
   $app->get('/plataformas', [ControllerPlataforma::class, 'getPlataformas']); // H

   // RUTAS JUEGOS
   $app->post('/juegos', [ControllerJuego::class, 'postNuevoJuego']); // I
   $app->put('/juegos/{id}', [ControllerJuego::class, 'putActualizarJuego']); //  J
   $app->delete('/juegos/{id}', [ControllerJuego::class, 'deleteEliminarJuego']); // K
   $app->get('/juegos', [ControllerJuego::class, 'getBuscarJuegos']); // M y L

$app->run();