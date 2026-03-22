### 📋 TIMELINE MAESTRO DEL PROYECTO SIRA (Versión 4.1 - Con Checkpoints)

**Equipo y Roles:**
* **Juan (Tú):** Arquitecto Backend, DevOps, Seguridad y API.
* **Alfonso:** Responsable de Datos (SQL), Documentación Técnica y Lógica de Negocio.
* **Jorge:** Supervisor, Diseño de Hardware y Presentaciones Ejecutivas.

---

#### 🟢 FASE I: INFRAESTRUCTURA Y BASE DE DATOS (✅ COMPLETADA)
*Cimientos del sistema: Virtualización, Docker y Diseño Relacional.*

* ✅ **T.0 Arquitectura Docker:** `docker-compose.yml` (API + DB + Nginx). **(Resp: Juan)**
* ✅ **T.1 Entorno y Git:** Configuración VM Ubuntu + Repo GitHub (`main`). **(Resp: Juan)**
* ✅ **T.2 Modelo de Datos:** Diagrama ER y reglas de negocio. **(Resp: Jorge)**
* ✅ **T.3 Schema SQL:** Script `10-schema.sql` (Tablas y Relaciones). **(Resp: Alfonso)**
* ✅ **T.4 Estabilidad:** Implementación de Healthchecks en contenedores. **(Resp: Juan)**
* ✅ **T.4.5 Documentación DevOps:** Guías de Backups, VPS, Variables y Arquitectura de Nginx añadidas a `docs/`. **(Resp: Juan)**

#### 🟢 FASE II: BACKEND CORE Y LÓGICA (✅ COMPLETADA)
*API funcional, seguridad básica y carga de datos reales.*

* ✅ **T.5 Modelos ORM:** Mapeo de tablas a objetos Python (`models.py`). **(Resp: Juan)**
* ✅ **T.6 Validaciones:** Schemas Pydantic para entrada de datos. **(Resp: Juan)**
* ✅ **T.7 Lógica CRUD:** Funciones de lectura/escritura en BBDD. **(Resp: Juan)**
* ✅ **T.8 Seguridad Base:** Hashing de contraseñas con `bcrypt`. **(Resp: Juan)**
* ✅ **T.9 Hito Administrativo:** Presentación de Reporte Mensual Fase I-II. **(Resp: Jorge)**
* ✅ **T.10 Datos Semilla:** Script `20-data.sql` (5 Clientes + Invernaderos). **(Resp: Alfonso)**
* ✅ **T.11 Routers Maestros:** Endpoints para Clientes y Parcelas. **(Resp: Juan)**
* ✅ **T.12 Documentación API:** Swagger UI y ReDoc configurados. **(Resp: Juan)**

🏁 **CHECKPOINT 1:** Backend Core y base_de_datos operativos. (✅ SUPERADO)

#### 🔵 FASE III: SEGURIDAD WEB Y FRONTEND (🚧 EN CURSO)
*Objetivo actual: Autenticación robusta y visualización de datos.*

* 🔄 **T.12.5 Depuración Core:** Estabilización de Schemas Pydantic y resolución de Errores 500 en respuestas HTTP. **(Resp: Juan)** 👈 *[Misión Inmediata]*
* 🔄 **T.13 Autenticación JWT:** Login seguro vía CIF y protección de rutas. **(Resp: Juan)**
* 📅 **T.14 Middleware:** Gestión de permisos y roles en la API. **(Resp: Juan)**
* 📅 **T.15 Frontend Base:** Interfaz visual con Streamlit. **(Resp: Juan)**
* 📅 **T.16 Dashboard:** Visualización de parcelas del cliente. **(Resp: Alfonso)**
* 📅 **T.17 Simulador IoT:** Generación de datos de sensores históricos. **(Resp: Alfonso)**

🏁 **CHECKPOINT 2:** Aplicación Web Segura y Visualización. (📅 OBJETIVO ACTUAL)

#### 🔴 FASE IV: INTEGRACIÓN IOT (FUTURO)
*Conexión con el mundo físico.*

* ⏳ **T.18 Raspberry Pi:** Scripts Python de envío de datos. **(Resp: Jorge)**
* ⏳ **T.19 Ingesta Masiva:** Endpoint optimizado para sensores. **(Resp: Juan)**
* ⏳ **T.20 Montaje Físico:** Sensores, cableado y carcasa 3D. **(Resp: Jorge)**
* ⏳ **T.21 Integración Externa:** Conexión botánica con Trefle.io (Investigación). **(Resp: Alfonso/Juan)**

🏁 **CHECKPOINT 3:** Sistema Integrado Hardware + Software. (⏳ FUTURO)
