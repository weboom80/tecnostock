<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

// Capturar término de búsqueda si existe
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Consulta SQL con búsqueda opcional
$sql = "SELECT p.*, c.nombre AS categoria_nombre 
        FROM producto p 
        INNER JOIN categoria c ON p.id_categoria = c.id_categoria 
        WHERE p.estado = 1";

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE :busqueda OR p.código LIKE :busqueda OR c.nombre LIKE :busqueda)";
}

$sql .= " ORDER BY p.id_producto DESC";

$stmt = $pdo->prepare($sql);

if (!empty($busqueda)) {
    $stmt->execute(['busqueda'=>"%$busqueda%"]);
} else {
    $stmt->execute();
}

$productos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TecnoStock - Control de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">TecnoStock</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Encabezado y Acciones principales -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Catálogo de Productos</h2>
            <div>
                <a href="nuevo_producto.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Registrar Producto</a>
                <a href="registrar_movimiento.php" class="btn btn-primary"><i class="bi bi-arrow-down-up"></i> Entradas / Salidas</a>
                <a href="historial_movimientos.php" class="btn btn-info text-white"><i class="bi bi-clock-history"></i> Ver Historial</a>
                <a href="productos_inactivos.php" class="btn btn-outline-secondary"><i class="bi bi-archive"></i> Ver Inactivos</a>
            </div>
        </div>

        <!-- Buscador -->
        <form method="GET" action="index.php" class="row g-2 mb-4">
            <div class="col-md-10">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por código, nombre o categoría..." value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Buscar</button>
            </div>
        </form>

        <!-- Tabla de Productos -->
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
                                <th>Stock Mínimo</th>
                                <th>Estado Stock</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron productos registrados.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos as $prod): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($prod['código']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($prod['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($prod['categoria_nombre']); ?></td>
                                        <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                                        <td><?php echo $prod['stock_actual']; ?></td>
                                        <td><?php echo $prod['stock_mínimo']; ?></td>
                                        <td>
                                            <!-- Regla de Negocio: Alerta visual de Bajo Stock -->
                                            <?php if ($prod['stock_actual'] <= $prod['stock_mínimo']): ?>
                                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> ¡Bajo Stock!</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="editar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil"></i> Editar</a>
                                            <a href="desactivar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas desactivar este producto?');"><i class="bi bi-trash"></i> Desactivar</a>
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