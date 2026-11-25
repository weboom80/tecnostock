# TecnoStock - Sistema de Control de Inventario

Aplicación web desarrollada en PHP (PDO), MySQL, HTML y Bootstrap para la gestión de inventario, control de entradas/salidas y trazabilidad de movimientos.

## Requisitos
- XAMPP (Apache + MySQL / MariaDB)
- Navegador web

## Instalación y ejecución local

1. **Clonar o descargar el repositorio:**
   Colocar la carpeta del proyecto dentro del directorio raíz del servidor local:
   `C:\xampp\htdocs\tecnostock`

2. **Iniciar servicios:**
   Abrir el Panel de Control de XAMPP e iniciar **Apache** y **MySQL**.

3. **Importar la base de datos:**
   - Abrir `http://localhost/phpmyadmin`
   - Crear una base de datos llamada `tecnostock`
   - Ir a la pestaña **Importar**, seleccionar el archivo `tecnostock.sql` incluido en este repositorio y ejecutar.

4. **Verificar conexión:**
   Revisar que las credenciales en `conexion.php` coincidan con tu entorno local (por defecto usuario `root` y contraseña vacía en XAMPP).

5. **Acceder al sistema:**
   Abrir en el navegador: `http://localhost/tecnostock/login.php`

## Funcionalidades principales
- Inicio y cierre de sesión con manejo de sesiones en PHP.
- CRUD de productos con validación de código único y baja lógica (`estado = 0`).
- Registro de entradas y salidas mediante transacciones SQL con bloqueo de stock negativo.
- Alertas visuales de stock mínimo e historial completo de movimientos con usuario responsable.