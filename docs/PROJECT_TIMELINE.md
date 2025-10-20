# Línea Temporal del Proyecto SIRA (TFG ASIR)

Este documento detalla las fases, tareas, responsables y estado del proyecto SIRA.

---

## Leyenda de Estados

* ✅ **Finalizado:** Tarea completada.
* 🔵 **En Desarrollo:** Tarea actualmente en progreso.
* 🔴 **Pendiente:** Tarea aún no iniciada.
* 🎯 **Checkpoint:** Hito de revisión del proyecto.

---

## I. Fase de Infraestructura y Baseline (COMPLETADA ✅)

- **Objetivo:** Establecer el entorno de desarrollo, el flujo de trabajo, la base tecnológica y presentar el concepto inicial.
- **Tareas:**
    - **Arquitectura de Software:**
        - Estado: ✅ Finalizado
        - Entregable: `docker-compose.yml` (Versión Maestra)
        - Módulo ASIR: IAW, ASO, SRI
        - Responsable: Juan
    - **Entorno de Desarrollo:**
        - Estado: ✅ Finalizado
        - Entregable: Máquina Dorada (.ova)
        - Módulo ASIR: ASO, SRI
        - Responsable: Juan
    - **Flujo de Trabajo (Git):**
        - Estado: ✅ Finalizado
        - Entregable: Reglas de Protección y Guía (`LEEME_PRIMERO`)
        - Módulo ASIR: Metodología
        - Responsable: Juan
    - **BBDD (Automatización):**
        - Estado: ✅ Finalizado
        - Entregable: Mapeo para inicialización automática
        - Módulo ASIR: GBD, ASO
        - Responsable: Juan
    - **Presentación Inicial:**
        - Estado: ✅ Finalizado
        - Entregable: `SIRA.pdf`
        - Módulo ASIR: Documentación
        - Responsable: Todo el equipo

---
## II. Fase de Desarrollo del Backend y Servicios (EN PROGRESO ➡️)

