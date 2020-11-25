<?php
// procedural mysqli connection

function connectDB(){
    try {
        $connection = new PDO(DB_TYPE.":host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET.";port=".DB_PORT, DB_USER, DB_PWD);
        $connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        if ($connection){
            return $connection;
        }
    }catch(PDOException $e){
        $error = $e->getCode();
        $error .= " : ";
        $error .= $e->getMessage();
        die($error);
    }
}