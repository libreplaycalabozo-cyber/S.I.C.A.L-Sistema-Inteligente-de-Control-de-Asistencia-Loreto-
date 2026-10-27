<?php
include('conexion.php');
date_default_timezone_set('America/Caracas');

$tipo_reporte   = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : "estudiantes";
$seccion_filtro = isset($_GET['seccion_id']) ? $_GET['seccion_id'] : "";
$fecha_filtro   = isset($_GET['fecha_filtro']) ? $_GET['fecha_filtro'] : date('Y-m-d');

// 🔍 1. CONSULTA PARA ASISTENCIA DE ESTUDIANTES (Pedagógica)
$query_est = "SELECT ae.fecha, e.nombre_completo AS alumno, m.nombre_materia, 
                     u1.nombre_completo AS prof1, u2.nombre_completo AS prof2, 
                     ae.estatus, ae.hora_marca 
              FROM asistencia_estudiantes ae 
              INNER JOIN estudiantes e ON ae.estudiante_id = e.id 
              INNER JOIN asignaciones_profesor ap ON ae.asignacion_id = ap.id 
              INNER JOIN materias m ON ap.materia_id = m.id 
              INNER JOIN usuarios u1 ON ap.profesor1_id = u1.id 
              LEFT JOIN usuarios u2 ON ap.profesor2_id = u2.id
              WHERE ae.fecha = '$fecha_filtro'";

if (!empty($seccion_filtro)) {
    $query_est .= " AND ap.seccion_id = '$seccion_filtro'";
}
$query_est .= " ORDER BY e.nombre_completo ASC";
$res_est = $conexion->query($query_est);

// 🔍 2. CONSULTA PARA ASISTENCIA DEL PERSONAL (Laboral)
$query_pers = "SELECT ap.fecha, u.nombre_completo, u.rol, ap.hora_entrada, ap.hora_salida, ap.estatus_laboral 
               FROM asistencia_personal ap 
               INNER JOIN usuarios u ON ap.usuario_id = u.id 
               WHERE ap.fecha = '$fecha_filtro'
               ORDER BY u.nombre_completo ASC";
$res_pers = $conexion->query($query_pers);

