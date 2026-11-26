# Análisis y requerimientos – TecnoStock

Caso 1 del Taller de Sistemas de Información: sistema de inventario para TecnoStock.

## 1. Problema

TecnoStock es un emprendimiento que vende accesorios tecnológicos y lleva sus productos en una planilla. Con ese método han aparecido códigos duplicados, cantidades mal registradas y poca claridad sobre cuánto stock hay realmente.

Se necesita una aplicación web simple, que corra en un servidor local y que un usuario sin conocimientos técnicos pueda usar, para mantener el catálogo y controlar las entradas y salidas de existencias sin perder la integridad de los datos.

## 2. Alcance

**Incluye**

- Inicio y cierre de sesión de un usuario previamente registrado.
- Registro, consulta, búsqueda, modificación y desactivación de productos.
- Registro de entradas y salidas de existencias, con actualización automática del stock.
- Alerta visual cuando el stock está bajo el mínimo definido.
- Historial de movimientos con su usuario responsable.
- Reactivación de productos desactivados.

**No incluye**

- Administración de usuarios ni de categorías desde la aplicación. Se cargan mediante el script `tecnostock.sql`.
- Roles distintos al encargado de inventario.
- Ventas, facturación, proveedores o reportes.

## 3. Actor

| Actor | Responsabilidad |
|---|---|
| Encargado de inventario | Administra los productos y registra los movimientos de existencias. Es el único usuario del sistema. |

## 4. Requerimientos funcionales

La última columna indica en qué archivo se implementa y en qué casos de `PRUEBAS.md` se verifica.

| ID | Requerimiento | Implementación | Pruebas |
|---|---|---|---|
| RF-01 | Iniciar y cerrar sesión con un usuario registrado | `login.php`, `logout.php` | CP-01 a CP-03, JS-01 a JS-03 |
| RF-02 | Registrar productos con código, nombre, categoría, descripción, precio, stock actual y stock mínimo | `nuevo_producto.php` | CP-04 a CP-09, JS-04 a JS-08 |
| RF-03 | Consultar el listado de productos activos | `index.php` | CP-04, CP-10 |
| RF-04 | Buscar productos por nombre, código o categoría | `index.php` (consulta con `LIKE`) | CP-10, CP-11 |
| RF-05 | Modificar precio, descripción, categoría y stock mínimo de un producto | `editar_producto.php` | CP-20, CP-21, JS-09 |
| RF-06 | Desactivar productos que ya no se comercializan y poder reactivarlos | `desactivar_producto.php`, `activar_producto.php`, `productos_inactivos.php` | CP-22, CP-23 |
| RF-07 | Registrar entradas y salidas de existencias | `registrar_movimiento.php` | CP-12 a CP-17, JS-10 a JS-16 |
| RF-08 | Mostrar alerta cuando el stock es bajo | `index.php` | CP-18 |
| RF-09 | Consultar el historial de movimientos | `historial_movimientos.php` | CP-19 |

Sobre RF-05: el caso pide poder modificar las cantidades de un producto. En el sistema la cantidad (stock actual) cambia únicamente mediante entradas y salidas (RF-07). Se decidió así para que toda variación de stock quede registrada en el historial y no haya cambios sin trazabilidad. El formulario de edición permite cambiar el stock mínimo.

## 5. Restricciones y requerimientos no funcionales

- Sin frameworks complejos: HTML5, Bootstrap 5, JavaScript, PHP 8 y MySQL/MariaDB.
- Debe funcionar en un servidor local (XAMPP).
- Acceso a datos con PDO y consultas preparadas.
- Las validaciones se hacen en JavaScript (respuesta inmediata) y se repiten en PHP (el servidor no confía en el navegador). Archivo del lado cliente: `js/validaciones.js`.
- Interfaz comprensible para un usuario no técnico: mensajes de error claros y confirmación antes de desactivar un producto.
- Bootstrap se carga desde un CDN, por lo que la vista con estilos requiere conexión a internet.

## 6. Reglas de negocio

| ID | Regla | Dónde se aplica | Pruebas |
|---|---|---|---|
| RN-01 | El código de producto no puede repetirse | Índice `UNIQUE` en la base de datos y verificación previa en `nuevo_producto.php` | CP-05, CP-25 |
| RN-02 | Precio, stock actual, stock mínimo y cantidad de un movimiento no pueden ser negativos | `CHECK` en la base de datos, validación en PHP y en JavaScript | CP-06, CP-08, CP-16, CP-21, CP-26, JS-05, JS-06, JS-14 |
| RN-03 | Todo movimiento registra fecha, tipo, cantidad, producto y usuario responsable | Columnas `NOT NULL` en `movimiento`; el usuario se toma de la sesión y la fecha de `current_timestamp()` | CP-19 |
| RN-04 | Una salida no puede dejar el stock bajo cero | Comparación en `registrar_movimiento.php`, aviso en JavaScript y `CHECK` en la base de datos | CP-14, CP-15, CP-17, CP-26, JS-12, JS-13 |
| RN-05 | Un producto con movimientos se desactiva, no se elimina físicamente | La aplicación solo cambia `estado` a 0; la clave foránea de `movimiento` impide borrar el producto | CP-22, CP-24 |
| RN-06 | El stock se actualiza automáticamente después de cada movimiento válido | Transacción en `registrar_movimiento.php`: se inserta el movimiento y se actualiza el stock juntos; si algo falla se hace `rollBack` | CP-12, CP-13, CP-17 |

La alerta de stock bajo (RF-08) se muestra cuando `stock_actual <= stock_mínimo`.

## 7. Modelo de datos

El modelo tiene cuatro tablas. Los datos de cada concepto están en una sola tabla (la categoría no se repite dentro de cada producto, y el usuario no se repite dentro de cada movimiento), lo que evita redundancia y mantiene el modelo en tercera forma normal.

| Tabla | Atributos | Claves y restricciones |
|---|---|---|
| `usuario` | id_usuario, nombre, correo, clave, estado | PK `id_usuario`; `correo` único |
| `categoria` | id_categoria, nombre, descripción, estado | PK `id_categoria` |
| `producto` | id_producto, código, nombre, descripción, precio, stock_actual, stock_mínimo, estado, id_categoria | PK `id_producto`; `código` único; FK `id_categoria` → `categoria`; `CHECK` de precio, stock actual y stock mínimo ≥ 0 |
| `movimiento` | id_movimiento, fecha_hora, tipo, cantidad, observación, id_producto, id_usuario | PK `id_movimiento`; FK `id_producto` → `producto`; FK `id_usuario` → `usuario`; `CHECK` cantidad > 0 |

Relaciones:

- Una categoría tiene muchos productos; cada producto pertenece a una categoría.
- Un producto tiene muchos movimientos; cada movimiento corresponde a un producto.
- Un usuario realiza muchos movimientos; cada movimiento tiene un usuario responsable.

El campo `estado` vale 1 para registros activos y 0 para inactivos.

## 8. Decisiones de diseño

- **Baja lógica:** los productos no se eliminan, se desactivan, para conservar el historial de movimientos.
- **Stock modificado solo por movimientos:** garantiza que el stock siempre se pueda explicar a partir del historial.
- **Transacción en los movimientos:** el registro del movimiento y la actualización del stock se hacen juntos, de modo que no puede quedar uno sin el otro.
- **Validación en dos niveles:** JavaScript orienta al usuario al momento de escribir; PHP y la base de datos (`CHECK`, `UNIQUE`, claves foráneas) garantizan las reglas aunque se evite el navegador.
