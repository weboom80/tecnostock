<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

if (isset($_GET['id'])) {
    $id_producto=intval($_GET['id']);

 //cambiar el estado de vuelta a1
    $sql="UPDATE producto SET estado=1 WHERE id_producto=:id";
    $stmt=$pdo->prepare($sql);
    $stmt->execute(['id'=>$id_producto]);
}

header('Location: productos_inactivos.php');
exit;