<?php



namespace App\Controllers;

use App\Models\Db;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 

class ControllerGenero {
    public function getGeneros(Request $request, Response $response)
    {
        try {
            $conex = new Db();
            $conex = $conex->getConnection();
            $conex = $conex->query("SELECT * FROM generos"); 
            $generos = $conex->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($generos));
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

    public function postNuevoGenero(Request $request, Response $response)
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        if (!isset($data['nombre']) || $data['nombre'] === "") {
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
            $response->getBody()->write(json_encode(['error' => 'El nombre del genero es requerido']));
            return $response;
        }

        $nombre = $data['nombre'];

        try{
            $conex = new Db();
            $conex = $conex->getConnection();

            $conex = $conex->prepare("INSERT INTO generos (nombre) VALUES (:nombre)");
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

    public function putActualizarGenero(Request $request, Response $response, $args)
    {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);

        $id = $args['id'];

        if (!isset($data['nombre']) || $data['nombre'] === "") {
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'El nombre del genero es requerido']));
            return $response;
        }
        
        $nombre = $data['nombre'];

        try{
            $conex = new Db();
            $conex = $conex->getConnection();

            $result = $conex->query("SELECT id FROM generos WHERE id = $id");
            if ($result->rowCount() < 1){
                $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
                $response->getBody()->write(json_encode(['error' => '404 Not Found: El id del genero no existe']));
            return $response;
            }
            $conex = $conex->prepare("UPDATE generos SET nombre = :nombre WHERE id = $id"); 
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

    public function deleteEliminarGenero(Request $request, Response $response, $args)
    {
    
        $id = $args['id'];
    
        try {
            $conex = new Db();
            $conex = $conex->getConnection();
    
            $result = $conex->query("SELECT id FROM generos WHERE id = $id");
            if ($result->rowCount() < 1) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
                $response->getBody()->write(json_encode(['error' => '404 Not Found: El id del genero no existe']));
                return $response;
            }
    
            $result = $conex->query("SELECT id FROM juegos WHERE id_genero = $id");
            if ($result->rowCount() > 0) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'No es posible eliminar el genero porque existen juegos en la base de datos relacionados con ese genero']));
                return $response;
            }
    
            $conex = $conex->prepare("DELETE FROM generos WHERE id = :id");
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

}