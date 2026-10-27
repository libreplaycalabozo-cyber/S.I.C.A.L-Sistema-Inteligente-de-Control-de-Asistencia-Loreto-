<?php
include('conexion.php'); // Aquí se conecta a la BD
$mensaje = "";

if (isset($_POST['btn_finalizar'])) {
    $nombre_escuela = $_POST['nombre_institucion'];
    $nombre_admin   = $_POST['nombre_admin'];
    $cedula_admin   = $_POST['cedula_admin'];
    $clave_admin    = $_POST['clave_admin'];

    // 1. PROCESAR EL LOGO (Corregido tmp_name)
    $nombre_logo = "logo_institucional.png"; 
    $ruta_destino = "img/" . $nombre_logo;
    
    // Verificamos si se subió un archivo y lo movemos
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $ruta_destino)) {
            // Logo guardado con éxito
        }
    }

    // 2. REGISTRAR SUPERUSUARIO MAESTRO
    $rol_maestro = "administrador,profesor";
    // Usamos REPLACE para evitar errores si el usuario ya existe
    $sql_u = "REPLACE INTO usuarios (cedula, nombre_completo, rol, contrasena) 
              VALUES ('$cedula_admin', '$nombre_admin', '$rol_maestro', '$clave_admin')";

    // 3. REGISTRAR CONFIGURACIÓN DE LA ESCUELA (Columnas corregidas)
    $sql_c = "REPLACE INTO configuracion_sistema (id, nombre_institucion, logo_institucion, hora_salida_legal) 
              VALUES (1, '$nombre_escuela', '$ruta_destino', '14:20:00')";

    if ($conexion->query($sql_u) && $conexion->query($sql_c)) {
        // Inicializamos el periodo escolar
        $conexion->query("INSERT INTO anio_escolar_activo (id, periodo, bloqueado) VALUES (1, '2025-2026', 0) ON DUPLICATE KEY UPDATE id=1");
        $mensaje = "exito";
    } else {
        $mensaje = "error: " . $conexion->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - Asistente de Configuración</title>
    <style>
        body { background: linear-gradient(135deg, #1e3799, #0984e3); font-family: 'Segoe UI', sans-serif; height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; }
        .setup-card { background: white; width: 100%; max-width: 500px; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); text-align: center; }
        .setup-card h2 { color: #2d3436; margin-bottom: 10px; font-size: 1.8rem; }
        .setup-card p { color: #636e72; font-size: 0.9rem; margin-bottom: 30px; }
        
        .form-group { text-align: left; margin-bottom: 15px; }
        label { display: block; font-weight: bold; font-size: 0.8rem; color: #2d3436; margin-bottom: 5px; text-transform: uppercase; }
        input[type="text"], input[type="password"], input[type="file"] { width: 100%; padding: 12px; border: 2px solid #dfe6e9; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: #0984e3; }
        
        .btn-finish { background: #00b894; color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: bold; font-size: 1rem; cursor: pointer; text-transform: uppercase; margin-top: 20px; transition: 0.3s; }
        .btn-finish:hover { background: #00947a; transform: translateY(-2px); }
        
        .logo-preview { width: 80px; height: 80px; background: #f1f2f6; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #b2bec3; border: 3px dashed #dfe6e9; }
        .success-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1000; }
    </style>
</head>
<body>

    <?php if($mensaje == "exito"): ?>
        <!-- PANTALLA DE ÉXITO FINAL -->
        <div class="success-overlay">
            <h1 style="font-size: 4rem; margin: 0;">🎉</h1>
            <h2 style="color: #2d3436;">¡Configuración Completada!</h2>
            <p style="color: #636e72;">S.I.C.A.L. 1.0 ha sido instalado con éxito.</p>
            <button class="btn-finish" style="width: 200px;" onclick="window.location.href='index.php'">Entrar al Sistema</button>
        </div>
    <?php endif; ?>

    <div class="setup-card">
        <div class="logo-preview" id="previewIcon">🏫</div>
        <h2>Bienvenido a S.I.C.A.L.</h2>
        <p>Complete los datos iniciales para activar su servidor institucional.</p>

        <form action="setup.php" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Nombre de la Institución:</label>
                <input type="text" name="nombre_institucion" placeholder="Ej: E.T.C. Dr. Rafael Loreto Loreto" required>
            </div>

            <div class="form-group">
                <label>Escudo / Logo (.png o .jpg):</label>
                <input type="file" name="logo" accept="image/*" required>
            </div>

            <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">
            <p style="margin-bottom: 15px; font-weight: bold; color: #1e3799;">CREAR CUENTA DE SUPERUSUARIO MAESTRO</p>

            <div class="form-group">
                <label>Nombre Completo del Administrador:</label>
                <input type="text" name="nombre_admin" placeholder="Ej: Carlos Mendoza" required>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>Cédula / ID:</label>
                    <input type="text" name="cedula_admin" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Clave Maestra:</label>
                    <input type="password" name="clave_admin" required>
                </div>
            </div>

            <button type="submit" name="btn_finalizar" class="btn-finish">Finalizar e Instalar SICAL v1.0</button>
        </form>
    </div>

</body>
</html>