$secciones = $conexion->query("SELECT * FROM secciones WHERE nombre_seccion NOT LIKE '%Administrativa%' ORDER BY nombre_seccion ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Central de Reportes</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        nav a { color: #f8f9fa; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .contenedor { max-width: 1100px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; }
        h4 { margin: 0 0 15px 0; color: #1e3799; font-size: 1.4rem; border-bottom: 2px solid #1e3799; padding-bottom: 8px; font-weight: bold; }
        
        /* 📋 DISEÑO DE FILTROS */
        .filtros { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #eee; margin-bottom: 25px; }
        .filtros div { flex: 1; min-width: 180px; }
        label { font-weight: bold; font-size: 0.75rem; color: #555; display: block; margin-bottom: 5px; text-transform: uppercase; }
        select, input { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; font-size: 0.9rem; outline: none; box-sizing: border-box; background: white; }
        
        .btn-buscar { background: #1e3799; color: white; padding: 11px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; transition: 0.3s; }
        .btn-imprimir { background: #9b59b6; color: white; padding: 11px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        .btn-buscar:hover { background: #152a75; }
        
        /* 📊 ESTILOS DE TABLAS */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; font-size: 0.85rem; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #f1f5f9; color: #1e3799; font-weight: bold; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; }
        tr:hover { background: #fcfcfc; }
        
        .badge { padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.7rem; text-transform: uppercase; display: inline-block; }
        .bg-presente { background: #d1e7dd; color: #0f5132; }
        .bg-ausente { background: #f8d7da; color: #842029; }
        .bg-permiso { background: #fff3cd; color: #664d03; }
        
        @media print {
            .no-print, nav, .filtros { display: none !important; }
            body { background: white; }
            .card { border: none; box-shadow: none; padding: 0; }
            .contenedor { max-width: 100%; padding: 0; }
            h4 { color: black; border-bottom: 2px solid black; }
        }
    </style>
</head>
<body>
    <nav class="no-print">
        <strong>⚙️ Central de Reportes Institucionales S.I.C.A.L.</strong>
        <a href="admin_panel.php">← VOLVER AL PANEL</a>
    </nav>

    <div class="contenedor">
        <div class="card no-print">
            <h4>📊 Generación de Auditorías de Asistencia</h4>
            <p style="font-size:0.85rem; color:#666;">Seleccione el tipo de reporte y los filtros necesarios para consultar el historial del plantel.</p>
            
            <form action="admin_reportes.php" method="GET" class="filtros">
                <!-- Selector de Tipo de Reporte -->
                <div>
                    <label>Tipo de Auditoría:</label>
                    <select name="tipo_reporte" onchange="this.form.submit()">
                        <option value="estudiantes" <?php echo ($tipo_reporte == 'estudiantes') ? 'selected' : ''; ?>>Pedagógica (Alumnos)</option>
                        <option value="personal" <?php echo ($tipo_reporte == 'personal') ? 'selected' : ''; ?>>Laboral (Docentes/Nómina)</option>
                    </select>
                </div>

                <!-- Filtro de Sección (Solo útil para estudiantes) -->
                <?php if($tipo_reporte == 'estudiantes'): ?>
                <div>
                    <label>Sección / Mención:</label>
                    <select name="seccion_id">
                        <option value="">-- Todas las Secciones --</option>
                        <?php $secciones->data_seek(0); while($s = $secciones->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($seccion_filtro == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo $s['nombre_seccion']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div>
                    <label>Fecha del Registro:</label>
                    <input type="date" name="fecha_filtro" value="<?php echo $fecha_filtro; ?>">
                </div>

                <div style="display: flex; gap: 10px; flex: 0.6;">
                    <button type="submit" class="btn-buscar">Filtrar</button>
                    <button type="button" onclick="window.print()" class="btn-imprimir">Generar PDF</button>
                </div>
            </form>
        </div>
        <div class="card">
            <?php if($tipo_reporte == 'estudiantes'): ?>
                <!-- 👨‍🎓 TABLA DE ASISTENCIA PEDAGÓGICA (ALUMNOS) -->
                <h4>Listado de Asistencia Estudiantil - Fecha: <?php echo date('d/m/Y', strtotime($fecha_filtro)); ?></h4>
                <table>
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Materia</th>
                            <th>Pareja Pedagógica (Docentes)</th>
                            <th>Estatus</th>
                            <th>Hora Marca</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_est && $res_est->num_rows > 0): ?>
                            <?php while($row = $res_est->fetch_assoc()): ?>
                                <tr>
                                    <td><b><?php echo $row['alumno']; ?></b></td>
                                    <td><?php echo $row['nombre_materia']; ?></td>
                                    <td>
                                        <?php 
                                            echo $row['prof1']; 
                                            if(!empty($row['prof2']) && $row['prof2'] !== '0') echo " / " . $row['prof2'];
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($row['estatus'] == 'presente') ? 'bg-presente' : 'bg-ausente'; ?>">
                                            <?php echo $row['estatus']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo !empty($row['hora_marca']) ? date('h:i A', strtotime($row['hora_marca'])) : '--:--'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:30px; color:#888;">No hay registros pedagógicos para esta fecha o sección.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            <?php else: ?>
                <!-- 💼 TABLA DE ASISTENCIA LABORAL (PERSONAL) -->
                <h4>Control de Nómina y Asistencia Laboral - Fecha: <?php echo date('d/m/Y', strtotime($fecha_filtro)); ?></h4>
                <table>
                    <thead>
                        <tr>
                            <th>Funcionario / Docente</th>
                            <th>Rol / Cargo</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Estatus Jornada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($res_pers && $res_pers->num_rows > 0): ?>
                            <?php while($p = $res_pers->fetch_assoc()): ?>
                                <tr>
                                    <td><b><?php echo $p['nombre_completo']; ?></b></td>
                                    <td><small><?php echo strtoupper($p['rol']); ?></small></td>
                                    <td><?php echo date('h:i A', strtotime($p['hora_entrada'])); ?></td>
                                    <td><?php echo !empty($p['hora_salida']) ? date('h:i A', strtotime($p['hora_salida'])) : '<span style="color:#e74a3b;">En Planta</span>'; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($p['estatus_laboral'] == 'presente') ? 'bg-presente' : 'bg-permiso'; ?>">
                                            <?php echo $p['estatus_laboral']; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:30px; color:#888;">No se han registrado entradas laborales para el personal docente u obrero en esta fecha.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
