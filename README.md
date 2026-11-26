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

6. **Iniciar sesión** con el usuario de prueba incluido en el script SQL:
   - Correo: `admin@tecnostock.cl`
   - Contraseña: `123456`

> Bootstrap se carga desde un CDN, por lo que se necesita conexión a internet para ver la interfaz con estilos.

## Funcionalidades principales
- Inicio y cierre de sesión con manejo de sesiones en PHP.
- CRUD de productos con validación de código único y baja lógica (`estado = 0`).
- Registro de entradas y salidas mediante transacciones SQL con bloqueo de stock negativo.
- Alertas visuales de stock mínimo e historial completo de movimientos con usuario responsable.
- Validaciones en JavaScript (`js/validaciones.js`) con mensajes junto a cada campo, repetidas en PHP y reforzadas con restricciones en la base de datos.

## Estructura del proyecto
- `login.php`, `logout.php`: inicio y cierre de sesión.
- `index.php`: listado de productos, búsqueda y alerta de stock bajo.
- `nuevo_producto.php`, `editar_producto.php`: registro y modificación de productos.
- `desactivar_producto.php`, `activar_producto.php`, `productos_inactivos.php`: baja lógica y reactivación.
- `registrar_movimiento.php`, `historial_movimientos.php`: entradas, salidas e historial.
- `conexion.php`: conexión PDO a MySQL.
- `js/validaciones.js`: validaciones del lado del cliente.
- `tecnostock.sql`: script de la base de datos con datos iniciales.

## Documentación
- [ANALISIS.md](ANALISIS.md): problema, alcance, actor, requerimientos, reglas de negocio y modelo de datos.
- [PRUEBAS.md](PRUEBAS.md): casos de prueba, datos usados y resultados.
