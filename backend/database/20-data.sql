/*

CASUÍSTICAS CUBIERTAS:
1. Cliente de entrevista con datos específicos (Cliente 1 - ANTONIO)
2. Cliente con 1 parcela y 1 invernadero con cultivo (Cliente 2 - DAVID MARTÍN)
3. Cliente con múltiples parcelas e invernaderos (algunos vacíos) (Cliente 3 - SERGIO PÉREZ)
4. Cliente con máximo de parcelas e invernaderos todos con cultivo (Cliente 4 - ANA LÓPEZ)
5. Cliente con parcela pero sin invernaderos (parcela sin construir) (Cliente 5 - LAURA GARCÍA)
6. Distribución geográfica en Almería y Murcia
7. Referencias catastrales únicas por parcela

DATOS DE LOS 5 CLIENTES:

CLIENTE 1: ANTONIO (cliente de entrevista)
- Empresa: Invernaderos El Sol de Almería S.L.
- CIF: B04XXXXXX
- Contacto: Antonio (600112233)
- Email: antonio.sol@gmail.com
- Contraseña: ORETANIA301
- 2 parcelas:
  * "La Finca Grande": Polígono 12, Parcela 45 - Níjar, Almería (CP: 04100)
  * "Los Pinos": Camino de la Loma - Águilas, Murcia (CP: 30880)
- 6 invernaderos (según entrevista):
  * Níjar: 4 invernaderos
    - Invernadero 1:  11m - 50m
    - Invernadero 2:  11m - 50m
    - Invernadero 3:  11m - 50m
    - Invernadero 4:  11m - 50m
  * Águilas: 2 invernaderos
    - Invernadero 1:  11m - 50m
    - Invernadero 2:  11m - 50m

CLIENTE 2: DAVID MARTÍN
- Empresa: Cultivos David Martín
- CIF: A12345678
- Contacto: David Martín (950111222)
- Email: david@cultivosdm.com
- Contraseña: ORETANIA302
- 1 parcela en El Ejido, Almería: Polígono 10, Parcela 50
* El Ejido 1 invernderos  
  - Invernadero 1: 9m - 30m

CLIENTE 3: SERGIO PÉREZ
- Empresa: AgroSergio Pérez  
- CIF: B87654321
- Contacto: Sergio Pérez (968333444)
- Email: sergio@agrosergio.com
- Contraseña: ORETANIA303
- 2 parcelas en Murcia:
  * Ctra. Molina de Segura, Km 5 - Molina de Segura
  * Polígono Industrial - Alcantarilla
- 4 invernaderos: 
  * Molina de Segura invernaderos
    - Invernadero 1:  6m - 12m 
    - Invernadero 2: 	6m - 12m 
  * Polígono Industrial - Alcantarilla invernaderos
    - Invernadero 3: 	6m - 12m 
    - Invernadero 4:  6m - 12m 

CLIENTE 4: ANA LÓPEZ
- Empresa: Invernaderos Ana López
- CIF: C11222333
- Contacto: Ana López (950555666)
- Email: ana@invernaderosal.com
- Contraseña: ORETANIA304
- 4 parcelas (2 Almería + 2 Murcia):
  * Paraje Los Llanos - El Ejido, Almería
  * Avda. de Roquetas - Roquetas de Mar, Almería
  * Ctra. Cartagena - Torre Pacheco, Murcia
  * Sangonera la Verde - Murcia
- 8 invernaderos:
  * Paraje Los Llanos - El Ejido
    - Invernadero 1: 	8m - 25m
    - Invernadero 2: 	8m - 25m
  *  Avda. de Roquetas - Roquetas de Mar 
    - Invernadero 3: 	8m - 25m
    - Invernadero 4:  8m - 25m
  * Ctra. Cartagena - Torre Pacheco 
    - Invernadero 1: 	9m - 30m
    - Invernadero 2: 	9m - 30m
  * Sangonera la Verde 
    - Invernadero 3: 	6m - 12m 
    - Invernadero 4:  6m - 12m 

CLIENTE 5: LAURA GARCÍA
- Empresa: Cultivos Laura García
- CIF: D44333444
- Contacto: Laura García (968777888)
- Email: laura@cultivoslg.es
- Contraseña: ORETANIA305
- 1 parcela en Águilas, Murcia: Ctra. de Águilas, Km 12
- 0 invernaderos (parcela sin construir)




TOTALES FINALES:
- 5 clientes
- 10 parcelas  
- 19 invernaderos
- 1 parcela sin invernaderos (Cliente 4)


*/
-- 2. INSERCIÓN DE DATOS (SEMILLA)

-- 1. LOCALIDADES
-- Insertamos todas las localidades necesarias para las parcelas
INSERT INTO LOCALIDAD (codigo_postal, municipio, provincia) VALUES
('04700', 'El Ejido', 'Almería'),
('04230', 'Huércal-Overa', 'Almería'),
('04001', 'Almería', 'Almería'),
('04710', 'Roquetas de Mar', 'Almería'),
('30500', 'Molina de Segura', 'Murcia'),
('30820', 'Alcantarilla', 'Murcia'),
('30593', 'Torre Pacheco', 'Murcia'),
('30700', 'Aguilas', 'Murcia'),
('04100', 'Níjar', 'Almería'),      
('30880', 'Águilas', 'Murcia');     

