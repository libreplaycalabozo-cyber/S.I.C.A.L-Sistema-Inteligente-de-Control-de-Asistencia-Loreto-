<?php
include('conexion.php');
$mensaje = "";

// 1. LÓGICA PARA ELIMINAR CARGA ADMINISTRATIVA
if (isset($_GET['eliminar'])) {
    $id_el = $_GET['eliminar'];
    $conexion->query("DELETE FROM horas_administrativas_docente WHERE id = '$id_el'");
    $mensaje = "<div class='alerta roja'>Carga administrativa retirada correctamente.</div>";
}

// 2. LÓGICA PARA GUARDAR ASIGNACIÓN
if (isset($_POST['btn_guardar_adm'])) {
    $profesor_id = $_POST['profesor_id'];
    $horas       = (int)$_POST['horas_asignadas'];
    $motivo      = $_POST['motivo_cargo'];

    $insertar = "INSERT INTO horas_administrativas_docente (profesor_id, horas_asignadas, motivo_cargo) VALUES ('$profesor_id', '$horas', '$motivo')";
    if ($conexion->query($insertar)) {
        $mensaje = "<div class='alerta verde'>¡Horas administrativas asignadas con éxito!</div>";
    }
}

$profesores = $conexion->query("SELECT id, nombre_completo FROM usuarios WHERE rol LIKE '%profesor%' ORDER BY nombre_completo ASC");
$tabla_adm = $conexion->query("SELECT h.id, u.nombre_completo, h.horas_asignadas, h.motivo_cargo FROM horas_administrativas_docente h INNER JOIN usuarios u ON h.profesor_id = u.id ORDER BY u.nombre_completo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Carga Administrativa</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 750px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); margin-bottom: 25px; }
        h4 { margin: 0 0 15px 0; color: #1e3799; font-size: 1.3rem; border-bottom: 2px solid #1e3799; padding-bottom: 8px; font-weight: bold; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 12px; }
        select, input { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 0.95rem; outline: none; }
        button { width: 100%; background: #1e3799; color: white; padding: 13px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 20px; font-size: 0.9rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; color: #1e3799; }
        .alerta { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .roja { background: #f8d7da; color: #842029; }
        .btn-el { background: #e74a3b; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 0.75rem; font-weight: bold; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        <div class="card">
            <h4>Asignar Horas Administrativas / Coordinaciones</h4>
            <form action="admin_horas_admin.php" method="POST">
                <label>Seleccione el Profesor:</label>
                <select name="profesor_id" required>
                    <option value="" disabled selected>-- Seleccione docente --</option>
                    <?php while($p = $profesores->fetch_assoc()) { ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre_completo']; ?></option>
                    <?php } ?>
                </select>

                <div style="display:flex; gap:15px;">
                    <div style="width:30%;">
                        <label>Cantidad de Horas Semanales:</label>
                        <input type="number" name="horas_asignadas" min="1" max="40" placeholder="Ej: 6" required>
                    </div>
                    <div style="width:70%;">
                        <label>Función o Coordinación Asignada:</label>
                        <input type="text" name="motivo_cargo" placeholder="Ej: Coordinador de Biblioteca Escolar" required autocomplete="off">
                    </div>
                </div>
                <button type="submit" name="btn_guardar_adm">Cargar Horas a Nómina</button>
            </form>
        </div>

        <div class="card">
            <h4>Historial de Cargas Administrativas Semanales</h4>
            <table>
                <thead><tr><th>Docente</th><th>Horas</th><th>Función Asignada</th><th>Acción</th></tr></thead>
                <tbody>
                    <?php if ($tabla_adm->num_rows > 0) {
                        while($row = $tabla_adm->fetch_assoc()) { ?>
                            <tr>
                                <td><b><?php echo $row['nombre_completo']; ?></b></td>
                                <td><span style="background:#eaecf4; padding:3px 8px; border-radius:4px; font-weight:bold;"><?php echo $row['horas_asignadas']; ?>h</span></td>
                                <td><?php echo $row['motivo_cargo']; ?></td>
                                <td><a href="admin_horas_admin.php?eliminar=<?php echo $row['id']; ?>" class="btn-el" onclick="return confirm('¿Retirar estas horas de la nómina?')">Retirar</a></td>
                            </tr>
                        <?php }
                    } else { echo "<tr><td colspan='4' style='text-align:center; color:#858796;'>No hay cargas administrativas asignadas este año todavía.</td></tr>"; } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
