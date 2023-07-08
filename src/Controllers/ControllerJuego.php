<?php

namespace App\Controllers;


use App\Models\Db;
use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 


const TIPOSIMG = array("image/png", "image/jpeg", "image/gif", "image/bmp", "image/svg");

class ControllerJuego {

    public function postNuevoJuego(Request $request, Response $response){
      
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);

            if (!isset($data['nombre']) || $data['nombre'] === "") {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'El nombre del juego es requerido']));
                return $response;
            }
            if (!isset($data['descripcion']) || mb_strlen($data['descripcion']) > 255  ) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La descripcion del juego es requerido y debe tener maximo 255 caracteres']));
                return $response;
            }
        
            $conex = new Db();
            $conex = $conex->getConnection();
            $generos = $conex->query("SELECT id FROM generos");
            $plataformas = $conex->query("SELECT id FROM plataformas");
            $generos = $generos->fetchAll(PDO::FETCH_COLUMN);
            $plataformas = $plataformas->fetchAll(PDO::FETCH_COLUMN);
            $conex= null;

            if (!isset($data['id_genero']) || !in_array($data['id_genero'],$generos,true)) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'El id del genero no es valido']));
                return $response;
            }
            if (!isset($data['id_plataforma']) || !in_array($data['id_plataforma'],$plataformas,true)) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'El id de la plataforma no es valido']));
                return $response;
            }

            if (!isset($data['imagen']) || $data['imagen'] === ""){  // RECIBO IMAGEN EN BASE64
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La imagen es obligatoria']));
                return $response;
            }
            if (!isset($data['tipo_imagen']) || (!in_array($data['tipo_imagen'],TIPOSIMG,true))){
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La imagen debe ser de un tipo valido']));
                return $response;
            }

            if (!isset($data['url']) || mb_strlen($data['url']) > 80  ) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La url del juego es requerido y debe tener maximo 80 caracteres']));
                return $response;
            }

            $conex = new Db();
            $conex = $conex->getConnection();
            $conex = $conex->prepare("INSERT INTO juegos (nombre,imagen,tipo_imagen,descripcion,pagina_url ,id_genero, id_plataforma) VALUES (:nombre,:imagen,:tipo_imagen,:descripcion, :pagina_url ,:id_genero, :id_plataforma)");
            $array_insert = [
                ":nombre" => $data['nombre'],
                ":imagen" => base64_decode($data['imagen']),
                ":tipo_imagen" => $data['tipo_imagen'],
                ":descripcion" => $data['descripcion'],
                ":pagina_url" => $data['url'],
                ":id_genero" => $data['id_genero'],
                ":id_plataforma" => $data['id_plataforma']
            ];
            $conex->execute($array_insert);
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

    public function putActualizarJuego(Request $request, Response $response, $args){
      
        $id = $args['id'];
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        $campos = "";
        $arrayValues = [];

        try {
            $conex = new Db();
            $conex = $conex->getConnection();

            $result = $conex->query("SELECT id FROM juegos WHERE id = $id");
            if ($result->rowCount() < 1){
                $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(404);
                $response->getBody()->write(json_encode(['error' => '404 Not Found: El id del juego no existe']));
            return $response;
            }

            if (isset($data['nombre'])) {
                if ($data['nombre'] === ""){
                    $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'El nombre del juego no debe estar vacio']));
                return $response;
                }else{
                    $campos.="nombre = :nombre,";
                    $arrayValues[':nombre'] = $data['nombre'];
                }
            }
            if (isset($data['descripcion'])) {
                if (mb_strlen($data['descripcion']) > 255){
                    $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La descripcion del juego no debe superar los 255 caracteres']));
                return $response;
                }else{
                    $campos.="descripcion = :descripcion,";
                    $arrayValues[':descripcion'] = $data['descripcion'];
                }
            }
        
        
            $conex = new Db();
            $conex = $conex->getConnection();
            $generos = $conex->query("SELECT id FROM generos");
            $plataformas = $conex->query("SELECT id FROM plataformas");
            $generos = $generos->fetchAll(PDO::FETCH_COLUMN);
            $plataformas = $plataformas->fetchAll(PDO::FETCH_COLUMN);
            $conex= null;

            if (isset($data['id_genero'])) {
                if (!in_array($data['id_genero'],$generos,true)){
                    $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'El genero del juego no es valido']));
                return $response;
                }else{
                    $campos.="id_genero = :id_genero,";
                    $arrayValues[':id_genero'] = $data['id_genero'];
                }
            }

            if (isset($data['id_plataforma'])) {
                if (!in_array($data['id_plataforma'],$plataformas,true)){
                    $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La plataforma del juego no es valido']));
                return $response;
                }else{
                    $campos.="id_plataforma = :id_plataforma,";
                    $arrayValues[':id_plataforma'] = $data['id_plataforma'];
                }
            }
           
            if (isset($data['imagen'])){   // RECIBO IMAGEN EN BASE64
                $campos.="imagen = :imagen,";
                $arrayValues[':imagen'] = base64_decode($data['imagen']);
            }
            if (isset($data['tipo_imagen'])) {
                if (!in_array($data['tipo_imagen'],TIPOSIMG,true)){
                    $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La imagen debe ser de un tipo valido']));
                return $response;
                }else{
                    $campos.="tipo_imagen = :tipo_imagen,";
                    $arrayValues[':tipo_imagen'] = $data['tipo_imagen'];
                }
            }
            if (isset($data['url'])) {
                if (mb_strlen($data['url']) > 80){
                    $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'La url del juego no debe superar los 80 caracteres']));
                return $response;
                }else{
                    $campos.="pagina_url = :url,";
                    $arrayValues[':url'] = $data['url'];
                }
            }
           
            $campos = substr($campos, 0, -1);   // BORRO LA ULTIMA COMA
            $conex = new Db();
            $conex = $conex->getConnection();
            $conex = $conex->prepare("UPDATE juegos SET $campos WHERE id = $id");
            
            $conex->execute($arrayValues);
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


    public function deleteEliminarJuego(Request $request, Response $response, $args)
    {
    
        $id = $args['id'];
    
        try {
            $conex = new Db();
            $conex = $conex->getConnection();
    
            $result = $conex->query("SELECT id FROM juegos WHERE id = $id");
            if ($result->rowCount() < 1) {
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(404);
                $response->getBody()->write(json_encode(['error' => '404 Not Found: El id del juego no existe']));
                return $response;
            }
    
    
            $conex = $conex->prepare("DELETE FROM juegos WHERE id = :id");
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

    public function getBuscarJuegos (Request $request, Response $response) { 

        $params = $request->getQueryParams();

        $query = "SELECT juegos.id, juegos.nombre, juegos.imagen, juegos.tipo_imagen, juegos.descripcion, juegos.pagina_url, generos.nombre AS nombre_genero, plataformas.nombre AS nombre_plataforma 
        FROM juegos
        JOIN generos ON juegos.id_genero = generos.id
        JOIN plataformas ON juegos.id_plataforma = plataformas.id";

        if (isset($params['nombre']) || isset($params['plataforma']) || isset($params['genero'])){
            $query.=" WHERE 1=1";
        }
        if (isset($params['nombre'])  && $params['nombre'] != "") {
            $nombre = filter_var($params['nombre'], FILTER_SANITIZE_STRING);
            $query .= " AND juegos.nombre LIKE '%$nombre%'";
        }
        if(isset($params['plataforma'])  && $params['plataforma'] != ""){
            $plataforma = filter_var($params['plataforma'],FILTER_SANITIZE_STRING);
            $query.=" AND juegos.id_plataforma = $plataforma";
        }
        if(isset($params['genero'])  && $params['genero'] != ""){
            $genero = filter_var($params['genero'],FILTER_SANITIZE_STRING);
            $query.=" AND juegos.id_genero = $genero";
        }
        if (isset($params['orden'])  && $params['orden'] != ""){
            if ($params['orden'] != "ASC" && $params['orden'] != "DESC"){
                $response = $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
                $response->getBody()->write(json_encode(['error' => 'El orden del juego no es valido']));
                return $response;
            }
        }else{
            $params['orden'] = "ASC";
        }
        $orden = $params['orden'];
        $query.= " ORDER BY juegos.nombre $orden";
        try{
            $conex = new Db();
            $conex = $conex->getConnection();
            $conex = $conex->query($query);
            $juegos = $conex->fetchAll(PDO::FETCH_ASSOC);
            foreach($juegos as &$juego){
                $juego['imagen'] = base64_encode($juego['imagen']); 
            }
            $conex = null;
           
            $response->getBody()->write(json_encode($juegos));
            $response = $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
            return $response;

        }catch (PDOException $e) {
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