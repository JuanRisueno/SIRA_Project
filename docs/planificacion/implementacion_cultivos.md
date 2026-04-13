# 🌿 Estructura de Implementación de Cultivos en SIRA

Este documento detalla la estrategia técnica para la gestión de cultivos y sus parámetros óptimos, integrando la API de **Perenual** y asegurando la precisión para la agricultura de Almería y Murcia.

## 1. Arquitectura del Sistema
Para cumplir con la restricción de **Strictly PHP (No JS)**, la implementación se basa en un flujo de procesamiento en el lado del servidor (Server-Side) utilizando formularios estándar y sesiones de PHP.

### Componentes Principales:
*   **Gestor de API (ServicioCultivo.php):** Clase encargada de las peticiones cURL a Perenual.
*   **Diccionario de Mapeo Local (DB):** Tabla que traduce nombres comunes ("Tomate Raff", "Pimiento Lamuyo") a nombres científicos.
*   **Interfaz de Pasos (UI):** Flujo síncrono de 3 pantallas para la creación de un cultivo.

---

## 2. Flujo de Implementación (Paso a Paso)

### Paso 1: Búsqueda y Resolución Cinatífica
El cliente introduce un nombre común. PHP procesa la petición:
1.  **Consulta LKB (Local Knowledge Base):** Se busca en una tabla interna si el nombre corresponde a un cultivo típico de Almería. Si existe, se obtiene su nombre científico (*ej. Solanum lycopersicum*).
2.  **Llamada a Perenual:** PHP realiza la búsqueda en la API usando el nombre científico para garantizar resultados botánicos precisos.
3.  **Presentación:** Se recarga la página mostrando una lista de "Candidatos" con foto y descripción.

### Paso 2: Selección y Extracción de Parámetros
El cliente selecciona el candidato correcto:
1.  PHP solicita los detalles extendidos (ID de Perenual).
2.  Se extraen datos de: Riego (Watering), Luz (Sunlight), pH y Temperaturas.
3.  **Normalización:** Si la API devuelve rangos vagos (ej. "Average"), PHP aplica valores numéricos estándar basados en la documentación técnica de Almería.

### Paso 3: Confirmación y Personalización (Modos de Control)
Al guardar el cultivo, el sistema define su **Estado de Sincronización**:

*   **Modo Perenual (Gestionado):**
    *   Los parámetros óptimos (pH, humedad, etc.) son **bloqueados (solo lectura)**.
    *   Solo el "Nombre Común" es editable para la interfaz del cliente.
    *   **Ventaja:** El cultivo entra en el ciclo de actualización automática de SIRA.
*   **Modo Manual (Independiente):**
    *   Todos los campos son editables.
    *   El usuario tiene control total sobre los rangos de seguridad.
    *   **Nota:** Estos cultivos son ignorados por el script de sincronización mensual.

---

## 3. Lógica de Sincronización Mensual (Sweep)
SIRA incorporará un script de mantenimiento (Cron Job) que se ejecutará periódicamente (ej. una vez al mes):

1.  **Filtrado:** Selecciona solo los cultivos marcados como `es_manual = FALSE` y que tengan un `external_api_id`.
2.  **Actualización:** Consulta la API de Perenual mediante el `external_api_id`.
3.  **Sobrescritura:** Si los parámetros han cambiado en la fuente oficial, se actualizan en la base de datos de SIRA para garantizar que la automatización del invernadero use los datos científicos más recientes.
4.  **Flexibilidad:** El cliente puede, en cualquier momento, cambiar un cultivo de "Perenual" a "Manual" para "congelar" sus valores y editarlos libremente.

---

## 4. Modelo de Datos (Ampliación SQL)

Para soportar esta lógica, modificaremos la base de datos actual:

```sql
-- Añadir campos de soporte botánico y control de sincronización
ALTER TABLE CULTIVO 
ADD COLUMN nombre_cientifico VARCHAR(150),
ADD COLUMN descripcion_botanica TEXT,
ADD COLUMN imagen_url VARCHAR(255),
ADD COLUMN es_manual BOOLEAN DEFAULT FALSE, -- Determina si es editable o gestionado por API
ADD COLUMN ultima_sincronizacion TIMESTAMPTZ;

-- Asegurar que los parámetros soportan decimales precisos
ALTER TABLE PARAMETROS_OPTIMOS 
ALTER COLUMN temp_optima_min TYPE DECIMAL(4,2),
ALTER COLUMN temp_optima_max TYPE DECIMAL(4,2);
```

---

## 4. Gestión de Ambigüedad (La "IA" en Backend)
¿Cómo sabe el sistema qué tomate elegir?
Implementaremos una **Lógica de Prioridad Regional**:
1.  Si la búsqueda devuelve múltiples resultados, PHP prioriza aquellos cuyo `common_name` o `scientific_name` coincida con los cultivos de nuestra lista maestra de Almería/Murcia.
2.  Se añade una etiqueta visual: **"Recomendado para tu zona"** en los resultados que coincidan con el PDF de planificación.

---

## 5. Seguridad y Rendimiento
*   **API Caching:** Los resultados de Perenual se almacenarán temporalmente en una tabla de caché para evitar latencia y consumo innecesario de la cuota de la API.
*   **Protección de Key:** La API Key se gestionará exclusivamente a través del archivo `.env` en el backend, inaccesible desde el navegador.

---

> [!NOTE]
> Esta implementación garantiza que el sistema sea profesional, robusto y fácil de usar, eliminando la necesidad de que el agricultor conozca términos técnicos botánicos mientras mantenemos la precisión científica necesaria para la automatización del riego.
