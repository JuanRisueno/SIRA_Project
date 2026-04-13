# 📡 Estrategia de Simulación y Hardware IoT - SIRA

Debido a la ausencia de hardware físico (Raspberry Pi/Sensores), este documento define cómo emularemos el ecosistema IoT para que sea creíble, funcional y visualmente impactante durante la presentación del proyecto.

---

## 1. Recomendaciones de Hardware (Modelo de Referencia)
Para que el proyecto sea técnicamente sólido, simularemos los siguientes componentes reales que se usarían en un invernadero de Almería:

### A. Sensores (Entrada)
1.  **DHT22 (Temperatura y Humedad Aire):** El estándar industrial básico para microclima.
2.  **Sensor de Humedad de Suelo (Capacitivo):** Para evitar la corrosión. Vital para el control del riego.
3.  **BH1750 (Luxómetro/Luz):** Mide la intensidad lumínica en Lux (parámetro clave para apertura de ventanas).
4.  **MH-Z19B (Sensor CO2):** Mide la concentración de dióxido de carbono (vital para ventilación en invernaderos cerrados).
5.  **Anemómetro (Velocidad del Viento):** Sensor crítico para la seguridad estructural; dispara el cierre automático de ventanas cenitales ante rachas fuertes.

### B. Actuadores (Salida)
1.  **Relé de Bomba de Riego:** Controla el flujo de agua.
2.  **Extractor / Ventilador:** Control de temperatura y renovación de aire.
3.  **Servomotor de Ventana Cenital:** Apertura física del techo del invernadero.
4.  **Malla de Sombreo:** Pantalla motorizada para reducir la radiación solar.
5.  **Iluminación de Trabajo / Apoyo:** Focos LED controlables para permitir el trabajo en horas sin luz solar (ej. 6:00 AM) o suplemento lumínico para el cultivo.

---

## 2. Estrategia de Simulación (Backend)
No usaremos datos estáticos. Crearemos un **"Generador de Telemetría Dinámica"**:

*   **Motor de Simulación (Python/PHP Script):** Un script que corre en segundo plano y escribe en la tabla `MEDICION` cada **15 segundos**. Esta frecuencia permite ver cambios dinámicos sin saturar la base de datos de logs.
*   **Ruido Blanco:** Los datos no serán planos (ej. 25.0, 25.0...). Añadiremos pequeñas variaciones aleatorias (±0.2) para que las gráficas parezcan reales.
*   **Ciclo de Retroalimentación:** Si el sistema detecta que la bomba de riego está "ON", la simulación de humedad de suelo debe subir gradualmente.

---

## 3. Escenarios de Presentación (Presets)
Para demostrar la "inteligencia" de SIRA, prepararemos 3 estados climáticos predefinidos:

### ☀️ Escenario A: Día Soleado de Primavera
*   **Estado:** Temperatura ideal (22°C), Luz Alta, Humedad 50%.
*   **Comportamiento esperado:** Ventanas medio abiertas, riego desactivado, CO2 estable.

### ⛈️ Escenario B: Día Lluvioso / Tormenta (Modo Seguridad)
*   **Estado:** Humedad muy alta (90%), Luz baja, **Viento fuerte (>40 km/h)**.
*   **Comportamiento esperado:** **Cierre total de ventanas cenitales** (bloqueo de seguridad para evitar daños estructurales), luces de apoyo encendidas, desactivación de extractores para mantener calor remanente.

### 🔥 Escenario C: Ola de Calor en Verano (Almería)
*   **Estado:** Temperatura crítica (>38°C), Humedad muy baja (20%), Luz Extrema.
*   **Comportamiento esperado:** Ventiladores al 100%, apertura total de ventanas, riego de emergencia activado para bajar la temperatura del suelo (nebulización).

---

## 4. Implementación en la Interfaz (PHP)
Dado que estamos limitados a **PHP puro**:

1.  **Auto-Refresh:** En la vista de sensores (`vista_iot.php`), incluiremos un tag `<meta http-equiv="refresh" content="15">`. Esto sincroniza el refresco visual de la web con el ciclo de escritura del simulador (15s).
2.  **Alertas Visuales:** Si un sensor simulado entra en rango crítico (ej. Rojo para calor extremo), la interfaz PHP cambiará los estilos CSS dinámicamente.
3.  **Selector de Presets:** Crearemos un panel oculto (Solo Admin) para cambiar el modo de simulación en vivo durante la defensa del proyecto.

---

## 5. Gestión de Volumen de Datos (Data Lifecycle)
Para evitar que la tabla `MEDICION` crezca indefinidamente y ralentice los servidores (un problema real en IoT de 15s), SIRA implementará una política de retención por capas:

1.  **Capa de Alta Resolución (48h):** Se mantienen todos los datos de 15 segundos durante los dos últimos días para visualización en tiempo real y análisis de incidentes.
2.  **Agregación Diaria (Downsampling):** Cada noche, un script consolidará las miles de mediciones del día anterior en **medias horarias** guardadas en una tabla `MEDICION_HISTORICA`.
3.  **Purga Automática:** Tras la agregación, se eliminarán los registros de 15 segundos más antiguos de 48h, manteniendo la tabla `MEDICION` siempre ligera y rápida.
4.  **Escalabilidad:** Se plantea el uso de **Particionado de Tablas** en PostgreSQL como siguiente paso evolutivo en caso de despliegue en una red de múltiples invernaderos.

---

## 6. Visualización Premium e Históricos (Gráficos "Wow")
Para que el apartado IoT sea el más profesional de SIRA sin usar JavaScript, implementaremos:

### A. Generación de Gráficos en PHP (SVG Dinámico)
*   **Técnica:** PHP procesará los datos de la DB y generará un gráfico vectorial (SVG) directamente en el HTML.
*   **Estética:** Uso de líneas suavizadas (Bezier curves), gradientes de color y sombras para un acabado moderno.
*   **Interactividad:** Implementación de efectos `:hover` en CSS para resaltar puntos de medición sin código cliente.

### B. Vistas Temporales (Tabs)
El dashboard permitirá conmutar entre cuatro periodos de visualización:
1.  **Hoy (Real-Time):** Datos de cada 15s de la tabla `MEDICION`.
2.  **Semana (Tendencia):** Medias horarias de la tabla `HISTORICA`.
3.  **Mes (Resumen):** Medias diarias para detectar patrones de consumo/clima.
4.  **Año (Reporte):** Comparativa estacional para el análisis de rendimiento del cultivo.

### C. Diseño "Glassmorphism"
*   Uso de fondos con desenfoque (`blur`) y bordes semi-transparentes para dar profundidad a la interfaz, haciendo que SIRA se sienta como una aplicación de monitorización de alto nivel.

---

> [!TIP]
> **Enfoque ASIR:** Este simulador se puede implementar como un **Servicio de Linux (Systemd)** que corre en el contenedor de Python. Esto demuestra conocimientos de administración de sistemas y automatización, compensando la falta de hardware físico.
