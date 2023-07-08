<?php

namespace App\Models;
use PDO;

class Db {
     private $host = "localhost";
     private $username = "root";
     private $password = "";
     private $database = "juegos_online";
     private $conexion;     
     
     public function __construct() {

        $pdo = new PDO('mysql:host='.$this->host.';dbname='.$this->database.';', $this->username, $this->password);


        $this->conexion = $pdo;
     }

     public function getConnection() {
         return $this->conexion;
     }

 }