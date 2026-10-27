<?php
include('conexion.php');
$mensaje = "";

if (isset($_POST['btn_asignar'])) {
    $profesor_id = $_POST['profesor_id'];
    $materia_id  = $_POST['materia_id'];
    $seccion_id  = $_POST['seccion_id'];
    $dia_semana  = $_POST['dia_semana']; 
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin    = $_POST['hora_fin'];

    $hora_inicio_formato = $hora_inicio . ":00";
    $hora_fin_formato    = $hora_fin . ":00";

    $insertar = "INSERT INTO asignaciones_profesor (profesor_id, materia_id, seccion_id, dia_semana, hora_inicio, hora_fin) 
                 VALUES ('$profesor_id', '$materia_id', '$seccion_id', '$dia_semana', '$hora_inicio_formato', '$hora_fin_formato')";
    
    if ($conexion->query($insertar)) {
        $mensaje = "<div class='alerta verde'>¡Asignación de horario y día guardada con éxito!</div>";
    } else {
        $mensaje = "<div class='alerta roja'>Error al guardar la asignación de horario.</div>";
    }
}

$profesores = $conexion->query("SELECT id, nombre_completo FROM usuarios WHERE rol LIKE '%profesor%' ORDER BY nombre_completo ASC");
$materias   = $conexion->query("SELECT * FROM materias ORDER BY nombre_materia ASC");
$secciones  = $conexion->query("SELECT * FROM secciones ORDER BY nombre_seccion ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Horarios</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .contenedor { max-width: 550px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); box-sizing: border-box; }
        .card h4 { margin: 0 0 20px 0; color: #212529; font-size: 1.4rem; text-align: center; border-bottom: 2px solid #1cc88a; padding-bottom: 10px; font-weight: bold; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 15px; }
        select, input[type="time"] { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 0.95rem; outline: none; }
        .bloque-horas { background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e3e6f0; margin-top: 20px; }
        .flex-horas { display: flex; gap: 15px; }
        .flex-horas div { width: 50%; }
        button { width: 100%; background: #1cc88a; color: white; padding: 14px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 25px; font-size: 0.9rem; }
        .alerta { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .roja { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        <div class="card">
            <h4>Vincular Docente y Horario</h4>
            <form action="admin_asignar.php" method="POST">
                <label>1. Seleccione el Profesor / Coordinador</label>
                <select name="profesor_id" required>
                    <option value="" disabled selected>-- Seleccione personal --</option>
                    <?php while($prof = $profesores->fetch_assoc()) { ?>
                        <option value="<?php echo $prof['id']; ?>"><?php echo $prof['nombre_completo']; ?></option>
                    <?php } ?>
                </select>

                <label>2. Asignar Materia o Responsabilidad</label>
                <select name="materia_id" required>
                    <option value="" disabled selected>-- Seleccione materia/cargo --</option>
                    <?php while($mat = $materias->fetch_assoc()) { ?>
                        <option value="<?php echo $mat['id']; ?>"><?php echo $mat['nombre_materia']; ?></option>
                    <?php } ?>
                </select>

                <label>3. Año / Sección</label>
                <select name="seccion_id" required>
                    <option value="" disabled selected>-- Seleccione sección --</option>
                    <?php while($sec = $secciones->fetch_assoc()) { ?>
                        <option value="<?php echo $sec['id']; ?>"><?php echo $sec['nombre_seccion']; ?></option>
                    <?php } ?>
                </select>

                <label>4. Día de la Semana</label>
                <select name="dia_semana" required>
                    <option value="lunes">Lunes</option>
                    <option value="martes">Martes</option>
                    <option value="miercoles">Miércoles</option>
                    <option value="jueves">Jueves</option>
                    <option value="viernes">Viernes</option>
                </select>

                <div class="bloque-horas">
                    <strong style="font-size:0.85rem; color:#333; display:block; margin-bottom:10px;">5. Definir Bloque Horario de la Clase</strong>
                    <div class="flex-horas">
                        <div>
                            <span style="font-size:0.8rem; color:#666;">Hora Inicio:</span>
                            <input type="time" name="hora_inicio" required>
                        </div>
                        <div>
                            <span style="font-size:0.8rem; color:#666;">Hora Fin:</span>
                            <input type="time" name="hora_fin" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="btn_asignar">Guardar Asignación de Horario</button>
            </form>
        </div>
    </div>
</body>
</html>
