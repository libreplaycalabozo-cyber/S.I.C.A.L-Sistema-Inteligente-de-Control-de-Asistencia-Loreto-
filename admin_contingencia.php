<?php
include('conexion.php');
date_default_timezone_set('America/Caracas');
$fecha_actual = date('Y-m-d');
$hora_actual = date('H:i:s');
$mensaje = "";

// 🔥 CASO A: BOTÓN DE PÁNICO - CIERRE MASIVO INSTANTÁNEO POR CATÁSTROFE NATURAL
if (isset($_POST['btn_panico'])) {
    $motivo_emergencia = $_POST['motivo_emergencia'];
    
    // Buscamos a todos los que firmaron entrada hoy y no han marcado salida
    $actualizar_salidas_emergencia = "UPDATE asistencia_personal 
                                      SET hora_salida = '$hora_actual', 
                                          estatus_laboral = 'evacuado_emergencia',
                                          motivo_salida_anticipada = 'EVACUACIÓN: $motivo_emergencia'
                                      WHERE fecha = '$fecha_actual' AND hora_salida IS NULL";
    
    if ($conexion->query($actualizar_salidas_emergencia)) {
        $afectados = $conexion->affected_rows;
        $mensaje = "<div class='alerta roja'>⚠️ BOTÓN DE EMERGENCIAS ACTIVADO: Se ha cerrado la jornada laboral de $afectados trabajadores con éxito. Todo el personal ha sido marcado como EVACUADO.</div>";
    }
}

// 🏛️ CASO B: REGISTRAR FERIADOS O DECRETOS OFICIALES (INASISTENCIA TOTAL)
if (isset($_POST['btn_decreto'])) {
    $fecha_d = $_POST['fecha_decreto'];
    $tipo_d  = $_POST['tipo_decreto'];
    $motivo  = $_POST['motivo_decreto'];
    $numero  = !empty($_POST['num_decreto']) ? $_POST['num_decreto'] : NULL;
    $ente    = !empty($_POST['ente_decreto']) ? $_POST['ente_decreto'] : NULL;

    $insertar = "INSERT INTO decretos_feriados (fecha, tipo, motivo, numero_decreto, ente_emisor) 
                 VALUES ('$fecha_d', '$tipo_d', '$motivo', '$numero', '$ente')";
    
    if ($conexion->query($insertar)) {
        
        // 🔄 Automatización: Creamos justificativos masivos en la tabla para que aparezcan en los reportes
        $usuarios_personal = $conexion->query("SELECT id FROM usuarios WHERE rol != 'administrador'");
        while($u = $usuarios_personal->fetch_assoc()) {
            $u_id = $u['id'];
            $conexion->query("INSERT INTO asistencia_personal (usuario_id, fecha, hora_entrada, hora_salida, estatus_laboral, motivo_salida_anticipada) 
                              VALUES ('$u_id', '$fecha_d', NULL, NULL, 'justificado_decreto', '$motivo')");
        }
        
        $mensaje = "<div class='alerta verde'>✓ Suspensión General cargada con éxito. El personal ha sido justificado de forma automática en los reportes para esa fecha.</div>";
    } else {
        $mensaje = "<div class='alerta roja'>Error: Esa fecha ya tiene una contingencia o feriado cargado.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decretos y Contingencias</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 650px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h4 { margin: 0 0 15px 0; font-size: 1.3rem; padding-bottom: 8px; font-weight: bold; }
        .t-rojo { color: #e74a3b; border-bottom: 2px solid #e74a3b; }
        .t-naranja { color: #e67e22; border-bottom: 2px solid #e67e22; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 12px; }
        input, select { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 0.95rem; outline: none; }
        button { width: 100%; color: white; padding: 14px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 20px; font-size: 0.9rem; }
        .btn-rojo { background: #e74a3b; box-shadow: 0 4px 6px rgba(231,74,59,0.2); }
        .btn-naranja { background: #e67e22; }
        .alerta { padding: 14px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; line-height: 1.4; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .roja { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>

        <!-- SECCIÓN 1: BOTÓN DE PÁNICO DE EVACUACIÓN INMEDIATA -->
        <div class="card" style="border: 2px solid #e74a3b;">
            <h4 class="t-rojo">🚨 Botón de Emergencia Escolar (Cierre Inmediato)</h4>
            <p style="font-size:0.85rem; color:#666; line-height:1.4;"><b>¡Uso Crítico!</b> Si ocurre un terremoto, inundación o catástrofe y el personal debe evacuar ya mismo el plantel, escriba el motivo y presione el botón. El sistema les **marcará la hora de salida a todos en este mismo segundo** de forma masiva.</p>
            <form action="admin_contingencia.php" method="POST" onsubmit="return confirm('¿ESTÁ SEGURO? Esta acción cerrará masivamente la jornada laboral de todo el personal activo en este instante.');">
                <label>Detalle de la Catástrofe / Emergencia:</label>
                <input type="text" name="motivo_emergencia" placeholder="Ej: Fuerte sismo regional / Desborde de quebrada" required autocomplete="off">
                <button type="submit" name="btn_panico" class="btn-rojo">🚨 EVACUAR PLANTEL Y MARCAR SALIDAS</button>
            </form>
        </div>

        <!-- SECCIÓN 2: REGISTRO DE DECRETOS Y FERIADOS -->
        <div class="card">
            <h4 class="t-naranja">🏛️ Registrar Feriados o Decretos Especiales</h4>
            <form action="admin_contingencia.php" method="POST">
                <label>Fecha de Suspensión de Actividades:</label>
                <input type="date" name="fecha_decreto" required>
                
                <div style="display:flex; gap:15px;">
                    <div style="width:40%;">
                        <label>Tipo de Suspensión:</label>
                        <select name="tipo_decreto" required>
                            <option value="feriado">Feriado Oficial</option>
                            <option value="decreto">Decreto Ejecutivo</option>
                        </select>
                    </div>
                    <div style="width:60%;">
                        <label>Motivo o Efeméride:</label>
                        <input type="text" name="motivo_decreto" placeholder="Ej: Día de la Juventud / Decreto de Duelo" required autocomplete="off">
                    </div>
                </div>

                <div style="display:flex; gap:15px;">
                    <div style="width:50%;">
                        <label>Número de Decreto (Opcional):</label>
                        <input type="text" name="num_decreto" placeholder="Ej: Gaceta Nro 42.123" autocomplete="off">
                    </div>
                    <div style="width:50%;">
                        <label>Ente Emisor (Opcional):</label>
                        <input type="text" name="ente_decreto" placeholder="Ej: Ejecutivo Nacional / Alcaldía" autocomplete="off">
                    </div>
                </div>

                <button type="submit" name="btn_decreto" class="btn-naranja">Guardar Suspensión General</button>
            </form>
        </div>

    </div>
</body>
</html>
