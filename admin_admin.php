<?php
include('conexion.php');
$mensaje = "";

if (isset($_POST['btn_admin'])) {
    $usuario_id = $_POST['docente_id']; // Recibimos el ID del docente seleccionado
    $pass       = $_POST['contrasena'];
    $pass_conf  = $_POST['contrasena_conf'];

    if ($pass !== $pass_conf) {
        $mensaje = "<div class='alerta roja'>Error: Las contraseñas ingresadas no coinciden. Intente de nuevo.</div>";
    } else {
        // 🔍 BUSCAMOS EL ROL ACTUAL DE ESE DOCENTE
        $buscar_user = "SELECT rol FROM usuarios WHERE id = '$usuario_id'";
        $res_user = $conexion->query($buscar_user);
        
        if ($res_user->num_rows > 0) {
            $user_data = $res_user->fetch_assoc();
            $rol_actual = $user_data['rol'];

            // Si el usuario ya es administrador, evitamos duplicarlo
            if (strpos($rol_actual, 'administrador') !== false) {
                $mensaje = "<div class='alerta roja'>Este usuario ya cuenta con rango de Administrador en el sistema.</div>";
            } else {
                // 🔄 LE AGREGAMOS EL ROL DE ADMINISTRADOR SIN BORRAR EL DE PROFESOR
                // Quedará guardado en la celda como: 'profesor,administrador'
                $nuevo_rol = $rol_actual . ",administrador";
                
                // Actualizamos su rol y le asignamos la nueva contraseña secreta de dirección
                $actualizar = "UPDATE usuarios 
                               SET rol = '$nuevo_rol', contrasena = '$pass' 
                               WHERE id = '$usuario_id'";
                
                if ($conexion->query($actualizar)) {
                    $mensaje = "<div class='alerta verde'>¡Privilegios otorgados con éxito! El docente ahora es también Administrador.</div>";
                } else {
                    $mensaje = "<div class='alerta roja'>Error técnico al procesar los privilegios.</div>";
                }
            }
        }
    }
}

// 🔍 CONSULTAMOS ÚNICAMENTE A LOS USUARIOS QUE SON DOCENTES O PERSONAL GENERAL Y NO SON ADMINS TODAVÍA
$docentes_disponibles = $conexion->query("SELECT id, nombre_completo, cedula FROM usuarios WHERE rol NOT LIKE '%administrador%' ORDER BY nombre_completo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Administradores</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        nav a { color: white; text-decoration: none; font-weight: bold; font-size: 0.9rem; }
        .contenedor { max-width: 480px; margin: auto; padding: 25px; box-sizing: border-box; }
        .card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .card h4 { margin: 0 0 20px 0; color: #212529; text-align: center; font-size: 1.3rem; border-bottom: 2px solid #5a5c69; padding-bottom: 10px; font-weight: bold; }
        label { font-weight: bold; font-size: 0.85rem; color: #495057; display: block; margin-bottom: 5px; margin-top: 15px; }
        
        select, input { width: 100%; padding: 11px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; font-size: 0.95rem; outline: none; }
        select:focus, input:focus { border-color: #5a5c69; }
        
        .campo-password { position: relative; display: flex; align-items: center; }
        .campo-password input { padding-right: 45px; }
        
        .btn-ver { position: absolute; right: 5px; background: none; border: none; color: #6c757d; font-size: 0.8rem; font-weight: bold; cursor: pointer; padding: 8px; user-select: none; text-transform: uppercase; }
        
        button[type="submit"] { width: 100%; background: #5a5c69; color: white; padding: 13px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; text-transform: uppercase; margin-top: 25px; font-size: 0.9rem; }
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
            <h4>🔐 Otorgar Rango de Administrador</h4>
            
            <form action="admin_admin.php" method="POST" onsubmit="return validarContrasenas();">
                
                <label>Seleccione el Docente / Personal</label>
                <select name="docente_id" required>
                    <option value="" disabled selected>-- Elija un docente de la lista --</option>
                    <?php if ($docentes_disponibles->num_rows > 0) {
                        while($doc = $docentes_disponibles->fetch_assoc()) { ?>
                            <option value="<?php echo $doc['id']; ?>"><?php echo $doc['nombre_completo']; ?> (C.I: <?php echo $doc['cedula']; ?>)</option>
                        <?php }
                    } else { ?>
                        <option value="" disabled>No hay docentes disponibles para ascender.</option>
                    <?php } ?>
                </select>
                
                <label>Asignar Contraseña Administrativa</label>
                <div class="campo-password">
                    <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña para el panel admin" required>
                    <button type="button" class="btn-ver" onclick="alternarVisibilidad('contrasena', this)">Ver</button>
                </div>
                
                <label>Confirmar Contraseña</label>
                <div class="campo-password">
                    <input type="password" id="contrasena_conf" name="contrasena_conf" placeholder="Repita la contraseña" required>
                    <button type="button" class="btn-ver" onclick="alternarVisibilidad('contrasena_conf', this)">Ver</button>
                </div>
                
                <button type="submit" name="btn_admin">Otorgar Permisos de Dirección</button>
            </form>
        </div>
    </div>

    <script>
        function alternarVisibilidad(idCampo, boton) {
            const campo = document.getElementById(idCampo);
            if (campo.type === "password") {
                campo.type = "text";
                boton.innerText = "Ocultar";
                boton.style.color = "#0d6efd";
            } else {
                campo.type = "password";
                boton.innerText = "Ver";
                boton.style.color = "#6c757d";
            }
        }

        function validarContrasenas() {
            const pass = document.getElementById('contrasena').value;
            const conf = document.getElementById('contrasena_conf').value;
            
            if (pass !== conf) {
                alert("⚠️ Error: Las contraseñas no coinciden. Por favor, verifíquelas.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
