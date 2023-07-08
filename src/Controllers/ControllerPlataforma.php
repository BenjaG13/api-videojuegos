<?php

namespace App\Controllers;


use App\Models\Db;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 


class ControllerPlataforma {

    public function postNuevaPlataforma(Request $request, Response $response)
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        if (!isset($data['nombre']) || $data['nombre'] === "") {
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'El nombre de la plataforma es requerido']));
            return $response;
        }

        $nombre = $data['nombre'];

        try{
            $conex = new Db();
            $conex = $conex->getConnection();

            $conex = $conex->prepare("INSERT INTO plataformas (nombre) VALUES (:nombre)");
            $conex->bindParam(':nombre', $nombre);
            $result = $conex->execute();
            $conex = null;

            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            return $response;
        }catch(PDOException $e){
            $error = array(
                "message" => $e->getMessage()
            );
            $response->getBody()->write(json_encode($e));
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
            return $response;
        }
    }

    public function putActualizarPlataforma(Request $request, Response $response, $args)
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        

        $id = $args['id'];
        $nombre = $data['nombre'];

        try{
            $conex = new Db();
            $conex = $conex->getConnection();

            $result = $conex->query("SELECT id FROM plataformas WHERE id = $id");
            if ($result->rowCount() < 1){
                $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
                $response->getBody()->write(json_encode(['error' => '404 Not Found: El id de la plataforma no existe']));
            return $response;
            }
            $conex = $conex->prepare("UPDATE plataformas SET nombre = :nombre WHERE id = $id"); 
            $conex->bindParam(':nombre', $nombre);
            $result = $conex->execute();
            $conex = null;

            $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            return $response;

        }catch(PDOException $e){
            $error = array(
                "message" => $e->getMessage()
            );
            $response->getBody()->write(json_encode($e));
            $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
            return $response;
        }
    }

    public function deleteEliminarPlataforma(Request $request, Response $response, $args)
    {
        
        $id = $args['id'];
    
        try {
            $conex = new Db();
            $conex = $conex->getConnection();
    
            $result = $conex->query("SELECT id FROM plataformas WHERE id = $id");
            if ($result->rowCount() < 1) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
                $response->getBody()->write(json_encode(['error' => '404 Not Found: El id de la plataforma no existe']));
                return $response;
            }
    
            $result = $conex->query("SELECT id FROM juegos WHERE id_plataforma = $id");
            if ($result->rowCount() > 0) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'No es posible eliminar la plataforma porque existen juegos en la base de datos relacionados con esa plataforma']));
                return $response;
            }
    
            $conex = $conex->prepare("DELETE FROM plataformas WHERE id = :id");
            $conex->bindParam(':id', $id);
            $result = $conex->execute();
            $conex = null;
    
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            return $response;
        } catch (PDOException $e) {
            $error = array(
                "message" => $e->getMessage()
            );
            $response->getBody()->write(json_encode($e));
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
            return $response;
        }
    }

    public function getPlataformas(Request $request, Response $response)
    {
        try {
            $conex = new Db();
            $conex = $conex->getConnection();
            $conex = $conex->query("SELECT * FROM plataformas");
            $plataforma = $conex->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($plataforma));
            $conex = null;
           
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            return $response;
        }catch(PDOException $e){
            $error = array(
                "message" => $e->getMessage()
            );
            $response->getBody()->write(json_encode($e));
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
            return $response;
        }
    }

}