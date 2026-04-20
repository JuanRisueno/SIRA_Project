## 1. El Sistema de Presets (Escenarios Estáticos)

Para garantizar una defensa 100% controlada, SIRA utiliza un catálogo de escenarios climáticos fijos. Estos actúan como "trajes" que el administrador puede ponerle al invernadero para demostrar su inteligencia.

### Escenarios Configurados:
1.  **Ideal**: Condiciones óptimas (Riego OFF, Ventanas entreabiertas, LED OFF).
2.  **Tormenta de Verano**: Viento > 50km/h (Cierre seguridad), Lluvia detectada, Temperatura alta.
3.  **Ola de Calor (Poniente)**: Temperatura > 40ºC, Radiación > 1000W/m², Humedad Suelo bajando.
4.  **Noche de Helada**: Temperatura < 5ºC (Calefacción ON), Radiación 0.
5.  **Tramo Laboral Nublado**: En jornada laboral, Radiación < 200W/m² (Luces ON).

### Funcionalidad Interna y Actuadores:
*   **Inyección Directa:** El script se conecta a PostgreSQL e inserta registros en la tabla `MEDICION`.
*   **Modo En Vivo:** Genera un registro cada **5-10 segundos** durante la ejecución para que los cambios se vean reflejados inmediatamente en la web.
*   **Variabilidad Realista:** Aplica pequeñas fluctuaciones (ruido) para evitar líneas rectas artificiales.
*   **Lógica de Actuadores (Failsafe):** El sistema detectará el estado inyectado y activará automáticamente los actuadores correspondientes (ej. encender luces en oscuridad, activar riego en humedad baja) para demostrar la respuesta del backend en tiempo real.


---

## 2. Visualización Robusta ("Gráficos Failsafe")

Para evitar errores matemáticos complejos en el renderizado SVG (como curvas Bezier) que podrían fallar con datos extremos, SIRA utiliza trazados geométricos simples y fiables:

### A. Trazados Lineales (`<polyline>`)
*   Se utilizan puntos de datos conectados por líneas rectas.
*   Cálculo matemático directo en PHP: `x = tiempo`, `y = valor_normalizado`.
*   Compatibilidad total con todos los navegadores sin necesidad de librerías externas.

### B. Gráficos de Barras
*   Para comparativas rápidas o históricos diarios, se utilizan elementos rectangulares (`<rect>`) de fácil depuración y alta visibilidad.

---

## 3. Gestión de Datos (Data Lifecycle)

Mantenemos la lógica de limpieza para asegurar que el sistema sea profesional y escalable:

1.  **Retención Corta (48h):** Los datos generados por el simulador se mantienen en alta resolución durante 2 días.
2.  **Downsampling Nocturno:** Un script (`mantenimiento.php` o similar) consolida los datos en medias horarias cada noche.
3.  **Purga de Logs:** Se eliminan los registros antiguos de 15s para mantener la base de datos ágil.

---

## 4. Interfaz PHP (No-JS)

*   **Refresco de Datos:** Se emplea la etiqueta `<meta http-equiv="refresh" content="10">` en el dashboard de monitorización.
*   **Sincronización:** El tiempo de refresco de la web coincide con la frecuencia del simulador manual, permitiendo ver el "en vivo" de la telemetría.
*   **Control Maestro (Botón "Randomize"):** Se incluirá un botón destacado en el panel de control que, al ser pulsado, disparará un evento de aleatorización del clima. Esto forzará un cambio inesperado de parámetros para demostrar cómo los actuadores reaccionan instantáneamente para proteger el cultivo.

---

## 4. Interfaz de Configuración de Jornada (Planificación UI)

Para facilitar la gestión al cliente, el diseño de SIRA contempla un panel de "Configuración del Invernadero" con:
*   **Selector de Días Laborales:** Checkboxes para marcar los días de operación.
*   **Gestor de Tramos Múltiples:** Interfaz para añadir/eliminar tramos horarias.
*   **Persistencia Descentralizada:** Al guardar, SIRA genera o actualiza el fichero `backend/app/config_clientes/jornada_{id}.json`.
*   **Interruptor Maestro (Overriding):** Botones de acción inmediata sobre cada actuador que activan la lógica de cortesía de 2 horas.

---

## 5. Inventario de Dispositivos (10 Canales de Simulación)

Para el MVP se han seleccionado 10 tipos de dispositivos (5 sensores y 5 actuadores) que permiten cubrir todos los escenarios críticos de la agricultura intensiva de Almería y Murcia:

### 📡 Sensores (Presets Geográficos)
1.  **Temperatura (ºC):** Control ambiental básico Almería/Murcia.
2.  **Lluvia (%):** Sensor de seguridad para cierre de ventanas.
3.  **Radiación Solar (W/m²):** Determina la necesidad de iluminación LED.
4.  **Humedad Suelo (%):** Dispara la lógica de riego inteligente.
5.  **Viento (km/h):** Sensor de seguridad estructural.

### ⚙️ Actuadores (Respuestas en Tiempo Real)
1.  **Electroválvula Riego:** Suministro hídrico.
2.  **Motor Ventana:** Ventilación y seguridad.
3.  **Iluminación LED:** Visibilidad laboral y fotoperiodo.
4.  **Ventilador Extractor:** Calidad del aire.
5.  **Calefacción:** Protección contra heladas.

---

> [!TIP]
> **Ventaja en la Defensa:** Al ejecutar el simulador manualmente con `--clima=tormenta`, el alumno demuestra control total sobre la lógica de negocio y puede explicar exactamente cómo reacciona el backend de SIRA ante situaciones críticas de forma inmediata.
