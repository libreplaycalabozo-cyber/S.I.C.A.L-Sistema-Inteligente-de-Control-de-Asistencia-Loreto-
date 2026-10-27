<?php
include('conexion.php');
date_default_timezone_set('America/Caracas');
$mensaje = "";

$bloques_horarios = array(
    1 => array('inicio' => '07:00:00', 'fin' => '08:20:00'), 
    2 => array('inicio' => '08:35:00', 'fin' => '09:55:00'), 
    3 => array('inicio' => '09:55:00', 'fin' => '11:15:00'), 
    4 => array('inicio' => '11:15:00', 'fin' => '12:35:00'), 
    5 => array('inicio' => '01:00:00', 'fin' => '02:20:00'), 
    6 => array('inicio' => '02:20:00', 'fin' => '03:20:00')  
);

$dias_semana = array('lunes', 'martes', 'miercoles', 'jueves', 'viernes');

if (isset($_POST['btn_generar'])) {
    $conexion->query("DELETE FROM asistencia_estudiantes");
    $conexion->query("DELETE FROM asignaciones_profesor");
    
    $materias_query = $conexion->query("SELECT * FROM materias WHERE nombre_materia NOT LIKE '%Planificacion%' AND nombre_materia NOT LIKE '%Atencion%'");
    $secciones_query = $conexion->query("SELECT * FROM secciones WHERE nombre_seccion NOT LIKE '%Administrativa%'");

    $id_planif = $conexion->query("SELECT id FROM materias WHERE nombre_materia LIKE '%Planificacion%'")->fetch_assoc()['id'] ?? 0;
    $id_atenc  = $conexion->query("SELECT id FROM materias WHERE nombre_materia LIKE '%Atencion%'")->fetch_assoc()['id'] ?? 0;
    $id_seccion_admin = $conexion->query("SELECT id FROM secciones WHERE nombre_seccion LIKE '%Administrativa%'")->fetch_assoc()['id'] ?? 0;

    if ($materias_query->num_rows == 0 || $secciones_query->num_rows == 0) {
        $mensaje = "<div class='alerta roja'>Error: Debe tener cargadas materias y secciones en el sistema.</div>";
    } else {
        $total_asignadas = 0;
        
        // FASE A: COMPILAR CLASES DE ALUMNOS (PAREJAS PEDAGÓGICAS)
        while($sec = $secciones_query->fetch_assoc()) {
            $seccion_id = $sec['id'];
            $materias_query->data_seek(0);
            
            while($mat = $materias_query->fetch_assoc()) {
                $materia_id = $mat['id'];
                $horas_totales = (int)$mat['horas_semanales'];
                
                $bloques_completos = floor($horas_totales / 2);
                $tiene_hora_impar = ($horas_totales % 2 != 0);
                $bloques_repartidos = 0;

                $buscar_pareja = $conexion->query("SELECT profesor1_id, profesor2_id FROM carga_academica_docente WHERE materia_id = '$materia_id' AND seccion_id = '$seccion_id'");
                if ($buscar_pareja->num_rows == 0) continue;
                $pareja = $buscar_pareja->fetch_assoc();
                $prof1_id = $pareja['profesor1_id'];
                $prof2_id = $pareja['profesor2_id'];

                // 1. BLOQUES COMPLETOS
                foreach ($dias_semana as $dia) {
                    if ($bloques_repartidos >= $bloques_completos) break;
                    for ($num_b = 1; $num_b <= 6; $num_b++) {
                        if ($bloques_repartidos >= $bloques_completos) break;
                        if ($num_b == 6 && $bloques_completos <= 2) continue; 

                        $h_inicio = $bloques_horarios[$num_b]['inicio'];
                        $h_fin = $bloques_horarios[$num_b]['fin'];

                        if ($horas_totales <= 4) {
                            $ya_tiene_hoy = $conexion->query("SELECT id FROM asignaciones_profesor WHERE seccion_id = '$seccion_id' AND materia_id = '$materia_id' AND dia_semana = '$dia'");
                            if ($ya_tiene_hoy->num_rows > 0) continue;
                        }

                        // TRIPLE ESCUDO DE SEGURIDAD ANTICHOCUE
                        $choc_seccion = $conexion->query("SELECT id FROM asignaciones_profesor WHERE seccion_id = '$seccion_id' AND dia_semana = '$dia' AND hora_inicio = '$h_inicio'");
                        $choc_prof1   = $conexion->query("SELECT id FROM asignaciones_profesor WHERE (profesor1_id = '$prof1_id' OR profesor2_id = '$prof1_id') AND dia_semana = '$dia' AND hora_inicio = '$h_inicio'");
                        
                        $choc_prof2_rows = 0;
                        if ($prof2_id != 0) {
                            $choc_prof2 = $conexion->query("SELECT id FROM asignaciones_profesor WHERE (profesor1_id = '$prof2_id' OR profesor2_id = '$prof2_id') AND dia_semana = '$dia' AND hora_inicio = '$h_inicio'");
                            $choc_prof2_rows = $choc_prof2->num_rows;
                        }

                        if ($choc_seccion->num_rows == 0 && $choc_prof1->num_rows == 0 && $choc_prof2_rows == 0) {
                            $conexion->query("INSERT INTO asignaciones_profesor (profesor1_id, profesor2_id, materia_id, seccion_id, hora_inicio, hora_fin, dia_semana) VALUES ('$prof1_id', '$prof2_id', '$materia_id', '$seccion_id', '$h_inicio', '$h_fin', '$dia')");
                            $bloques_repartidos++; $total_asignadas++;
                        }
                    }
                }

                // 2. HORA IMPAR SUELTA (40 min)
                if ($tiene_hora_impar) {
                    $hora_impar_asignada = false;
                    foreach ($dias_semana as $dia) {
                        if ($hora_impar_asignada) break;
                        for ($num_b = 1; $num_b <= 5; $num_b++) { 
                            if ($hora_impar_asignada) break;
                            $h_inicio = $bloques_horarios[$num_b]['inicio'];
                            $h_fin_impar = date('H:i:s', strtotime($h_inicio . ' +40 minutes'));

                            $choc_seccion = $conexion->query("SELECT id FROM asignaciones_profesor WHERE seccion_id = '$seccion_id' AND dia_semana = '$dia' AND (('$h_inicio' >= hora_inicio AND '$h_inicio' < hora_fin) OR ('$h_fin_impar' > hora_inicio AND '$h_fin_impar' <= hora_fin))");
                            $choc_prof1   = $conexion->query("SELECT id FROM asignaciones_profesor WHERE (profesor1_id = '$prof1_id' OR profesor2_id = '$prof1_id') AND dia_semana = '$dia' AND (('$h_inicio' >= hora_inicio AND '$h_inicio' < hora_fin) OR ('$h_fin_impar' > hora_inicio AND '$h_fin_impar' <= hora_fin))");
                            
                            $choc_prof2_rows = 0;
                            if ($prof2_id != 0) {
                                $choc_prof2 = $conexion->query("SELECT id FROM asignaciones_profesor WHERE (profesor1_id = '$prof2_id' OR profesor2_id = '$prof2_id') AND dia_semana = '$dia' AND (('$h_inicio' >= hora_inicio AND '$h_inicio' < hora_fin) OR ('$h_fin_impar' > hora_inicio AND '$h_fin_impar' <= hora_fin))");
                                $choc_prof2_rows = $choc_prof2->num_rows;
                            }

                            if ($choc_seccion->num_rows == 0 && $choc_prof1->num_rows == 0 && $choc_prof2_rows == 0) {
                                $conexion->query("INSERT INTO asignaciones_profesor (profesor1_id, profesor2_id, materia_id, seccion_id, hora_inicio, hora_fin, dia_semana) VALUES ('$prof1_id', '$prof2_id', '$materia_id', '$seccion_id', '$h_inicio', '$h_fin_impar', '$dia')");
                                $hora_impar_asignada = true; $total_asignadas++;
                            }
                        }
                    }
                }
            }
        }
        // ⚡ FASE B: INYECCIÓN AUTOMÁTICA DE HORAS FIJAS OBLIGATORIAS (CORREGIDA SIN WARNINGS)
        $res_p_lista = $conexion->query("SELECT id FROM usuarios WHERE rol LIKE '%profesor%'");
        while ($p_row = $res_p_lista->fetch_assoc()) {
            $p_id = $p_row['id'];
            
            // 1. Planificación Institucional (1 Bloque de 80 min)
            $plan_asignado = false;
            foreach ($dias_semana as $dia) {
                if ($plan_asignado) break;
                for ($num_b = 1; $num_b <= 5; $num_b++) {
                    $h_inicio = $bloques_horarios[$num_b]['inicio']; 
                    $h_fin = $bloques_horarios[$num_b]['fin'];
                    
                    // Consulta limpia de choque ordinario
                    $choc_prof = $conexion->query("SELECT id FROM asignaciones_profesor WHERE (profesor1_id = '$p_id' OR profesor2_id = '$p_id') AND dia_semana = '$dia' AND hora_inicio = '$h_inicio'");
                    if ($choc_prof->num_rows == 0 && $id_planif > 0 && $id_seccion_admin > 0) {
                        $conexion->query("INSERT INTO asignaciones_profesor (profesor1_id, profesor2_id, materia_id, seccion_id, hora_inicio, hora_fin, dia_semana) VALUES ('$p_id', '0', '$id_planif', '$id_seccion_admin', '$h_inicio', '$h_fin', '$dia')");
                        $plan_asignado = true; $total_asignadas++; break;
                    }
                }
            }

            // 2. Atención al Representante (1 Bloque de 80 min)
            $aten_asignada = false;
            foreach ($dias_semana as $dia) {
                if ($aten_asignada) break;
                for ($num_b = 1; $num_b <= 5; $num_b++) {
                    $h_inicio = $bloques_horarios[$num_b]['inicio']; 
                    $h_fin = $bloques_horarios[$num_b]['fin'];
                    
                    // Consulta limpia de choque ordinario
                    $choc_prof2 = $conexion->query("SELECT id FROM asignaciones_profesor WHERE (profesor1_id = '$p_id' OR profesor2_id = '$p_id') AND dia_semana = '$dia' AND hora_inicio = '$h_inicio'");
                    if ($choc_prof2->num_rows == 0 && $id_atenc > 0 && $id_seccion_admin > 0) {
                        $conexion->query("INSERT INTO asignaciones_profesor (profesor1_id, profesor2_id, materia_id, seccion_id, hora_inicio, hora_fin, dia_semana) VALUES ('$p_id', '0', '$id_atenc', '$id_seccion_admin', '$h_inicio', '$h_fin', '$dia')");
                        $aten_asignada = true; $total_asignadas++; break;
                    }
                }
            }
        }
        $mensaje = "<div class='alerta verde'>¡Algoritmo Regulador S.I.C.A.L. completado con éxito y libre de advertencias!</div>";
    }
}

