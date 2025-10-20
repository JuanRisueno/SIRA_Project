-- BORRADO SEGURO DE TABLAS: Borramos en orden inverso (FK antes que PK).
DROP TABLE IF EXISTS invernaderos;
DROP TABLE IF EXISTS clientes;

---

-- CREACIÓN DE TABLA PRINCIPAL: CLIENTES
CREATE TABLE clientes (
    id_cliente SERIAL PRIMARY KEY,
    nombre_cliente VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    hash_contrasena VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- CREACIÓN DE TABLA ASOCIADA: INVERNADEROS
CREATE TABLE invernaderos (
    id_invernadero SERIAL PRIMARY KEY,

    -- CLAVE FORÁNEA (FK): Sintaxis simplificada.
    fk_id_cliente INT NOT NULL
        REFERENCES clientes(id_cliente)
        ON DELETE CASCADE,

    nombre_invernadero VARCHAR(100) NOT NULL,
    ubicacion VARCHAR(255),
    fecha_creacion TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);