<?php
// Incluimos la conexión (que ya arranca la sesión automáticamente)
include('conexion.php');
date_default_timezone_set('America/Caracas');

// Leer las horas oficiales directamente desde la base de datos para los de la jornada
$consulta_config = "SELECT * FROM configuracion_sistema WHERE id = 1";
$res_config = $conexion->query($consulta_config);
$config = $res_config->fetch_assoc();
$hora_legal_salida = $config['hora_salida_legal']; 

if (isset($_POST['cedula']) && isset($_POST['accion_seleccionada'])) {
    $cedula = $_POST['cedula'];
    $accion = $_POST['accion_seleccionada'];

    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');
    $hora_formateada = date('h:i A');

    // 🛡️ REGLA MAESTRA DE DECRETOS Y FERIADOS
    $buscar_decreto = "SELECT * FROM decretos_feriados WHERE fecha = '$fecha_actual'";
    $res_decreto = $conexion->query($buscar_decreto);

    if ($res_decreto->num_rows > 0 && $accion != 'panel_admin') {
        $dec = $res_decreto->fetch_assoc();
        $tipo_d = ($dec['tipo'] == 'feriado') ? 'DÍA FERIADO OFICIAL' : 'DECRETO EJECUTIVO NO LABORAL';
        $detalle_d = $dec['motivo'];
        $num_d = !empty($dec['numero_decreto']) ? "\\nDecreto Nro: " . $dec['numero_decreto'] : "";
        $ente_d = !empty($dec['ente_emisor']) ? "\\nEmitido por: " . $dec['ente_emisor'] : "";
        
        echo "<script>alert('S.I.C.A.L. INFORMA:\\nHoy las actividades están suspendidas por:\\n⚠️ $tipo_d: $detalle_d $num_d $ente_d'); window.location.href = 'index.php';</script>";
        exit();
    }

    $consulta = "SELECT * FROM usuarios WHERE cedula = '$cedula'";
    $resultado = $conexion->query($consulta);

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $usuario_id = $usuario['id'];
        $nombre = $usuario['nombre_completo'];
        $roles_usuario = $usuario['rol'];

        $consulta_asistencia = "SELECT * FROM asistencia_personal WHERE usuario_id = '$usuario_id' AND fecha = '$fecha_actual'";
        $res_asistencia = $conexion->query($consulta_asistencia);

        // =========================================================================
        // CASO MULTI-OPCIÓN 3: PANEL ADMINISTRADOR CON SESIÓN DE RANGO PRIVADO
        // =========================================================================
        if ($accion == 'panel_admin') {
            if (isset($_POST['contrasena_admin'])) {
                $pass_admin = $_POST['contrasena_admin'];
                
                // Buscamos que coincida la identificación Y la contraseña de cualquier administrativo
                $consulta_admin = "SELECT * FROM usuarios WHERE cedula = '$cedula' AND contrasena = '$pass_admin'";
                $res_admin = $conexion->query($consulta_admin);

                if ($res_admin->num_rows > 0) {
                    $u_admin = $res_admin->fetch_assoc();
                    $rol_check = $u_admin['rol'];

                    // Verificar que no sea un usuario profesor puro o estudiante
                    if (strpos($rol_check, 'administrador') !== false || strpos($rol_check, 'subdirector') !== false || strpos($rol_check, 'secretario') !== false || strpos($rol_check, 'control_estudios') !== false) {
                        
                        // ⚡ ASIGNACIÓN CRÍTICA: Guardamos su rango exacto en la sesión
                        $_SESSION['usuario_rol'] = $rol_check;
                        
                        header("Location: admin_panel.php");
                        exit();
                    } else {
                        echo "<script>alert('Acceso Denegado: Su usuario no cuenta con credenciales administrativas.'); window.location.href = 'index.php';</script>";
                        exit();
                    }
                } else {
                    echo "<script>alert('Acceso Denegado: Código de Funcionario o Contraseña incorrectos.'); window.location.href = 'index.php';</script>";
                    exit();
                }
            }
        }

        // =========================================================================
        // CASO MULTI-OPCIÓN 1: REPORTAR ASISTENCIA LABORAL (ENTRADA / SALIDA)
        // =========================================================================
        if ($accion == 'asistencia_laboral') {
            if ($res_asistencia->num_rows == 0) {
                $insertar = "INSERT INTO asistencia_personal (usuario_id, fecha, hora_entrada, estatus_laboral) 
                             VALUES ('$usuario_id', '$fecha_actual', '$hora_actual', 'presente')";
                if ($conexion->query($insertar)) {
                    echo "<script>alert('¡Buenos días $nombre! Su ENTRADA LABORAL ha sido registrada a las $hora_formateada.'); window.location.href = 'index.php';</script>";
                }
            } else {
                $datos_asistencia = $res_asistencia->fetch_assoc();
                
                if ($datos_asistencia['estatus_laboral'] != 'presente') {
                    echo "<script>alert('Su jornada de hoy cuenta con una condición especial justificativa: " . strtoupper($datos_asistencia['estatus_laboral']) . "'); window.location.href = 'index.php';</script>";
                    exit();
                }

                if (!empty($datos_asistencia['hora_salida'])) {
                    echo "<script>alert('Usted ya registró su entrada y salida laboral por el día de hoy.'); window.location.href = 'index.php';</script>";
                    exit();
                }

                if (strtotime($hora_actual) < strtotime($hora_legal_salida)) {
                    if ($datos_asistencia['salida_anticipada_autorizada'] == 0) {
                        echo "<script>alert('Bloqueado: No ha cumplido su jornada laboral legal. Hora de salida autorizada: " . date('h:i A', strtotime($hora_legal_salida)) . ". Requiere autorización de Dirección.'); window.location.href = 'index.php';</script>";
                        exit();
                    }
                }

                $actualizar = "UPDATE asistencia_personal SET hora_salida = '$hora_actual' WHERE id = " . $datos_asistencia['id'];
                if ($conexion->query($actualizar)) {
                    echo "<script>alert('¡Feliz tarde $nombre! Su SALIDA LABORAL ha sido asentada a las $hora_formateada.'); window.location.href = 'index.php';</script>";
                }
            }
        }
        
        // =========================================================================
        // CASO MULTI-OPCIÓN 2: ENTRAR AL PANEL DE ASISTENCIA POR SECCIÓN (DOCENTES)
        // =========================================================================
        else if ($accion == 'panel_docente') {
            if (strpos($roles_usuario, 'profesor') === false) {
                echo "<script>alert('Acceso denegado: Esta opción es de uso exclusivo para el Personal Docente.'); window.location.href = 'index.php';</script>";
                exit();
            }
            if ($res_asistencia->num_rows == 0) {
                echo "<script>alert('¡Atención Prof. $nombre!: No puede acceder al panel de asistencia por sección si primero no ha reportado su ASISTENCIA LABORAL de llegada hoy. Por favor, marque su entrada primero.'); window.location.href = 'index.php';</script>";
                exit();
            }
            $_SESSION['profesor_id'] = $usuario_id;
            $_SESSION['nombre_profesor'] = $nombre;
            $_SESSION['fecha_sesion'] = $fecha_actual;
            header("Location: panel_profesor.php");
            exit();
        }

    } else {
        echo "<script>alert('Error: El número de identificación introducido no se encuentra en el sistema.'); window.location.href = 'index.php';</script>";
    }
}
?>
