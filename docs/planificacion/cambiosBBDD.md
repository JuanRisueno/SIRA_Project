# 📜 Registro de Cambios: Base de Datos (SIRA)

Este documento centraliza todas las modificaciones realizadas en el esquema de la base de datos PostgreSQL del proyecto SIRA, asegurando la trazabilidad entre el código Python (SQLAlchemy) y los scripts SQL.

---

## [v4.0] - 2026-04-14 (Actual)
### MVP Robusto - Local Knowledge Base
- **Tabla `PARAMETROS_OPTIMOS`:**
    - `[ADD]` Columna `ph_ideal DECIMAL(3,1)` para almacenar el rango químico óptimo.
    - `[DATA]` Carga masiva de parámetros reales para 8 cultivos (Tomate, Pimiento, Sandía, Pepino, Melón, Calabacín, Berenjena, Judía verde).
- **Tabla `CULTIVO`:**
    - `[DATA]` Limpieza de referencias a APIs externas y consolidación de nombres locales.

---

## [v3.1] - 2026-04-13
### Optimización de Búsquedas
- **Extensiones:**
    - `[ADD]` Activación de la extensión `unaccent` de PostgreSQL para permitir búsquedas insensibles a tildes.
- **Índices:**
    - `[ADD]` Índices GIN (implícitos mediante búsquedas funcionales) para mejorar el rendimiento de filtrado en el Dashboard.

---

## [v3.0] - 2026-04-10
### Seguridad y Ciclo de Vida
- **Soft Delete:**
    - `[ADD]` Columna `activa BOOLEAN DEFAULT TRUE` en las tablas `CLIENTE`, `PARCELA` e `INVERNADERO`.
- **Seguridad:**
    - `[MOD]` Aumento del tamaño de `hash_contrasena` a 255 caracteres para soportar hashes bcrypt robustos.
- **Tipos de Datos:**
    - `[MOD]` Optimización de `CHAR` vs `VARCHAR` en campos de longitud fija como `CIF` (9), `CP` (5) y `REF_CATASTRAL` (14).

---

## [v2.0] - 2026-01-15
### Homogeneización Backend-BBDD
- **Sincronización:**
    - Ajuste de nombres de columnas y tipos entre `models.py` y `10-schema.sql` para garantizar que SQLAlchemy no genere errores de mapping.
- **Relaciones:**
    - Refuerzo de claves foráneas y restricciones `ON DELETE RESTRICT` para integridad referencial.

---

## [v1.0] - 2025-11-20
### Esquema Inicial
- Creación de las tablas base: `CLIENTE`, `LOCALIDAD`, `PARCELA`, `INVERNADERO`, `CULTIVO`, `SENSOR`, `ACTUADOR`, `MEDICION`.

---

> [!TIP]
> **Mantenimiento:** Cualquier cambio futuro en el esquema mediante `ALTER TABLE` debe ser registrado en este archivo antes de ser aplicado a los scripts de inicialización.
