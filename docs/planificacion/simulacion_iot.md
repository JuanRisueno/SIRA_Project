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
```

### Funcionalidad Interna:
*   **Inyección Directa:** El script se conecta a PostgreSQL e inserta registros en la tabla `MEDICION`.
*   **Modo En Vivo:** Genera un registro cada **5-10 segundos** durante la ejecución para que los cambios se vean reflejados inmediatamente en la web.
*   **Variabilidad Realista:** Aplica pequeñas fluctuaciones (ruido) para evitar líneas rectas artificiales.

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

---

> [!TIP]
> **Ventaja en la Defensa:** Al ejecutar el simulador manualmente con `--clima=tormenta`, el alumno demuestra control total sobre la lógica de negocio y puede explicar exactamente cómo reacciona el backend de SIRA ante situaciones críticas de forma inmediata.
