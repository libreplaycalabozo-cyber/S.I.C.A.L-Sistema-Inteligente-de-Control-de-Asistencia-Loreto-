<?php
include('conexion.php');
// Destruir todas las variables de la sesión activa
$_SESSION = array();
session_destroy();

// Redireccionar a la pantalla de inicio del colegio
header("Location: index.php");
exit();
?>
