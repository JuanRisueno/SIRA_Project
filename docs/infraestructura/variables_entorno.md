# 🔐 Diccionario de Variables de Entorno (.env)

El proyecto SIRA utiliza un archivo centralizado `.env` para gestionar la configuración de la infraestructura, seguridad y despliegue. Este archivo **nunca** debe versionarse en Git (está en `.gitignore`) por seguridad.

---

## 🏗️ Configuración de Despliegue (Nginx/Docker)
Estas variables definen cómo se expone el sistema al exterior.

| Variable | Descripción | Valor por Defecto |
| :--- | :--- | :--- |
| `SIRA_PORT` | Puerto público del servicio (Proxy Nginx). | `8085` |

---

## 🗄️ Infraestructura de Datos (PostgreSQL)
Valores inyectados en los contenedores `sira_db` (como parámetros de inicialización) y `sira_api` (para la cadena de conexión).

| Variable | Descripción | Valor Local |
| :--- | :--- | :--- |
| `DB_USER` | Usuario propietario de la base de datos. | `juanrisueno` |
| `DB_PASSWORD` | Contraseña de acceso a la instancia. | `juan1234` |
| `DB_NAME` | Nombre lógico de la base de datos SIRA. | `sira_db` |

> [!NOTE]
> En entorno Docker, la API construye automáticamente la URL: `postgresql://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}`.

---

## 💻 Configuración del Frontend (PHP)
Variables y constantes definidas en `frontend/includes/config.php` para la comunicación del cliente con el servidor de datos.

| Variable (PHP) | Descripción | Lógica de Detección |
| :--- | :--- | :--- |
| `SIRA_API_BASE` | URL raíz de la REST API de SIRA. | Detecta `/.dockerenv`. Si existe, usa `http://api:8000`. Si no, `http://localhost:8000`. |

---

## 🛡️ Seguridad y Autenticación (JWT)
Variables críticas para la firma y validación de sesiones de usuario. Si estas variables no se definen en el `.env`, el sistema utiliza valores de contingencia de desarrollo.

| Variable | Descripción | Recomendación |
| :--- | :--- | :--- |
| `JWT_SECRET_KEY` | Clave criptográfica para firmar los tokens. | Generar con `openssl rand -hex 32` |
| `ALGORITHM` | Algoritmo de firma (Default: `HS256`). | No modificar salvo requerimiento. |
| `ACCESS_TOKEN_EXPIRE_MINUTES` | Duración de la sesión (Default: `30`). | 30 a 1440 (24h). |

---

## 🌿 Integración Botánica (Futuras API)
Variables reservadas para la conexión con el Banco de Datos LKB (API Perenual).

| Variable | Descripción | Estado |
| :--- | :--- | :--- |
| `PERENUAL_API_KEY` | Token de acceso a la API externa botánica. | *En Planificación* |

---

> [!IMPORTANT]
> Para el despliegue en producción (ej. AWS/Vercel), asegúrate de cambiar la `JWT_SECRET_KEY` y las credenciales de la base de datos a valores de alta entropía.

**Documentación de Infraestructura SIRA**  
*Última actualización: 23 de Abril de 2026 (Sincronización con V15.0)*
