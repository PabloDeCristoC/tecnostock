<?php
// desactivar_producto.php
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

if ($id === false || $id === null || $id < 1) {
  exit('Producto inválido. <a href="dashboard.php">Volver</a>');
}

$sql = 'UPDATE producto SET est_producto = 0 WHERE id_producto = :id';
$sentencia = $pdo->prepare($sql);
$sentencia->execute([':id' => $id]);

header('Location: dashboard.php?desactivado=1');
exit;