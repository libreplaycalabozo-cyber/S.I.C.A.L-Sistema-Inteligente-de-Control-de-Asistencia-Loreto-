<?php
include('conexion.php');
date_default_timezone_set('America/Caracas');
$fecha_actual = date('Y-m-d');
$mensaje = "";

// 1. PROCESAR SALIDA ANTICIPADA (PARA LOS QUE SÍ VINIERON)
if (isset($_POST['btn_autorizar'])) {
    $asistencia_id = $_POST['asistencia_id'];
    $motivo = $_POST['motivo_salida'];
    $actualizar = "UPDATE asistencia_personal SET salida_anticipada_autorizada = 1, motivo_salida_anticipada = '$motivo' WHERE id = '$asistencia_id'";
    if ($conexion->query($actualizar)) {
        $mensaje = "<div class='alerta verde'>¡Permiso de salida temprana otorgado con éxito!</div>";
    }
}

// 2. PROCESAR REPOSO O PERMISO COMPLETOS (PARA LOS QUE NO VAN A VENIR HOY)
if (isset($_POST['btn_inasistencia'])) {
    $user_id = $_POST['user_id'];
    $tipo_permiso = $_POST['tipo_permiso']; // 'reposo' o 'permiso_especial'
    $motivo = $_POST['motivo_inasistencia'];

    // Verificar si ya tiene algún registro hoy para no duplicar
    $revisar = $conexion->query("SELECT id FROM asistencia_personal WHERE usuario_id = '$user_id' AND fecha = '$fecha_actual'");
    if ($revisar->num_rows > 0) {
        $mensaje = "<div class='alerta roja'>Error: Este usuario ya tiene un registro de asistencia o permiso el día de hoy.</div>";
    } else {
        $insertar = "INSERT INTO asistencia_personal (usuario_id, fecha, hora_entrada, estatus_laboral, motivo_salida_anticipada) 
                     VALUES ('$user_id', '$fecha_actual', NULL, '$tipo_permiso', '$motivo')";
        if ($conexion->query($insertar)) {
            $mensaje = "<div class='alerta verde'>¡Justificativo de inasistencia cargado exitosamente para todo el día!</div>";
        }
    }
}

// Consultas para los formularios
$todos_usuarios = $conexion->query("SELECT id, nombre_completo, rol FROM usuarios WHERE rol != 'administrador' ORDER BY nombre_completo ASC");

$consulta_hoy = "SELECT ap.id AS asistencia_id, u.nombre_completo, u.rol, ap.hora_entrada, ap.hora_salida, ap.salida_anticipada_autorizada, ap.estatus_laboral 
                 FROM asistencia_personal ap
                 INNER JOIN usuarios u ON ap.usuario_id = u.id
                 WHERE ap.fecha = '$fecha_actual' AND u.rol != 'administrador'
                 ORDER BY ap.hora_entrada DESC";
