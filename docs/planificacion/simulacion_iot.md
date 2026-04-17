# 📡 Estrategia de Simulación IoT - SIRA (Modo Defensa)

Para garantizar una presentación dinámica y controlada, SIRA utiliza un simulador de telemetría manual que permite forzar estados climáticos en tiempo real ante el tribunal.

---

## 1. El Simulador Manual (`simulador.py`)

Se ha descartado el uso de servicios automáticos (systemd) para evitar procesos "ocultos" que puedan fallar sin previo aviso. El simulador es un script independiente de Python que se ejecuta bajo demanda.

### Ejecución por Consola:
El administrador puede lanzar el script con argumentos específicos para demostrar la respuesta del sistema:

```bash
# Simular un escenario de tormenta (Viento alto, luz baja)
python simulador.py --clima=tormenta

# Simular una ola de calor (Temperatura > 40ºC)
python simulador.py --clima=calor_extremo

# Simular estado óptimo
python simulador.py --clima=ideal

# Simular estado aleatorio (Nuevo)
python simulador.py --clima=random
```

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

### 📡 Sensores (Entrada de Datos)
1.  **Temperatura Aire (ºC):** Control ambiental básico para evitar estrés térmico.
2.  **Humedad Relativa (%):** Crítico para la prevención de hongos y enfermedades foliares.
3.  **Radiación Solar (W/m²):** Permite gestionar la activación de iluminación LED y fotoperiodo.
4.  **Humedad Suelo (%):** El parámetro maestro que dispara la lógica de riego inteligente.
5.  **Viento (km/h):** Sensor de seguridad para la integridad estructural (cierre de ventanas).

### ⚙️ Actuadores (Acciones del Backend)
1.  **Electroválvula Riego:** Ejecuta el suministro hídrico según la humedad del suelo.
2.  **Motor Ventana:** Regula la temperatura y humedad mediante ventilación natural.
3.  **Iluminación LED:** Garantiza visibilidad en jornada laboral y optimiza el fotoperiodo.
4.  **Ventilador Extractor:** Control forzado de la calidad del aire y evacuación de calor.
5.  **Calefacción:** Activación ante riesgo de heladas (mantenimiento de temperatura mínima).

---

> [!TIP]
> **Ventaja en la Defensa:** Al ejecutar el simulador manualmente con `--clima=tormenta`, el alumno demuestra control total sobre la lógica de negocio y puede explicar exactamente cómo reacciona el backend de SIRA ante situaciones críticas de forma inmediata.
