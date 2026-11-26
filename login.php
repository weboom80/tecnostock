<?php
session_start();
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    if (!empty($correo) && !empty($clave)) {
        //consulta preparada usando pdo
        $sql = "SELECT * FROM usuario WHERE correo = :correo AND estado = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch();

        //verificamos si el usuario existe y la clave coincide
        if ($usuario && $clave === $usuario['clave']) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header('Location: index.php'); //redirige panel principal
            exit;
        } else {
            $error = 'Correo o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor, completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - TecnoStock</title>
    <!-- Bootstrap CSS para una interfaz limpia sin diseñar CSS desde cero -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center mb-4">TecnoStock</h3>
        <h5 class="text-center text-muted mb-4 fs-6">Control de Inventario</h5>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" data-validar="login" novalidate>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
    <script src="js/validaciones.js"></script>
</body>
</html>