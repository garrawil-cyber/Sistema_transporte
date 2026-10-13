# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/)
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [Unreleased]

### Planeado
- Módulo de reservas online para clientes finales
- Integración con WhatsApp Business API para notificaciones
- App móvil progresiva (PWA) para conductores
- Dashboard estadístico con más gráficos interactivos
- Integración con Google Maps para rutas
- Múltiples idiomas (español, inglés, quechua)
- Módulo de comisiones para vendedores
- Pasarela de pago en línea (MercadoPago, Niubiz)

---

## [1.0.0] - 2026-10-13

### 🎉 Versión inicial estable

#### Añadido

**Autenticación y Roles**
- Login/Logout con hash bcrypt
- 5 roles predefinidos: ADMIN, VENDEDOR, BODEGA, CONTADOR, CONDUCTOR
- Sesiones seguras con duración configurable
- Sistema de auditoría de accesos (login/logout)
- Sistema de favoritos (accesos rápidos por usuario)

**Módulo de Ventas**
- Venta de pasajes con mapa de asientos interactivo
- Soporte multi-piso (cubierta inferior y superior)
- Elementos especiales: TV, escalera, asientos bloqueados
- Reserva temporal de asientos (15 minutos)
- Múltiples métodos de pago (efectivo, tarjeta, transferencia)
- Emisión de boletas electrónicas (modo simulado SUNAT)
- Anulación de ventas del mismo día
- Impresión de ticket térmico
- Cálculo automático de IGV

**Módulo de Encomiendas**
- Registro con autocompletado por DNI
- Guía única con formato `GUI-YYYYMMDD-XXXX`
- Generación de PDF con código QR de rastreo
- Envío automático por email al destinatario
- Estados: REGISTRADO → EN_BODEGA → EN_TRANSITO → ENTREGADO → DEVUELTO
- Rastreo público sin necesidad de login
- Historial de cambios de estado
- Reimpresión de guías

**Módulo de Giros**
- Cálculo automático de comisión (3.5% configurable)
- Código único `GIR-YYMMDD-XXXX`
- Comprobante PDF en formato A5
- Envío automático por email
- Estados: REGISTRADO → PAGADO → ANULADO
- Registro de método de pago origen y destino
- Impresión de comprobante

**Administración de Flota**
- CRUD de buses con configuración multi-piso
- Asignación de asientos por fila y columna
- Definición de elementos especiales (TV, escalera)
- Estado del bus: ACTIVO, MANTENIMIENTO, BAJA
- CRUD de rutas (origen, destino, distancia, precio)
- Programación de viajes con fecha/hora salida y llegada
- Generación de manifiestos de pasajeros
- Estados del viaje: PROGRAMADO, EN_CURSO, COMPLETADO, CANCELADO

**Gestión de Personas**
- CRUD de clientes con búsqueda por DNI
- CRUD de conductores con licencia y foto
- Registro de contacto de emergencia
- Control de licencia con fecha de vencimiento
- Sueldo base y porcentaje de comisión

**Caja y Finanzas**
- Apertura y cierre de caja
- Registro de ingresos y egresos
- Arqueo con diferencia (sistema vs real)
- Reportes por rango de fechas
- Historial de movimientos

**Control de Combustible**
- Registro de cargas por bus
- Cálculo de rendimiento km/galón
- Registro de kilometraje
- Tipos de combustible: DIESEL, GASOLINA, GNV

**Mantenimiento**
- Programación de mantenimiento preventivo
- Registro de mantenimiento correctivo
- Costos de mano de obra y repuestos
- Próximo mantenimiento por km o fecha

**Facturación Electrónica**
- Integración con Greenter (UBL 2.1)
- Tipos: FACTURA, BOLETA, NOTA_CREDITO, NOTA_DEBITO
- Series configurables (F001, B001, FC01, FD01)
- Correlativos automáticos por serie
- Modo simulado para pruebas
- Envío por email con PDF adjunto

**Auditoría**
- Registro de todas las acciones del sistema
- Almacenamiento de valores anteriores y nuevos
- Registro de IP, User-Agent, duración
- Filtros por usuario, acción, tabla, fecha
- Exportación a Excel

**Sistema**
- Backups manuales de base de datos
- Backups automáticos programados
- Gestión de configuraciones clave-valor
- Dashboard con KPIs en tiempo real
- Gráficos de últimos 7 días
- Diseño responsive (móvil, tablet, desktop)
- Sidebar colapsable en desktop
- FAB (botón flotante) para acciones rápidas en móvil
- Búsqueda en vivo con filtros colapsables

#### Características Técnicas
- Arquitectura MVC pura (sin frameworks)
- PDO con prepared statements (anti SQL injection)
- Validación cliente/servidor de documentos
- SweetAlert2 para notificaciones y confirmaciones
- Bootstrap 5.3 + Font Awesome 6.4
- Dompdf para generación de PDFs
- BaconQrCode para códigos QR
- PHPMailer para correos SMTP
- Compatible con XAMPP, Laragon, WAMP

#### Seguridad
- Hash bcrypt para todas las contraseñas
- Escape HTML en todas las vistas
- Validación de sesión en cada controlador
- Control de acceso granular por roles
- Sanitización de entradas con `filter_var()`
- Auditoría completa con trazabilidad
- Protección CSRF (recomendado implementar en producción)
- Headers de seguridad configurables

#### Documentación
- README.md con descripción completa
- Banner SVG personalizado
- Logo del proyecto
- CHANGELOG.md
- CONTRIBUTING.md
- LICENSE (MIT)

---

## Formato del Changelog

### Tipos de cambios

- `Añadido` para nuevas funcionalidades
- `Cambiado` para cambios en funcionalidades existentes
- `Obsoleto` para funcionalidades que serán removidas
- `Eliminado` para funcionalidades removidas
- `Corregido` para corrección de bugs
- `Seguridad` para vulnerabilidades corregidas

### Versionado Semántico

- **MAJOR** (1.x.x): Cambios incompatibles con versiones anteriores
- **MINOR** (x.1.x): Nuevas funcionalidades compatibles
- **PATCH** (x.x.1): Correcciones de bugs compatibles

---

## Enlaces

- [Repositorio](https://github.com/garrawil-cyber/Sistema_transporte)
- [Issues](https://github.com/garrawil-cyber/Sistema_transporte/issues)
- [Releases](https://github.com/garrawil-cyber/Sistema_transporte/releases)
- [README](README.md)
- [Contribuir](CONTRIBUTING.md)

---

[Unreleased]: https://github.com/garrawil-cyber/Sistema_transporte/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/garrawil-cyber/Sistema_transporte/releases/tag/v1.0.0