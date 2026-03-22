# Diccionario de Variables de Entorno (.env)

El proyecto SIRA requiere de una serie de variables confidenciales y de configuración. Por seguridad, estos valores **nunca** deben subirse a `git` y deben residir localmente en un archivo `.env` o en los variables "Secrets" del panel de control del servidor en la nube.

Esta es la recopilación exhaustiva de las variables de entorno necesarias para el correcto funcionamiento del ecosistema.

## Variables de la base_de_datos (PostgreSQL)
Estas variables se inyectan en `sira_db` al arrancar el contenedor y también en `sira_api` para construir la cadena en Python.

| Variable | Descripción | Valor Local Típico |
| :--- | :--- | :--- |
| `DB_USER` | Nombre del usuario administrador de la BBDD. | `usuario` |
| `DB_PASSWORD` | Contraseña del administrador del postgres. | `contraseña` |
| `DB_NAME` | Nombre de la base de datos instalada. | `sira_db` |

_Nota: Si cambian, se actualizará dinámicamente el `DATABASE_URL` del contenedor Python gracias a la interpolación en `docker-compose.yml` (`postgresql://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}`)._

## Variables de Seguridad de la API (JWT)
Variables requeridas cuando la autenticación se pase de "Modo Dev" a Producción Segura, tal como se define en `pasos_para_produccion.txt`.

| Variable | Descripción | Ejemplo / Medio de Obtención |
| :--- | :--- | :--- |
| `SECRET_KEY` | Clave secreta larga criptográfica usada para firmar los JSON Web Tokens, validando la identidad. | Generar nueva con: `openssl rand -hex 32` |
| `ALGORITHM` | Algoritmo de encriptación y firma del Token JWT. | `HS256` |
| `ACCESS_TOKEN_EXPIRE_MINUTES` | Tiempo de vida del Token proporcionado al Front/Usuario en el momento de Log-In. | `30` (o `1440` para un día entero) |

## Variables de Integración a Futuro (Third-Party)
Según la justificación de uso en la planificación del proyecto, SIRA se conectará con Trefle.io para la consulta de datos botánicos reales.

| Variable | Descripción | Ejemplo |
| :--- | :--- | :--- |
| `TREFLE_API_KEY` | Token estático otorgado por Trefle.io requerido para realizar peticiones fiables a su API. | `tz738KjA09...` |
