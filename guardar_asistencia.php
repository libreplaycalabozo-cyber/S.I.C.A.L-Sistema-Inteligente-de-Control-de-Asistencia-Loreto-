<?php
// 1. Incluir la conexión a la base de datos
include('conexion.php');

// Asegurar la zona horaria correcta de Venezuela
date_default_timezone_set('America/Caracas');

// 2. Verificar que se hayan recibido los datos esperados
if (isset($_POST['asignacion_id']) && isset($_POST['asistencia'])) {
    
    $asignacion_id = $_POST['asignacion_id'];
    $asistencias = $_POST['asistencia']; // Esto es un arreglo con [estudiante_id => estatus]
    
    $fecha_actual = date('Y-m-d');
    $hora_actual = date('H:i:s');

    // 3. REGLA DE SEGURIDAD DE LOS 10 MINUTOS:
    // Verificamos si ya existe algún registro previo de asistencia para esta clase el día de hoy
    $consulta_control = "SELECT hora_registro FROM asistencia_estudiantes 
                         WHERE asignacion_id = '$asignacion_id' AND fecha = '$fecha_actual' 
                         LIMIT 1";
    $resultado_control = $conexion->query($consulta_control);

    if ($resultado_control->num_rows > 0) {
        // Ya se había pasado asistencia hoy. Validamos cuánto tiempo ha pasado
        $registro_previo = $resultado_control->fetch_assoc();
        $hora_primer_registro = $registro_previo['hora_registro'];

        // Convertimos las horas a segundos para restar la diferencia de tiempo
        $segundos_diferencia = strtotime($hora_actual) - strtotime($hora_primer_registro);
        $minutos_transcurridos = $segundos_diferencia / 60;

        // Si pasaron más de 10 minutos, bloqueamos el guardado en el servidor
        if ($minutos_transcurridos > 10) {
            echo "
            <script>
                alert('Error reglamentario: Han pasado más de 10 minutos desde el primer pase de lista. No se permiten más modificaciones hoy.');
                window.location.href = 'panel_profesor.php';
            </script>
            ";
            exit(); // Detiene por completo la ejecución del código
        }
    }

    // 4. GUARDAR O ACTUALIZAR LOS DATOS DE ASISTENCIA
    // Recorremos la lista de alumnos uno por uno
    foreach ($asistencias as $estudiante_id => $estatus) {
        
        // Revisamos si este estudiante específico ya tiene un registro hoy en esta materia
        $consulta_alumno = "SELECT id FROM asistencia_estudiantes 
                            WHERE estudiante_id = '$estudiante_id' AND asignacion_id = '$asignacion_id' AND fecha = '$fecha_actual'";
        $resultado_alumno = $conexion->query($consulta_alumno);

        if ($resultado_alumno->num_rows > 0) {
            // Si ya existe, actualizamos su estatus (por si llegó tarde dentro de los 10 min)
            $actualizar = "UPDATE asistencia_estudiantes SET estatus = '$estatus' 
                           WHERE estudiante_id = '$estudiante_id' AND asignacion_id = '$asignacion_id' AND fecha = '$fecha_actual'";
            $conexion->query($actualizar);
        } else {
            // Si es la primera vez en el día, insertamos el nuevo registro
            $insertar = "INSERT INTO asistencia_estudiantes (estudiante_id, asignacion_id, fecha, hora_registro, estatus) 
                         VALUES ('$estudiante_id', '$asignacion_id', '$fecha_actual', '$hora_actual', '$estatus')";
            $conexion->query($insertar);
        }
    }

    // 5. Mensaje de éxito final y redirección al panel del docente
    echo "
    <script>
        alert('¡Control de asistencia guardado con éxito!');
        window.location.href = 'panel_profesor.php';
    </script>
    ";

} else {
    // Redirección de seguridad si intentan entrar al archivo directamente sin enviar datos
    header("Location: panel_profesor.php");
    exit();
}
?>
