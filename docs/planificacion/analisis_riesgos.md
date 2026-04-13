# ⚠️ Análisis de Riesgos y Mitigación - SIRA

Este documento identifica los posibles "puntos de dolor" y desafíos técnicos que podrían surgir durante la implementación de los módulos de Cultivos e IoT, junto con sus estrategias de mitigación.

---

## 1. Implementación de Cultivos (API Perenual)

### 🚩 Riesgo A: Datos Nulos o Inconsistentes en la API
**Problema:** Perenual es una base de datos botánica inmensa pero no siempre completa. Algunos cultivos pueden devolver valores como `null` para el pH o descripciones en inglés que no encajan en la UI.
*   **Mitigación:** Implementar una **Capa de Normalización** en PHP. Si la API devuelve `null`, el sistema consultará automáticamente el "LKB" (Local Knowledge Base) de Almería o asignará un valor seguro por defecto (ej. pH 6.5) marcándolo como "Dato Estimado".

### 🚩 Riesgo B: Cuotas y Latencia de la API
**Problema:** Las APIs externas tienen límites de peticiones (Rate Limiting). Si muchos clientes buscan cultivos a la vez, podríamos ser bloqueados. Además, la búsqueda puede tardar 2-3 segundos, haciendo que la web parezca lenta.
*   **Mitigación:** Implementar un **Caché de Búsqueda**. Antes de llamar a Perenual, PHP mirará si ese cultivo ya ha sido buscado y guardado en nuestra base de datos en las últimas 24h.

### 🚩 Riesgo C: Ambigüedad Botánica
**Problema:** Existen 10 tipos de "Tomate". El cliente podría elegir uno ornamental por error que tenga parámetros letales para un tomate de producción.
*   **Mitigación:** Priorización por **Palabras Clave Regionales**. Al recibir los resultados de la API, PHP puntuará más alto aquellos que contengan términos como "General", "Garden" o nombres científicos validados en nuestro PDF maestro.

---

## 2. Sistema de Simulación IoT

### 🚩 Riesgo D: Bloqueo de E/S en la Base de Datos (I/O Wait)
**Problema:** Con 10 sensores escribiendo cada 15 segundos en una máquina pequeña (como una VM o Raspberry), PostgreSQL podría empezar a consumir mucha CPU solo gestionando los índices de la tabla `MEDICION`.
*   **Mitigación:** Uso de **Tablas Unlogged** para datos temporales o, en su defecto, asegurar que el script de simulación use **inserciones por lotes (bulk inserts)** si simulamos múltiples invernaderos a la vez.

### 🚩 Riesgo E: Desfase de Pantalla (No-JS)
**Problema:** El `<meta refresh>` recarga toda la página. Si el usuario está en medio de un formulario cuando la página se refresca, perderá los datos.
*   **Mitigación:** Aplicar el refresco **solo en las vistas de monitorización** (Dashboards). Las páginas de gestión (Añadir Cultivo, Configuración) no deben tener auto-refresco para garantizar la integridad de la entrada de datos.

---

## 3. Desafíos de Arquitectura (ASIR Focus)

### 🚩 Riesgo F: El Script de Simulación se detiene
**Problema:** Si el script de Python/PHP que genera los datos se cae, las gráficas de SIRA se quedarán planas ("Línea muerta"), lo cual da una imagen de sistema inestable.
*   **Mitigación:** Configurar el simulador como un **Servicio de Systemd** con política de `Restart=always`. Así, si el script falla por un error, Linux lo levantará automáticamente en menos de un segundo.

### 🚩 Riesgo G: Crecimiento de Logs
**Problema:** Los logs de error de PHP y Nginx pueden llenar el disco si la API de Perenual empieza a dar errores de conexión repetidos.
*   **Mitigación:** Configurar **Logrotate** en el servidor para asegurar que los logs nunca ocupen más de un espacio definido (ej. 100MB).

---

## 4. Riesgos Avanzados (Docker e Infraestructura)

### 🚩 Riesgo H: El Infierno de las Zonas Horarias (Timezone Hell)
**Problema:** Los contenedores Docker suelen usar UTC (hora de Londres). Si el simulador mete datos en hora UTC y el frontend de PHP busca datos en hora local (España/CEST), las gráficas aparecerán vacías porque el sistema creerá que los datos son de "dentro de dos horas".
*   **Mitigación:** Inyectar la variable de entorno `TZ=Europe/Madrid` en todos los servicios de `docker-compose.yml` y usar el tipo `TIMESTAMPTZ` en PostgreSQL para manejar las conversiones automáticamente.

### 🚩 Riesgo I: Efecto "Estampida" en la Sincronización (Rate Limiting)
**Problema:** El barrido mensual de Perenual podría lanzar 50 peticiones en el mismo segundo, haciendo que la API nos bloquee por sospecha de ataque.
*   **Mitigación:** Implementar **Jitter / Sleep**. Añadir un retardo aleatorio (`sleep(2)`) entre cada petición en el script de actualización para "dejar respirar" a la API externa.

### 🚩 Riesgo J: Inyección de Datos Fantasma (Seguridad IoT)
**Problema:** Si el endpoint de recepción de datos de sensores es público, cualquiera podría inyectar telemetría falsa (ej. 500ºC) para sabotear el sistema.
*   **Mitigación:** Implementar un **IoT-Token privado** compartido entre el simulador y la API. Cualquier petición sin este token en la cabecera será rechazada con un error 401.

---

> [!TIP]
> **Conclusión para la Defensa:** Anticipar estos problemas ante el tribunal demuestra que no solo has programado una web, sino que has diseñado un **sistema robusto** capaz de sobrevivir en un entorno de producción real.
