<?php
// editar_producto.php
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: index.html');
  exit;
}

require_once 'conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
  exit('Producto inválido. <a href="dashboard.php">Volver</a>');
}

$sql = 'SELECT * FROM producto WHERE id_producto = :id';
$sentencia = $pdo->prepare($sql);
$sentencia->execute([':id' => $id]);
$producto = $sentencia->fetch();

if (!$producto) {
  exit('Producto no encontrado. <a href="dashboard.php">Volver</a>');
}

$categorias = $pdo->query('SELECT id_categoria, nom_categoria FROM categoria WHERE est_categoria = 1 ORDER BY nom_categoria')->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Editar producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <main class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 760px;">
      <div class="card-body p-4">
        <h1 class="h3 mb-3">Editar producto</h1>

        <form action="actualizar_producto.php" method="post">
          <input type="hidden" name="id" value="<?= (int) $producto['id_producto'] ?>">
          <div class="row g-3">
            <div class="col-md-4">
              <label for="codigo" class="form-label">Código</label>
              <input type="text" class="form-control" id="codigo" name="codigo"
                     maxlength="20" pattern="[A-Za-z0-9-]+"
                     value="<?= htmlspecialchars($producto['codigo_producto'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-md-8">
              <label for="nombre" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="nombre" name="nombre"
                     minlength="3" maxlength="100"
                     value="<?= htmlspecialchars($producto['nom_producto'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="col-md-6">
              <label for="categoria_id" class="form-label">Categoría</label>
              <select class="form-select" id="categoria_id" name="categoria_id" required>
                <?php foreach ($categorias as $categoria): ?>
                  <option value="<?= (int) $categoria['id_categoria'] ?>"
                    <?= $categoria['id_categoria'] == $producto['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($categoria['nom_categoria'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="precio" class="form-label">Precio</label>
              <input type="number" class="form-control" id="precio" name="precio"
                     min="0" step="0.01" value="<?= (float) $producto['precio_producto'] ?>" required>
            </div>
            <div class="col-md-6">
              <label for="stock_actual" class="form-label">Stock actual</label>
              <input type="number" class="form-control" id="stock_actual" name="stock_actual"
                     min="0" step="1" value="<?= (int) $producto['stock_actual_prod'] ?>" required>
            </div>
            <div class="col-md-6">
              <label for="stock_minimo" class="form-label">Stock mínimo</label>
              <input type="number" class="form-control" id="stock_minimo" name="stock_minimo"
                     min="0" step="1" value="<?= (int) $producto['stock_minimo_prod'] ?>" required>
            </div>
            <div class="col-12">
              <label for="descripcion" class="form-label">Descripción</label>
              <textarea class="form-control" id="descripcion" name="descripcion"
                        rows="3" maxlength="255"><?= htmlspecialchars($producto['desc_producto'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="col-12 d-flex gap-2">
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
              <a href="dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </main>
</body>
</html>