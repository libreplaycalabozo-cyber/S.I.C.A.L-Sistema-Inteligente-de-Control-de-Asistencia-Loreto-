<?php
include('conexion.php');
$mensaje = "";

if (isset($_POST['btn_seccion'])) {
    $nombre_seccion = $_POST['nombre_seccion'];
    $insertar = "INSERT INTO secciones (nombre_seccion) VALUES ('$nombre_seccion')";
    if ($conexion->query($insertar)) {
        $mensaje = "<div class='alerta verde'>¡Sección creada con éxito!</div>";
    }
}

if (isset($_POST['btn_materia'])) {
    $nombre_materia = $_POST['nombre_materia'];
    $horas_semanales = $_POST['horas_semanales']; 
    $insertar = "INSERT INTO materias (nombre_materia, horas_semanales) VALUES ('$nombre_materia', '$horas_semanales')";
    if ($conexion->query($insertar)) {
        $mensaje = "<div class='alerta verde'>¡Materia registrada con éxito con $horas_semanales horas semanales!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Estructura</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .contenedor { max-width: 500px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h4 { margin: 0 0 15px 0; color: #212529; font-size: 1.3rem; border-bottom: 3px solid #f6c23e; padding-bottom: 8px; font-weight: bold; }
        input, select { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 1rem; outline: none; }
        button { width: 100%; background: #f6c23e; color: #212529; padding: 13px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 0.9rem; }
        .alerta { padding: 12px; margin-bottom: 20px; border-radius: 6px; font-weight: bold; text-align: center; }
        .verde { background: #d1e7dd; color: #0f5132; }
    </style>
</head>
<body>
    <nav><a href="admin_panel.php">← VOLVER AL PANEL</a></nav>
    <div class="contenedor">
        <?php echo $mensaje; ?>
        
        <div class="card">
            <h4>Crear Año / Sección</h4>
            <form action="admin_estructura.php" method="POST">
                <input type="text" name="nombre_seccion" placeholder="Ej: 1er Ano - Seccion A" required autocomplete="off">
                <button type="submit" name="btn_seccion">Guardar Sección</button>
            </form>
        </div>

        <div class="card">
            <h4>Registrar Materia</h4>
            <form action="admin_estructura.php" method="POST">
                <input type="text" name="nombre_materia" placeholder="Ej: Contabilidad de Costos" required autocomplete="off">
                
                <label style="font-weight:bold; font-size:0.85rem; color:#555; display:block; margin-bottom:5px;">Horas Académicas Semanales:</label>
                <select name="horas_semanales" required>
                    <option value="2">2 Horas (1 Bloque)</option>
                    <option value="4" selected>4 Horas (2 Bloques)</option>
                    <option value="5">5 Horas (2 Bloques + 1 Hora suelta)</option>
                    <option value="6">6 Horas (3 Bloques - Requiere Bloque Especial)</option>
                    <option value="7">7 Horas (3 Bloques + 1 Hora suelta)</option>
                    <option value="8">8 Horas (4 Bloques)</option>
                    <option value="9">9 Horas (4 Bloques + 1 Hora suelta)</option>
                </select>
                
                <button type="submit" name="btn_materia">Guardar Materia</button>
            </form>
        </div>
    </div>
</body>
</html>