- **Objetivo:** Construir la base de datos completa, la lógica de la API, integrar servicios externos (cultivos, meteo) y preparar la recepción de datos IoT.
- **Tareas:**
    - **0. Configuración Entorno Individual:**
        - Estado: ✅ Finalizado
        - Capa: Entorno
        - Responsable: Jorge, Alfonso
        - Tareas Clave: Importar VM, seguir `LEEME_PRIMERO`.
        - Módulo ASIR: ASO
    - **1. Diseño BBDD de Precisión:**
        - Estado: 🔴 Pendiente
        - Capa: BBDD
        - Responsable: Jorge
        - Tareas Clave: Completar `10-schema.sql` (todas las tablas).
        - Módulo ASIR: GBD
    - **2. Carga Datos Iniciales (BBDD):**
        - Estado: 🔴 Pendiente
        - Capa: BBDD
        - Responsable: Alfonso
        - Tareas Clave: Añadir `INSERT`s básicos (`clientes`, `invernaderos`) a `20-data.sql`.
        - Módulo ASIR: GBD
    - **3. Conexión ORM:**
        - Estado: 🔴 Pendiente
        - Capa: API/BBDD
        - Responsable: Juan
        - Tareas Clave: Crear `models.py` (ORM SQLAlchemy).
        - Módulo ASIR: Python, GBD
    - **4. Carga Datos Cultivos (Script):**
        - Estado: 🔴 Pendiente
        - Capa: API/SRI
        - Responsable: Juan
        - Tareas Clave: Crear `ingesta_cultivos.py`.
        - Módulo ASIR: Python, SRI
    - **5. Carga Datos Cultivos (BBDD):**
        - Estado: 🔴 Pendiente
        - Capa: BBDD
        - Responsable: Alfonso
        - Tareas Clave: Añadir `INSERT`s de cultivos a `20-data.sql`.
        - Módulo ASIR: GBD
    - **6. CRUD Básico API:**
        - Estado: 🔴 Pendiente
        - Capa: API
        - Responsable: Jorge
        - Tareas Clave: Crear *endpoints* (`GET`, `POST`) iniciales.
        - Módulo ASIR: IAW, Python
    - 🎯 **Checkpoint #1:**
        - Estado: 🔴 Pendiente
        - Capa: Todo
        - Responsable: Todo el equipo
        - Tareas Clave: Verificar - Entorno OK, BBDD básica OK, API básica responde.
        - Módulo ASIR: Calidad
    - **7. Integración IoT (MQTT):**
        - Estado: 🔴 Pendiente
        - Capa: Infraestructura
        - Responsable: Juan
        - Tareas Clave: Añadir Broker **Mosquitto** a `docker-compose.yml`.
        - Módulo ASIR: SRI, ASO
    - **8. Simulación Datos IoT:**
        - Estado: 🔴 Pendiente
        - Capa: IoT/BBDD
        - Responsable: Alfonso
        - Tareas Clave: Crear/ejecutar `simulador_sensor.py` para enviar datos a Mosquitto.
        - Módulo ASIR: Python, SRI
    - **9. Receptor MQTT:**
        - Estado: 🔴 Pendiente
        - Capa: API/BBDD
        - Responsable: Juan / Jorge
        - Tareas Clave: Crear *script* Python que escuche de Mosquitto y guarde en `mediciones`.
        - Módulo ASIR: Python, SRI, GBD
    - 🎯 **Checkpoint #2:**
        - Estado: 🔴 Pendiente
        - Capa: Todo
        - Responsable: Todo el equipo
        - Tareas Clave: Verificar - Flujo MQTT completo (Mosquitto OK, simulador envía, receptor guarda).
        - Módulo ASIR: Calidad
    - **10. API Meteorológica:**
        - Estado: 🔴 Pendiente
        - Capa: API/SRI
        - Responsable: Juan
        - Tareas Clave: Implementar consumo de API externa.
        - Módulo ASIR: Python, SRI
    - **11. Lógica de Riego Inteligente:**
        - Estado: 🔴 Pendiente
        - Capa: API
        - Responsable: Juan / Jorge
        - Tareas Clave: Desarrollar *endpoint* y lógica de decisión (combina BBDD, sensores, meteo).
        - Módulo ASIR: IAW, Python
    - 🎯 **Checkpoint #3:**
        - Estado: 🔴 Pendiente
        - Capa: Todo
        - Responsable: Todo el equipo
        - Tareas Clave: Verificar - Lógica Riego (Endpoint funciona, consulta API meteo, devuelve algo).
        - Módulo ASIR: Calidad

---
## III. Fase de Aplicación, Pruebas y Despliegue (FUTURO ☁️)

- **Objetivo:** Crear la interfaz de usuario, conectar la Raspberry Pi, asegurar el sistema y preparar la documentación final.
- **Tareas:**
    - **12. Aplicación Web (Frontend):**
        - Estado: 🔴 Pendiente
        - Capa: Frontend
        - Responsable: Juan / Alfonso
        - Enfoque: Desarrollar interfaz con **Streamlit**.
        - Módulo ASIR: Python, IAW
    - **13. Conexión IoT Real:**
        - Estado: 🔴 Pendiente
        - Capa: Hardware/IoT
        - Responsable: Alfonso
        - Enfoque: Configurar Raspberry Pi.
        - Módulo ASIR: IoT / Python
    - **14. Pruebas de Integridad BBDD:**
        - Estado: 🔴 Pendiente
        - Capa: BBDD
        - Responsable: Alfonso
        - Enfoque: Verificar `FOREIGN KEY` y `ON DELETE CASCADE`.
        - Módulo ASIR: GBD
    - **15. Seguridad:**
        - Estado: 🔴 Pendiente
        - Capa: Infraestructura
        - Responsable: Juan
        - Enfoque: Configurar **HTTPS**, *firewall*, SSH.
        - Módulo ASIR: SAD
    - **16. Estabilización y Pruebas:**
        - Estado: 🔴 Pendiente
        - Capa: Todo
        - Responsable: Todo el equipo
        - Enfoque: Pruebas de carga, depuración final.
        - Módulo ASIR: Calidad
    - **17. Documentación Final:**
        - Estado: 🔴 Pendiente
        - Capa: Documentación
        - Responsable: Todo el equipo
        - Enfoque: Redactar Memoria Final, Guion.
        - Módulo ASIR: Documentación