$profesores = [];
$res_p = $conexion->query("SELECT id, nombre_completo FROM usuarios WHERE rol LIKE '%profesor%' ORDER BY nombre_completo ASC");
if ($res_p) { while($p = $res_p->fetch_assoc()) { $profesores[] = $p; } }

$secciones = [];
$res_s = $conexion->query("SELECT id, nombre_seccion FROM secciones WHERE nombre_seccion NOT LIKE '%Administrativa%' ORDER BY nombre_seccion ASC");
if ($res_s) { while($s = $res_s->fetch_assoc()) { $secciones[] = $s; } }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Generador de Horarios Co-Docentes</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 1000px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #ddd; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; }
        button { background: #1e3799; color: white; padding: 14px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 0.9rem; }
        .btn-print { background: #9b59b6; color: white; display: inline-block; margin-top: 10px; padding: 14px 25px; font-size: 0.9rem; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-transform: uppercase; text-decoration: none; }
        .alerta { padding: 14px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .seccion-horarios { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; margin-top: 20px; }
        .bloque-print { margin-bottom: 60px; page-break-after: always; text-align: left; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.8rem; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 6px; text-align: center; vertical-align: middle; height: 40px; overflow: hidden; }
        th { background: #f1f5f9; color: #1e3799; font-weight: bold; }
        .txt-libre { color: #94a3b8; font-style: italic; font-size: 0.75rem; }
        .celda-materia { background: #f8fafc; font-weight: bold; border-left: 4px solid #1e3799 !important; }
        .celda-materia-sec { background: #f8fafc; font-weight: bold; border-left: 4px solid #1cc88a !important; }
        .linea-impar-sup { border-bottom: 2px dashed #e67e22 !important; background: #fffaf0; }
        .lbl-impar { color: #e67e22; font-size: 0.65rem; font-weight: bold; display: block; }
        .fila-receso { background: #ffeaa7 !important; color: #d35400 !important; font-weight: bold; font-size: 0.8rem; text-transform: uppercase; }
        .badge-horas { display: inline-block; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.8rem; margin-top: 4px; }
        .bg-completo { background: #d1e7dd; color: #0f5132; }
        .bg-falta { background: #fff3cd; color: #664d03; }
        @media print { .no-print, nav { display: none !important; } body { background: white; } .seccion-horarios { border: none; } }
    </style>
</head>
<body>
    <nav class="no-print"><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        <div class="card no-print">
            <h4>Generador de Horarios v1.0 (Parejas Pedagógicas)</h4>
            <p>El algoritmo distribuirá las materias validando que el aula y ambos docentes estén libres simultáneamente.</p>
            <form action="admin_generar_horarios.php" method="POST" style="display:inline-block;">
                <button type="submit" name="btn_generar">🤖 CALCULAR MALLA INSTITUCIONAL</button>
            </form>
            <button onclick="window.print();" class="btn-print">🖨️ GENERAR PDF DE HORARIOS</button>
        </div>

        <div class="seccion-horarios">
            <h3 class="no-print" style="color:#333; border-bottom:2px solid #1e3799; padding-bottom:5px;">📋 Distribución Completa por Profesor</h3>
            <?php foreach($profesores as $prof): 
                $p_id = $prof['id'];
                $cuenta_horas = 0;
                $res_h = $conexion->query("SELECT ap.hora_inicio, ap.hora_fin FROM asignaciones_profesor ap WHERE ap.profesor1_id = '$p_id' OR ap.profesor2_id = '$p_id'");
                while($h_row = $res_h->fetch_assoc()){
                    $dur = (strtotime($h_row['hora_fin']) - strtotime($h_row['hora_inicio'])) / 60;
                    $cuenta_horas += ($dur <= 40) ? 1 : 2;
                }
                $res_adm = $conexion->query("SELECT SUM(horas_asignadas) as t, GROUP_CONCAT(motivo_cargo SEPARATOR ' / ') as c FROM horas_administrativas_docente WHERE profesor_id = '$p_id'");
                $data_adm = $res_adm->fetch_assoc();
                $h_adm = $data_adm['t'] ?? 0;
                $total_h = $cuenta_horas + $h_adm;
            ?>
                <div class="bloque-print">
                    <div style="display:flex; justify-content:space-between; border-bottom:2px solid #1e3799; padding-bottom:5px; margin-bottom:10px;">
                        <div>
                            <h4 style="margin:0; color:#1e3799;">DOCENTE: <?php echo $prof['nombre_completo']; ?></h4>
                            <small>E.T.C. Dr. Rafael Loreto Loreto | S.I.C.A.L. 1.0</small>
                        </div>
                        <div class="no-print" style="text-align:right;">
                            <strong>Estatus de Carga:</strong><br>
                            <span class="badge-horas <?php echo ($total_h >= 40) ? 'bg-completo' : 'bg-falta'; ?>">
                                📊 Total: <?php echo $total_h; ?>h / 40h
                            </span>
                        </div>
                    </div>
                    <table>
                        <thead><tr><th style="width:130px;">Horario</th><th>Lunes</th><th>Martes</th><th>Miércoles</th><th>Jueves</th><th>Viernes</th></tr></thead>
                        <tbody>
                            <?php 
                            $horas_v = array(
                                1=>array('t'=>'07:00 a 07:40','b'=>1,'s'=>'sup'), 2=>array('t'=>'07:40 a 08:20','b'=>1,'s'=>'inf'),
                                3=>array('t'=>'08:35 a 09:15','b'=>2,'s'=>'sup'), 4=>array('t'=>'09:15 a 09:55','b'=>2,'s'=>'inf'),
                                5=>array('t'=>'09:55 a 10:35','b'=>3,'s'=>'sup'), 6=>array('t'=>'10:35 a 11:15','b'=>3,'s'=>'inf'),
                                7=>array('t'=>'11:15 a 11:55','b'=>4,'s'=>'sup'), 8=>array('t'=>'11:55 a 12:35','b'=>4,'s'=>'inf'),
                                9=>array('t'=>'01:00 a 01:40','b'=>5,'s'=>'sup'), 10=>array('t'=>'01:40 a 02:20','b'=>5,'s'=>'inf'),
                                11=>array('t'=>'02:20 a 02:50','b'=>6,'s'=>'sup'), 12=>array('t'=>'02:50 a 03:20','b'=>6,'s'=>'inf')
                            );
                            for($h=1; $h<=12; $h++):
                                $h_conf = $horas_v[$h]; $b_id = $h_conf['b']; $pos = $h_conf['s'];
                                if($h==3 && $pos=='sup') echo "<tr class='fila-receso'><td>08:20-08:35</td><td colspan='5'>☕ RECESO DE LA MAÑANA (15 MIN)</td></tr>";
                                if($h==9 && $pos=='sup') echo "<tr class='fila-receso'><td>12:35-01:00</td><td colspan='5'>🍲 RECESO DE ALMUERZO (25 MIN)</td></tr>";
                            ?>
                                <tr>
                                    <td style="background:#f8f9fa; font-weight:bold;"><?php echo $h_conf['t']; ?></td>
                                    <?php foreach($dias_semana as $d): 
                                        $q = $conexion->query("SELECT m.nombre_materia, s.nombre_seccion, ap.hora_fin, u1.nombre_completo as p1, u2.nombre_completo as p2 FROM asignaciones_profesor ap INNER JOIN materias m ON ap.materia_id = m.id INNER JOIN secciones s ON ap.seccion_id = s.id INNER JOIN usuarios u1 ON ap.profesor1_id = u1.id LEFT JOIN usuarios u2 ON ap.profesor2_id = u2.id WHERE (ap.profesor1_id = '$p_id' OR ap.profesor2_id = '$p_id') AND ap.dia_semana = '$d' AND ap.hora_inicio = '".$bloques_horarios[$b_id]['inicio']."'");
                                        $c = $q->fetch_assoc();
                                        $es_m = (!empty($c) && $c['hora_fin'] != $bloques_horarios[$b_id]['fin']);
                                        if(!empty($c)):
                                            if($es_m):
                                                if($pos=='sup'): echo "<td class='linea-impar-sup'><b>".$c['nombre_materia']."</b><br><small>".$c['nombre_seccion']."</small><span class='lbl-impar'>[40m]</span></td>";
                                                else: echo "<td><span class='txt-libre'>libre</span></td>"; endif;
                                            else:
                                                if($pos=='sup'): 
                                                    $co_doc = ($c['p1'] == $prof['nombre_completo']) ? $c['p2'] : $c['p1'];
                                                    echo "<td rowspan='2' class='celda-materia'><b>".$c['nombre_materia']."</b><br><small>".$c['nombre_seccion']."</small>";
                                                    if(!empty($co_doc) && $co_doc != "0") echo "<br><i style='font-size:0.7rem; color:#555;'>Con: $co_doc</i>";
                                                    echo "</td>";
                                                endif;
                                            endif;
                                        else:
                                            if($pos=='sup'): 
                                                if(!empty($data_adm['c'])): echo "<td rowspan='2'><span style='color:#27ae60; font-weight:bold; font-size:0.75rem;'>💼 ".strtoupper($data_adm['c'])."</span><br><small style='color:#7f8c8d;'>[Oficina]</small></td>";
                                                else: echo "<td rowspan='2'><span class='txt-libre'>libre</span></td>"; endif;
                                            endif;
                                        endif;
                                    endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
            
            <h3 class="no-print" style="color:#333; border-bottom:2px solid #1cc88a; padding-bottom:5px; margin-top:50px;">📋 Distribución Completa por Año y Sección</h3>
            <?php foreach($secciones as $sec): $s_id = $sec['id']; ?>
                <div class="bloque-print">
                    <h4 style="margin:0; color:#1cc88a;">HORARIO DE CLASES: <?php echo $sec['nombre_seccion']; ?></h4>
                    <small>E.T.C. Dr. Rafael Loreto Loreto | S.I.C.A.L. 1.0</small>
                    <table>
                        <thead><tr><th style="width:130px;">Horario</th><th>Lunes</th><th>Martes</th><th>Miércoles</th><th>Jueves</th><th>Viernes</th></tr></thead>
                        <tbody>
                            <?php for($b=1; $b<=12; $b++):
                                // ESCUDO DE PROTECCIÓN: Evaluamos de forma segura usando $b y validamos que exista en el arreglo
                                $h_conf = $horas_v[$b] ?? null; 
                                if (!$h_conf) continue; // Si no hay configuración para este bloque, saltamos para evitar errores
                                
                                $b_id = $h_conf['b'] ?? ''; 
                                $pos  = $h_conf['s'] ?? '';
                                
                                // Ajustamos la validación de los recesos con el indexador correcto ($b)
                                if($b==3 && $pos=='sup') echo "<tr class='fila-receso'><td>08:20-08:35</td><td colspan='5'>☕ RECESO DE LA MAÑANA (15 MIN)</td></tr>";
                                if($b==9 && $pos=='sup') echo "<tr class='fila-receso'><td>12:35-01:00</td><td colspan='5'>🍲 RECESO DE ALMUERZO (25 MIN)</td></tr>";
                                
                                // Capturamos las horas de inicio y fin blindando contra valores nulos
                                $hora_inicio_busqueda = $bloques_horarios[$b_id]['inicio'] ?? '';
                                $hora_fin_busqueda    = $bloques_horarios[$b_id]['fin'] ?? '';
                            ?>
                                <tr>
                                    <td style="background:#f8f9fa; font-weight:bold;"><?php echo htmlspecialchars($h_conf['t'] ?? '-'); ?></td>
                                    <?php foreach($dias_semana as $d): 
                                        // Inicializamos la consulta controlando que las variables de tiempo no estén vacías
                                        $c = null;
                                        if (!empty($hora_inicio_busqueda)) {
                                            $q = $conexion->query("SELECT m.nombre_materia, u1.nombre_completo as p1, u2.nombre_completo as p2, ap.hora_fin FROM asignaciones_profesor ap INNER JOIN materias m ON ap.materia_id = m.id INNER JOIN usuarios u1 ON ap.profesor1_id = u1.id LEFT JOIN usuarios u2 ON ap.profesor2_id = u2.id WHERE ap.seccion_id = '$s_id' AND ap.dia_semana = '$d' AND ap.hora_inicio = '$hora_inicio_busqueda'");
                                            if ($q) {
                                                $c = $q->fetch_assoc();
                                            }
                                        }
                                        
                                        $es_m = (!empty($c) && $c['hora_fin'] != $hora_fin_busqueda);
                                        if(!empty($c)):
                                            if($es_m):
                                                if($pos=='sup'): echo "<td class='linea-impar-sup'><b>".htmlspecialchars($c['nombre_materia'])."</b><br><small>".htmlspecialchars($c['p1']);
                                                    if(!empty($c['p2']) && $c['p2'] != "0") echo " / " . htmlspecialchars($c['p2']);
                                                    echo "</small><span class='lbl-impar'>[40m]</span></td>";
                                                else: echo "<td><span class='txt-libre'>libre</span></td>"; endif;
                                            else:
                                                if($pos=='sup'): echo "<td rowspan='2' class='celda-materia-sec'><b>".htmlspecialchars($c['nombre_materia'])."</b><br><small>".htmlspecialchars($c['p1']);
                                                    if(!empty($c['p2']) && $c['p2'] != "0") echo " / " . htmlspecialchars($c['p2']);
                                                    echo "</small></td>";
                                                endif;
                                            endif;
                                        else:
                                            if($pos=='sup'): echo "<td rowspan='2'><span class='txt-libre'>libre</span></td>"; endif;
                                        endif;
                                    endforeach; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
