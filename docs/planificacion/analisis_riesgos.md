# ⚠️ Análisis de Riesgos y Mitigación - SIRA (MVP Robusto)

Este documento identifica los desafíos técnicos críticos para la estabilidad del sistema, eliminando los riesgos asociados a servicios externos y centrándose en la robustez de la infraestructura local.

---

## 1. Riesgos de Infraestructura y Datos

### 🚩 Riesgo A: El Infierno de las Zonas Horarias (Timezone Hell)
**Problema:** Los contenedores Docker suelen usar UTC. Si el simulador inserta datos en UTC y el Dashboard de PHP los busca en hora local (España), las gráficas aparecerán vacías porque el sistema creerá que los datos son del futuro o del pasado.
*   **Mitigación:** Inyectar forzosamente la variable `TZ=Europe/Madrid` en todos los archivos `docker-compose.yml` y configurar PostgreSQL para manejar `TIMESTAMPTZ` de forma coherente.

### 🚩 Riesgo B: Inyección de Datos Fantasma (Seguridad IoT)
**Problema:** Aunque el simulador sea local, la API de recepción de datos debe ser segura. Un acceso no autorizado podría inyectar registros falsos (ej. 1000ºC) para desestabilizar la lógica de alarmas.
*   **Mitigación:** Implementar un **IoT-Token privado**. El simulador manual enviará una clave secreta en la cabecera; cualquier petición sin este token será rechazada con un código 401 (Unauthorized).

---

## 2. Riesgos de la Interfaz (No-JS)

### 🚩 Riesgo C: Desfase de Pantalla y Pérdida de Datos
**Problema:** Al no usar JavaScript, el refresco automático se hace mediante `<meta refresh>`. Esto recarga toda la página cada 10-15 segundos. Si el usuario está escribiendo en un formulario de configuración, el refresco le borrará lo escrito.
*   **Mitigación:** Aplicar el auto-refresco **únicamente en las vistas de monitorización exclusiva**. Las páginas de edición, gestión de cultivos o administración no tendrán esta etiqueta, priorizando la integridad de los datos sobre la actualización visual en vivo.

---

## 3. Riesgos de Rendimiento Local

### 🚩 Riesgo D: Bloqueo de E/S por Alta Frecuencia
**Problema:** Si el simulador se ejecuta con una frecuencia muy alta (menos de 1 segundo), PostgreSQL podría saturar el disco de la máquina virtual con logs de transacciones.
*   **Mitigación:** Establecer un límite de seguridad en el `simulador.py` para no permitir inserciones a intervalos menores de 5 segundos, asegurando la fluidez del sistema anfitrión.

---

## 4. Riesgos de Administración (ASIR)

### 🚩 Riesgo E: La "Línea Muerta" en la Defensa
**Problema:** Llegar a la presentación y que las gráficas no se muevan porque el simulador no se ha iniciado.
*   **Mitigación:** Documentar y preparar un **script de arranque rápido** que levante el entorno Docker y el simulador en modo "ideal" con un solo comando, reduciendo el estrés durante la demo.

### 🚩 Riesgo F: Errores Humanos en Alta Manual
**Problema:** Al permitir introducir cultivos libremente, el usuario podría introducir rangos absurdos (ej. Temperatura mínima de 100ºC) por error tipográfico.
*   **Mitigación:** Implementar **Validación a nivel de Formulario (Server-side)** en PHP. El sistema rechazará valores que se salgan de límites biológicos razonables (ej. Temperaturas fuera del rango 0-50ºC) antes de guardarlos.

---

> [!IMPORTANT]
> Al eliminar la dependencia de la API externa Perenual, hemos erradicado los riesgos de latencia de red, rate limiting y ambigüedad botánica. El enfoque ahora es el **control absoluto del entorno local**, asegurando que el 100% de la funcionalidad dependa exclusivamente de nuestro código.