$resultado_hoy = $conexion->query($consulta_hoy);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Permisos</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .contenedor { max-width: 750px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        h4 { color: #212529; font-size: 1.3rem; margin: 0 0 15px 0; border-bottom: 2px solid #e74a3b; padding-bottom: 8px; font-weight: bold; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 10px; }
        select, input[type="text"] { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 0.95rem; box-sizing: border-box; outline: none; }
        .form-inline { display: flex; gap: 10px; margin-top: 10px; }
        .form-inline input { flex-grow: 1; }
        button { background: #e74a3b; color: white; padding: 11px 15px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 0.8rem; }
        .btn-negro { background: #212529; width: 100%; margin-top: 15px; padding: 12px; }
        .tarjeta-personal { background: #f8f9fc; padding: 15px; border-radius: 8px; border: 1px solid #e3e6f0; margin-bottom: 10px; }
        .estado-ok { background: #d1e7dd; color: #0f5132; padding: 8px; border-radius: 6px; font-weight: bold; text-align: center; font-size: 0.85rem; }
        .alerta { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .roja { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>

        <!-- SECCIÓN A: CARGAR REPOSO O PERMISO DE TODO EL DÍA -->
        <div class="card" style="border-top: 4px solid #4e73df;">
            <h4 style="border-bottom: 2px solid #4e73df;">📋 Registrar Reposo / Permiso Completo (Todo el Día)</h4>
            <form action="admin_permisos.php" method="POST">
                <label>Seleccione el Trabajador:</label>
                <select name="user_id" required>
                    <option value="" disabled selected>-- Seleccione al personal ausente --</option>
                    <?php while($u = $todos_usuarios->fetch_assoc()) { ?>
                        <option value="<?php echo $u['id']; ?>"><?php echo $u['nombre_completo']; ?> (<?php echo $u['rol']; ?>)</option>
                    <?php } ?>
                </select>
                <div style="display:flex; gap:15px; margin-top:10px;">
                    <div style="width:40%;">
                        <label>Tipo de Justificativo:</label>
                        <select name="tipo_permiso" required>
                            <option value="reposo">Reposo Médico</option>
                            <option value="permiso_especial">Permiso Especial</option>
                        </select>
                    </div>
                    <div style="width:60%;">
                        <label>Motivo o Descripción:</label>
                        <input type="text" name="motivo_inasistencia" placeholder="Ej: Reposo de 72 hrs del IVSS" required autocomplete="off">
                    </div>
                </div>
                <button type="submit" name="btn_inasistencia" class="btn-negro">Cargar Justificativo del Día</button>
            </form>
        </div>

        <!-- SECCIÓN B: TRABAJADORES ACTIVOS HOY (SALIDAS TEMPRANAS) -->
        <div class="card">
            <h4>🚪 Personal Activo Hoy / Salidas Anticipadas</h4>
            <?php if ($resultado_hoy->num_rows > 0) { 
                while($row = $resultado_hoy->fetch_assoc()) {
            ?>
                    <div class="tarjeta-personal">
                        <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                            <strong><?php echo $row['nombre_completo']; ?></strong>
                            <span style="font-size:0.75rem; font-weight:bold; color:#666; text-transform:uppercase;"><?php echo $row['rol']; ?></span>
                        </div>
                        
                        <div style="font-size:0.85rem; color:#5a5c69; margin-bottom:10px;">
                            <?php if ($row['estatus_laboral'] == 'presente') { ?>
                                🕒 <b>Entrada:</b> <?php echo date('h:i A', strtotime($row['hora_entrada'])); ?>
                                <?php echo !empty($row['hora_salida']) ? " | 🚪 <b>Salida:</b> ".date('h:i A', strtotime($row['hora_salida'])) : ""; ?>
                            <?php } else { ?>
                                📑 <b>Condición Especial:</b> <span style="color:#4e73df; font-weight:bold; text-transform:uppercase;"><?php echo $row['estatus_laboral']; ?></span>
                            <?php } ?>
                        </div>

                        <?php if ($row['estatus_laboral'] != 'presente') { ?>
                            <div class="estado-ok" style="background:#e0e0e0; color:#333;">✓ Justificado para todo el día</div>
                        <?php } else if (!empty($row['hora_salida'])) { ?>
                            <div class="estado-ok" style="background:#e2e3e5; color:#383d41;">Jornada finalizada (Ya se retiró)</div>
                        <?php } else if ($row['salida_anticipada_autorizada'] == 1) { ?>
                            <div class="estado-ok">✓ Salida anticipada ya autorizada</div>
                        <?php } else { ?>
                            <form action="admin_permisos.php" method="POST" class="form-inline">
                                <input type="hidden" name="asistencia_id" value="<?php echo $row['asistencia_id']; ?>">
                                <input type="text" name="motivo_salida" placeholder="Motivo de salida antes de hora legal" required autocomplete="off">
                                <button type="submit" name="btn_autorizar">Autorizar</button>
                            </form>
                        <?php } ?>
                    </div>
            <?php 
                } 
            } else {
                echo "<div style='text-align:center; color:#6c757d; font-size:0.9rem; padding:10px;'>Ningún trabajador activo reportado hoy.</div>";
            } ?>
        </div>

    </div>
</body>
</html>
