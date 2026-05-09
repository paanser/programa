-- Migracion v2: estado, validez, herrajes, instalacion
-- Ejecutar sobre instalaciones existentes que ya tienen la tabla quotes

ALTER TABLE quotes
    ADD COLUMN IF NOT EXISTS status        VARCHAR(30)    NOT NULL DEFAULT 'draft'  AFTER notes,
    ADD COLUMN IF NOT EXISTS valid_until   DATE           DEFAULT NULL               AFTER status,
    ADD COLUMN IF NOT EXISTS hardware_cost DECIMAL(10,2)  NOT NULL DEFAULT 0.00      AFTER labor_cost,
    ADD COLUMN IF NOT EXISTS installation_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00   AFTER hardware_cost;

-- Indices utiles para filtrar por estado
ALTER TABLE quotes
    ADD INDEX IF NOT EXISTS idx_status (status);
