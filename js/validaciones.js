// Validaciones del lado del cliente de TecnoStock.
// Dan respuesta inmediata al usuario, pero NO reemplazan a PHP:
// las mismas reglas se vuelven a comprobar en el servidor.
//
// Cada formulario se identifica con el atributo data-validar:
//   login | producto | movimiento

// ---------- Funciones de apoyo ----------

// Muestra el mensaje de error debajo del campo (clases de Bootstrap)
function mostrarError(campo, mensaje) {
    limpiarError(campo);
    campo.classList.add('is-invalid');
    var aviso = document.createElement('div');
    aviso.className = 'invalid-feedback';
    aviso.textContent = mensaje;
    campo.insertAdjacentElement('afterend', aviso);
}

function limpiarError(campo) {
    campo.classList.remove('is-invalid');
    var siguiente = campo.nextElementSibling;
    if (siguiente && siguiente.classList.contains('invalid-feedback')) {
        siguiente.remove();
    }
}

// Entero >= 0 (stock) o > 0 (cantidad de un movimiento)
function esEntero(valor, minimo) {
    return /^\d+$/.test(valor) && parseInt(valor, 10) >= minimo;
}

// ---------- Reglas por campo ----------
// Cada función devuelve el mensaje de error, o '' si el valor es válido.

function validarTexto(campo, mensaje) {
    if (campo.value.trim() === '') {
        return mensaje;
    }
    return '';
}

function validarSeleccion(campo, etiqueta) {
    if (campo.value === '') {
        return 'Debes seleccionar ' + etiqueta + '.';
    }
    return '';
}

function validarCorreo(campo) {
    var valor = campo.value.trim();
    if (valor === '') {
        return 'El correo es obligatorio.';
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
        return 'Ingresa un correo válido.';
    }
    return '';
}

function validarPrecio(campo) {
    var valor = campo.value.trim();
    if (valor === '') {
        return 'El precio es obligatorio.';
    }
    if (isNaN(Number(valor)) || Number(valor) < 0) {
        return 'El precio no puede ser negativo.';
    }
    return '';
}

function validarStock(campo, etiqueta) {
    var valor = campo.value.trim();
    if (valor === '') {
        return etiqueta + ' es obligatorio.';
    }
    if (!esEntero(valor, 0)) {
        return etiqueta + ' debe ser un número entero mayor o igual a 0.';
    }
    return '';
}

// ---------- Formularios ----------

// Cada formulario devuelve una lista de pares [campo, funciónDeValidación]
function reglasLogin(f) {
    return [
        [f.elements['correo'], function (c) { return validarCorreo(c); }],
        [f.elements['clave'],  function (c) { return validarTexto(c, 'La contraseña es obligatoria.'); }]
    ];
}

// Sirve para "nuevo producto" y "editar producto":
// en editar no existen los campos codigo ni stock_actual, por eso se revisan si existen
function reglasProducto(f) {
    var reglas = [];
    if (f.elements['codigo']) {
        reglas.push([f.elements['codigo'], function (c) { return validarTexto(c, 'El código es obligatorio.'); }]);
    }
    reglas.push([f.elements['nombre'], function (c) { return validarTexto(c, 'El nombre es obligatorio.'); }]);
    reglas.push([f.elements['id_categoria'], function (c) { return validarSeleccion(c, 'una categoría'); }]);
    reglas.push([f.elements['precio'], validarPrecio]);
    if (f.elements['stock_actual']) {
        reglas.push([f.elements['stock_actual'], function (c) { return validarStock(c, 'El stock inicial'); }]);
    }
    reglas.push([f.elements['stock_minimo'], function (c) { return validarStock(c, 'El stock mínimo'); }]);
    return reglas;
}

function reglasMovimiento(f) {
    return [
        [f.elements['id_producto'], function (c) { return validarSeleccion(c, 'un producto'); }],
        [f.elements['tipo'], function (c) { return validarSeleccion(c, 'el tipo de movimiento'); }],
        [f.elements['cantidad'], function (c) { return validarCantidad(f, c); }]
    ];
}

// Regla de negocio: una salida no puede dejar el stock bajo cero.
// El stock de cada producto viene en el atributo data-stock de la opción elegida.
function validarCantidad(f, campo) {
    var valor = campo.value.trim();
    if (valor === '') {
        return 'La cantidad es obligatoria.';
    }
    if (!esEntero(valor, 1)) {
        return 'La cantidad debe ser un número entero mayor a 0.';
    }
    var opcion = f.elements['id_producto'].selectedOptions[0];
    if (f.elements['tipo'].value === 'salida' && opcion && opcion.dataset.stock !== undefined) {
        var stock = parseInt(opcion.dataset.stock, 10);
        if (parseInt(valor, 10) > stock) {
            return 'Stock insuficiente: solo hay ' + stock + ' unidades disponibles.';
        }
    }
    return '';
}

// Texto de ayuda con el stock disponible del producto elegido
function actualizarStockDisponible(f) {
    var ayuda = document.getElementById('stock-disponible');
    if (!ayuda) { return; }
    var opcion = f.elements['id_producto'].selectedOptions[0];
    if (opcion && opcion.dataset.stock !== undefined) {
        ayuda.textContent = 'Stock disponible: ' + opcion.dataset.stock;
    } else {
        ayuda.textContent = '';
    }
}

// ---------- Conexión con la página ----------

document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[data-validar]');
    if (!form) { return; }

    var reglas;
    switch (form.getAttribute('data-validar')) {
        case 'login':      reglas = reglasLogin(form); break;
        case 'producto':   reglas = reglasProducto(form); break;
        case 'movimiento': reglas = reglasMovimiento(form); break;
        default: return;
    }

    // Valida un campo y muestra u oculta su mensaje. Devuelve true si es válido.
    function revisar(par) {
        var mensaje = par[1](par[0]);
        if (mensaje === '') {
            limpiarError(par[0]);
            return true;
        }
        mostrarError(par[0], mensaje);
        return false;
    }

    // Al enviar: se revisan todos los campos y, si alguno falla, no se envía
    form.addEventListener('submit', function (evento) {
        var primerError = null;
        reglas.forEach(function (par) {
            if (!revisar(par) && primerError === null) {
                primerError = par[0];
            }
        });
        if (primerError !== null) {
            evento.preventDefault();
            primerError.focus();
        }
    });

    // Mientras el usuario escribe, se revisa el campo que está cambiando
    reglas.forEach(function (par) {
        ['input', 'change'].forEach(function (nombreEvento) {
            par[0].addEventListener(nombreEvento, function () {
                revisar(par);
                // En movimientos, cambiar producto o tipo cambia si la cantidad es válida
                if (form.getAttribute('data-validar') === 'movimiento') {
                    actualizarStockDisponible(form);
                    if (form.elements['cantidad'].value.trim() !== '') {
                        revisar(reglas[2]);
                    }
                }
            });
        });
    });
});
