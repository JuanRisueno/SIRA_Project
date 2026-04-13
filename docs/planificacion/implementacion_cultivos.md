# 🌿 Estructura de Implementación de Cultivos en SIRA (Local-First)

Este documento detalla la estrategia de gestión de cultivos basada en la filosofía **Scope Management**, priorizando la estabilidad total y la independencia de servicios externos para el MVP del TFG.

## 1. Arquitectura "Local-First"
Para garantizar un sistema failsafe durante la defensa, hemos eliminado la dependencia de APIs externas (Perenual). SIRA utiliza ahora una base de conocimiento local (**LKB - Local Knowledge Base**) integrada directamente en la base de datos PostgreSQL.

### Componentes Actualizados:
*   **Diccionario Maestro (Tabla LKB):** Tabla estática con parámetros científicos para los cultivos más representativos de Almería y Murcia.
*   **Lógica de Clonación:** Al seleccionar un cultivo, PHP copia los valores ideales a la tabla del invernadero del usuario.
*   **Interfaz Simplificada:** Flujo de un solo paso mediante formularios estándar PHP.

---

## 2. Flujo de Usuario Simplificado

El proceso de añadir un cultivo se ha reducido a la mínima expresión para evitar errores en vivo:

1.  **Selección de Variedad (Modo Asistido):** El agricultor elige en un desplegable (`<select>`) una de las 5 variedades estratégicas (Tomate, Pimiento, Sandía, Pepino, Melón). PHP carga los valores por defecto que pueden ser ajustados.
2.  **Alta Manual (Modo Libre):** Se incluye una opción de "Otro / Personalizado" que habilita un formulario en blanco. Aquí el usuario puede introducir un nombre de cultivo arbitrario y definir sus propios parámetros de seguridad desde cero.
3.  **Confirmación y Guardado:** En ambos casos, el sistema valida que los rangos numéricos sean lógicos antes de persistirlos en la base de datos del invernadero.

---

## 3. Modelo de Datos (Esquema Robusto)

El esquema se simplifica al no necesitar IDs externos ni campos de sincronización:

```sql
-- Tabla Maestra de Conocimiento (LKB)
CREATE TABLE CULTIVO_MAESTRO (
    id SERIAL PRIMARY KEY,
    nombre_comun VARCHAR(50) UNIQUE,
    temp_min_ideal DECIMAL(4,2),
    temp_max_ideal DECIMAL(4,2),
    hum_min_ideal INT,
    hum_max_ideal INT,
    ph_ideal DECIMAL(3,1)
);

-- Insertar el Top 5 de Almería/Murcia
INSERT INTO CULTIVO_MAESTRO (nombre_comun, temp_min_ideal, temp_max_ideal, hum_min_ideal, hum_max_ideal, ph_ideal)
VALUES 
('Tomate', 18.00, 27.00, 60, 80, 6.0),
('Pimiento', 20.00, 28.00, 65, 85, 6.5),
('Sandía', 22.00, 32.00, 60, 75, 6.2),
('Pepino', 18.00, 25.00, 70, 90, 6.0),
('Melón', 25.00, 35.00, 55, 70, 6.8);
```

---

## 4. Ventajas del Pivotaje Estratégico
*   **Estabilidad Absoluta:** El sistema funciona sin conexión a internet (entorno local controlado).
*   **Rendimiento:** Las consultas son instantáneas (milisegundos) al ser locales.
*   **Simplificación del Código:** Eliminamos toda la lógica de cURL, manejo de errores de red y gestión de claves API.
*   **Control Total:** El tribunal puede ver cómo los datos "están ahí", sin depender de la volatilidad de una API de terceros.

---

> [!IMPORTANT]
> Esta versión prioriza un **MVP Robusto** que garantice que nada falle el día de la presentación, enfocando el éxito en la correcta gestión de la infraestructura y no en la integración de servicios de terceros que podrían estar caídos o cambiar su formato de datos.
