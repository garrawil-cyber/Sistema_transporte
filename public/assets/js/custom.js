/**
 * ============================================================
 * FUNCIONES PERSONALIZADAS CON SWEETALERT2
 * Sistema de Transporte
 * ============================================================
 */

// Configuración global de SweetAlert2
const SwalConfig = {
    confirmButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    customClass: {
        popup: 'swal-custom-popup',
        title: 'swal-custom-title',
        confirmButton: 'swal-btn-confirm',
        cancelButton: 'swal-btn-cancel'
    }
};

// ============================================================
// 1. CONFIRMACIÓN GENÉRICA (reemplaza confirm())
//    ✅ Acepta HTML en el mensaje
// ============================================================
function confirmar(mensajeHtml, callback, opciones = {}) {
    Swal.fire({
        ...SwalConfig,
        title: opciones.titulo || '¿Estás seguro?',
        html: mensajeHtml,                       // ✅ HTML en lugar de text
        icon: opciones.icono || 'question',
        showCancelButton: true,
        confirmButtonText: opciones.confirmarTexto || 'Sí, continuar',
        cancelButtonText: opciones.cancelarTexto || 'Cancelar',
        confirmButtonColor: opciones.confirmColor || '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
    return false;
}

// ============================================================
// 2. CONFIRMACIÓN DE ELIMINACIÓN
// ============================================================
function confirmarEliminar(url, nombre = 'este registro', callback) {
    Swal.fire({
        ...SwalConfig,
        title: '¿Eliminar registro?',
        html: `Estás a punto de eliminar <strong>${nombre}</strong>.<br><small class="text-muted">Esta acción no se puede deshacer.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i>Sí, eliminar',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof callback === 'function') {
                callback();
            } else if (url) {
                Swal.fire({
                    title: 'Eliminando...',
                    html: 'Por favor espera',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                window.location.href = url;
            }
        }
    });
    return false;
}

// ============================================================
// 3. CONFIRMACIÓN DE ANULACIÓN
//    ✅ Acepta HTML en el mensaje
// ============================================================
function confirmarAnular(url, mensajeHtml = '¿Anular este registro?') {
    Swal.fire({
        ...SwalConfig,
        title: '¿Anular?',
        html: mensajeHtml,                       // ✅ HTML en lugar de text
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-ban me-1"></i>Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ffc107'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Anulando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            window.location.href = url;
        }
    });
    return false;
}

// ============================================================
// 4. CONFIRMACIÓN DE CERRAR CAJA
// ============================================================
function confirmarCerrarCaja(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error(`confirmarCerrarCaja: no se encontró el formulario con id "${formId}"`);
        if (typeof mostrarToast === 'function') {
            mostrarToast('No se pudo cerrar la caja: formulario no encontrado', 'error');
        }
        return false;
    }

    Swal.fire({
        title: '¿Cerrar caja?',
        html: `Esta acción cerrará la caja actual.<br>
               <small class="text-muted">No podrás registrar más ventas hasta abrir una nueva caja.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-lock me-1"></i>Sí, cerrar caja',
        cancelButtonText: 'Revisar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Cerrando caja...',
                text: 'Generando comprobante',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            form.submit();
        }
    });
    return false;
}

// ============================================================
// 5. ALERTAS DE ÉXITO / ERROR / INFO
// ============================================================
function alertaExito(mensaje, titulo = '¡Éxito!') {
    Swal.fire({
        ...SwalConfig,
        title: titulo,
        text: mensaje,
        icon: 'success',
        confirmButtonColor: '#198754',
        timer: 3000,
        timerProgressBar: true
    });
}

function alertaError(mensaje, titulo = 'Error') {
    Swal.fire({
        ...SwalConfig,
        title: titulo,
        text: mensaje,
        icon: 'error',
        confirmButtonColor: '#dc3545'
    });
}

function alertaInfo(mensaje, titulo = 'Información') {
    Swal.fire({
        ...SwalConfig,
        title: titulo,
        text: mensaje,
        icon: 'info',
        confirmButtonColor: '#0dcaf0'
    });
}

function alertaAdvertencia(mensaje, titulo = 'Advertencia') {
    Swal.fire({
        ...SwalConfig,
        title: titulo,
        text: mensaje,
        icon: 'warning',
        confirmButtonColor: '#ffc107'
    });
}

// ============================================================
// 6. TOAST (NOTIFICACIÓN ESQUINA)
// ============================================================
function mostrarToast(mensaje, tipo = 'success', duracion = 3000) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: duracion,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: tipo,
        title: mensaje
    });
}

