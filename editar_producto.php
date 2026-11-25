<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

$error='';
$id_producto=isset($_GET['id']) ? intval($_GET['id']) : 0;

//obtener los datos del producto en el presente
$stmt=$pdo->prepare("SELECT * FROM producto WHERE id_producto=:id");
$stmt->execute(['id'=>$id_producto]);
$producto=$stmt->fetch();

if (!$producto) {
    header('Location: index.php');
    exit;
}

//cargar categorias activas
$stmt_cat=$pdo->query("SELECT * FROM categoria WHERE estado = 1 ORDER BY nombre ASC");
$categorias=$stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $id_categoria = intval($_POST['id_categoria']);

    if (empty($nombre) || empty($id_categoria)) {
        $error = 'Por favor, completa los campos obligatorios.';
    } elseif ($precio < 0 || $stock_minimo < 0) {
        $error = 'El precio y el stock mínimo no pueden ser negativos.';
    } else {
        //actualizar los datos del producto x consulta
        $sql = "UPDATE producto 
                SET nombre = :nombre, descripción = :descripcion, precio = :precio, stock_mínimo = :stock_minimo, id_categoria = :id_categoria 
                WHERE id_producto = :id";
        $stmt_upd = $pdo->prepare($sql);
        $stmt_upd->execute([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock_minimo' => $stock_minimo,
            'id_categoria' => $id_categoria,
            'id' => $id_producto
        ]);

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Editar Producto</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="editar_producto.php?id=<?php echo $id_producto; ?>">
                    <div class="mb-3">
                        <label class="form-label">Código (No modificable)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($producto['código']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría *</label>
                        <select name="id_categoria" class="form-select" required>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $producto['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"><?php echo htmlspecialchars($producto['descripción']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" name="precio" class="form-control" min="0" value="<?php echo $producto['precio']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Mínimo *</label>
                            <input type="number" name="stock_minimo" class="form-control" min="0" value="<?php echo $producto['stock_mínimo']; ?>" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning text-dark">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>