<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datos de conexión local
$host = "localhost";
$user = "root";
$pass = "";
$db   = "control_asistencia";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// RADAR DE PRIMER ARRANQUE Y CARGA DE CONFIGURACIÓN
$archivo_actual = basename($_SERVER['PHP_SELF']);

if ($archivo_actual != 'setup.php') {
    // 1. Verificación de Administrador existente
    $check_admin = $conexion->query("SELECT id FROM usuarios WHERE rol LIKE '%administrador%' LIMIT 1");
    if ($check_admin->num_rows == 0) {
        header("Location: setup.php");
        exit();
    }

    // 2. CARGA DE NOMBRE Y LOGO (NUEVO)
    if (!isset($_SESSION['inst_nombre']) || !isset($_SESSION['inst_logo'])) {
        $query_inst = $conexion->query("SELECT nombre_institucion, logo_institucion FROM configuracion_sistema LIMIT 1");
        if ($query_inst && $query_inst->num_rows > 0) {
            $data_inst = $query_inst->fetch_assoc();
            $_SESSION['inst_nombre'] = $data_inst['nombre_institucion'];
            $_SESSION['inst_logo']   = !empty($data_inst['logo_institucion']) ? $data_inst['logo_institucion'] : 'img/logo_predeterminado.png';
        } else {
            $_SESSION['inst_nombre'] = "E.T.C. Dr. Rafael Loreto Loreto";
            $_SESSION['inst_logo']   = "img/logo_predeterminado.png";
        }
    }
}
// --- PIE DE PÁGINA OFICIAL E INDELEBLE DE S.I.C.A.L ---
register_shutdown_function(function() {
    $archivo = basename($_SERVER['PHP_SELF']);
    if ($archivo != 'setup.php') {
        // Si es el index, usamos un estilo fijo abajo para que no se cuadre a la derecha
        $es_index = ($archivo == 'index.php');
        $estilo_extra = $es_index ? 'position: fixed; bottom: 0; left: 0; z-index: 9999;' : '';

        echo '
        <footer style="width: 100%; text-align: center; padding: 12px 0; background-color: #f8f9fa; border-top: 1px solid #e0e0e0; clear: both; box-sizing: border-box; ' . $estilo_extra . '">
            <small style="color: #6c757d; font-family: Arial, sans-serif; font-size: 0.82rem; letter-spacing: 0.5px; display: block;">
                S.I.C.A.L VER 1.0, Diseñado por Prof. Carlos A. Blanco R, para soporte escribir al WhatsApp 0424-342-8254
            </small>
        </footer>';
    }
});
?>
