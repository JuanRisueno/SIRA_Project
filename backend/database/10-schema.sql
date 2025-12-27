/*

=============================================================================

            SCRIPT DE CREACIÓN DE ESQUEMA (DDL) - PROYECTO SIRA

=============================================================================

Tarea 4 (Alfonso) / Tarea 5 (Jorge)

Versión: 3.0 (Versión final supervisada por Jorge)

*/

-- 1. TABLAS DE CATÁLOGO (Sin dependencias)
create table if not exists CLIENTE (
    cliente_id serial primary key,
    nombre_empresa varchar(150) not null,
    -- CAMBIO (Jorge): Optimizado a char(9) (longitud fija)
    cif char(9) unique not null,
    email_admin varchar(150) not null,
    -- CAMBIO (Jorge): Optimizado a varchar(13) para prefijos
    telefono varchar(13) not null,
    persona_contacto varchar(100) not null,
    -- (Aumentado a 255 para hashes modernos)
    hash_contrasena varchar(255) not null,
    -- Soft Delete
    activa BOOLEAN DEFAULT TRUE
);

create table if not exists LOCALIDAD (
    -- CAMBIO (Jorge): Optimizado a char(5) (longitud fija)
    codigo_postal char(5) primary key,
    municipio varchar(100) not null,
    provincia varchar(100) not null
);

create table if not exists CULTIVO (
    cultivo_id serial primary key,
    nombre_cultivo varchar(100) unique not null,
    external_api_id varchar(100) unique null
);

create table if not exists TIPO_SENSOR (
    tipo_sensor_id serial primary key,
    nombre_tipo varchar(100) unique not null,
    unidad_medida varchar(20) not null
);

create table if not exists TIPO_ACTUADOR (
    tipo_actuador_id serial primary key,
    nombre_tipo varchar(100) unique not null
);

-- 2. TABLAS DEPENDIENTES (Con FKs)
create table if not exists PARCELA (
    parcela_id serial primary key,
    cliente_id int not null,
    -- CAMBIO (Jorge): Optimizado a char(5) (coherencia con LOCALIDAD)
    codigo_postal char(5) not null,
    -- CAMBIO (Jorge): Optimizado a char(14)
    ref_catastral char(14) unique not null,
    direccion varchar(150) not null,
    -- Soft Delete
    activa BOOLEAN DEFAULT TRUE,
    foreign key (cliente_id) references CLIENTE(cliente_id),
    foreign key (codigo_postal) references LOCALIDAD(codigo_postal)
);

create table if not exists INVERNADERO (
    invernadero_id serial primary key,
    nombre VARCHAR(50) NOT NULL,
    parcela_id int not null,
    cultivo_id int null,
    fecha_plantacion date null,
    largo_m decimal(8,2) not null,
    ancho_m decimal(8,2) not null,
    -- Soft Delete
    activa BOOLEAN DEFAULT TRUE,
    foreign key (parcela_id) references PARCELA(parcela_id),
    foreign key (cultivo_id) references CULTIVO(cultivo_id)
);

create table if not exists PARAMETROS_OPTIMOS (
    parametro_id serial primary key,
    cultivo_id int not null,
    fase_crecimiento varchar(50) not null,
    temp_optima_min decimal(5,2) not null,
    temp_optima_max decimal(5,2) not null,
    humedad_optima_min decimal(5,2) not null,
    humedad_optima_max decimal(5,2) not null,
    necesidad_hidrica decimal(8,2) not null,
    foreign key (cultivo_id) references CULTIVO(cultivo_id)
);

create table if not exists SENSOR (
    sensor_id serial primary key,
    invernadero_id int null, -- (Permite NULL, 0..1, para inventario)
    tipo_sensor_id int not null,
    ubicacion_sensor varchar(100) null,
    estado_sensor varchar(20) null,
    foreign key (invernadero_id) references INVERNADERO(invernadero_id),
    foreign key (tipo_sensor_id) references TIPO_SENSOR(tipo_sensor_id)
);

create table if not exists MEDICION (
    medicion_id serial primary key,
    sensor_id int not null,
    fecha_hora timestamptz not null default CURRENT_TIMESTAMP,
    valor decimal(10,2) not null,
    foreign key (sensor_id) references SENSOR(sensor_id)
);

create table if not exists ACTUADOR (
    actuador_id serial primary key,
    invernadero_id int null, -- (Permite NULL, 0..1, para inventario)
    tipo_actuador_id int not null,
    ubicacion_actuador varchar(100) null,
    estado_actuador varchar(20) null,
    foreign key (invernadero_id) references INVERNADERO(invernadero_id),
    foreign key (tipo_actuador_id) references TIPO_ACTUADOR(tipo_actuador_id)
);

create table if not exists ACCION_ACTUADOR (
    accion_id serial primary key,
    actuador_id int not null,
    fecha_hora timestamptz not null default CURRENT_TIMESTAMP,
    accion_detalle varchar(100) not null,
    foreign key (actuador_id) references ACTUADOR(actuador_id)
);

create table if not exists RECOMENDACION_RIEGO (
    recomendacion_id serial primary key,
    invernadero_id int not null,
    fecha_recomendacion timestamptz not null default CURRENT_TIMESTAMP,
    cantidad_ml decimal(8,2) not null,
    duracion_min int not null,
    razon_logica varchar(255) not null,
    foreign key (invernadero_id) references INVERNADERO(invernadero_id)
);

-- =============================================================================
-- --- 3. CREACIÓN DE ÍNDICES  ---
-- =============================================================================

-- FKs de PARCELA
CREATE INDEX idx_parcela_cliente ON PARCELA(cliente_id);
CREATE INDEX idx_parcela_codpostal ON PARCELA(codigo_postal);

-- FKs de INVERNADERO
CREATE INDEX idx_invernadero_parcela ON INVERNADERO(parcela_id);
CREATE INDEX idx_invernadero_cultivo ON INVERNADERO(cultivo_id);

-- FK de PARAMETROS_OPTIMOS
CREATE INDEX idx_parametros_cultivo ON PARAMETROS_OPTIMOS(cultivo_id);

-- FKs de SENSOR
CREATE INDEX idx_sensor_invernadero ON SENSOR(invernadero_id);
CREATE INDEX idx_sensor_tipo ON SENSOR(tipo_sensor_id);

-- FKs de MEDICION (CRÍTICO PARA IOT Y RENDIMIENTO)
CREATE INDEX idx_medicion_sensor ON MEDICION(sensor_id);
-- CRÍTICO: Optimiza la búsqueda de "últimas mediciones" (ORDER BY fecha_hora)
CREATE INDEX idx_medicion_fecha ON MEDICION(fecha_hora DESC);

-- FKs de ACTUADOR
CREATE INDEX idx_actuador_invernadero ON ACTUADOR(invernadero_id);
CREATE INDEX idx_actuador_tipo ON ACTUADOR(tipo_actuador_id);

-- FK de ACCION_ACTUADOR
CREATE INDEX idx_accion_actuador ON ACCION_ACTUADOR(actuador_id);

-- FK de RECOMENDACION_RIEGO
CREATE INDEX idx_recomendacion_invernadero ON RECOMENDACION_RIEGO(invernadero_id);
