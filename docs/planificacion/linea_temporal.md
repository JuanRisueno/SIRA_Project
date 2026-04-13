### 📋 TIMELINE MAESTRO DEL PROYECTO SIRA (Versión 4.3 - Con Despliegue y Defensa)

**Equipo y Roles:**
* **Juan (Tú):** Arquitecto Backend, DevOps, Seguridad, API.
* **Alfonso:** Responsable de Datos (SQL), Documentación Técnica, Lógica de Negocio y Diseño CSS.
* **Jorge:** Supervisor, Diseño de Hardware, Presentaciones Ejecutivas y Responsable del Frontend.

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
* ✅ **T.11 Creación de usuarios administradores:** Implementación de roles Root/Admin y selector de gestión. **(Resp: Jorge)**
* ✅ **T.12 Routers Maestros:** Endpoints para Clientes y Parcelas. **(Resp: Juan)**
* ✅ **T.13 Documentación API:** Swagger UI y ReDoc configurados. **(Resp: Juan)**

🏁 **CHECKPOINT 1:** Backend Core y base_de_datos operativos. (✅ SUPERADO)

#### 🔵 FASE III: SEGURIDAD WEB Y FRONTEND (🚧 EN CURSO)
*Objetivo actual: Autenticación robusta y visualización de datos.*

* ✅ **T.14 Depuración Core:** Estabilización de Schemas Pydantic y resolución de Errores HTTP. **(Resp: Juan)**
* ✅ **T.15 Autenticación JWT:** Login seguro vía CIF y conexión correcta Frontend-Docker `api:8000`. **(Resp: Juan)**
* ✅ **T.16 Frontend Base (PHP):** Interfaz visual inicial con archivos básicos PHP. **(Resp: Jorge)**
* ✅ **T.17 Diseño UI:** Estilos base y plantillas CSS. **(Resp: Alfonso)**
* ✅ **T.18 Jerarquía y Frontend Pro:** Reestructuración de la API y el Dashboard en PHP puro para visualización dinámica de Localidad -> Parcela -> Invernadero. Aplicación de estándar modular e inyecciones de interfaz segura contra fallos. **(Resp: Juan)**

🏁 **CHECKPOINT 2:** Aplicación Web Segura y Visualización. (📅 OBJETIVO ACTUAL)

#### 🔴 FASE IV: INTEGRACIÓN IOT (FUTURO)
*Conexión con el mundo físico.*

* ⏳ **T.19 BBDD de Cultivos y Parámetros:** Implementación de base de datos híbrida (Manual + Perenual) y lógica de sincronización. **(Resp: Juan)**
* ⏳ **T.20 Adaptación del Frontend a la BBDD de Cultivos:** Sincronización de la interfaz con la nueva base de datos de cultivos y visualización de parámetros. **(Resp: Jorge/Alfonso)**
* ⏳ **T.21 Simulador IoT:** Generación de datos de sensores históricos. **(Resp: Jorge)**
* ⏳ **T.22 Raspberry Pi:** Scripts Python de envío de datos. **(Resp: Juan/Jorge)**
* ⏳ **T.23 Ingesta Masiva:** Endpoint optimizado para sensores. **(Resp: Juan)**
* ⏳ **T.24 Montaje Físico:** Sensores, cableado y carcasa 3D. **(Resp: Jorge)**

🏁 **CHECKPOINT 3:** Sistema Integrado Hardware + Software. (⏳ FUTURO)

#### 🟣 FASE V: HARDENING Y SEGURIDAD CRIPTOGRÁFICA (FUTURO)
*Activación de las defensas finales y preparación para protección de grado de producción.*

* 📅 **T.25 Refactorización de Contraseñas (SQL):** Encriptar las contraseñas en texto plano del archivo `20-data.sql` transformándolas en hashes legítimos mediante Bcrypt. **(Resp: Alfonso/Juan)**
* 📅 **T.26 Activación de Auth Final:** Reemplazar los controladores de seguridad temporales (`auth.py`, `jwt.py`) por su versión definitiva `_final.py`, habilitando la verificación y validación criptográfica en el login. **(Resp: Juan)**
* 📅 **T.27 Auditoría de Seguridad:** Pruebas de validación en los diferentes endpoints antes del empaquetado para la defensa del TFG. **(Resp: Juan)**

🏁 **CHECKPOINT 4:** Sistema Blindado y Listo para Defensa de TFG. (⏳ FUTURO)

#### 🟠 FASE VI: DESPLIEGUE EN PRODUCCIÓN Y DEFENSA (META FINAL)
*Puesta en marcha del producto en el mundo real simulando un entorno empresarial.*

* 📅 **T.28 Infraestructura Cloud VPS:** Contratación de un servidor virtual (Ionos, AWS, DigitalOcean) y fortificación inicial por SSH. **(Resp: Juan)**
* 📅 **T.29 Migración Docker:** Despliegue de todos los contenedores exactos que tenemos en local directamente en el VPS (Git Clone + Docker Compose). **(Resp: Juan)**
* 📅 **T.30 Nombres de Dominio y SSL:** Configuración de un dominio/subdominio e integración con "Certbot" (Let's Encrypt) para darle a la aplicación el candado verde de HTTPS. **(Resp: Juan)**
* 📅 **T.31 Ensayos Técnicos:** Simulaciones de presentación, pruebas de estrés o "apagado" de servicios en vivo para ensayar cómo respondería el sistema. **(Resp: Jorge/Alfonso/Juan)**

🏁 **CHECKPOINT 5:** ¡Proyecto Entregado y Defendido con Éxito! (🏆 META FINAL)
