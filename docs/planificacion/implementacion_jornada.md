# 🗓️ Lógica de Implementación: Jornada Laboral (No-JS & JSON-Based)

Este documento detalla la arquitectura y el flujo de datos para la gestión de turnos y jornadas laborales en SIRA, optimizado para la defensa del TFG sin dependencias de base de datos para configuración y con **0% JavaScript**.

---

## 1. Arquitectura de Almacenamiento

Para mantener la base de datos relacional limpia de configuraciones de usuario variables, se utiliza un sistema de **ficheros JSON independientes**.

*   **Ubicación:** `backend/app/config_clientes/`
*   **Nombre de Fichero:** `jornada_{cliente_id}.json`
*   **Formato de Datos:**
    ```json
    {
      "default": [
        {"inicio": "08:00", "fin": "14:00"},
        {"inicio": "16:00", "fin": "20:00"}
      ],
      "0": [], 
      "1": null,
      "6": [
        {"inicio": "09:00", "fin": "13:00"}
      ]
    }
    ```
    *   **`default`**: Jornada base que se aplica a cualquier día no especificado.
    *   **`0` al `6`**: Representan de Domingo (0) a Sábado (6).
    *   **`null`**: Indica que el día **hereda** la configuración de `default`.
    *   **`[]`**: Indica que el día es **No Laborable** (descanso).
    *   **`[...]`**: Indica una jornada **específica** de hasta 3 tramos que anula al `default`.

---

## 2. Interfaz de Usuario (0% JavaScript)

Siguiendo las restricciones del proyecto, la interfaz se basa puramente en PHP y envíos POST estándar.

### Estructura del Formulario (`formulario_jornada.php`)
*   **Bloque Global:** 3 filas de inputs (Inicio/Fin) para definir el `default`.
*   **Bloque Diario:** Una lista de los 7 días (L-D). Cada día tiene:
    *   Un selector de estado: `Herencia (Default)`, `Descanso (Cerrado)`, o `Especial`.
    *   Si es `Especial`, se muestran 3 filas de inputs para ese día concreto.
*   **Botón de Guardado:** Único envío POST al servidor.

### Procesamiento en Servidor (PHP)
1.  Recibe el array masivo de inputs.
2.  Limpia los campos vacíos.
3.  Construye el objeto JSON según la lógica de herencia.
4.  Realiza una petición `PUT` a la API de FastAPI con el JSON final.

---

## 3. API y Validación (FastAPI)

El backend de Python es el encargado final de garantizar la integridad de los datos.

*   **Endpoint:** `PUT /api/v1/config/jornada/{cliente_id}`
*   **Validaciones Críticas:**
    1.  **Límite de Tramos:** Máximo 3 objetos por cada clave de día.
    2.  **Consistencia Temporal:** `inicio` < `fin` en todos los tramos.
    3.  **Seguridad:** Validar que el `cliente_id` del token coincide con el del recurso (o que el usuario sea Admin/Root).

---

## 4. Lógica de Decisión para Actuadores (IoT)

Cuando el simulador o el backend comprueban si deben encender las luces:

1.  Determinar el `cliente_id` del invernadero.
2.  Cargar el fichero `jornada_{id}.json`.
3.  Obtener el día actual (`0-6`).
4.  Resolver la jornada:
    *   Si el día específico tiene datos -> Usar esos datos.
    *   Si el día es `null` -> Usar `default`.
    *   Si el día es `[]` -> Parada (No laborable).
5.  Comprobar si `now()` está dentro de alguno de los tramos resueltos.
6.  Aplicar lógica de **Cortesía de 2 Horas** si hubo acción manual previa.

---

> [!TIP]
> **Ventaja de esta implementación:** Al no usar base de datos para los horarios, podemos hacer copias de seguridad de las configuraciones de los clientes simplemente copiando una carpeta, y la base de datos se mantiene enfocada exclusivamente en los datos de telemetría y estructura.
