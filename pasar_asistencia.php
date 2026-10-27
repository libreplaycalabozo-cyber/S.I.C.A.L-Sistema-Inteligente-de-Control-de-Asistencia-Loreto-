<?php
include('conexion.php');
date_default_timezone_set('America/Caracas');

// Obtener el ID de la asignación desde el enlace
$asignacion_id = isset($_GET['asignacion']) ? $_GET['asignacion'] : 1;

// Consultar los datos de la Materia y Sección
$consulta_info = "SELECT m.nombre_materia, s.nombre_seccion 
                  FROM asignaciones_profesor ap
                  INNER JOIN materias m ON ap.materia_id = m.id
                  INNER JOIN secciones s ON ap.seccion_id = s.id
                  WHERE ap.id = '$asignacion_id'";
$res_info = $conexion->query($consulta_info);
$info = $res_info->fetch_assoc();

// Consultar los estudiantes pertenecientes a esta sección
$consulta_alumnos = "SELECT e.id AS estudiante_id, e.nombre_completo, e.cedula 
                     FROM asignaciones_profesor ap
                     INNER JOIN estudiantes e ON ap.seccion_id = e.seccion_id
                     WHERE ap.id = '$asignacion_id'
                     ORDER BY e.nombre_completo ASC";
$resultado_alumnos = $conexion->query($consulta_alumnos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasar Asistencia</title>
    <style>
        /* ESTILOS AUTÓNOMOS INTEGRADOS */
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 0; 
        }
        nav { 
            background: #212529; 
            padding: 15px; 
            color: white; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        nav a { color: #f8f9fa; text-decoration: none; font-size: 0.9rem; font-weight: bold; }
        nav strong { font-size: 1rem; }
        
        .temporizador-alerta { 
            background-color: #fff3cd; 
            color: #664d03; 
            padding: 12px; 
            text-align: center; 
            font-weight: bold; 
            border-bottom: 1px solid #ffe69c;
            font-size: 0.95rem;
        }
        
        .contenedor { 
            max-width: 600px; 
            margin: auto; 
            padding: 15px; 
            box-sizing: border-box;
        }
        
        .caja-info { 
            background: white; 
            padding: 15px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            border: 1px solid #e3e6f0; 
        }
        .caja-info h5 { margin: 0 0 5px 0; color: #212529; font-size: 1.15rem; }
        .caja-info small { color: #6c757d; font-size: 0.85rem; }
        
        /* FILAS DE ALUMNOS */
        .tarjeta-estudiante { 
            background: white; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 10px; 
            border: 1px solid #e3e6f0; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
            box-sizing: border-box;
        }
        .info-alumno small { color: #858796; font-size: 0.8rem; display: block; margin-bottom: 2px; }
        .info-alumno strong { color: #212529; font-size: 1rem; }
        
        /* BOTONES DE ASISTENCIA COMPACTOS (P - A - T) */
        .grupo-opciones { display: flex; gap: 8px; }
        .grupo-opciones label { 
            background-color: #f8f9fa; 
            border: 1px solid #d1d3e2; 
            padding: 8px 14px; 
            border-radius: 6px; 
            font-weight: bold; 
            font-size: 0.9rem; 
            cursor: pointer;
            user-select: none;
        }
        /* Cambios de color dinámicos al seleccionar con el dedo */
        .grupo-opciones input[type="radio"] { display: none; }
        .grupo-opciones input[type="radio"]:checked + .lab-p { background-color: #198754; color: white; border-color: #198754; }
        .grupo-opciones input[type="radio"]:checked + .lab-a { background-color: #dc3545; color: white; border-color: #dc3545; }
        .grupo-opciones input[type="radio"]:checked + .lab-t { background-color: #ffc107; color: #212529; border-color: #ffc107; }
        
        .btn-enviar { 
            width: 100%; 
            background: #198754; 
            color: white; 
            padding: 16px; 
            border: none; 
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            font-size: 1.05rem; 
            margin-top: 15px; 
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(25,135,84,0.15);
        }
        .btn-enviar:disabled { background-color: #858796; cursor: not-allowed; box-shadow: none; }
    </style>
</head>
<body>

    <nav>
        <a href="panel_profesor.php">← VOLVER</a>
        <strong><?php echo $info['nombre_seccion']; ?></strong>
    </nav>

    <div id="barra-tiempo" class="temporizador-alerta">
        Tiempo restante para correcciones: <span id="minutos-restantes">10:00</span>
    </div>

    <div class="contenedor">
        
        <div class="caja-info">
            <h5>Materia: <?php echo $info['nombre_materia']; ?></h5>
            <small>Fecha de Control: <?php echo date('d-m-Y'); ?></small>
        </div>

        <form action="guardar_asistencia.php" method="POST">
            <input type="hidden" name="asignacion_id" value="<?php echo $asignacion_id; ?>">
            
            <h6 style="color: #4e73df; text-transform: uppercase; font-weight: bold; margin: 0 0 12px 0; font-size: 0.85rem; letter-spacing: 0.5px;">Listado de Alumnos</h6>

            <?php 
            if ($resultado_alumnos->num_rows > 0) {
                $num = 1;
                while($alumno = $resultado_alumnos->fetch_assoc()) {
                    $est_id = $alumno['estudiante_id'];
            ?>
                    <div class="tarjeta-estudiante">
                        <div class="info-alumno">
                            <small>Nro. <?php echo str_pad($num, 2, "0", STR_PAD_LEFT); ?></small>
                            <strong><?php echo $alumno['nombre_completo']; ?></strong>
                        </div>
                        
                        <!-- Selectores masivos de un toque -->
                        <div class="grupo-opciones">
                            <input type="radio" id="p_<?php echo $est_id; ?>" name="asistencia[<?php echo $est_id; ?>]" value="presente" checked>
                            <label for="p_<?php echo $est_id; ?>" class="lab-p">P</label>

                            <input type="radio" id="a_<?php echo $est_id; ?>" name="asistencia[<?php echo $est_id; ?>]" value="ausente">
                            <label for="a_<?php echo $est_id; ?>" class="lab-a">A</label>

                            <input type="radio" id="t_<?php echo $est_id; ?>" name="asistencia[<?php echo $est_id; ?>]" value="tarde">
                            <label for="t_<?php echo $est_id; ?>" class="lab-t">T</label>
                        </div>
                    </div>
            <?php 
                    $num++;
                }
            } else {
                echo "<div style='background: white; padding: 15px; border-radius: 8px; text-align: center; border: 1px dashed #ced4da;'>No hay estudiantes registrados en esta sección.</div>";
            }
            ?>

            <button type="submit" id="btn-guardar" class="btn-enviar">
                GUARDAR CONTROL DIARIO
            </button>
        </form>
    </div>

    <!-- Script de Cuenta Regresiva de 10 Minutos -->
    <script>
        let tiempoRestante = 10 * 60; 
        const elementoContador = document.getElementById('minutos-restantes');
        const barraTiempo = document.getElementById('barra-tiempo');
        const botonGuardar = document.getElementById('btn-guardar');
        const controles = document.querySelectorAll('input[type="radio"]');

        const cuentaRegresiva = setInterval(() => {
            let minutos = Math.floor(tiempoRestante / 60);
            let segundos = tiempoRestante % 60;
            segundos = segundos < 10 ? '0' + segundos : segundos;
            elementoContador.textContent = `${minutos}:${segundos}`;

            if (tiempoRestante <= 60) {
                barraTiempo.style.backgroundColor = '#f8d7da';
                barraTiempo.style.color = '#842029';
                barraTiempo.style.borderBottom = '1px solid #f5c2c7';
            }

            if (tiempoRestante <= 0) {
                clearInterval(cuentaRegresiva);
                barraTiempo.innerHTML = "⚠️ ¡TIEMPO REGLAMENTARIO AGOTADO! Edición deshabilitada.";
                barraTiempo.style.backgroundColor = '#f8d7da';
                barraTiempo.style.color = '#842029';
                botonGuardar.disabled = true;
                controles.forEach(control => control.disabled = true);
            }
            tiempoRestante--;
        }, 1000);
    </script>
</body>
</html>
