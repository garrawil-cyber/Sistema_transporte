![Sistema de Transporte](docs/banner.svg)

 # 🚌 Sistema de Transporte

Sistema web integral para la gestión de una empresa de transporte terrestre de pasajeros y encomiendas. Desarrollado en **PHP puro (MVC)** con **MySQL** y **Bootstrap 5**, optimizado para entornos con recursos limitados y compatible con despliegues en XAMPP/Laragon.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-%3E%3D8.0-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-%3E%3D8.0-4479A1?logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Stack Tecnológico](#-stack-tecnológico)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación](#-instalación)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Base de Datos](#-base-de-datos)
- [Configuración](#-configuración)
- [Credenciales por Defecto](#-credenciales-por-defecto)
- [Uso del Sistema](#-uso-del-sistema)
- [Módulo de Facturación Electrónica](#-módulo-de-facturación-electrónica)
- [API Interna](#-api-interna)
- [Seguridad](#-seguridad)
- [Auditoría](#-auditoría)
- [Backups](#-backups)
- [Contribución](#-contribución)
- - [Changelog](#-changelog)
- [Licencia](#-licencia)

---

## ✨ Características

- 🎫 **Venta de pasajes** con mapa de asientos interactivo (multi-piso, elementos especiales: TV, escalera, bloqueados).
- 📦 **Gestión de encomiendas** con guía PDF, código QR y envío automático por email.
- 💸 **Giros de dinero** con cálculo automático de comisión y comprobante en PDF.
- 🚌 **Administración de flota**: buses, rutas y viajes con manifiestos.
- 👥 **Clientes y conductores** con validación de DNI/licencia.
- 💳 **Facturación electrónica** (Boletas/Facturas) en modo simulación SUNAT (Greenter).
- 💰 **Control de caja** con arqueo, ingresos/egresos y cierre diario.
- ⛽ **Control de combustible** con rendimiento por galón.
- 🔧 **Mantenimiento de buses** preventivo y correctivo.
- 📊 **Dashboard con KPIs** y gráficos de los últimos 7 días.
- 🔐 **Roles y permisos**: ADMIN, VENDEDOR, BODEGA, CONTADOR, CONDUCTOR.
- 📝 **Auditoría completa**: cada acción queda registrada con usuario, IP y valores anteriores/nuevos.
- 💾 **Backups** manuales y automáticos.
- 📱 **Diseño responsive** con sidebar colapsable y FAB para móvil.

---

## 🧩 Módulos del Sistema

| Módulo | Descripción | Estado |
|---|---|---|
| **Auth** | Login/Logout con hash bcrypt, sesiones seguras | ✅ |
| **Dashboard** | KPIs generales, gráficos y accesos rápidos | ✅ |
| **Ventas** | Venta de pasajes con selección de asiento | ✅ |
| **Encomiendas** | Registro, guías PDF, seguimiento con QR | ✅ |
| **Giros** | Envío de dinero con comisión automática | ✅ |
| **Viajes** | Programación de salidas, manifiestos, estados | ✅ |
| **Rutas** | Catálogo de origen-destino con precios | ✅ |
| **Buses** | Flota con configuración de asientos por piso | ✅ |
| **Conductores** | Registro con licencia, foto y sueldo | ✅ |
| **Clientes** | Base de clientes con búsqueda por DNI | ✅ |
| **Usuarios** | Gestión de cuentas con roles | ✅ |
| **Caja** | Apertura, movimientos y cierre diario | ✅ |
| **Combustible** | Cargas por bus con rendimiento | ✅ |
| **Mantenimiento** | Programación y costos | ✅ |
| **Facturación** | Boletas/Facturas electrónicas | ⚠️ Simulado |
| **Reportes** | Exportación a Excel/PDF | ✅ |
| **Auditoría** | Bitácora completa de acciones | ✅ |
| **Backups** | Gestión de respaldos de BD | ✅ |

---

## 🛠 Stack Tecnológico

### Backend
- **PHP 8.0+** (MVC puro, sin frameworks)
- **MySQL 8.0+** (InnoDB, UTF-8)
- **Composer** (dependencias)
- **PHPMailer** (envío de correos)
- **Dompdf** (generación de PDFs)
- **BaconQrCode** (generación de QR)
- **Greenter** (facturación electrónica SUNAT)

### Frontend
- **Bootstrap 5.3**
- **Font Awesome 6.4**
- **SweetAlert2** (modales y notificaciones)
- **JavaScript vanilla** (validaciones, AJAX)

### Herramientas
- **XAMPP** o **Laragon** (entorno local)
- **Git** (control de versiones)
- **MySQL Workbench** (administración de BD)

---

## 💻 Requisitos del Sistema

### Servidor
- PHP >= 8.0
- MySQL >= 8.0 o MariaDB >= 10.4
- Apache/Nginx con `mod_rewrite` habilitado
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `xml`

### Cliente
- Navegador moderno (Chrome, Firefox, Edge, Safari)
- JavaScript habilitado
- Resolución mínima: 360x640 (móvil) / 1366x768 (desktop)

### Desarrollo
- Composer 2.x
- Node.js (opcional, para herramientas de build)
- Editor con soporte PHP (VS Code + extensión PHP Intelephense)

---

## 🚀 Instalación

### Paso 1: Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/sistema-transporte.git
cd sistema-transporte
