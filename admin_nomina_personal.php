<?php
// Incluimos la conexión que ya maneja la sesión y el radar de arranque
include 'conexion.php';

// Capturamos el rol exacto tal como lo haces en tu panel
$rol_actual = $_SESSION['usuario_rol']; // Ajusta aquí si usas $_SESSION['user_rol'] o similar

// 🛡️ MISMA LÓGICA DE CONTROL DE ACCESOS DE TU PANEL
$ver_todo       = (strpos($rol_actual, 'administrador') !== false);
$subdirector    = ($ver_todo || strpos($rol_actual, 'subdirector') !== false);

// Si no es administrador ni subdirector, se le niega el acceso
if (!$subdirector) {
    header("Location: admin_panel.php");
    exit();
}

// Consultamos todo el personal registrado en la institución
$query_personal = "SELECT id, cedula, nombre_completo, rol FROM usuarios ORDER BY nombre_completo ASC";
$resultado_personal = $conexion->query($query_personal);
// --- NUEVA LÓGICA DE EDICIÓN DE PERSONAL ---
$usuario_editar = null;

// A. Si se hace clic en Editar, buscamos los datos del usuario seleccionado
if (isset($_GET['editar_id'])) {
    $id_edit = intval($_GET['editar_id']);
    $query_edit = $conexion->query("SELECT id, cedula, nombre_completo, rol FROM usuarios WHERE id = $id_edit");
    if ($query_edit && $query_edit->num_rows > 0) {
        $usuario_editar = $query_edit->fetch_assoc();
    }
}

// B. Si el usuario envía el formulario con los cambios guardados
if (isset($_POST['guardar_cambios'])) {
    $id_update = intval($_POST['id_usuario']);
    $cedula_new = trim($_POST['cedula']);
    $nombre_new = trim($_POST['nombre_completo']);
    $rol_new    = trim($_POST['rol']);

    if (!empty($cedula_new) && !empty($nombre_new) && !empty($rol_new)) {
        $stmt = $conexion->prepare("UPDATE usuarios SET cedula = ?, nombre_completo = ?, rol = ? WHERE id = ?");
        $stmt->bind_param("sssi", $cedula_new, $nombre_new, $rol_new, $id_update);
        
        if ($stmt->execute()) {
            // Recargamos la página limpia para ver los cambios aplicados de inmediato
            header("Location: admin_nomina_personal.php?mensaje=actualizado");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>S.I.C.A.L - Nómina del Personal</title>
    <!-- Mantenemos la consistencia visual utilizando tus mismos estilos del sistema -->
    <link rel="stylesheet" href="css/estilos.css"> <!-- Ajusta la ruta de tu CSS si es necesario -->
    <style>
        .tabla-nomina { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .tabla-nomina th { background: #20c997; color: white; padding: 12px; text-align: left; font-size: 0.95rem; }
        .tabla-nomina td { padding: 12px; border-bottom: 1px solid #eee; color: #333; font-size: 0.9rem; }
        .tabla-nomina tr:hover { background: #f9f9f9; }
        .badge-rol { background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase; margin-right: 4px; display: inline-block; }
        .btn-accion { padding: 6px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; text-decoration: none; font-size: 0.85rem; display: inline-block; }
        .btn-editar { background: #ffc107; color: #212529; margin-right: 5px; }
        .btn-regresar { background: #6c757d; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">

    <!-- CABECERA DINÁMICA CON EL LOGO Y NOMBRE RECIÉN CONFIGURADOS -->
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #20c997; padding-bottom: 15px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center;">
            <?php if(!empty($_SESSION['inst_logo'])): ?>
                <img src="<?php echo $_SESSION['inst_logo']; ?>" alt="Logo" style="max-height: 60px; width: auto; margin-right: 15px;">
            <?php endif; ?>
            <div>
                <h2 style="margin: 0; color: #333; font-size: 1.6rem;">10. Nómina del Personal Completo</h2>
                <p style="margin: 0; color: #6c757d; font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['inst_nombre']); ?></p>
            </div>
        </div>
        <a href="admin_panel.php" class="btn-regresar" style="margin-bottom: 0;">Volver al Panel</a>
    </div>

    <!-- TABLA DE EXAMINACIÓN DE PERSONAL -->
        <!-- MENSAJE DE ÉXITO AL ACTUALIZAR -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'actualizado'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; font-weight: bold; text-align: center;">
            ✓ Datos del personal actualizados correctamente en la nómina.
        </div>
    <?php endif; ?>

    <!-- FORMULARIO INTERACTIVO DE EDICIÓN (Solo visible al hacer clic en Editar) -->
    <?php if ($usuario_editar): ?>
        <div style="background: #fff; border: 2px solid #ffc107; border-radius: 8px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h3 style="margin-top: 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                ✏️ Corregir Datos de: <span style="color: #007bff;"><?php echo htmlspecialchars($usuario_editar['nombre_completo']); ?></span>
            </h3>
            
            <form action="admin_nomina_personal.php" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <input type="hidden" name="id_usuario" value="<?php echo $usuario_editar['id']; ?>">
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555; font-size: 0.9rem;">Cédula de Identidad:</label>
                    <input type="text" name="cedula" value="<?php echo htmlspecialchars($usuario_editar['cedula']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555; font-size: 0.9rem;">Nombre Completo:</label>
                    <input type="text" name="nombre_completo" value="<?php echo htmlspecialchars($usuario_editar['nombre_completo']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #555; font-size: 0.9rem;">Cargos / Roles (Separados por coma):</label>
                    <input type="text" name="rol" value="<?php echo htmlspecialchars($usuario_editar['rol']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" placeholder="ej: profesor,secretario">
                </div>
                
                <div style="display: flex; align-items: flex-end; gap: 10px; grid-column: span 1;">
                    <button type="submit" name="guardar_cambios" style="background: #28a745; color: white; border: none; padding: 10px 15px; border-radius: 4px; font-weight: bold; cursor: pointer; flex: 1;">Guardar Cambios</button>
                    <a href="admin_nomina_personal.php" style="background: #dc3545; color: white; text-decoration: none; padding: 9px 15px; border-radius: 4px; font-weight: bold; text-align: center; flex: 1;">Cancelar</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <table class="tabla-nomina">
        <thead>
            <tr>
                <th>Cédula de Identidad</th>
                <th>Nombre Completo (Personal)</th>
                <th>Cargo / Roles Asignados</th>
                <th style="text-align: center; width: 150px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado_personal && $resultado_personal->num_rows > 0): ?>
                <?php while($user = $resultado_personal->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight: bold;"><?php echo htmlspecialchars($user['cedula']); ?></td>
                        <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                        <td>
                            <?php 
                            // Convertimos la cadena de roles (ej: "administrador,profesor") en etiquetas individuales legibles
                            $roles = explode(',', $user['rol']);
                            foreach($roles as $r) {
                                echo "<span class='badge-rol'>" . htmlspecialchars(trim($r)) . "</span>";
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <!-- Botón que enviará el ID por URL para abrir el formulario de edición -->
                            <a href="admin_nomina_personal.php?editar_id=<?php echo $user['id']; ?>" class="btn-accion btn-editar">Editar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #6c757d; padding: 30px;">
                        No hay ningún personal registrado en la base de datos actualmente.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>
