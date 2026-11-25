<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

//consulta para obtener el historial uniendo las tablas movimiento producto y usuario
$sql = "SELECT m.fecha_hora, m.tipo, m.cantidad, m.observación, 
               p.código AS codigo_producto, p.nombre AS nombre_producto, 
               u.nombre AS nombre_usuario
        FROM movimiento m
        INNER JOIN producto p ON m.id_producto = p.id_producto
        INNER JOIN usuario u ON m.id_usuario = u.id_usuario
        ORDER BY m.fecha_hora DESC";

$stmt = $pdo->query($sql);
$movimientos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Movimientos - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-clock-history"></i> Historial de Entradas y Salidas</h2>
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver al Catálogo</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Tipo</th>
                                <th>Código Producto</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Observación</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($movimientos)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No hay movimientos registrados todavía.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr>
                                        <td><?php echo $mov['fecha_hora']; ?></td>
                                        <td>
                                            <?php if ($mov['tipo'] === 'entrada'): ?>
                                                <span class="badge bg-success">Entrada</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Salida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($mov['codigo_producto']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($mov['nombre_producto']); ?></strong></td>
                                        <td><?php echo $mov['cantidad']; ?></td>
                                        <td><?php echo htmlspecialchars($mov['observación'] ?: 'Sin observaciones'); ?></td>
                                        <td><?php echo htmlspecialchars($mov['nombre_usuario']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>