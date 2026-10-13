/**
 * ============================================================
 * VALIDACIÓN DE DOCUMENTOS DE IDENTIDAD
 * Sistema de Transporte
 * ============================================================
 */

const DocumentoValidator = {
    REGLAS: {
        DNI:       { min: 8, max: 8, soloNumeros: true,  patron: /^\d{8}$/,                          placeholder: 'Ej: 12345678',    maxLength: 8  },
        CE:        { min: 9, max: 12, soloNumeros: false, patron: /^[A-Z0-9]{9,12}$/,                placeholder: 'Ej: E001234567',  maxLength: 12 },
        PASAPORTE: { min: 6, max: 12, soloNumeros: false, patron: /^[A-Z0-9]{6,12}$/,                placeholder: 'Ej: AB123456',    maxLength: 12 }
    },

    getRegla(tipo) {
        return this.REGLAS[tipo] || this.REGLAS.DNI;
    },

    limpiar(valor, tipo) {
        if (!valor) return '';
        valor = valor.toUpperCase().trim();
        const regla = this.getRegla(tipo);
        if (regla.soloNumeros) {
            return valor.replace(/\D/g, '');
        }
        return valor.replace(/[^A-Z0-9]/g, '');
    },

    validar(valor, tipo) {
        const regla = this.getRegla(tipo);
        const limpio = this.limpiar(valor, tipo);

        if (!limpio) {
            return { valido: false, mensaje: 'El número de documento es obligatorio' };
        }
        if (limpio.length < regla.min) {
            return { valido: false, mensaje: `${tipo} debe tener al menos ${regla.min} caracteres` };
        }
        if (limpio.length > regla.max) {
            return { valido: false, mensaje: `${tipo} debe tener máximo ${regla.max} caracteres` };
        }
        if (!regla.patron.test(limpio)) {
            return { valido: false, mensaje: `Formato de ${tipo} inválido` };
        }

        return { valido: true, mensaje: '' };
    }
};

/**
 * Aplica la validación en vivo a un input de documento
 */
function aplicarValidacionDocumento(inputId, selectId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const select = selectId ? document.getElementById(selectId) : null;

    function validarAhora(mostrarError = false) {
        const tipo = select ? select.value : 'DNI';
        const regla = DocumentoValidator.getRegla(tipo);

        input.setAttribute('maxlength', regla.maxLength);
        input.setAttribute('placeholder', regla.placeholder);

        const valorLimpio = DocumentoValidator.limpiar(input.value, tipo);
        if (valorLimpio !== input.value) {
            input.value = valorLimpio;
        }

        const resultado = DocumentoValidator.validar(input.value, tipo);

        input.classList.remove('is-invalid', 'is-valid');

        let feedback = input.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentElement.appendChild(feedback);
        }

        if (!resultado.valido && mostrarError) {
            input.classList.add('is-invalid');
            feedback.textContent = resultado.mensaje;
        } else if (resultado.valido) {
            input.classList.add('is-valid');
            feedback.textContent = '';
        }

        return resultado.valido;
    }

    const tipoInicial = select ? select.value : 'DNI';
    input.value = DocumentoValidator.limpiar(input.value, tipoInicial);

    if (input.value.length > 0) {
        setTimeout(() => validarAhora(true), 100);
    }

    // Bloquear caracteres no permitidos
    input.addEventListener('keypress', function(e) {
        const tipo = select ? select.value : 'DNI';
        const regla = DocumentoValidator.getRegla(tipo);
        const teclasControl = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
        if (teclasControl.includes(e.key) || e.ctrlKey || e.metaKey) return;

        if (regla.soloNumeros) {
            if (!/^\d$/.test(e.key)) e.preventDefault();
        } else {
            if (!/^[A-Za-z0-9]$/.test(e.key)) e.preventDefault();
        }
    });

    // Sanear al pegar
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const tipo = select ? select.value : 'DNI';
        const texto = (e.clipboardData || window.clipboardData).getData('text');
        const limpio = DocumentoValidator.limpiar(texto, tipo);
        const regla = DocumentoValidator.getRegla(tipo);
        input.value = limpio.slice(0, regla.maxLength);
        validarAhora(false);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });

    // Validación en vivo
    input.addEventListener('input', function() {
        const tipo = select ? select.value : 'DNI';
        const regla = DocumentoValidator.getRegla(tipo);

        const valorLimpio = DocumentoValidator.limpiar(input.value, tipo);
        if (valorLimpio !== input.value) {
            const cursorPos = input.selectionStart;
            input.value = valorLimpio;
            const nuevaPos = Math.min(cursorPos, valorLimpio.length);
            input.setSelectionRange(nuevaPos, nuevaPos);
        }

        if (input.value.length > regla.maxLength) {
            input.value = input.value.slice(0, regla.maxLength);
        }

        validarAhora(false);

        if (input.value.length === regla.maxLength && regla.patron.test(input.value)) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });

    // Validar al salir
    input.addEventListener('blur', function() {
        if (input.value.length > 0) {
            validarAhora(true);
        }
    });

    // Revalidar si cambia el tipo
    if (select) {
        select.addEventListener('change', function() {
            input.value = DocumentoValidator.limpiar(input.value, select.value);
            const regla = DocumentoValidator.getRegla(select.value);
            if (input.value.length > regla.maxLength) {
                input.value = input.value.slice(0, regla.maxLength);
            }
            validarAhora(input.value.length > 0);
        });
    }

    // Exponer función para validar al enviar
    input.validarDocumento = function() {
        return validarAhora(true);
    };

    return input;
}

// ✅ FIN DEL ARCHIVO - NO agregar aquí aplicarSoloLetras, aplicarTelefono, etc.
// Esas funciones están en form-helpers.js