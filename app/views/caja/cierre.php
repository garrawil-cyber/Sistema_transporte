<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-lock me-2"></i><?php echo $titulo; ?></h1>
    <a href="<?php echo BASE_URL; ?>caja" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Resumen de la caja -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Resumen del Día</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td><i class="fas fa-money-bill-wave text-success me-2"></i>Monto Inicial</td>
                            <td class="text-end"><strong>S/ <?php echo number_format($resumen['caja']['monto_inicial'], 2); ?></strong></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-ticket-alt text-primary me-2"></i>Ventas en Efectivo</td>
                            <td class="text-end text-success">+ S/ <?php echo number_format($resumen['ventas']['EFECTIVO'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-box text-warning me-2"></i>Encomiendas</td>
                            <td class="text-end text-success">+ S/ <?php echo number_format($resumen['encomiendas']['total'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-money-bill-transfer text-info me-2"></i>Comisiones de Giros</td>
                            <td class="text-end text-success">+ S/ <?php echo number_format($resumen['giros']['total'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-arrow-down text-success me-2"></i>Ingresos Manuales</td>
                            <td class="text-end text-success">+ S/ <?php echo number_format($resumen['total_ingresos'], 2); ?></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-arrow-up text-danger me-2"></i>Egresos Manuales</td>
                            <td class="text-end text-danger">- S/ <?php echo number_format($resumen['total_egresos'], 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-primary">
                        <tr>
                            <th><i class="fas fa-calculator me-2"></i>TOTAL EN SISTEMA</th>
                            <th class="text-end">
                                <h4 class="mb-0">S/ <?php echo number_format($resumen['monto_final_sistema'], 2); ?></h4>
                            </th>
                        </tr>
                    </tfoot>
                </table>

                <div class="alert alert-info mt-3 mb-0">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        El <strong>Monto en Sistema</strong> solo incluye transacciones en efectivo. 
                        Las ventas con tarjeta y transferencia se registran por separado.
                    </small>
                </div>
            </div>
        </div>

        <!-- Formulario de cierre -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Arqueo y Cierre</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>caja/procesarCierre" id="formCierre">
                    <div class="mb-4">
                        <label for="monto_final_real" class="form-label fw-bold">
                            <i class="fas fa-hand-holding-usd text-success me-1"></i>
                            Monto Real en Caja (S/) *
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">S/</span>
                            <input type="number" step="0.01" name="monto_final_real" id="monto_final_real" 
                                   class="form-control form-control-lg text-center fw-bold" 
                                   placeholder="0.00" required autofocus>
                        </div>
                        <small class="text-muted">
                            Cuente el efectivo físico en caja e ingrese el monto total aquí
                        </small>
                    </div>

                    <!-- Cálculo de diferencia en tiempo real -->
                    <div class="alert alert-warning" id="alertaDiferencia" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Diferencia Detectada</h6>
                                <small id="textoDiferencia"></small>
                            </div>
                            <h3 class="mb-0" id="valorDiferencia">S/ 0.00</h3>
                        </div>
                    </div>

                    <!-- ✅ NUEVO: Alerta grande para diferencias significativas -->
                    <div class="alert alert-danger d-none" id="alertaDiferenciaGrande">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-3x me-3"></i>
                            <div>
                                <h5 class="mb-1">¡Atención! Diferencia significativa</h5>
                                <p class="mb-0 small">
                                    Hay una diferencia de <strong id="montoDiferenciaGrande">S/ 0.00</strong> 
                                    que supera el umbral de S/ 10.00. 
                                    Por favor, verifique el efectivo físico y justifique en observaciones.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones" class="form-label fw-bold">
                            <i class="fas fa-comment me-1"></i>Observaciones
                        </label>
                        <textarea name="observaciones" id="observaciones" class="form-control" rows="3"
                                  placeholder="Ej: Faltó S/ 5.00 por error en vuelto..."></textarea>
                        <small class="text-muted" id="ayudaObservaciones" style="display:none;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Recomendado:</strong> Justifique la diferencia para auditoría.
                        </small>
                    </div>

                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡Atención!</strong> Al cerrar la caja, no podrá registrar más ventas hasta abrir una nueva.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger btn-lg" 
                                onclick="confirmarCerrarCaja('formCierre')">
                            <i class="fas fa-lock me-2"></i>Confirmar Cierre de Caja
                        </button>
                        <a href="<?php echo BASE_URL; ?>caja" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const montoSistema = <?php echo (float)$resumen['monto_final_sistema']; ?>;
    const inputReal = document.getElementById('monto_final_real');
    const alerta = document.getElementById('alertaDiferencia');
    const alertaGrande = document.getElementById('alertaDiferenciaGrande');
    const texto = document.getElementById('textoDiferencia');
    const valor = document.getElementById('valorDiferencia');
    const montoGrande = document.getElementById('montoDiferenciaGrande');
    const ayudaObs = document.getElementById('ayudaObservaciones');
    const UMBRAL_GRANDE = 10; // S/ 10

    inputReal.addEventListener('input', function() {
        const montoReal = parseFloat(this.value) || 0;
        const diferencia = montoReal - montoSistema;
        const absDiff = Math.abs(diferencia);

        if (this.value === '') {
            alerta.style.display = 'none';
            alertaGrande.classList.add('d-none');
            ayudaObs.style.display = 'none';
            return;
        }

        alerta.style.display = 'block';
        valor.textContent = 'S/ ' + absDiff.toFixed(2);

        if (absDiff < 0.01) {
            // Cuadre perfecto
            alerta.className = 'alert alert-success';
            texto.innerHTML = '<i class="fas fa-check-circle me-1"></i>¡Cuadre perfecto! El monto coincide con el sistema.';
            valor.style.color = '#198754';
            alertaGrande.classList.add('d-none');
            ayudaObs.style.display = 'none';
        } else if (diferencia > 0) {
            // Sobra efectivo
            alerta.className = 'alert alert-info';
            texto.innerHTML = '<i class="fas fa-info-circle me-1"></i>Sobra efectivo en caja (a favor del sistema).';
            valor.style.color = '#0d6efd';
            
            if (absDiff > UMBRAL_GRANDE) {
                montoGrande.textContent = 'S/ ' + diferencia.toFixed(2);
                alertaGrande.classList.remove('d-none');
                alertaGrande.className = 'alert alert-info';
                alertaGrande.querySelector('h5').textContent = 'Sobra efectivo en caja';
                ayudaObs.style.display = 'block';
            } else {
                alertaGrande.classList.add('d-none');
                ayudaObs.style.display = 'none';
            }
        } else {
            // Falta efectivo
            alerta.className = 'alert alert-danger';
            texto.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Falta efectivo en caja (debe justificar).';
            valor.style.color = '#dc3545';
            
            if (absDiff > UMBRAL_GRANDE) {
                montoGrande.textContent = 'S/ ' + absDiff.toFixed(2);
                alertaGrande.classList.remove('d-none');
                alertaGrande.className = 'alert alert-danger';
                alertaGrande.querySelector('h5').textContent = '¡Atención! Falta efectivo';
                ayudaObs.style.display = 'block';
            } else {
                alertaGrande.classList.add('d-none');
                ayudaObs.style.display = 'none';
            }
        }
    });

    // Inicializar con el monto del sistema (opcional: solo llenar el campo)
    // inputReal.value = montoSistema.toFixed(2);
    // inputReal.dispatchEvent(new Event('input'));
});

// ✅ Confirmación de cierre con SweetAlert2
function confirmarCerrarCaja(formId) {
    const form = document.getElementById(formId);
    const montoReal = parseFloat(document.getElementById('monto_final_real').value) || 0;
    const montoSistema = <?php echo (float)$resumen['monto_final_sistema']; ?>;
    const diferencia = montoReal - montoSistema;
    const absDiff = Math.abs(diferencia);
    
    let icono = 'success';
    let color = '#198754';
    let mensajeDif = '';
    
    if (absDiff < 0.01) {
        mensajeDif = '<p class="text-success mb-0"><i class="fas fa-check-circle me-1"></i><strong>Cuadre perfecto</strong></p>';
    } else if (diferencia > 0) {
        icono = 'info';
        color = '#0d6efd';
        mensajeDif = `<p class="text-info mb-0"><i class="fas fa-info-circle me-1"></i>Sobra: <strong>S/ ${diferencia.toFixed(2)}</strong></p>`;
    } else {
        icono = 'warning';
        color = '#dc3545';
        mensajeDif = `<p class="text-danger mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Falta: <strong>S/ ${absDiff.toFixed(2)}</strong></p>`;
    }
    
    Swal.fire({
        title: '¿Cerrar caja?',
        html: `
            <div class="text-start">
                <p><strong>Monto en sistema:</strong> S/ ${montoSistema.toFixed(2)}</p>
                <p><strong>Monto real contado:</strong> S/ ${montoReal.toFixed(2)}</p>
                <hr>
                ${mensajeDif}
                <hr>
                <p class="mb-0 small text-muted">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Esta acción no se puede deshacer. Deberá abrir una nueva caja para seguir vendiendo.
                </p>
            </div>
        `,
        icon: icono,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-lock me-1"></i>Sí, cerrar caja',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: color,
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Cerrando caja...',
                html: 'Procesando cierre y guardando arqueo',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            form.submit();
        }
    });
}
</script>
