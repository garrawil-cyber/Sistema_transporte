/**
 * ============================================================
 * FORM HELPERS
 * Utilidades reutilizables para formularios
 * Sistema de Transporte
 * ============================================================
 */

// ============================================================
// 1. HELPERS GENÉRICOS
// ============================================================

/**
 * Aplica una transformación al input en tiempo real
 * @param {string} inputId
 * @param {function} transformFn - Recibe el valor, devuelve el valor transformado
 */
function aplicarTransformacion(inputId, transformFn) {
    const input = document.getElementById(inputId);
    if (!input) return null;

    input.addEventListener('input', function() {
        const antes = this.value;
        const despues = transformFn(antes);
        if (antes !== despues) {
            const cursorPos = this.selectionStart;
            this.value = despues;
            const nuevaPos = Math.min(cursorPos, despues.length);
            this.setSelectionRange(nuevaPos, nuevaPos);
        }
    });

    return input;
}

// ============================================================
// 2. VALIDADORES ESPECÍFICOS
// ============================================================


/**
 * Solo números, con longitud máxima
 */
function aplicarSoloNumeros(inputId, maxDigitos = null) {
    return aplicarTransformacion(inputId, function(valor) {
        let limpio = valor.replace(/\D/g, '');
        if (maxDigitos !== null) {
            limpio = limpio.slice(0, maxDigitos);
        }
        return limpio;
    });
}

/**
 * Solo teléfono (números, máx 9 dígitos por defecto)
 */
function aplicarTelefono(inputId, maxDigitos = 9) {
    return aplicarSoloNumeros(inputId, maxDigitos);
}

/**
 * Solo alfanumérico (mayúsculas, permite guiones)
 */
function aplicarAlfanumerico(inputId, maxLength = 20, permitirGuiones = true) {
    const patron = permitirGuiones ? /[^A-Z0-9\-]/g : /[^A-Z0-9]/g;
    return aplicarTransformacion(inputId, function(valor) {
        return valor.toUpperCase().replace(patron, '').slice(0, maxLength);
    });
}

/**
 * Solo letras (incluye tildes, ñ y espacios) — para nombres de personas
 */
