<?php
// actualizar_producto.php
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

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$codigo = strtoupper(trim($_POST['codigo'] ?? ''));
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoriaId = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
$precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
$stockActual = filter_input(INPUT_POST, 'stock_actual', FILTER_VALIDATE_INT);
$stockMinimo = filter_input(INPUT_POST, 'stock_minimo', FILTER_VALIDATE_INT);

$errores = [];

if ($id === false || $id === null || $id < 1) {
  $errores[] = 'Producto inválido.';
}
if ($codigo === '' || !preg_match('/^[A-Z0-9-]+$/', $codigo)) {
  $errores[] = 'Código obligatorio o inválido.';
}
if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
  $errores[] = 'Nombre entre 3 y 100 caracteres.';
}
if ($categoriaId === false || $categoriaId === null || $categoriaId < 1) {
  $errores[] = 'Categoría inválida.';
}
if ($precio === false || $precio === null || $precio < 0) {
  $errores[] = 'Precio inválido.';
}
if ($stockActual === false || $stockActual === null || $stockActual < 0) {
  $errores[] = 'Stock actual inválido.';
}
if ($stockMinimo === false || $stockMinimo === null || $stockMinimo < 0) {
  $errores[] = 'Stock mínimo inválido.';
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

$sql = 'UPDATE producto SET
          codigo_producto = :codigo,
          nom_producto = :nombre,
          desc_producto = :descripcion,
          precio_producto = :precio,
          stock_actual_prod = :stock_actual,
          stock_minimo_prod = :stock_minimo,
          id_categoria = :id_categoria
        WHERE id_producto = :id';

try {
  $sentencia = $pdo->prepare($sql);
  $sentencia->execute([
    ':codigo' => $codigo,
    ':nombre' => $nombre,
    ':descripcion' => $descripcion !== '' ? $descripcion : null,
    ':precio' => $precio,
    ':stock_actual' => $stockActual,
    ':stock_minimo' => $stockMinimo,
    ':id_categoria' => $categoriaId,
    ':id' => $id,
  ]);
  header('Location: dashboard.php?actualizado=1');
  exit;
} catch (PDOException $error) {
  if ($error->getCode() === '23000') {
    exit('El código ya existe en otro producto. <a href="dashboard.php">Volver</a>');
  }
  exit('No fue posible actualizar. <a href="dashboard.php">Volver</a>');
}