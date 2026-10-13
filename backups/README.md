# Backups del Sistema de Transporte

## Estructura
- `manual/` → Backups creados manualmente desde el panel
- `automatico/` → Backups programados por cron/tarea programada

## Formato
- Nombre: `backup_tipo_YYYY-MM-DD_HH-MM-SS.sql`
- Codificación: UTF-8
- Motor: InnoDB (transaccional)

## Restaurar un backup
Ejecuta desde phpMyAdmin o terminal:
```bash
mysql -u root -p sistema_transporte < backups/manual/backup_manual_2026-09-12_15-30-00.sql