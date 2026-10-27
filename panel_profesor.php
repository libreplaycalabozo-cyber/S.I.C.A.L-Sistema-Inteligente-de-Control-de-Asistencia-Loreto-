<?php
include('conexion.php'); 
date_default_timezone_set('America/Caracas');

$fecha_hoy = date('Y-m-d');

// Traducir el día actual al español para la Base de Datos
$dias_ingles = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
$dias_espanol = array('lunes', 'martes', 'miercoles', 'jueves', 'viernes');
$dia_actual_semana = str_replace($dias_ingles, $dias_espanol, date('l'));

if (!isset($_SESSION['profesor_id']) || $_SESSION['fecha_sesion'] != $fecha_hoy) {
    $_SESSION = array();
    session_destroy();
    echo "<script>alert('Su sesión ha expirado por cambio de día. Debe reportar su llegada laboral de hoy.'); window.location.href = 'index.php';</script>";
    exit();
}

$profesor_id = $_SESSION['profesor_id'];
$nombre_profesor = $_SESSION['nombre_profesor'];

// Filtramos la consulta por el día actual de la semana
$consulta_clases = "SELECT ap.id AS asignacion_id, m.nombre_materia, s.nombre_seccion, ap.hora_inicio, ap.hora_fin 
 FROM asignaciones_profesor ap
 INNER JOIN materias m ON ap.materia_id = m.id
 INNER JOIN secciones s ON ap.seccion_id = s.id
 WHERE ap.profesor_id = '$profesor_id' AND ap.dia_semana = '$dia_actual_semana'
 ORDER BY ap.hora_inicio ASC";

// CONTROL DE ERRORES: Si la base de datos está vacía, evitamos que el sistema se rompa
$tiene_clases = false;
$resultado_clases = null;

try {
    $resultado_clases = $conexion->query($consulta_clases);
    if ($resultado_clases && $resultado_clases->num_rows > 0) {
        $tiene_clases = true;
    }
} catch (mysqli_sql_exception $e) {
    // Si la tabla o columna no existen todavía, dejamos el sistema en "sin clases" limpiamente
    $tiene_clases = false; 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Portal Docente</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.15); }
        nav strong { font-size: 0.9rem; color: #eaecf4; }
        nav a { color: #f8f9fa; text-decoration: none; font-size: 0.9rem; font-weight: bold; }
        .titulo-nav-3d { font-size: 1.5rem; font-weight: 900; color: #4e73df; text-transform: uppercase; letter-spacing: 1px; text-shadow: 1px 1px 0px #0c2461, 2px 2px 4px rgba(0,0,0,0.4); margin-right: 10px; }
        .contenedor { max-width: 600px; margin: auto; padding: 20px; box-sizing: border-box; }
        h4 { color: #333; margin-top: 10px; margin-bottom: 20px; font-size: 1.3rem; }
        .tarjeta-bloque { background: white; padding: 20px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #e3e6f0; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); box-sizing: border-box; }
        .info-clase h5 { margin: 0 0 6px 0; color: #212529; font-size: 1.2rem; }
        .info-clase span { color: #4e73df; font-weight: 600; font-size: 0.95rem; display: block; margin-bottom: 8px; }
        .hora-badge { background: #eaecf4; padding: 6px 12px; border-radius: 6px; font-weight: bold; font-size: 0.8rem; color: #495057; display: inline-block; }
        .btn-accion { padding: 12px 16px; border-radius: 8px; font-weight: bold; border: none; cursor: pointer; font-size: 0.9rem; text-transform: uppercase; text-decoration: none; display: inline-block; text-align: center; }
        .btn-azul { background-color: #4e73df; color: white; }
        .btn-gris { background-color: #858796; color: white; cursor: not-allowed; }
    </style>
</head>
<body>

    <nav>
        <div style="display: flex; align-items: center;">
            <span class="titulo-nav-3d">S.I.C.A.L</span>
            <strong style="border-left: 1px solid #6e707e; padding-left: 12px;">Prof. <?php echo $nombre_profesor; ?></strong>
        </div>
        <a href="logout.php">SALIR</a>
    </nav>

    <div class="contenedor">
        <h4>📅 Sus Actividades para hoy (<?php echo strtoupper($dia_actual_semana); ?>)</h4>

        <?php 
        $hora_actual_sistema = date('H:i:s');

              if ($tiene_clases) {

            while($clase = $resultado_clases->fetch_assoc()) {
                $h_inicio = $clase['hora_inicio'];
                $h_fin = $clase['hora_fin'];
                $esta_en_horario = ($hora_actual_sistema >= $h_inicio && $hora_actual_sistema <= $h_fin);
        ?>
                <div class="tarjeta-bloque" style="<?php echo $esta_en_horario ? 'border-left: 5px solid #4e73df;' : 'opacity: 0.65; border-left: 5px solid #858796;'; ?>">
                    <div class="info-clase">
                        <h5><?php echo $clase['nombre_materia']; ?></h5>
                        <span>Ubicación: <?php echo $clase['nombre_seccion']; ?></span>
                        <div class="hora-badge">🕒 <?php echo date('h:i A', strtotime($h_inicio)); ?> - <?php echo date('h:i A', strtotime($h_fin)); ?></div>
                    </div>
                    
                    <div>
                        <?php if ($esta_en_horario) { ?>
                            <a href="pasar_asistencia.php?asignacion=<?php echo $clase['asignacion_id']; ?>" class="btn-accion btn-azul">
                                INGRESAR
                            </a>
                        <?php } else { ?>
                            <button class="btn-accion btn-gris" disabled>
                                BLOQUEADO
                            </button>
                        <?php } ?>
                    </div>
                </div>
        <?php 
            }
        } else {
            echo "<div style='background: white; padding: 20px; border-radius: 12px; text-align: center; color: #6c757d; border: 1px dashed #ccc;'>Usted no tiene actividades o clases asignadas para el día de hoy según su horario.</div>";
        }
        ?>
    </div>
</body>
</html>
