<?php
// dashboard.php
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: index.html');
  exit;
}

require_once 'conexion.php';

$buscar = trim($_GET['buscar'] ?? '');

$sql = 'SELECT
          p.id_producto,
          p.codigo_producto,
          p.nom_producto,
          c.nom_categoria,
          p.precio_producto,
          p.stock_actual_prod,
          p.stock_minimo_prod
        FROM producto p
        INNER JOIN categoria c ON c.id_categoria = p.id_categoria
        WHERE p.est_producto = 1';

$parametros = [];

if ($buscar !== '') {
  $sql .= ' AND (p.nom_producto LIKE :buscar
             OR p.codigo_producto LIKE :buscar2
             OR c.nom_categoria LIKE :buscar3)';
  $parametros[':buscar'] = '%' . $buscar . '%';
  $parametros[':buscar2'] = '%' . $buscar . '%';
  $parametros[':buscar3'] = '%' . $buscar . '%';
}

$sql .= ' ORDER BY p.id_producto DESC';

$sentencia = $pdo->prepare($sql);
$sentencia->execute($parametros);
$productos = $sentencia->fetchAll();

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TecnoStock - Productos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">Productos</h1>
      <div>
        <span class="text-secondary me-3">
          Hola, <?= htmlspecialchars($_SESSION['nom_usuario'], ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Cerrar sesión</a>
      </div>
    </div>

    <?php
$mensajes = [
  'creado' => 'Producto creado correctamente.',
  'actualizado' => 'Producto actualizado correctamente.',
  'movimiento' => 'Movimiento registrado correctamente.',
  'desactivado' => 'Producto desactivado correctamente.',
];
foreach ($mensajes as $clave => $texto):
  if (isset($_GET[$clave])):
?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php
  endif;
endforeach;
?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <a href="crear_producto.php" class="btn btn-primary">+ Nuevo producto</a>

  <form action="dashboard.php" method="get" class="d-flex gap-2">
    <input type="text"
           class="form-control"
           name="buscar"
           placeholder="Buscar por nombre, código o categoría..."
           value="<?= htmlspecialchars($buscar, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-outline-primary">Buscar</button>
    <?php if ($buscar !== ''): ?>
      <a href="dashboard.php" class="btn btn-outline-secondary">Limpiar</a>
    <?php endif; ?>
  </form>
</div>

    <div class="card shadow-sm">
      <div class="card-body">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Código</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $producto): ?>
              <?php $stockBajo = $producto['stock_actual_prod'] < $producto['stock_minimo_prod']; ?>
              <tr class="<?= $stockBajo ? 'table-danger' : '' ?>">
                <td><?= htmlspecialchars($producto['codigo_producto'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($producto['nom_producto'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($producto['nom_categoria'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>$<?= number_format((float) $producto['precio_producto'], 0, ',', '.') ?></td>
                <td><?= (int) $producto['stock_actual_prod'] ?></td>
                <td>
                  <?php if ($stockBajo): ?>
                    <span class="badge bg-danger">Stock bajo</span>
                  <?php else: ?>
                    <span class="badge bg-success">Disponible</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="editar_producto.php?id=<?= (int) $producto['id_producto'] ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                  <a href="movimiento.php?id=<?= (int) $producto['id_producto'] ?>" class="btn btn-sm btn-outline-secondary">Movimiento</a>
                  <form action="desactivar_producto.php" method="post" class="d-inline"
      onsubmit="return confirm('¿Seguro que deseas desactivar este producto?');">
  <input type="hidden" name="id" value="<?= (int) $producto['id_producto'] ?>">
  <button type="submit" class="btn btn-sm btn-outline-danger">Desactivar</button>
</form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>