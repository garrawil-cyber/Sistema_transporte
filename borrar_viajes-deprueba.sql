-- ============================================================
-- BORRAR VIAJE DE PRUEBA 15 (y sus datos relacionados)
-- Orden de borrado respetando dependencias (hijos antes que padres).
-- ============================================================

-- 0. Verificación previa — confirma que no hay ventas reales antes de borrar.
--    Si esto devuelve algo distinto de 0, DETENTE y revisa antes de continuar.
SELECT COUNT(*) AS ventas_existentes
FROM ventas_pasajes
WHERE viaje_id IN (15);

START TRANSACTION;

-- 1. Comprobantes electrónicos ligados a ventas de este viaje (si hubiera)
DELETE ce FROM comprobantes_electronicos ce
INNER JOIN ventas_pasajes vp ON ce.venta_id = vp.id
WHERE vp.viaje_id IN (15);

-- 2. Ventas de pasajes de este viaje (por seguridad, debería ser 0 filas)
DELETE FROM ventas_pasajes
WHERE viaje_id IN (15);

-- 3. Asientos generados para este viaje
DELETE FROM asientos
WHERE viaje_id IN (15);

-- 4. El viaje en sí
DELETE FROM viajes
WHERE id IN (15);

COMMIT;
-- Si algo se ve mal antes del COMMIT, ejecuta ROLLBACK; en su lugar.