<?php

session_start();

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    header('Location: login.php?error=1');
    exit;
}

$usuario = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
    header('Location: login.php?error=1');
    exit;
}

$sql = "SELECT * FROM usuarios WHERE usuario = :usuario AND activo = 1 LIMIT 1";
$consulta = $conexion->prepare($sql);
$consulta->bindParam(':usuario', $usuario);
$consulta->execute();

$admin = $consulta->fetch();

if (!$admin) {
    header('Location: login.php?error=1');
    exit;
}

if (!password_verify($password, $admin['password'])) {
    header('Location: login.php?error=1');
    exit;
}

session_regenerate_id(true);

$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_usuario'] = $admin['usuario'];
$_SESSION['admin_rol'] = $admin['rol'];

header('Location: admin.php');
exit;