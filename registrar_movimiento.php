<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

//cargar productos activos para mostrarlos en el selector
$stmt = $pdo->query("SELECT id_producto, código, nombre, stock_actual FROM producto WHERE estado = 1 ORDER BY nombre ASC");
$productos = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $tipo = trim($_POST['tipo']); 
    $cantidad = intval($_POST['cantidad']);
    $observacion = trim($_POST['observacion']);

    if (empty($id_producto) || empty($tipo) || $cantidad <= 0) {
        $error = 'Por favor, completa todos los campos obligatorios y asegúrate de que la cantidad sea mayor a 0.';
    } else {
        //obtener el stock actual del producto seleccionado
        $stmt_prod = $pdo->prepare("SELECT stock_actual, nombre FROM producto WHERE id_producto = :id");
        $stmt_prod->execute(['id' => $id_producto]);
        $prod = $stmt_prod->fetch();

        if (!$prod) {
            $error = 'El producto seleccionado no es válido.';
        } else {
            $stock_actual = intval($prod['stock_actual']);
            $nuevo_stock = $stock_actual;

            if ($tipo === 'entrada') {
                $nuevo_stock += $cantidad;
            } elseif ($tipo === 'salida') {
                // Regla de Negocio: Una salida no puede dejar el stock bajo cero[cite: 1]
                if ($cantidad > $stock_actual) {
                    $error = "Stock insuficiente. El stock actual de '{$prod['nombre']}' es {$stock_actual} y la salida solicitada es de {$cantidad}.";
                } else {
                    $nuevo_stock -= $cantidad;
                }
            } else {
                $error = 'Tipo de movimiento no válido.';
            }

            if (empty($error)) {
                try {
                    //uso una transaccion para garantizar que ambos cambios ocurran correctamente
                    $pdo->beginTransaction();

                    //1registrar el movimiento (regla de negocio: fecha,tipo,cantidad,producto y usuario)
                    $sql_mov = "INSERT INTO movimiento (tipo, cantidad, observación, id_producto, id_usuario) 
                                VALUES (:tipo, :cantidad, :observacion, :id_producto, :id_usuario)";
                    $stmt_mov = $pdo->prepare($sql_mov);
                    $stmt_mov->execute([
                        'tipo' => $tipo,
                        'cantidad' => $cantidad,
                        'observacion' => $observacion,
                        'id_producto' => $id_producto,
                        'id_usuario' => $_SESSION['id_usuario']
                    ]);

                    //2actualizar automaticamente el stock del producto
                    $sql_upd = "UPDATE producto SET stock_actual = :nuevo_stock WHERE id_producto = :id_producto";
                    $stmt_upd = $pdo->prepare($sql_upd);
                    $stmt_upd->execute([
                        'nuevo_stock' => $nuevo_stock,
                        'id_producto' => $id_producto
                    ]);

                    $pdo->commit();
                    header('Location: index.php');
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Error al procesar el movimiento: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Movimiento - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Registrar Entrada o Salida</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="registrar_movimiento.php" data-validar="movimiento" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Producto *</label>
                        <select name="id_producto" class="form-select" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?php echo $p['id_producto']; ?>" data-stock="<?php echo $p['stock_actual']; ?>">
                                    <?php echo htmlspecialchars($p['código'] . ' - ' . $p['nombre'] . ' (Stock actual: ' . $p['stock_actual'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Movimiento *</label>
                        <select name="tipo" class="form-select" required>
                            <option value="">Seleccione tipo</option>
                            <option value="entrada">Entrada (Aumenta stock)</option>
                            <option value="salida">Salida (Reduce stock)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cantidad *</label>
                        <input type="number" name="cantidad" class="form-control" min="1" required>
                        <div class="form-text" id="stock-disponible"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observación / Motivo</label>
                        <textarea name="observacion" class="form-control" rows="2" placeholder="Ej: Compra a proveedor o venta a cliente"></textarea>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/validaciones.js"></script>
</body>
</html>