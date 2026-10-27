<?php include 'conexion.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I.C.A.L - E.T.C Dr Rafael Loreto Loreto</title>
    
    <style>
        body { 
            background-color: #f4f6f9; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh; 
            margin: 0; 
            padding: 20px;
            box-sizing: border-box;
        }
        .container { width: 100%; max-width: 480px; }
        .text-center { text-align: center; }
        .mb-4 { margin-bottom: 25px; }
        .text-muted { color: #6c757d; font-size: 0.95rem; }
        .card-asistencia { background: white; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); padding: 30px; }
        .menu-grid { display: flex; flex-direction: column; gap: 15px; }
        
        /* 🚀 EFECTO DE LETRAS EN 3D CON ALTO CONTRASTE */
        .titulo-3d {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1e3799; /* Azul Corporativo */
            text-transform: uppercase;
            letter-spacing: 3px;
            margin: 0;
            text-shadow: 
                1px 1px 0px #0c2461,
                2px 2px 0px #0c2461,
                3px 3px 0px #0c2461,
                4px 4px 0px #0c2461,
                5px 5px 8px rgba(0, 0, 0, 0.4);
        }

        /* ESTILOS SOLICITADOS */
        .significado-cursiva {
            font-style: italic;
            color: #4b6584;
            font-size: 0.95rem;
            margin: 5px 0 2px 0;
            display: block;
        }
        .version-negrita {
            font-weight: bold;
            color: #2f3542;
            font-size: 0.85rem;
            display: block;
            margin-bottom: 12px;
        }
        .institucion-texto {
            font-weight: bold; 
            color: #0c2461; 
            margin: 0 0 5px 0; 
            font-size: 0.9rem;
        }

        .btn-menu { text-align: left; padding: 16px 20px; border-radius: 12px; font-weight: bold; border: none; cursor: pointer; color: white; width: 100%; font-size: 1.05rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-verde { background-color: #198754; }
        .btn-azul { background-color: #0d6efd; }
        .btn-oscuro { background-color: #212529; }
        .nota-menu { font-size: 0.8rem; display: block; font-weight: normal; margin-top: 5px; opacity: 0.85; }
        .oculto { display: none !important; }
        .form-label { font-size: 1.1rem; color: #495057; display: block; margin-bottom: 15px; }
        .form-control-lg { width: 100%; padding: 14px; font-size: 1.2rem; text-align: center; border-radius: 8px; border: 1px solid #ced4da; box-sizing: border-box; outline: none; margin-bottom: 12px; }
        .btn-flex { display: flex; gap: 12px; margin-top: 20px; }
        .btn-accion { width: 50%; padding: 14px; font-weight: bold; border-radius: 8px; border: none; cursor: pointer; font-size: 1rem; text-transform: uppercase; }
        .btn-gris { background-color: #6c757d; color: white; }
        .btn-primario { background-color: #0d6efd; color: white; }
    </style>
</head>
<body>

    <div class="container">
        
            <div class="text-center mb-4">
                <!-- LOGO DINÁMICO DESDE EL SETUP -->
                <?php if(!empty($_SESSION['inst_logo'])): ?>
                    <div class="mb-3">
                        <img src="<?php echo $_SESSION['inst_logo']; ?>" alt="Logo de la Institución" style="max-height: 120px; width: auto;" class="img-fluid central-logo">
                    </div>
                <?php endif; ?>

                <h1 class="titulo-3d">S.I.C.A.L</h1>
                <span class="significado-cursiva">(Sistema Inteligente de Control de Asistencia)</span>
                <span class="version-negrita">versión 1.0</span>
                
                <!-- Muestra el nombre registrado o un texto de bienvenida si está vacío -->
                <p class="institucion-texto">
                    <?php echo !empty($_SESSION['inst_nombre']) ? htmlspecialchars($_SESSION['inst_nombre']) : "¡Bienvenido a S.I.C.A.L!"; ?>
                </p>
                <p class="text-muted" id="reloj-digital"></p>
            </div>
                <?php if (empty($_SESSION['inst_nombre'])): ?>
                    <div style="background-color: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; text-align: center; margin-bottom: 20px; border: 1px solid #ffeeba;">
                        <strong>⚙️ Configuración Inicial Requerida:</strong> El sistema está vacío. 
                        <a href="setup.php" style="color: #533f03; font-weight: bold; text-decoration: underline; margin-left: 10px;">Ir a Configurar Institución y Registro</a>
                    </div>
                <?php endif; ?>

        <div class="card card-asistencia">
            
            <!-- MENÚ PRINCIPAL -->
            <div id="menu-principal" class="menu-grid">
                <button onclick="mostrarFormulario('asistencia_laboral')" class="btn-menu btn-verde">
                    REPORTAR ASISTENCIA LABORAL
                    <span class="nota-menu">Para profesores, personal administrativo y obrero.</span>
                </button>

                <button onclick="mostrarFormulario('panel_docente')" class="btn-menu btn-azul">
                    PANEL DE ASISTENCIA POR SECCIÓN
                    <span class="nota-menu">Solo para docentes que ya marcaron su asistencia.</span>
                </button>

                <button onclick="mostrarFormulario('panel_admin')" class="btn-menu btn-oscuro">
                    ENTRAR AL PANEL ADMINISTRADOR
                    <span class="nota-menu">Solo para directores o funcionarios asignados.</span>
                </button>
            </div>

            <!-- CONTENEDOR DEL FORMULARIO DINÁMICO -->
            <div id="contenedor-formulario" class="oculto">
                <form id="form-cedula" action="procesar_inicio.php" method="POST">
                    <input type="hidden" id="accion_seleccionada" name="accion_seleccionada" value="">
                    
                    <div class="text-center">
                        <strong id="label-formulario" class="form-label fw-bold">Identificación</strong>
                    </div>
                    
                    <div>
                        <input type="text" class="form-control-lg" id="cedula" name="cedula" placeholder="Ingrese Identificación" required autocomplete="off">
                        <input type="password" class="form-control-lg oculto" id="contrasena_admin" name="contrasena_admin" placeholder="Ingrese Contraseña Secreta">
                    </div>

                    <div class="btn-flex">
                        <button type="button" onclick="volverAlMenu()" class="btn-accion btn-gris">CANCELAR</button>
                        <button type="submit" class="btn-accion btn-primario">CONTINUAR</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        function mostrarFormulario(opcion) {
            document.getElementById('menu-principal').classList.add('oculto');
            document.getElementById('contenedor-formulario').classList.remove('oculto');
            document.getElementById('accion_seleccionada').value = opcion;
            
            const label = document.getElementById('label-formulario');
            const inputCedula = document.getElementById('cedula');
            const inputPass = document.getElementById('contrasena_admin');
            
            inputPass.classList.add('oculto');
            inputPass.required = false;
            inputPass.value = "";

            if(opcion === 'asistencia_laboral') {
                label.innerText = 'REGISTRAR LLEGADA O SALIDA LABORAL';
                inputCedula.placeholder = "Ingrese su Cédula";
            } else if(opcion === 'panel_docente') {
                label.innerText = 'ACCEDER AL CONTROL DE CLASES';
                inputCedula.placeholder = "Ingrese Cédula Docente";
            } else if(opcion === 'panel_admin') {
                label.innerText = 'ACCESO PRIVADO DIRECCIÓN';
                inputCedula.placeholder = "Código de Administrador / Cédula";
                inputPass.classList.remove('oculto');
                inputPass.required = true;
            }
            inputCedula.focus();
        }

        function volverAlMenu() {
            document.getElementById('contenedor-formulario').classList.add('oculto');
            document.getElementById('menu-principal').classList.remove('oculto');
            document.getElementById('cedula').value = '';
            document.getElementById('contrasena_admin').value = '';
        }

        function actualizarReloj() {
            const ahora = new Date();
            const opciones = { weekday: 'long', day: 'numeric', month: 'short' };
            document.getElementById('reloj-digital').innerText = `${ahora.toLocaleDateString('es-ES', opciones)} | ${ahora.toLocaleTimeString('es-ES')}`;
        }
        setInterval(actualizarReloj, 1000);
        actualizarReloj();
    </script>
</body>
</html>
