<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

require_once 'conexion.php';

//carga categorias activas para el select
$stmt = $pdo->query("SELECT * FROM categoria WHERE estado = 1 ORDER BY nombre ASC");
$categorias = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $stock_actual = intval($_POST['stock_actual']);
    $stock_minimo = intval($_POST['stock_minimo']);
    $id_categoria = intval($_POST['id_categoria']);

    //validaciones de servidor(PHP) segun reglas de negocio
    if (empty($codigo) || empty($nombre) || empty($id_categoria)) {
        $error = 'Por favor, completa todos los campos obligatorios.';
    } elseif ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0) {
        $error = 'Los valores numéricos (precio y stock) no pueden ser negativos.';
    } else {
        //verificar si el codigo ya existe
        $stmt_check = $pdo->prepare("SELECT id_producto FROM producto WHERE código = :codigo");
        $stmt_check->execute(['codigo' => $codigo]);

        if ($stmt_check->fetch()) {
            $error = 'El código de producto ya existe. Por favor utiliza uno diferente.';
        } else {
            //insertar el nuevo producto mediante consulta pre
            $sql = "INSERT INTO producto (código, nombre, descripción, precio, stock_actual, stock_mínimo, id_categoria, estado) 
                    VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, :id_categoria, 1)";
            $stmt_insert = $pdo->prepare($sql);
            $stmt_insert->execute([
                'codigo' => $codigo,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'stock_actual' => $stock_actual,
                'stock_minimo' => $stock_minimo,
                'id_categoria' => $id_categoria
            ]);

            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Registrar Nuevo Producto</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" action="nuevo_producto.php" data-validar="producto" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Código del Producto *</label>
                        <input type="text" name="codigo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Producto *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría *</label>
                        <select name="id_categoria" class="form-select" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" name="precio" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Inicial *</label>
                            <input type="number" name="stock_actual" class="form-control" min="0" value="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stock Mínimo *</label>
                            <input type="number" name="stock_minimo" class="form-control" min="0" value="5" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="js/validaciones.js"></script>
</body>
</html>