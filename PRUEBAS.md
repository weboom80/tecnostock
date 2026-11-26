# Pruebas – TecnoStock

Este documento reúne los casos de prueba del sistema, los datos usados y los resultados obtenidos. Cada caso se relaciona con un requerimiento (RF) o una regla de negocio (RN) de [ANALISIS.md](ANALISIS.md).

## 1. Entorno y método

- Fecha de ejecución: 25-11-2026.
- Servidor: PHP 8 con el servidor web integrado (`php -S`) y MariaDB 10.11, la misma familia de motor que trae XAMPP.
- Base de datos: importada desde `tecnostock.sql` sin modificaciones.
- Casos CP-01 a CP-23: peticiones HTTP a cada página con sesión iniciada. Se envían directamente los formularios, sin pasar por JavaScript, para comprobar que PHP valida por sí solo. Después de cada caso se revisó el contenido de la tabla en la base de datos.
- Casos CP-24 a CP-26: sentencias SQL ejecutadas directamente en MariaDB, para comprobar que la base de datos protege la integridad aunque la aplicación fallara.
- Casos JS-01 a JS-16: cada formulario, tal como lo entrega PHP, se cargó en un DOM simulado (jsdom con Node.js) y se simularon el ingreso de datos y el envío.

## 2. Datos de prueba

| Dato | Valor |
|---|---|
| Usuario | `admin@tecnostock.cl` / `123456` |
| Producto existente 1 | `8320000`, WF-C510, stock 5, mínimo 1, categoría Audio |
| Producto existente 2 | `8320001`, Monitor Acer, stock 14, mínimo 3, categoría Periféricos PC |
| Producto de prueba | `T-001`, Cable USB-C, precio 5990, stock inicial 2, mínimo 3, categoría Accesorios Móviles |

Los productos de prueba se eliminaron de la base al terminar para dejarla como en el script original.

## 3. Pruebas de servidor y base de datos

Tipo: **N** = flujo normal, **E** = error esperado, **L** = caso límite.

### Sesión (RF-01)

| ID | Tipo | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-01 | N | Iniciar sesión con `admin@tecnostock.cl` y clave correcta | Redirige a `index.php` | Aprobado |
| CP-02 | E | Iniciar sesión con clave incorrecta | Muestra "Correo o contraseña incorrectos" y no inicia sesión | Aprobado |
| CP-03 | E | Abrir `index.php` sin sesión | Redirige a `login.php` | Aprobado |

### Registro y búsqueda de productos (RF-02, RF-03, RF-04)

| ID | Tipo | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-04 | N | Registrar `T-001` con datos válidos | Se guarda con estado 1 y aparece en el listado | Aprobado: en la BD quedó `T-001`, 5990.00, stock 2, mínimo 3, estado 1 |
| CP-05 | E | Registrar otro producto con el código `T-001` | Mensaje de código existente; no se crea otra fila | Aprobado: sigue habiendo una sola fila con `T-001` |
| CP-06 | E | Registrar producto con precio -5 | Mensaje de valores negativos; no se guarda | Aprobado |
| CP-07 | E | Registrar producto con código vacío | Mensaje de campos obligatorios; no se guarda | Aprobado |
| CP-08 | E | Registrar producto con stock inicial -4 | Mensaje de valores negativos; no se guarda | Aprobado |
| CP-09 | L | Registrar producto con precio 0, stock 0 y mínimo 0 | Se guarda | Aprobado: precio 0.00 en la BD |
| CP-10 | N | Buscar `monitor` (nombre), `8320000` (código) y `audio` (categoría) | Devuelve los productos que coinciden | Aprobado: 1, 1 y 2 filas (en la categoría Audio había dos productos en esa ejecución) |
| CP-11 | E | Buscar un texto sin coincidencias (`zzzz`) | Muestra "No se encontraron productos registrados" | Aprobado |

### Movimientos de existencias (RF-07, RN-04, RN-06)

El producto usado es `T-001`, que parte con stock 2.

| ID | Tipo | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-12 | N | Entrada de 10 unidades | Stock pasa de 2 a 12 | Aprobado: stock 12 |
| CP-13 | N | Salida de 5 unidades | Stock pasa de 12 a 7 | Aprobado: stock 7 |
| CP-14 | E | Salida de 100 unidades (stock 7) | Rechazada con mensaje de stock insuficiente; el stock no cambia | Aprobado: stock sigue en 7 |
| CP-15 | L | Salida de 8 unidades (stock 7, es decir, stock + 1) | Rechazada; el stock no cambia | Aprobado: stock sigue en 7 |
| CP-16 | E | Cantidad 0 y cantidad -3 | Rechazadas con mensaje; no se registra el movimiento | Aprobado |
| CP-17 | L | Salida de 7 unidades (igual al stock) | Se acepta y el stock queda en 0 | Aprobado: stock 0 |

