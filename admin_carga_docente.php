<?php
include('conexion.php');
$mensaje = "";

// 🗑️ LÓGICA PARA ELIMINAR ASIGNACIÓN CON UNIÓN DE PAREJAS
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $conexion->query("DELETE FROM carga_academica_docente WHERE id = '$id_eliminar'");
    $mensaje = "<div class='alerta roja'>Asignación de la pareja docente retirada correctamente.</div>";
}

// 💾 LÓGICA PARA GUARDAR O ACTUALIZAR CARGA CO-DOCENTE
if (isset($_POST['btn_guardar_carga'])) {
    $prof1_id   = $_POST['profesor1_id'];
    $prof2_id   = $_POST['profesor2_id'];
    $materia_id = $_POST['materia_id'];
    $seccion_id = $_POST['seccion_id'];
    $id_editar  = $_POST['id_editar'];

    if ($prof1_id == $prof2_id) {
        $mensaje = "<div class='alerta roja'>Error: No puede asignar al mismo profesor como docente 1 y docente 2.</div>";
    } else {
        if (!empty($id_editar)) {
            $actualizar = "UPDATE carga_academica_docente SET profesor1_id = '$prof1_id', profesor2_id = '$prof2_id', materia_id = '$materia_id', seccion_id = '$seccion_id' WHERE id = '$id_editar'";
            if ($conexion->query($actualizar)) {
                $mensaje = "<div class='alerta verde'>¡Carga co-docente actualizada con éxito!</div>";
            }
        } else {
            $revisar = $conexion->query("SELECT id FROM carga_academica_docente WHERE materia_id = '$materia_id' AND seccion_id = '$seccion_id'");
            if ($revisar->num_rows > 0) {
                $mensaje = "<div class='alerta roja'>Error: Esa materia en esa sección ya tiene profesores asignados.</div>";
            } else {
                $insertar = "INSERT INTO carga_academica_docente (profesor1_id, profesor2_id, materia_id, seccion_id) VALUES ('$prof1_id', '$prof2_id', '$materia_id', '$seccion_id')";
                if ($conexion->query($insertar)) {
                    $mensaje = "<div class='alerta verde'>¡Pareja pedagógica registrada con éxito para el nuevo año!</div>";
                }
            }
        }
    }
}

$edit_p1 = ""; $edit_p2 = ""; $edit_mat = ""; $edit_sec = ""; $id_ed = "";
if (isset($_GET['editar'])) {
    $id_ed = $_GET['editar'];
    $res_ed = $conexion->query("SELECT * FROM carga_academica_docente WHERE id = '$id_ed'");
    $data_ed = $res_ed->fetch_assoc();
    $edit_p1 = $data_ed['profesor1_id'];
    $edit_p2 = $data_ed['profesor2_id'];
    $edit_mat = $data_ed['materia_id'];
    $edit_sec = $data_ed['seccion_id'];
}

