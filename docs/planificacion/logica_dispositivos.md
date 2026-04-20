# 🧠 Lógica de Operación: Sensores y Actuadores SIRA (Modo Simulación)

Este documento describe el funcionamiento técnico y los algoritmos de decisión del sistema SIRA. En el marco del **MVP Robusto para la defensa del TFG**, toda la interacción física está **simulada por software**, priorizando la demostración de la lógica de control.

---

## 1. Escenarios de Telemetría (Presets de Murcia y Almería)

Dado que no se cuenta con hardware físico, el sistema activa "fotos fijas" de estados meteorológicos preconfigurados. Al pulsar el botón **Randomize**, el sistema selecciona uno de estos presets e inyecta los valores en la base de datos de forma instantánea.

| Sensor | Unidad | Propósito en el Preset |
| :--- | :--- | :--- |
| **Temperatura** | ºC | Control de calefacción y ventanas. |
| **Viento** | km/h | Seguridad estructural (Cierre forzado). |
| **Lluvia** | % / Binario | Detección de tormentas para cierre de ventanas. |
| **Radiación Solar** | W/m² | Control de iluminación LED y fotoperiodo. |
| **Reloj (Horario)** | HH:MM | Condición maestra para jornada laboral (Luces). |

---

## 2. Jerarquía de Seguridad (Prioridades de Control)

El sistema SIRA opera bajo una jerarquía estricta. Una orden de seguridad **siempre** anula una orden de optimización climática.

1.  **Nivel 1: Seguridad Estructural (Crítico)** -> Protección contra viento y tormentas.
2.  **Nivel 2: Seguridad Biológica (Rescate)** -> Evitar heladas o quemaduras por radiación.
3.  **Nivel 3: Optimización Climática** -> Mantener el rango ideal de crecimiento.

## 3. Algoritmos de Decisión (Backend Simulado)

En esta fase del proyecto, el Backend procesa las mediciones inyectadas y decide el estado teórico de los actuadores. El resultado se refleja en la base de datos (`estado_actuador`) y es visualizado por el agricultor en el Dashboard.

### 💧 Electroválvula Riego (Prioridad 3)
*   **Activación:** Si `Humedad Suelo` < 60%.
*   **Parada:** Si `Humedad Suelo` >= 85%.
*   **Nota:** Si la `Humedad Suelo` cae por debajo del 40%, se dispara una **Alerta Crítica** al móvil del cliente.

### 🪟 Motor Ventana (Prioridad 1 y 3)
*   **Apertura (Clima):** Si `Temperatura` > 30ºC.
*   **Cierre por SEGURIDAD:** Si `Viento` > 45 km/h O se detecta `Lluvia`.
*   **BLOQUEO:** El viento y la lluvia tienen prioridad absoluta sobre el calor interior.

### 🌑 Iluminación LED (Prioridad Estricta)
La iluminación es el único actuador que depende de una condición externa al clima: la Jornada Laboral.
1.  **Fuera de Jornada:** Las luces permanecen **APAGADAS** sin excepción (Invernadero Cerrado).
2.  **En Jornada Laboral:** 
    *   **ON:** Si `Radiación Solar` < 200 W/m² (Día nublado o crepúsculo).
    *   **OFF:** Si la luz natural es suficiente (> 250 W/m²).
3.  **Control Manual:** El agricultor tiene un botón de "Override" para encender/apagar a voluntad independientemente de la lógica anterior.

### 🌀 Ventilador Extractor (Prioridad 3)
*   **Activación:** Si `Hum_Relativa` > 90% (Incluso si las ventanas están cerradas por viento, para evacuar humedad).
*   **Propósito:** Renovación forzada del aire y prevención de *Botrytis*.

### 🛡️ Calefacción (Prioridad 2)
*   **Activación:** Si `Temp_Aire` < 10ºC (Temperatura de rescate).
*   **Parada:** Si `Temp_Aire` >= 12ºC (Histéresis de 2 grados para eficiencia energética).

---

## 4. Escenarios de Defensa (Failsafe Mode)

### 🚩 Caso 1: Tormenta con Calor (Conflicto de Lógica)
*   **Situación:** Interior a 35ºC (requiere ventilación) pero Viento exterior a 60km/h.
*   **Acción SIRA:** Las ventanas **permanecen cerradas**. Se activan los **Extractores** al 100% para evacuar el calor. El sistema prefiere un pico de temperatura temporal a una rotura de la estructura.

### 🚩 Caso 2: Jornada Laboral Nublada
*   **Situación:** Hora 10:00 AM, Tormenta oscura, Radiación < 100 W/m².
*   **Acción SIRA:** Se activa la **Iluminación LED** automáticamente para garantizar que los operarios puedan trabajar de forma segura en el interior de la nave.

---

## 5. Control Cooperativo: El Factor Humano (Planificación)

SIRA implementa una lógica híbrida que permite al agricultor intervenir en cualquier momento sin desactivar el sistema automático.

### A. El "Respeto de las 2 Horas"
Si un usuario realiza un cambio manual (ej. enciende las luces a las 06:30):
1.  El sistema detecta la discrepancia entre el estado deseado y el actual.
2.  Se activa un estado de **"Cortesía"** para ese dispositivo.
3.  SIRA no intentará corregir el estado del dispositivo hasta que pasen **120 minutos** de inactividad manual O hasta que ocurra un **cambio de tramo laboral**.

### B. Lógica de Jornada Partida (Configuración por Cliente)
Para el cálculo de iluminación y clima laboral, el sistema está planificado para consultar ficheros independientes:
1.  **Fichero de Configuración:** SIRA busca el archivo `backend/app/config_clientes/jornada_{id_cliente}.json`.
2.  **Calendario Semanal:** El JSON contiene los días marcados como "laborales" y sus respectivos tramos.
3.  **Tramos Múltiples:** Comprueba si la hora actual está contenida en alguno de los intervalos (ej. Mañana/Tarde).
4.  **Decisión:** Si se cumplen las condiciones de horario y la luz es baja (< 200 W/m²), se activa la iluminación.

---

> [!IMPORTANT]
> **Contexto de Simulación:** Aunque el sistema está diseñado para conectarse a hardware real en el futuro (vía MQTT o API), para el éxito de la defensa nos centramos en demostrar que la **Inteligencia de SIRA** es capaz de analizar datos de sensores simulados, respetar la voluntad del usuario y gestionar horarios complejos de forma autónoma.
