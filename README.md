# S.I.C.A.L. (Sistema Inteligente de Control de Asistencia y Horarios)

Sistema web institucional desarrollado para la gestión académica, optimización de cargas docentes y control de asistencia en la **E.T.C. Dr. Rafael Loreto Loreto**.

## 🛠️ Tecnologías utilizadas
* **PHP** (Lógica del backend)
* **MySQL / MariaDB** (Gestión de base de datos a través de phpMyAdmin)
* **HTML5 / CSS3 / JavaScript** (Interfaz de usuario)
* **XAMPP** (Entorno de desarrollo local)

---

## 📥 Guía de Instalación Local

Sigue estos pasos para poner en marcha el sistema en tu computadora utilizando XAMPP:

1. **Clonar o descargar el repositorio:**
   * Descarga este repositorio como archivo ZIP desde el botón verde **Code > Download ZIP** en GitHub, o clónalo.
   
2. **Ubicación de los archivos:**
   * Descomprime y coloca la carpeta del proyecto dentro del directorio raíz de tu servidor local XAMPP:
     `C:\xampp\htdocs\asistencia`

3. **Configurar la Base de Datos:**
   * Inicia los servicios de **Apache** y **MySQL** desde el panel de control de XAMPP.
   * Abre tu navegador y entra a [phpMyAdmin](http://localhost/phpmyadmin/).
   * Crea una nueva base de datos llamada `control_asistencia`.
   * Entra a la base de datos recién creada, ve a la pestaña **Importar**, selecciona el archivo `control_asistencia.sql` incluido en este repositorio y haz clic en **Continuar**.

4. **Ejecutar el Sistema:**
   * Abre tu navegador web y accede a la siguiente dirección:
     `http://localhost/asistencia/`

---

## 🏫 Institución
E.T.C. Dr. Rafael Loreto Loreto — 4to Año de Telemática.
