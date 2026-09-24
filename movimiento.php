<?php
// movimiento.php
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

$sql = 'SELECT id_producto, codigo_producto, nom_producto, stock_actual_prod
        FROM producto
        WHERE id_producto = :id AND est_producto = 1';
$sentencia = $pdo->prepare($sql);
$sentencia->execute([':id' => $id]);
$producto = $sentencia->fetch();

if (!$producto) {
  exit('Producto no encontrado. <a href="dashboard.php">Volver</a>');
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar movimiento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <main class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
      <div class="card-body p-4">
        <h1 class="h3 mb-1">Registrar movimiento</h1>
        <p class="text-secondary">
          <?= htmlspecialchars($producto['codigo_producto'], ENT_QUOTES, 'UTF-8') ?> —
          <?= htmlspecialchars($producto['nom_producto'], ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p>Stock actual: <strong><?= (int) $producto['stock_actual_prod'] ?></strong></p>

        <form action="guardar_movimiento.php" method="post" onsubmit="deshabilitarBoton(this)">
          <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">

          <div class="mb-3">
            <label for="tipo" class="form-label">Tipo de movimiento</label>
            <select class="form-select" id="tipo" name="tipo" required>
              <option value="">Seleccione...</option>
              <option value="entrada">Entrada</option>
              <option value="salida">Salida</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad"
                   min="1" step="1" required>
          </div>

          <div class="mb-3">
            <label for="observacion" class="form-label">Observación</label>
            <textarea class="form-control" id="observacion" name="observacion"
                      rows="2" maxlength="255"></textarea>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Registrar movimiento</button>
            <a href="dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </main>
    <script>
    function deshabilitarBoton(formulario) {
      const boton = formulario.querySelector('button[type="submit"]');
      boton.disabled = true;
      boton.textContent = 'Guardando...';
    }
  </script>
</body>
</html>