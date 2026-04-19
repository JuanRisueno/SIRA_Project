# 🕒 Implementación de Jornada Laboral y Herencia Global

Este documento describe la arquitectura técnica del sistema de gestión de horarios de SIRA, incluyendo la lógica de herencia jerárquica entre el cliente y sus infraestructuras.

---

## 1. Modelo de Datos y Jerarquía

El sistema utiliza un modelo de persistencia híbrido basado en archivos JSON para permitir una resolución dinámica de horarios.

### 🏗️ Niveles de Configuración
1.  **Nivel Maestro (Cliente)**: Define la política horaria base para toda la organización.
    - Archivo: `config_clientes/jornada_cliente_{id}.json`
2.  **Nivel Individual (Invernadero)**: Configuración específica por nave.
    - Archivo: `config_invernaderos/jornada_inv_{id}.json`
    - Campo clave: `heredar_de_global` (Boolean).

---

## 2. Lógica de Herencia y Sincronización

### 🔄 El Flag `heredar_de_global`
- **TRUE**: El invernadero ignora su propia configuración local y adopta estrictamente lo definido en el Maestro del cliente.
- **FALSE**: El invernadero utiliza su configuración individual, permitiendo desviaciones específicas (ej: turnos especiales para un cultivo concreto).

### ⚡ Sincronización Masiva (Push Strategy)
Para simplificar la gestión, al guardar la **Política Global**, el backend ejecuta una sincronización automática:
1. Guarda el JSON maestro del cliente.
2. Itera por todos los invernaderos vinculados al cliente.
3. Activa `heredar_de_global = true` en todos ellos.
Esto asegura que un solo cambio en el Maestro se propague instantáneamente a toda la flota de naves.

---

## 3. API y Endpoints (FastAPI)

El backend expone rutas diferenciadas para gestionar cada nivel:

*   **Configuración Individual**:
    - `GET /api/v1/config/jornada/invernadero/{id}`
    - `POST /api/v1/config/jornada/invernadero/{id}`
*   **Configuración Maestra**:
    - `GET /api/v1/config/jornada/cliente/{id}`
    - `POST /api/v1/config/jornada/cliente/{id}`
*   **Resumen de Estados**:
    - `GET /api/v1/config/jornada/cliente/{id}/resumen`: Devuelve el estado de sincronización y configuración de todas las naves del cliente para el dashboard.

---

## 4. Resolución del Horario (Algoritmo de Decisión)

Cuando un actuador IoT necesita saber si es "horario laborable", el sistema sigue este orden de precedencia:

1.  **Cargar Configuración de Nave**: ¿Tiene el flag `heredar_de_global` activo?
    - **SÍ**: Cargar el JSON del **Cliente**.
    - **NO**: Usar el JSON del **Invernadero**.
2.  **Determinar Día Actual**:
    - Si el día tiene tramos específicos -> Usar tramos.
    - Si el día es `null` (Heredar) -> Usar bloque `default`.
    - Si el día es `[]` (Vacio) -> Marcar como No Laborable.
3.  **Evaluación Temporal**: Comprobar si `now()` coincide con algún tramo definido.

---

## 5. Interfaz de Usuario (Frontend Premium)

- **Banner Maestro**: Acceso directo a la política global desde el resumen.
- **Indicador 🔗**: Icono visual en las tarjetas para identificar naves sincronizadas.
- **Bloqueo Dinámico**: El formulario individual se bloquea visualmente y vía JS si la herencia está activa, evitando ediciones contradictorias.

---

> [!IMPORTANT]
> Esta implementación garantiza que el agricultor pueda gestionar 1 o 100 naves con el mismo esfuerzo, manteniendo la flexibilidad de personalización unidad por unidad.
