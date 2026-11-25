<?php
$host='localhost';
$dbname='tecnostock';
$username='root';
$password=''; //por defecto en xampp viene vacia

try {
    //conexion segura mediante pdo
    $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$username,$password);
    
// configurar Pdo para que lance excepciones en caso de fallo
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>