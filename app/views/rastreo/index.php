<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rastreo de Encomienda - <?php echo FACTURACION_RAZON_SOCIAL; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .rastreo-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header-empresa {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        
        .header-empresa i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .header-empresa h1 {
            color: #212529;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .header-empresa p {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        
        .buscador {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        
        .buscador h4 {
            color: #212529;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .input-guia {
            font-size: 1.2rem;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .input-guia:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .btn-buscar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-size: 1.2rem;
            padding: 15px 40px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-buscar:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-buscar:disabled {
            opacity: 0.7;
            transform: none;
        }
        
        .info-ayuda {
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 15px;
        }
        
        /* Resultado del rastreo */
        .resultado {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card-resultado {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        
        .guia-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .guia-numero {
            font-size: 1.8rem;
            font-weight: 700;
            color: #212529;
        }
        
        .guia-numero small {
            display: block;
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 400;
            text-transform: uppercase;
        }
        
        .estado-badge {
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-icon {
            position: absolute;
            left: -33px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e0e0e0;
            color: white;
            font-size: 0.8rem;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e0e0e0;
        }
        
        .timeline-item.activo .timeline-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 0 0 2px #667eea;
        }
        
        .timeline-item.completado .timeline-icon {
            background: #198754;
            box-shadow: 0 0 0 2px #198754;
        }
        
        .timeline-content {
            padding-left: 10px;
        }
        
        .timeline-content h6 {
            font-weight: 700;
            margin-bottom: 5px;
            color: #212529;
        }
        
        .timeline-content p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .timeline-content small {
            color: #adb5bd;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .info-item small {
            display: block;
            color: #6c757d;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .info-item strong {
            color: #212529;
            font-size: 0.95rem;
        }
        
        .footer {
            text-align: center;
            color: white;
            padding: 20px;
            font-size: 0.85rem;
        }
        
        .footer a {
            color: white;
            text-decoration: underline;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        @media (max-width: 576px) {
            .guia-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .guia-numero {
                font-size: 1.4rem;
            }
            
            .header-empresa h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

<div class="rastreo-container">
    
    <!-- Header -->
    <div class="header-empresa">
        <i class="fas fa-box"></i>
        <h1><?php echo FACTURACION_RAZON_SOCIAL; ?></h1>
        <p>Rastreo de Encomiendas</p>
    </div>
    
    <!-- Buscador -->
    <div class="buscador">
        <h4 class="text-center">
            <i class="fas fa-search me-2"></i>Consulta tu envío
        </h4>
        <form id="formRastreo" onsubmit="buscarEncomienda(event)">
            <div class="mb-3">
                <label class="form-label fw-bold">
                    <i class="fas fa-barcode me-1"></i>Número de Guía
                </label>
                <input type="text" 
                       id="guiaInput" 
                       class="form-control input-guia text-center" 
                       placeholder="Ej: GUI-20260001" 
                       value="<?php echo htmlspecialchars($guia ?? ''); ?>"
                       autocomplete="off"
                       maxlength="20"
                       required>
            </div>
            <button type="submit" class="btn btn-buscar" id="btnBuscar">
                <i class="fas fa-search me-2"></i>Buscar Encomienda
            </button>
        </form>
        <div class="info-ayuda">
            <i class="fas fa-info-circle me-1"></i>
            Ingresa el número de guía que aparece en tu comprobante
        </div>
    </div>
    
    <!-- Resultado -->
    <div class="resultado" id="resultado"></div>
    
    <!-- Footer -->
    <div class="footer">
        <p class="mb-1">
            <i class="fas fa-map-marker-alt me-1"></i><?php echo FACTURACION_DIRECCION; ?>
        </p>
        <p class="mb-1">
            <i class="fas fa-phone me-1"></i><?php echo FACTURACION_TELEFONO; ?>
            <span class="mx-2">|</span>
            <i class="fas fa-envelope me-1"></i><?php echo FACTURACION_EMAIL; ?>
        </p>
        <p class="mb-0 mt-2 small">
            &copy; <?php echo date('Y'); ?> <?php echo FACTURACION_RAZON_SOCIAL; ?> - Todos los derechos reservados
        </p>
    </div>
    
</div>

<script>
// ============================================================
// BÚSQUEDA DE ENCOM IENDA POR GUÍA (AJAX)
// ============================================================
function buscarEncomienda(e) {
    e.preventDefault();
    
    const guia = document.getElementById('guiaInput').value.trim().toUpperCase();
    const btn = document.getElementById('btnBuscar');
    const resultado = document.getElementById('resultado');
    
    if (!guia) {
        Swal.fire({
            icon: 'warning',
            title: 'Campo vacío',
            text: 'Ingresa el número de guía para buscar'
        });
        return;
    }
    
    // Loading
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Buscando...';
    resultado.innerHTML = '';
    resultado.style.display = 'none';
    
    fetch('<?php echo BASE_URL; ?>rastreo/buscar?guia=' + encodeURIComponent(guia))
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search me-2"></i>Buscar Encomienda';
            
            if (data.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrado',
                    text: data.error
                });
                return;
            }
            
            // Renderizar resultado
            resultado.innerHTML = renderResultado(data.data);
            resultado.style.display = 'block';
            
            // Scroll suave
            resultado.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search me-2"></i>Buscar Encomienda';
            console.error('Error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Intente nuevamente.'
            });
        });
}

// ============================================================
// RENDERIZAR RESULTADO
// ============================================================
function renderResultado(data) {
    const estados = {
        'REGISTRADO': { label: 'Registrado', color: 'info', icon: 'fa-file-alt' },
        'EN_BODEGA': { label: 'En Bodega', color: 'warning', icon: 'fa-warehouse' },
        'EN_TRANSITO': { label: 'En Tránsito', color: 'primary', icon: 'fa-truck' },
        'ENTREGADO': { label: 'Entregado', color: 'success', icon: 'fa-check-circle' },
        'DEVUELTO': { label: 'Devuelto', color: 'danger', icon: 'fa-undo' }
    };
    
    const estado = estados[data.estado] || { label: data.estado, color: 'secondary', icon: 'fa-circle' };
    
    // Determinar progreso del timeline
    const pasos = ['REGISTRADO', 'EN_BODEGA', 'EN_TRANSITO', 'ENTREGADO'];
    const indiceActual = pasos.indexOf(data.estado);
    
    let timelineHTML = '';
    const timelineSteps = [
        { estado: 'REGISTRADO', titulo: 'Encomienda Registrada', descripcion: 'Tu paquete ha sido registrado en el sistema', fecha: data.fecha_registro, icono: 'fa-file-alt' },
        { estado: 'EN_BODEGA', titulo: 'En Bodega', descripcion: 'Tu paquete se encuentra en bodega esperando el viaje', fecha: null, icono: 'fa-warehouse' },
        { estado: 'EN_TRANSITO', titulo: 'En Tránsito', descripcion: 'Tu paquete está en camino al destino', fecha: data.fecha_salida, icono: 'fa-truck' },
        { estado: 'ENTREGADO', titulo: 'Entregado', descripcion: 'Tu paquete ha sido entregado exitosamente', fecha: data.fecha_entrega, icono: 'fa-check-circle' }
    ];
    
    timelineSteps.forEach((step, index) => {
        let clase = '';
        if (index <= indiceActual) {
            clase = (index === indiceActual) ? 'activo' : 'completado';
        }
        
        timelineHTML += `
            <div class="timeline-item ${clase}">
                <div class="timeline-icon">
                    <i class="fas ${step.icono}"></i>
                </div>
                <div class="timeline-content">
                    <h6>${step.titulo}</h6>
                    <p>${step.descripcion}</p>
                    ${step.fecha ? `<small><i class="far fa-clock me-1"></i>${formatearFecha(step.fecha)}</small>` : ''}
                </div>
            </div>
        `;
    });
    
    return `
        <div class="card-resultado">
            <!-- Header del resultado -->
            <div class="guia-header">
                <div>
                    <div class="guia-numero">
                        <small>Número de Guía</small>
                        ${data.guia}
                    </div>
                </div>
                <div>
                    <span class="badge bg-${estado.color} estado-badge">
                        <i class="fas ${estado.icon} me-1"></i>${estado.label}
                    </span>
                </div>
            </div>
            
            <!-- Info grid -->
            <div class="info-grid mb-4">
                <div class="info-item">
                    <small><i class="fas fa-route me-1"></i>Ruta</small>
                    <strong>${data.origen} → ${data.destino}</strong>
                </div>
                <div class="info-item">
                    <small><i class="fas fa-user me-1"></i>Remitente</small>
                    <strong>${data.remitente_nombre}</strong>
                </div>
                <div class="info-item">
                    <small><i class="fas fa-user-check me-1"></i>Destinatario</small>
                    <strong>${data.destinatario_nombre}</strong>
                </div>
                <div class="info-item">
                    <small><i class="fas fa-weight-hanging me-1"></i>Peso</small>
                    <strong>${data.peso_kg} kg</strong>
                </div>
                <div class="info-item">
                    <small><i class="fas fa-bus me-1"></i>Bus</small>
                    <strong>${data.placa}</strong>
                </div>
                <div class="info-item">
                    <small><i class="fas fa-calendar-day me-1"></i>Fecha de Salida</small>
                    <strong>${formatearFecha(data.fecha_salida)}</strong>
                </div>
            </div>
            
            <!-- Timeline -->
            <h5 class="mb-3">
                <i class="fas fa-stream me-2"></i>Seguimiento del Envío
            </h5>
            <div class="timeline">
                ${timelineHTML}
            </div>
            
            <!-- Botón nueva búsqueda -->
            <div class="text-center mt-4">
                <button class="btn btn-outline-primary" onclick="nuevaBusqueda()">
                    <i class="fas fa-search me-1"></i>Buscar otra encomienda
                </button>
            </div>
        </div>
    `;
}

// ============================================================
// FORMATEAR FECHA
// ============================================================
function formatearFecha(fecha) {
    if (!fecha) return 'Pendiente';
    
    const d = new Date(fecha.replace(' ', 'T'));
    const opciones = { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return d.toLocaleDateString('es-PE', opciones);
}

// ============================================================
// NUEVA BÚSQUEDA
// ============================================================
function nuevaBusqueda() {
    document.getElementById('guiaInput').value = '';
    document.getElementById('resultado').style.display = 'none';
    document.getElementById('guiaInput').focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ============================================================
// AUTO-BÚSQUEDA SI VIENE CON GUÍA EN LA URL
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const guia = document.getElementById('guiaInput').value.trim();
    if (guia) {
        // Auto-buscar después de 500ms
        setTimeout(() => {
            document.getElementById('formRastreo').dispatchEvent(new Event('submit'));
        }, 500);
    }
});
</script>

</body>
</html>