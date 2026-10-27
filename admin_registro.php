<?php
include('conexion.php');
$mensaje = "";

// 🛠️ PROCESAR REGISTRO DE PERSONAL (Profesores, Administrativos, Obreros)
if (isset($_POST['btn_personal'])) {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $rol = $_POST['select_rol'];

    // ⚡ TRUCO: Guardamos la misma cédula en el campo 'contrasena' de forma automática
    $insertar = "INSERT INTO usuarios (cedula, nombre_completo, rol, contrasena) VALUES ('$cedula', '$nombre', '$rol', '$cedula')";
    
    if ($conexion->query($insertar)) {
        $mensaje = "<div class='alerta verde'>¡Personal registrado exitosamente! Su contraseña por defecto es su número de cédula.</div>";
    } else {
        $mensaje = "<div class='alerta roja'>Error: Ese número de cédula ya se encuentra registrado en el sistema.</div>";
    }
}

// 🛠️ PROCESAR REGISTRO DE ESTUDIANTES
if (isset($_POST['btn_estudiante'])) {
    $cedula = $_POST['cedula_est'];
    $nombre = $_POST['nombre_est'];
    $seccion_id = $_POST['seccion_est'];

    $insertar = "INSERT INTO estudiantes (cedula, nombre_completo, seccion_id) VALUES ('$cedula', '$nombre', '$seccion_id')";
    
    if ($conexion->query($insertar)) {
        $mensaje = "<div class='alerta verde'>¡Estudiante registrado y asignado con éxito!</div>";
    } else {
        $mensaje = "<div class='alerta roja'>Error al registrar estudiante: La cédula escolar ya existe.</div>";
    }
}

$secciones_query = $conexion->query("SELECT * FROM secciones ORDER BY nombre_seccion ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Personas</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .contenedor { max-width: 850px; margin: auto; padding: 25px; box-sizing: border-box; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); box-sizing: border-box; }
        .card h5 { margin: 0 0 15px 0; font-size: 1.2rem; font-weight: bold; padding-bottom: 8px; }
        .t-azul { color: #4e73df; border-bottom: 2px solid #4e73df; }
        .t-verde { color: #1cc88a; border-bottom: 2px solid #1cc88a; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 12px; }
        input, select { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 0.95rem; outline: none; }
        input:focus, select:focus { border-color: #4e73df; }
        button { width: 100%; color: white; padding: 13px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 25px; font-size: 0.9rem; }
        .btn-azul { background: #4e73df; }
        .btn-verde { background: #1cc88a; }
        .alerta { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
        .roja { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        <div class="grid">
            
            <!-- FORMULARIO 1: REGISTRAR DOCENTES / PERSONAL -->
            <div class="card">
                <h5 class="t-azul">Registrar Personal</h5>
                <form action="admin_registro.php" method="POST">
                    <label>Cédula / Identificación</label>
                    <input type="text" name="cedula" placeholder="Ej: 14926329" required autocomplete="off">
                    
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" placeholder="Ej: Mendoza, Carlos" required autocomplete="off">
                    
                    <label>Rol Institucional</label>
                    <select name="select_rol" required>
                        <option value="profesor">Profesor / Docente</option>
                        <option value="administrativo">Administrativo</option>
                        <option value="obrero">Personal Obrero</option>
                    </select>
                    
                    <button type="submit" name="btn_personal" class="btn-azul">Guardar Personal</button>
                </form>
            </div>

            <!-- FORMULARIO 2: REGISTRAR ESTUDIANTES -->
            <div class="card">
                <h5 class="t-verde">Registrar Estudiante</h5>
                <form action="admin_registro.php" method="POST">
                    <label>Cédula Escolar / Identificación</label>
                    <input type="text" name="cedula_est" required autocomplete="off">
                    
                    <label>Apellidos y Nombres</label>
                    <input type="text" name="nombre_est" placeholder="Ej: Perez, Juan" required autocomplete="off">
                    
                    <label>Asignar a Año / Sección</label>
                    <select name="seccion_est" required>
                        <option value="" disabled selected>-- Seleccione sección --</option>
                        <?php while($sec = $secciones_query->fetch_assoc()) { ?>
                            <option value="<?php echo $sec['id']; ?>"><?php echo $sec['nombre_seccion']; ?></option>
                        <?php } ?>
                    </select>
                    
                    <button type="submit" name="btn_estudiante" class="btn-verde">Guardar Estudiante</button>
                </form>
            </div>
            
        </div>
    </div>
</body>
</html>