-- 2. CLIENTES
-- NOTA TÉCNICA: 
-- Para facilitar el desarrollo inicial, la contraseña activa (hash) es 'sol1234'.
-- Se indica en comentarios la contraseña única 'ORETANIA...' que debería tener en producción.
INSERT INTO CLIENTE (nombre_empresa, cif, email_admin, telefono, persona_contacto, hash_contrasena) VALUES
-- Cliente 1 (Antonio) | Pass Real: ORETANIA301 | Pass Actual: sol1234
('Invernaderos El Sol de Almería S.L.', 'B04XXXXXX', 'antonio.sol@gmail.com', '600112233', 'Antonio', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxwKc.6IymCs7CN52au9gm.C8O752'),

-- Cliente 2 (David) | Pass Real: ORETANIA302 | Pass Actual: sol1234
('Cultivos David Martín', 'A12345678', 'david@cultivosdm.com', '950111222', 'David Martín', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxwKc.6IymCs7CN52au9gm.C8O752'),

-- Cliente 3 (Sergio) | Pass Real: ORETANIA303 | Pass Actual: sol1234
('AgroSergio Pérez', 'B87654321', 'sergio@agrosergio.com', '968333444', 'Sergio Pérez', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxwKc.6IymCs7CN52au9gm.C8O752'),

-- Cliente 4 (Ana) | Pass Real: ORETANIA304 | Pass Actual: sol1234
('Invernaderos Ana López', 'C11222333', 'ana@invernaderosal.com', '950555666', 'Ana López', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxwKc.6IymCs7CN52au9gm.C8O752'),

-- Cliente 5 (Laura) | Pass Real: ORETANIA305 | Pass Actual: sol1234
('Cultivos Laura García', 'D44333444', 'laura@cultivoslg.es', '968777888', 'Laura García', '$2b$12$EixZaYVK1fsbw1ZfbX3OXePaWxwKc.6IymCs7CN52au9gm.C8O752');

-- 3. PARCELAS
-- Cliente 1: Antonio (2 parcelas)
INSERT INTO PARCELA (cliente_id, codigo_postal, ref_catastral, direccion) VALUES
(1, '04100', '99999999B', 'Polígono 12, Parcela 45 - Níjar, Almería'),
(1, '30880', '88888888B', 'Camino de la Loma - Águilas, Murcia');

-- Cliente 2: David (1 parcela)
INSERT INTO PARCELA (cliente_id, codigo_postal, ref_catastral, direccion) VALUES
(2, '04700', '12345678A', 'Polígono 10, Parcela 50 - El Ejido, Almería');

-- Cliente 3: Sergio (2 parcelas)
INSERT INTO PARCELA (cliente_id, codigo_postal, ref_catastral, direccion) VALUES
(3, '30500', '11111111B', 'Ctra. Molina de Segura, Km 5 - Molina de Segura, Murcia'),
(3, '30820', '22222222B', 'Polígono Industrial - Alcantarilla, Murcia');

-- Cliente 4: Ana (4 parcelas)
INSERT INTO PARCELA (cliente_id, codigo_postal, ref_catastral, direccion) VALUES
(4, '04700', '33333333C', 'Paraje Los Llanos - El Ejido, Almería'),
(4, '04710', '44444444C', 'Avda. de Roquetas - Roquetas de Mar, Almería'),
(4, '30593', '55555555C', 'Ctra. Cartagena - Torre Pacheco, Murcia'),
(4, '30820', '66666666C', 'Sangonera la Verde - Murcia');

-- Cliente 5: Laura (1 parcela vacía)
INSERT INTO PARCELA (cliente_id, codigo_postal, ref_catastral, direccion) VALUES
(5, '30700', '77777777D', 'Ctra. de Águilas, Km 12 - Águilas, Murcia');


-- 4. INVERNADEROS
-- Nota: Respetamos las dimensiones solicitadas (Largo x Ancho) y añadimos el NOMBRE.

-- Cliente 1: Antonio
-- Parcela 1 (ID: 1): Níjar (11m x 50m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Nave 1', 1, NULL, NULL, 50, 11),
('Nave 2', 1, NULL, NULL, 50, 11),
('Nave 3', 1, NULL, NULL, 50, 11),
('Nave 4', 1, NULL, NULL, 50, 11);

-- Parcela 2 (ID: 2): Águilas (11m x 50m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Nave A', 2, NULL, NULL, 50, 11),
('Nave B', 2, NULL, NULL, 50, 11);

-- Cliente 2: David
-- Parcela 3 (ID: 3): El Ejido (9m x 30m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 1', 3, NULL, NULL, 30, 9);

-- Cliente 3: Sergio
-- Parcela 4 (ID: 4): Molina (6m x 12m - Pequeñas)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 1', 4, NULL, NULL, 12, 6),
('Invernadero 2', 4, NULL, NULL, 12, 6);

-- Parcela 5 (ID: 5): Alcantarilla (6m x 12m - Pequeñas)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 3', 5, NULL, NULL, 12, 6),
('Invernadero 4', 5, NULL, NULL, 12, 6);

-- Cliente 4: Ana
-- Parcela 6 (ID: 6): El Ejido (8m x 25m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 1', 6, NULL, NULL, 25, 8),
('Invernadero 2', 6, NULL, NULL, 25, 8);

-- Parcela 7 (ID: 7): Roquetas (8m x 25m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 3', 7, NULL, NULL, 25, 8),
('Invernadero 4', 7, NULL, NULL, 25, 8);

-- Parcela 8 (ID: 8): Torre Pacheco (9m x 30m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 1', 8, NULL, NULL, 30, 9),
('Invernadero 2', 8, NULL, NULL, 30, 9);

-- Parcela 9 (ID: 9): Sangonera (6m x 12m)
INSERT INTO INVERNADERO (nombre, parcela_id, cultivo_id, fecha_plantacion, largo_m, ancho_m) VALUES
('Invernadero 3', 9, NULL, NULL, 12, 6),
('Invernadero 4', 9, NULL, NULL, 12, 6);