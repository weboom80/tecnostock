<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

//consulta solo los productos desactivados
$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM producto p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria 
        WHERE p.estado = 0 
        ORDER BY p.id_producto DESC";

$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos Inactivos - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-archive"></i> Productos Inactivos (Desactivados)</h2>
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver al Catálogo</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock Actual</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No hay productos inactivos en este momento.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($prod['código']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($prod['categoria_nombre']); ?></td>
                                        <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                                        <td><?php echo $prod['stock_actual']; ?></td>
                                        <td class="text-center">
                                            <a href="activar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Reactivar</a>
                                        </td>
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