$profesores = $conexion->query("SELECT id, nombre_completo FROM usuarios WHERE rol LIKE '%profesor%' ORDER BY nombre_completo ASC");
$materias   = $conexion->query("SELECT * FROM materias ORDER BY nombre_materia ASC");
$secciones  = $conexion->query("SELECT * FROM secciones WHERE nombre_seccion NOT LIKE '%Administrativa%' ORDER BY nombre_seccion ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Carga Co-Docente</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 900px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; }
        h4 { margin: 0 0 15px 0; color: #1e3799; font-size: 1.3rem; border-bottom: 2px solid #1e3799; padding-bottom: 8px; font-weight: bold; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 12px; }
        select { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 0.95rem; outline: none; }
        button { width: 100%; background: #1e3799; color: white; padding: 13px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 20px; font-size: 0.9rem; }
        .btn-cancelar { background: #6c757d; margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; color: #1e3799; }
        .alerta { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .roja { background: #f8d7da; color: #842029; }
        .btn-accion { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.75rem; font-weight: bold; color: white; margin-right: 5px; }
        .btn-ed { background: #f6c23e; color: #212529; }
        .btn-el { background: #e74a3b; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        
        <div class="card">
            <h4><?php echo !empty($id_ed) ? "✏️ Corregir Asignación de Pareja" : "Asignar Parejas Pedagógicas a Materias"; ?></h4>
            <form action="admin_carga_docente.php" method="POST">
                <input type="hidden" name="id_editar" value="<?php echo $id_ed; ?>">
                
                <div style="display:flex; gap:15px; flex-wrap:wrap;">
                    <div style="flex: 1; min-width: 280px;">
                        <label>Seleccione el Profesor Primario (Docente 1):</label>
                        <select name="profesor1_id" required>
                            <option value="" disabled selected>-- Seleccione docente 1 --</option>
                            <?php 
                            $profesores->data_seek(0);
                            while($p = $profesores->fetch_assoc()) { ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($edit_p1 == $p['id']) ? 'selected' : ''; ?>><?php echo $p['nombre_completo']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                        <div style="flex: 1; min-width: 280px;">
                            <label>Docente 2 (Opcional):</label>
                            <select name="profesor2_id">
                                <!-- 🌟 OPCIÓN UNIVERSAL: Si la institución solo usa 1 profesor, eligen esta -->
                                <option value="0">-- Solo un Docente (No aplica) --</option>
                                <?php 
                                $profesores->data_seek(0);
                                while($p = $profesores->fetch_assoc()) { ?>
                                    <option value="<?php echo $p['id']; ?>" <?php echo ($edit_p2 == $p['id']) ? 'selected' : ''; ?>>
                                        <?php echo $p['nombre_completo']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                </div>

                <label>Materia o Asignatura:</label>
                <select name="materia_id" required>
                    <option value="" disabled selected>-- Seleccione asignatura --</option>
                    <?php while($m = $materias->fetch_assoc()) { ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo ($edit_mat == $m['id']) ? 'selected' : ''; ?>><?php echo $m['nombre_materia']; ?></option>
                    <?php } ?>
                </select>

                <label>Año / Sección Mención:</label>
                <select name="seccion_id" required>
                    <option value="" disabled selected>-- Seleccione sección --</option>
                    <?php while($s = $secciones->fetch_assoc()) { ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($edit_sec == $s['id']) ? 'selected' : ''; ?>><?php echo $s['nombre_seccion']; ?></option>
                    <?php } ?>
                </select>

                <button type="submit" name="btn_guardar_carga"><?php echo !empty($id_ed) ? "ACTUALIZAR CORRECCIÓN" : "Guardar Carga Co-Docente"; ?></button>
                <?php if(!empty($id_ed)) { echo "<a href='admin_carga_docente.php' class='btn-accion btn-cancelar' style='display:block; text-align:center; padding:13px; margin-top:10px;'>CANCELAR EDICIÓN</a>"; } ?>
            </form>
        </div>

        <div class="card">
            <h4>Resumen de Distribución de Co-Docentes</h4>
            <table>
                <thead>
                    <tr>
                        <th>Materia</th>
                        <th>Año y Sección</th>
                        <th>Docente 1</th>
                        <th>Docente 2</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $tabla_carga = $conexion->query("SELECT c.id, m.nombre_materia, s.nombre_seccion, u1.nombre_completo AS prof1, u2.nombre_completo AS prof2 FROM carga_academica_docente c INNER JOIN materias m ON c.materia_id = m.id INNER JOIN secciones s ON c.seccion_id = s.id INNER JOIN usuarios u1 ON c.profesor1_id = u1.id INNER JOIN usuarios u2 ON c.profesor2_id = u2.id ORDER BY s.nombre_seccion ASC, m.nombre_materia ASC");
                    if ($tabla_carga && $tabla_carga->num_rows > 0) {
                        while($row = $tabla_carga->fetch_assoc()) { ?>
                            <tr>
                                <td><b><?php echo $row['nombre_materia']; ?></b></td>
                                <td><span style="background:#e8f0fe; color:#1e3799; padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:bold;"><?php echo $row['nombre_seccion']; ?></span></td>
                                <td><?php echo $row['prof1']; ?></td>
                                <td><?php echo $row['prof2']; ?></td>
                                <td>
                                    <a href="admin_carga_docente.php?editar=<?php echo $row['id']; ?>" class="btn-accion btn-ed">Editar</a>
                                    <a href="admin_carga_docente.php?eliminar=<?php echo $row['id']; ?>" class="btn-accion btn-el" onclick="return confirm('¿Está seguro de eliminar esta asignación?')">Borrar</a>
                                </td>
                            </tr>
                        <?php }
                    } else { echo "<tr><td colspan='5' style='text-align:center; color:#858796;'>No ha realizado ninguna asignación de parejas docentes todavía.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
