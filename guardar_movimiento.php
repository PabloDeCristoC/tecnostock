<?php
// guardar_movimiento.php
session_start();

if (!isset($_SESSION['id_usuario'])) {
  header('Location: index.html');
  exit;
}

require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido.');
}

$idProducto = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
$tipo = trim($_POST['tipo'] ?? '');
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
$observacion = trim($_POST['observacion'] ?? '');
$idUsuario = $_SESSION['id_usuario'];

$errores = [];

if ($idProducto === false || $idProducto === null || $idProducto < 1) {
  $errores[] = 'Producto inválido.';
}
if (!in_array($tipo, ['entrada', 'salida'], true)) {
  $errores[] = 'Tipo de movimiento inválido.';
}
if ($cantidad === false || $cantidad === null || $cantidad < 1) {
  $errores[] = 'Cantidad inválida.';
}

if ($errores) {
  http_response_code(422);
  echo '<h1>Revise los datos</h1><ul>';
  foreach ($errores as $error) {
    echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
  }
  echo '</ul><a href="dashboard.php">Volver</a>';
  exit;
}

try {
  $pdo->beginTransaction();

  $sqlProducto = 'SELECT stock_actual_prod FROM producto WHERE id_producto = :id FOR UPDATE';
  $sentenciaProducto = $pdo->prepare($sqlProducto);
  $sentenciaProducto->execute([':id' => $idProducto]);
  $producto = $sentenciaProducto->fetch();

  if (!$producto) {
    throw new Exception('Producto no encontrado.');
  }

  $stockActual = (int) $producto['stock_actual_prod'];
  $nuevoStock = $tipo === 'entrada'
    ? $stockActual + $cantidad
    : $stockActual - $cantidad;

  if ($nuevoStock < 0) {
    throw new Exception('Stock insuficiente para esta salida.');
  }

  $sqlUpdate = 'UPDATE producto SET stock_actual_prod = :stock WHERE id_producto = :id';
  $sentenciaUpdate = $pdo->prepare($sqlUpdate);
  $sentenciaUpdate->execute([':stock' => $nuevoStock, ':id' => $idProducto]);

  $sqlInsert = 'INSERT INTO movimiento
                  (tipo_movimiento, cant_movimiento, obs_movimiento, id_producto, id_usuario)
                VALUES
                  (:tipo, :cantidad, :observacion, :id_producto, :id_usuario)';
  $sentenciaInsert = $pdo->prepare($sqlInsert);
  $sentenciaInsert->execute([
    ':tipo' => $tipo,
    ':cantidad' => $cantidad,
    ':observacion' => $observacion !== '' ? $observacion : null,
    ':id_producto' => $idProducto,
    ':id_usuario' => $idUsuario,
  ]);

  $pdo->commit();
  header('Location: dashboard.php?movimiento=1');
  exit;
} catch (Exception $error) {
  $pdo->rollBack();
  exit(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8') . ' <a href="dashboard.php">Volver</a>');
}