function aplicarSoloLetras(inputId, maxLength = 100) {
    const patron = /[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g;
    return aplicarTransformacion(inputId, function(valor) {
        return valor.replace(patron, '').slice(0, maxLength);
    });
}

/**
 * Aplica un límite numérico a un input number
 */
function aplicarRangoNumerico(inputId, min = null, max = null, decimales = 2) {
    const input = document.getElementById(inputId);
    if (!input) return null;

    input.addEventListener('blur', function() {
        let v = parseFloat(this.value);

        if (isNaN(v)) {
            v = min !== null ? min : 0;
        }

        if (min !== null && v < min) v = min;
        if (max !== null && v > max) v = max;

        this.value = v.toFixed(decimales);
    });

    return input;
}

/**
 * Valida un email al salir del campo
 */
function aplicarValidacionEmail(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return null;

    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    input.addEventListener('blur', function() {
        const val = this.value.trim();
        if (val && !patron.test(val)) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
            if (typeof mostrarToast === 'function') {
                mostrarToast('Email inválido', 'warning');
            }
        } else if (val) {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    });

    return input;
}

// ============================================================
// 3. UTILIDADES DE FOTO
// ============================================================

/**
 * Aplica preview + validación a un input file de imagen
 * @param {string} inputId
 * @param {string} previewId
 * @param {object} opciones - { maxMB: 2, tipos: ['image/jpeg', ...] }
 */
function aplicarPreviewFoto(inputId, previewId, opciones = {}) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return null;

    const maxMB = opciones.maxMB || 2;
    const tiposPermitidos = opciones.tipos || ['image/jpeg', 'image/png', 'image/webp'];

    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        // Validar tamaño
        if (file.size > maxMB * 1024 * 1024) {
            if (typeof mostrarToast === 'function') {
                mostrarToast(`La imagen no puede superar ${maxMB} MB`, 'warning');
            }
            this.value = '';
            return;
        }

        // Validar tipo
        if (!tiposPermitidos.includes(file.type)) {
            if (typeof mostrarToast === 'function') {
                mostrarToast('Solo se permiten imágenes JPG, PNG o WEBP', 'warning');
            }
            this.value = '';
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}"
                style="width: 100%; height: 100%; object-fit: cover;">`;
        };
        reader.readAsDataURL(file);
    });

    return input;
}

// ============================================================
// 4. VALIDACIÓN DE FECHAS
// ============================================================

/**
 * Valida que una fecha sea futura (>= hoy)
 */
function aplicarFechaFutura(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return null;

    input.addEventListener('blur', function() {
        if (!this.value) return;

        // ✅ FIX zona horaria: new Date('YYYY-MM-DD') se interpreta como
        //    medianoche UTC, no medianoche local. En zonas UTC-negativas
        //    (ej. Perú, UTC-5) esto corría el día un día hacia atrás al
        //    convertir a hora local, haciendo que HOY se marcara como
        //    fecha pasada por error. Se arma la fecha manualmente en
        //    hora local a partir de los componentes Y-M-D.
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        const [anio, mes, dia] = this.value.split('-').map(Number);
        const valor = new Date(anio, mes - 1, dia);
        valor.setHours(0, 0, 0, 0);

        if (valor < hoy) {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        } else {
            this.classList.add('is-valid');
            this.classList.remove('is-invalid');
        }
    });

    return input;
}

/**
 * Valida que fecha_fin > fecha_inicio
 */
function aplicarOrdenFechas(inputInicioId, inputFinId) {
    const inputInicio = document.getElementById(inputInicioId);
    const inputFin = document.getElementById(inputFinId);
    if (!inputInicio || !inputFin) return;

    function validar() {
        if (inputInicio.value && inputFin.value) {
            if (new Date(inputFin.value) <= new Date(inputInicio.value)) {
                inputFin.classList.add('is-invalid');
                inputFin.classList.remove('is-valid');
                return false;
            }
            inputFin.classList.add('is-valid');
            inputFin.classList.remove('is-invalid');
            return true;
        }
        return true;
    }

    inputInicio.addEventListener('change', validar);
    inputFin.addEventListener('change', validar);
}

// ============================================================
// 5. HELPERS DE UI
// ============================================================

/**
 * Genera una contraseña aleatoria y la asigna a un input
 */
function generarPasswordEnInput(inputId, longitud = 10) {
    const input = document.getElementById(inputId);
    if (!input) return null;

    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    let pass = '';
    for (let i = 0; i < longitud; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    input.value = pass;

    if (typeof mostrarToast === 'function') {
        mostrarToast('Contraseña generada', 'info');
    }

    return pass;
}

/**
 * Muestra errores en un modal SweetAlert (lista)
 */
function mostrarErroresValidacion(errores, titulo = 'Revise los datos') {
    if (!errores || errores.length === 0) return;

    if (typeof Swal === 'undefined') {
        alert(errores.join('\n'));
        return;
    }

    Swal.fire({
        title: titulo,
        html: '<ul class="text-start mb-0">'
            + errores.map(e => `<li>${e}</li>`).join('')
            + '</ul>',
        icon: 'warning',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#ffc107'
    });
}

/**
 * Confirma una acción con SweetAlert
 * @returns {Promise<boolean>}
 */
function confirmarAccion(titulo, mensaje = '', opciones = {}) {
    if (typeof Swal === 'undefined') {
        return Promise.resolve(confirm(titulo));
    }

    return Swal.fire({
        title: titulo,
        html: mensaje,
        icon: opciones.icono || 'question',
        showCancelButton: true,
        confirmButtonText: opciones.confirmarTexto || 'Sí, continuar',
        cancelButtonText: opciones.cancelarTexto || 'Cancelar',
        confirmButtonColor: opciones.confirmColor || '#198754'
    }).then(r => r.isConfirmed);
}