// ============================================================
// 7. LOADING SPINNER
// ============================================================
function mostrarLoading(mensaje = 'Cargando...') {
    Swal.fire({
        title: mensaje,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

function cerrarLoading() {
    Swal.close();
}

// ============================================================
// 8. CONFIRMAR CON INPUT (para restauración, cambios críticos)
// ============================================================
function confirmarConTexto(url, textoRequerido = 'CONFIRMAR', mensaje = 'Escribe la palabra para confirmar') {
    Swal.fire({
        ...SwalConfig,
        title: 'Confirmación requerida',
        html: `${mensaje}: <strong>${textoRequerido}</strong>`,
        input: 'text',
        inputPlaceholder: 'Escribe la palabra aquí...',
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        confirmButtonColor: '#dc3545',
        inputValidator: (value) => {
            if (value !== textoRequerido) {
                return 'El texto no coincide';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
    return false;
}

// ============================================================
// 9. ✅ CONFIRMACIÓN PERSONALIZADA — CORREGIDA
//    Acepta HTML crudo en el mensaje y lo renderiza correctamente
// ============================================================
function confirmarPersonalizado(titulo, mensajeHtml, textoConfirmar, colorConfirmar, callback) {
    Swal.fire({
        ...SwalConfig,
        title: titulo,
        html: mensajeHtml,                       // ✅ HTML en lugar de text
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: textoConfirmar || 'Sí, continuar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: colorConfirmar || '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
    return false;
}

function confirmarEmision(form, tipo) {
    // ✅ Se quitó event.preventDefault(): dependía del objeto global
    //    implícito `event`, que Firefox nunca implementó (window.event
    //    no existe ahí) — rompía la emisión de comprobante en ese
    //    navegador. El `return false;` de abajo ya es suficiente para
    //    bloquear la acción por defecto, igual que el resto de
    //    funciones de este archivo.
    Swal.fire({
        title: `¿Emitir ${tipo}?`,
        html: `Se enviará el comprobante a SUNAT.<br>
               <small class="text-muted">Modo: <strong>simulado</strong></small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `<i class="fas fa-paper-plane me-1"></i>Sí, emitir`,
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Emitiendo comprobante...',
                text: 'Conectando con SUNAT',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            form.submit();
        }
    });
    return false;
}

// ============================================================
// FUNCIONES ESPECÍFICAS ADICIONALES
// ============================================================

// Confirmar eliminación de movimiento de caja
function confirmarEliminarMovimiento(url, concepto, monto) {
    Swal.fire({
        title: '¿Eliminar movimiento?',
        html: `Movimiento: <strong>${concepto}</strong><br>
               Monto: <strong>S/ ${monto}</strong><br>
               <small class="text-muted">Esta acción no se puede deshacer.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-alt me-1"></i>Sí, eliminar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
    return false;
}

// Confirmar cambio de estado de encomienda
function confirmarCambioEstado(url, estado, guia) {
    const mensajes = {
        'EN_BODEGA': 'La encomienda estará lista en bodega.',
        'EN_TRANSITO': 'La encomienda está en camino al destino.',
        'ENTREGADO': 'La encomienda será marcada como ENTREGADA.',
        'DEVUELTO': 'La encomienda regresará al remitente.'
    };
    
    Swal.fire({
        title: `¿Marcar como ${estado}?`,
        html: `<strong>${guia}</strong><br>${mensajes[estado] || ''}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        confirmButtonColor: estado === 'ENTREGADO' ? '#198754' : 
                           estado === 'DEVUELTO' ? '#dc3545' : '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
    return false;
}

// Confirmar envío de email
function confirmarEnvioEmail(url, destinatario) {
    Swal.fire({
        title: '¿Enviar por email?',
        html: `Se enviará a: <strong>${destinatario}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-paper-plane me-1"></i>Sí, enviar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            window.location.href = url;
        }
    });
    return false;
}

// ============================================================
// 10. AUTO-CERRAR FLASH MESSAGES
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // Auto-cerrar alertas flash después de 5 segundos
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });

    // Convertir alertas flash a SweetAlert2 (opcional)
    const flashAlert = document.querySelector('.alert-dismissible');
    if (flashAlert && flashAlert.dataset.autoSwal === 'true') {
        const tipo = flashAlert.classList.contains('alert-success') ? 'success' :
                     flashAlert.classList.contains('alert-danger') ? 'error' :
                     flashAlert.classList.contains('alert-warning') ? 'warning' : 'info';
        const mensaje = flashAlert.textContent.trim();
        flashAlert.style.display = 'none';
        mostrarToast(mensaje, tipo);
    }
});

// ============================================================
// UTILIDADES
// ============================================================

// Confirmar con Enter
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && Swal.isVisible()) {
        const confirmBtn = Swal.getConfirmButton();
        if (confirmBtn && document.activeElement !== Swal.getInput()) {
            e.preventDefault();
            confirmBtn.click();
        }
    }
});

// Confirmar con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && Swal.isVisible()) {
        const cancelBtn = Swal.getCancelButton();
        if (cancelBtn) cancelBtn.click();
    }
});

// ============================================================
// ALIAS DE COMPATIBILIDAD
// Para código que use nombres antiguos
// ============================================================
if (typeof window.confirmarAccion === 'undefined') {
    // Si form-helpers no se cargó, definimos un fallback
    window.confirmarAccion = function(titulo, mensaje, opciones = {}) {
        return Swal.fire({
            title: titulo,
            html: mensaje,
            icon: opciones.icono || 'question',
            showCancelButton: true,
            confirmButtonText: opciones.confirmarTexto || 'Sí, continuar',
            cancelButtonText: opciones.cancelarTexto || 'Cancelar',
            confirmButtonColor: opciones.confirmColor || '#198754'
        }).then(r => r.isConfirmed);
    };
}