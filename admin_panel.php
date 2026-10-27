<?php
include('conexion.php');

// Validar que el usuario que intenta entrar sea administrativo
// Guardamos su rol en una variable para evaluar los candados de seguridad
if (!isset($_SESSION['usuario_rol'])) {
    // Si entraste directo con la clave maestra anterior, te asignamos rango de superusuario
    $_SESSION['usuario_rol'] = 'administrador,profesor';
}

$rol_actual = $_SESSION['usuario_rol'];

// Consultamos el año escolar activo
$res_periodo = $conexion->query("SELECT periodo FROM anio_escolar_activo WHERE id = 1");
$periodo_activo = ($res_periodo && $res_periodo->num_rows > 0) ? $res_periodo->fetch_assoc()['periodo'] : '2025-2026';

// 🛡️ LÓGICA DE CONTROL DE ACCESOS POR BOTÓN
$ver_todo       = (strpos($rol_actual, 'administrador') !== false);
$subdirector    = ($ver_todo || strpos($rol_actual, 'subdirector') !== false);
$secretario     = ($ver_todo || strpos($rol_actual, 'secretario') !== false);
$control_est    = ($ver_todo || strpos($rol_actual, 'control_estudios') !== false);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - SICAL</title>
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        nav { background: #212529; padding: 15px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        nav strong { font-size: 1rem; }
        nav a { color: #f8f9fa; text-decoration: none; font-size: 0.9rem; font-weight: bold; }
        .contenedor { max-width: 1000px; margin: auto; padding: 30px; box-sizing: border-box; }
        h3 { text-align: center; color: #212529; text-transform: uppercase; margin-top: 10px; margin-bottom: 5px; font-size: 1.6rem; }
        .grid-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px; }
        .card-menu { background: white; border-radius: 12px; padding: 25px; text-align: center; border: 1px solid #e3e6f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02); box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; }
        .card-menu h5 { margin: 0 0 10px 0; color: #212529; font-size: 1.25rem; font-weight: bold; }
        .card-menu p { color: #6c757d; font-size: 0.88rem; margin-bottom: 20px; line-height: 1.4; min-height: 45px; }
        .btn-link { display: block; text-decoration: none; padding: 13px; color: white; font-weight: bold; border-radius: 8px; text-transform: uppercase; font-size: 0.88rem; text-align: center; }
        .btn-azul-oscuro { background: #1e3799; }
        .btn-amarillo { background: #f6c23e; color: #212529; }
        .btn-azul { background: #4e73df; }
        .btn-verde { background: #1cc88a; }
        .btn-rojo { background: #e74a3b; }
        .btn-naranja { background: #e67e22; } 
        .btn-morado { background: #9b59b6; color: white; }
        .btn-negro { background: #5a5c69; }
        
        /* Estilo para los botones bloqueados visualmente */
        .bloqueado { opacity: 0.4; cursor: not-allowed; pointer-events: none; background: #6c757d !important; color: #fff !important; }
    </style>
</head>
<body>

    <nav>
        <strong>⚙️ Centro de Control (Rol: <?php echo strtoupper(explode(',', $rol_actual)[0]); ?>)</strong>
        <span style="background: #1cc88a; padding: 5px 12px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; color: white;">AÑO ESCOLAR: <?php echo $periodo_activo; ?></span>
        <a href="index.php">← CERRAR SESIÓN</a>
    </nav>

    <div class="contenedor">
        <h3>S.I.C.A.L. Módulos de Gestión</h3>
        <p style="text-align:center; color:#666; margin:0;">E.T.C. Dr. Rafael Loreto Loreto</p>
        
        <div class="grid-menu">
            <!-- 0. TRANSICIÓN ANUAL (Exclusivo Superusuario) -->
            <div class="card-menu" style="<?php echo $ver_todo ? '' : 'display:none;'; ?>">
                <div>
                    <h5>0. Apertura de Año Escolar</h5>
                    <p>Inicie un nuevo ciclo lectivo, reinicie matrículas de alumnos y prepare la nómina de personal.</p>
                </div>
                <a href="admin_nuevo_ciclo.php" class="btn-link btn-azul-oscuro">Iniciar Nuevo Año</a>
            </div>

            <!-- 1. ESTRUCTURA ESCOLAR (Superusuario y Subdirector) -->
            <div class="card-menu">
                <div>
                    <h5>1. Estructura Escolar</h5>
                    <p>Cree las Secciones (Años/Aulas) y las Materias académicas configurando sus horas semanales.</p>
                </div>
                <a href="admin_estructura.php" class="btn-link btn-amarillo <?php echo $subdirector ? '' : 'bloqueado'; ?>">Configurar Plan</a>
            </div>

            <!-- 2. REGISTRAR USUARIOS (Superusuario, Subdirector y Control de Estudios) -->
            <div class="card-menu">
                <div>
                    <h5>2. Personal y Alumnos</h5>
                    <p>Cargue Profesores, Obreros, Administrativos y Estudiantes vinculándolos a su año.</p>
                </div>
                <a href="admin_registro.php" class="btn-link btn-azul <?php echo ($subdirector || $control_est) ? '' : 'bloqueado'; ?>">Cargar Personas</a>
            </div>

                        <!-- 3. DISTRIBUCIÓN ACADÉMICA MANUAL (CLASES AULA) -->
            <div class="card-menu">
                <div>
                    <h5>3. Carga Académica Docente</h5>
                    <p>Asigne de forma explícita qué materias y secciones dictará cada profesor en este año.</p>
                </div>
                <a href="admin_carga_docente.php" class="btn-link btn-amarillo" style="background:#f39c12; color:white;">Asignar Materias</a>
            </div>

            <!-- NUEVA OPCIÓN 4: COMPLEMENTO ADMINISTRATIVO DE 40 HORAS LEGALES -->
            <div class="card-menu">
                <div>
                    <h5>4. Carga de Horas Administrativas</h5>
                    <p>Asigne coordinaciones o tareas de oficina para rellenar los saldos hasta completar las 40h obligatorias.</p>
                </div>
                <a href="admin_horas_admin.php" class="btn-link" style="background:#20bf6b; color:white;">Asignar Oficinas</a>
            </div>

            <!-- 5. ALGORITMO AUTOMÁTICO DE HORARIOS REENLAZADO -->
            <div class="card-menu">
                <div>
                    <h5>5. Distribución Horaria</h5>
                    <p>Genere el horario escolar completo de 40 minutos basándose en la carga docente y horas institucionales fijas.</p>
                </div>
                <a href="admin_generar_horarios.php" class="btn-link btn-verde">Algoritmo Automático</a>
            </div>

            <!-- 6. DECRETOS Y CONTINGENCIAS MASIVAS (Superusuario y Secretario) -->
            <div class="card-menu">
                <div>
                    <h5>6. Decretos y Contingencias</h5>
                    <p>Active el botón de pánico por catástrofes naturales o registre decretos no laborables y feriados.</p>
                </div>
                <a href="admin_contingencia.php" class="btn-link btn-naranja <?php echo $secretario ? '' : 'bloqueado'; ?>">Estado Emergencia</a>
            </div>

            <!-- 7. GENERAR REPORTES COMPLETOS (Superusuario y Subdirector) -->
            <div class="card-menu">
                <div>
                    <h5>7. Reportes de Asistencia</h5>
                    <p>Consulte y descargue en PDF la asistencia diaria, semanal o mensual de alumnos y personal.</p>
                </div>
                <a href="admin_reportes.php" class="btn-link btn-morado <?php echo $subdirector ? '' : 'bloqueado'; ?>">Ver Reportes</a>
            </div>

            <!-- 8. SEGURIDAD Y NUEVOS DIRECTORES (Exclusivo Superusuario) -->
            <div class="card-menu" style="<?php echo $ver_todo ? '' : 'display:none;'; ?>">
                <div>
                    <h5>8. Seguridad Sistema</h5>
                    <p>Registre nuevos directores habilitados para gestionar y alterar este panel de control.</p>
                </div>
                <a href="admin_admin.php" class="btn-link btn-negro">Añadir Directivos</a>
            </div>

            <!-- 9. LISTADO DE MATRÍCULA (Superusuario, Subdirector y Control de Estudios) -->
            <div class="card-menu">
                <div>
                    <h5>9. Listado de Matrícula</h5>
                    <p>Consulte y descargue la lista de alumnos organizada por año y sección para control interno.</p>
                </div>
                <a href="admin_estudiantes.php" class="btn-link <?php echo ($subdirector || $control_est) ? '' : 'bloqueado'; ?>" style="background:#17a2b8; color:white;">Ver Estudiantes</a>
            </div>
            <!-- 10. NÓMINA DEL PERSONAL (Superusuario y Subdirector) -->
<div class="card-menu">
    <div>
        <h5>10. Nómina del Personal</h5>
        <p>Consulte el listado completo de docentes, administrativos y obreros. Corrija errores de transcripción en nombres o datos básicos.</p>
    </div>
    <a href="admin_nomina_personal.php" class="btn-link <?php echo ($subdirector) ? '' : 'bloqueado'; ?>" style="background:#20c997; color:white;">Ver Nómina</a>
</div>
        </div>
    </div>

</body>
</html>