### Alerta e historial (RF-08, RF-09, RN-03)

| ID | Tipo | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-18 | N | Ver el listado con `T-001` en stock 0 (mínimo 3) y el Monitor Acer en stock 14 (mínimo 3) | `T-001` con insignia roja "¡Bajo Stock!"; el monitor con "Normal" | Aprobado |
| CP-19 | N | Abrir el historial después de los movimientos válidos | Cada movimiento muestra fecha, tipo, cantidad, producto y responsable | Aprobado: los 3 movimientos válidos de `T-001` quedaron con usuario y fecha, y el historial muestra al responsable |

### Edición y desactivación (RF-05, RF-06, RN-05)

| ID | Tipo | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-20 | N | Editar `T-001`: nombre, descripción, precio 7990, mínimo 4 y otra categoría | Los cambios se guardan | Aprobado: la BD quedó con "Cable USB-C 2m", 7990.00, mínimo 4 y categoría 2 |
| CP-21 | E | Editar con precio -1 | Mensaje de valores negativos; no se guarda | Aprobado |
| CP-22 | N | Desactivar `T-001` | Estado 0; no aparece en el listado; aparece en inactivos; la fila sigue en la BD | Aprobado |
| CP-23 | N | Reactivar `T-001` desde la lista de inactivos | Estado 1; vuelve al listado | Aprobado |

### Integridad en la base de datos (RN-01, RN-02, RN-04, RN-05)

| ID | Tipo | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| CP-24 | E | `DELETE` de un producto que tiene movimientos | La clave foránea lo impide | Aprobado: error 1451 (restricción de clave foránea) |
| CP-25 | E | `INSERT` de un producto con un código ya existente | El índice único lo impide | Aprobado: error 1062 (entrada duplicada para la clave `código`) |
| CP-26 | E | `UPDATE` que deja el stock de un producto en -1 | La restricción `CHECK` lo impide | Aprobado: error 4025 (falla la restricción `stock_actual`) |

## 4. Pruebas de validación en JavaScript

Estas pruebas comprueban que el formulario muestra el error junto al campo y no se envía cuando los datos no cumplen las reglas (archivo `js/validaciones.js`).

| ID | Formulario | Caso | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|
| JS-01 | Login | Enviar vacío | No se envía; avisa que correo y contraseña son obligatorios | Aprobado |
| JS-02 | Login | Correo sin formato válido | No se envía; "Ingresa un correo válido." | Aprobado |
| JS-03 | Login | Datos válidos | Se envía sin mensajes de error | Aprobado |
| JS-04 | Nuevo producto | Enviar vacío | No se envía; marca código, nombre, categoría y precio | Aprobado |
| JS-05 | Nuevo producto | Precio -5 | No se envía; "El precio no puede ser negativo." | Aprobado |
| JS-06 | Nuevo producto | Stock inicial -2 | No se envía; pide un entero mayor o igual a 0 | Aprobado |
| JS-07 | Nuevo producto | Stock inicial 2.5 | No se envía; pide un número entero | Aprobado |
| JS-08 | Nuevo producto | Datos válidos, y también precio 0 (límite) | Se envía sin mensajes de error | Aprobado |
| JS-09 | Editar producto | Datos cargados sin cambios, y nombre en blanco | Los datos cargados se envían; el nombre en blanco no | Aprobado |
| JS-10 | Movimiento | Enviar vacío | No se envía; avisa producto, tipo y cantidad | Aprobado |
| JS-11 | Movimiento | Elegir un producto con stock 14 | Muestra "Stock disponible: 14" | Aprobado |
| JS-12 | Movimiento | Salida de 15 con stock 14 | No se envía; "Stock insuficiente: solo hay 14 unidades disponibles." | Aprobado |
| JS-13 | Movimiento | Salida de 14 con stock 14 (límite) | Se envía sin mensajes de error | Aprobado |
| JS-14 | Movimiento | Cantidad 0 y cantidad -3 | No se envía; pide un entero mayor a 0 | Aprobado |
| JS-15 | Movimiento | Entrada de 500 unidades | Se envía sin mensajes de error (las entradas no dependen del stock) | Aprobado |
| JS-16 | Movimiento | Cantidad 500 y cambiar el tipo de entrada a salida | La cantidad se revalida y aparece el error de stock insuficiente | Aprobado |

## 5. Resumen

| Grupo | Casos | Aprobados |
|---|---|---|
| Servidor y base de datos (CP-01 a CP-26) | 26 | 26 |
| JavaScript (JS-01 a JS-16) | 16 | 16 |

Todos los casos pasaron. Las reglas de negocio RN-01 a RN-06 quedaron comprobadas en la aplicación y, las de integridad, también directamente en la base de datos.
