<?php
// procesar_login.php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit('Método no permitido.');
}

$correo = trim($_POST['correo'] ?? '');
$clave = $_POST['clave'] ?? '';

if ($correo === '' || $clave === '') {
  exit('Debe ingresar correo y contraseña. <a href="index.html">Volver</a>');
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
  exit('El formato del correo no es válido. <a href="index.html">Volver</a>');
}

$sql = 'SELECT id_usuario, nom_usuario, clave_usuario, estado
        FROM usuario
        WHERE email_usuario = :correo';

$sentencia = $pdo->prepare($sql);
$sentencia->execute([':correo' => $correo]);
$usuario = $sentencia->fetch();

if (
  !$usuario
  || $usuario['estado'] != 1
  || !password_verify($clave, $usuario['clave_usuario'])
) {
  exit('Correo o contraseña incorrectos. <a href="index.html">Volver</a>');
}

$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nom_usuario'] = $usuario['nom_usuario'];

header('Location: dashboard.php');
exit;