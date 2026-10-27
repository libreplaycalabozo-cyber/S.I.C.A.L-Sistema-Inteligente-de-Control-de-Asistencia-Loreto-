<?php
include('conexion.php');
$mensaje = "";

// Obtener la sección seleccionada (si existe)
$seccion_id = isset($_GET['seccion_id']) ? $_GET['seccion_id'] : "";

// Consultar todas las secciones para el filtro
$secciones = $conexion->query("SELECT * FROM secciones WHERE nombre_seccion NOT LIKE '%Administrativa%' ORDER BY nombre_seccion ASC");

// Consultar estudiantes según el filtro
$query_estudiantes = "SELECT e.id, e.cedula, e.nombre_completo, s.nombre_seccion 
                      FROM estudiantes e 
                      INNER JOIN secciones s ON e.seccion_id = s.id ";

if (!empty($seccion_id)) {
    $query_estudiantes .= " WHERE e.seccion_id = '$seccion_id' ";
}

$query_estudiantes .= " ORDER BY s.nombre_seccion ASC, e.nombre_completo ASC";
$res_estudiantes = $conexion->query($query_estudiantes);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Lista de Estudiantes</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 900px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; }
        h4 { margin: 0 0 15px 0; color: #1e3799; font-size: 1.4rem; border-bottom: 2px solid #1e3799; padding-bottom: 8px; font-weight: bold; }
        
        .filtros { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 20px; }
        select { flex-grow: 1; padding: 11px; border-radius: 6px; border: 1px solid #ccc; font-size: 0.95rem; outline: none; }
        .btn-buscar { background: #1e3799; color: white; padding: 11px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #f8f9fa; color: #333; font-weight: bold; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background: #fcfcfc; }
        .no-data { text-align: center; color: #888; padding: 30px; font-style: italic; }
        
        @media print {
            .no-print, nav, .filtros { display: none !important; }
            body { background: white; }
            .card { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <nav class="no-print">
        <strong style="font-size: 1.2rem;">S.I.C.A.L</strong>
        <a href="admin_panel.php">← VOLVER AL PANEL</a>
    </nav>

    <div class="contenedor">
        <div class="card">
            <h4>📋 Matrícula de Estudiantes</h4>
            
            <form action="admin_estudiantes.php" method="GET" class="filtros no-print">
                <div style="flex-grow: 1;">
                    <label style="font-weight:bold; font-size:0.8rem; color:#555; display:block; margin-bottom:5px;">Filtrar por Sección:</label>
                    <select name="seccion_id">
                        <option value="">-- Ver Todas las Secciones --</option>
                        <?php while($s = $secciones->fetch_assoc()) { ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($seccion_id == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo $s['nombre_seccion']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn-buscar">Filtrar Lista</button>
                <button type="button" onclick="window.print()" class="btn-buscar" style="background:#212529;">Imprimir</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">N°</th>
                        <th>Cédula / ID</th>
                        <th>Apellidos y Nombres</th>
                        <th>Año y Sección</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($res_estudiantes->num_rows > 0) {
                        $cont = 1;
                        while($e = $res_estudiantes->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $cont++; ?></td>
                                <td><b><?php echo $e['cedula']; ?></b></td>
                                <td><?php echo $e['nombre_completo']; ?></td>
                                <td><span style="background:#e8f0fe; color:#1e3799; padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold;"><?php echo $e['nombre_seccion']; ?></span></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="4" class="no-data">No hay estudiantes registrados en esta selección.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
