<?php
include('conexion.php');
$mensaje = "";

// Consultamos el estado actual del año escolar
$res_config = $conexion->query("SELECT * FROM anio_escolar_activo WHERE id = 1");
$config = $res_config->fetch_assoc();
$esta_bloqueado = $config['bloqueado'];

if (isset($_POST['btn_iniciar_ciclo']) && $esta_bloqueado == 0) {
    $nuevo_periodo = $_POST['nuevo_periodo'];
    $f_inicio = $_POST['fecha_inicio'];
    $f_fin = $_POST['fecha_fin'];

    // 1. Actualizamos datos y ACTIVAMOS EL BLOQUEO DE SEGURIDAD
    $conexion->query("UPDATE anio_escolar_activo SET 
                      periodo = '$nuevo_periodo', 
                      fecha_inicio = '$f_inicio', 
                      fecha_fin = '$f_fin', 
                      bloqueado = 1 
                      WHERE id = 1");

    // 2. Limpieza controlada por apertura única
    $conexion->query("DELETE FROM asistencia_estudiantes");
    $conexion->query("DELETE FROM estudiantes");
    $conexion->query("DELETE FROM asignaciones_profesor");
    $conexion->query("DELETE FROM asistencia_personal");
    $conexion->query("DELETE FROM carga_academica_docente"); // También limpiamos la carga manual

    $mensaje = "<div class='alerta verde'>🚀 ¡AÑO ESCOLAR $nuevo_periodo INICIADO! El sistema se ha configurado y bloqueado para este ciclo. Ya no podrá volver a resetearlo por seguridad.</div>";
    
    // Actualizamos la variable local para ocultar el formulario de inmediato
    $esta_bloqueado = 1;
    $config['periodo'] = $nuevo_periodo;
    $config['fecha_inicio'] = $f_inicio;
    $config['fecha_fin'] = $f_fin;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Apertura Anual</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 550px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 20px; }
        h4 { margin: 0 0 15px 0; color: #1e3799; font-size: 1.4rem; font-weight: bold; border-bottom: 2px solid #1e3799; padding-bottom: 8px; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 15px; text-align: left;}
        input { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 1rem; outline: none; }
        button { width: 100%; background: #1e3799; color: white; padding: 14px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 25px; }
        .alerta { padding: 14px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .info-bloqueo { background: #fff3cd; color: #664d03; padding: 15px; border-radius: 8px; border: 1px solid #ffe69c; text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        
        <div class="card">
            <h4>📅 Configurar Nueva Escolaridad</h4>
            
            <?php if ($esta_bloqueado == 1) { ?>
                <!-- VISTA DE SISTEMA YA INICIADO (BLOQUEADO) -->
                <div class="info-bloqueo">
                    <span style="font-size: 2rem;">🔒</span><br>
                    SISTEMA INICIALIZADO Y BLOQUEADO<br>
                    <span style="font-size: 0.9rem; font-weight: normal;">El período <b><?php echo $config['periodo']; ?></b> ya está en curso. Por seguridad del historial académico, no se permite un segundo reinicio.</span>
                </div>
                <div style="margin-top: 20px; text-align: left; font-size: 0.9rem; color: #555;">
                    📍 <b>Inicio:</b> <?php echo date('d-m-Y', strtotime($config['fecha_inicio'])); ?><br>
                    🏁 <b>Culminación:</b> <?php echo date('d-m-Y', strtotime($config['fecha_fin'])); ?>
                </div>

            <?php } else { ?>
                <!-- VISTA DE FORMULARIO DE APERTURA (DISPONIBLE SOLO UNA VEZ) -->
                <p style="font-size:0.88rem; color:#666; text-align:center;">Introduzca los datos del nuevo ciclo escolar. Esta acción es <b>irreversible</b> para proteger los datos.</p>
                
                <form action="admin_nuevo_ciclo.php" method="POST" onsubmit="return confirm('¿ESTÁ SEGURO? Esta acción borrará estudiantes y horarios para iniciar el nuevo ciclo y bloqueará esta opción hasta el próximo año.');">
                    <label>Nombre del Período:</label>
                    <input type="text" name="nuevo_periodo" placeholder="Ej: 2026-2027" required>
                    
                    <div style="display: flex; gap: 10px;">
                        <div style="width: 50%;">
                            <label>Fecha de Inicio:</label>
                            <input type="date" name="fecha_inicio" required>
                        </div>
                        <div style="width: 50%;">
                            <label>Fecha de Cierre:</label>
                            <input type="date" name="fecha_fin" required>
                        </div>
                    </div>

                    <button type="submit" name="btn_iniciar_ciclo">🚀 INICIAR ESCOLARIDAD Y BLOQUEAR</button>
                </form>
            <?php } ?>
        </div>
    </div>
</body>
</html>
