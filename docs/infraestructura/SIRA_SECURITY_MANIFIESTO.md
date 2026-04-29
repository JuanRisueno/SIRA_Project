# 🛡️ Manifiesto Maestro de Seguridad e Infraestructura SIRA — "Iron Fortress"

Este documento constituye la **Fuente Única de Verdad** técnica del proyecto **SIRA (Sistema Integral de Riego Automático)**. Consolida la arquitectura de red, la gestión de identidad, la protección de datos y la filosofía defensiva en un ecosistema robusto denominado **"Iron Fortress"**.

---

## 1. 🏛️ Arquitectura de Red y Perímetro

### Proxy Inverso (Nginx) y Blindaje
SIRA utiliza **Nginx** como el único punto de entrada al sistema (`sira_nginx`).
- **Aislamiento de Puertos**: Los servicios de Backend (FastAPI) y Base de Datos (PostgreSQL) operan en una red privada interna de Docker. Es físicamente imposible acceder a la base de datos (puerto 5432) o a la API (puerto 8000) directamente desde el exterior.
- **Seguridad Perimetral**: Nginx centraliza el tráfico, gestiona las cabeceras de seguridad y actúa como escudo contra escaneos de puertos no autorizados.
- **Aislamiento en Producción (AWS)**: En despliegues AWS EC2, se debe mapear Nginx al **puerto 80** (estándar HTTP), ya que AWS suele restringir puertos personalizados como el `8085` por defecto en sus Security Groups. El puerto `22` se reserva para SSH administrativo.

### Infraestructura Dockerizada
- **Volúmenes Nombrados**: Se separan los datos persistentes (`postgres_data`, `sira_security_history`) del código fuente.
- **Higiene de Git**: El archivo `.env` y las carpetas de datos de seguridad están estrictamente excluidos mediante `.gitignore` para evitar fugas de secretos en el repositorio.

---

## 2. 🔑 Gestión de Identidad y Acceso (IAM)

### Autenticación JWT (Stateless)
- **Token por Sesión**: La comunicación utiliza tokens JWT firmados digitalmente.
- **Persistencia Segura**: Tras el login, el token se almacena exclusivamente en la **sesión del servidor PHP**, mitigando el secuestro de sesión (Session Hijacking) y ataques XSS que intenten leer el LocalStorage.
- **Claims de Seguridad**: El token transporta de forma segura el `rol` del usuario (`root` > `admin` > `cliente`), asegurando que el acceso a los datos sea estrictamente jerárquico.

### Criptografía Bcrypt
- **Hashing de Grado Industrial**: Cada contraseña se procesa con **Bcrypt (12 rondas)**.
- **Salado Dinámico**: La generación de una "sal" aleatoria por usuario impide ataques de tablas arcoíris.
- **Inmutabilidad**: Incluso en la inicialización del sistema (`20-data.sql`), solo se manejan hashes. SIRA es un sistema **"Cero Texto Plano"**.

---

## 3. 🏰 El Búnker "Iron Fortress" (Historial y Rotación)

SIRA implementa una capa de persistencia defensiva descentralizada para proteger contra la fatiga de credenciales y ataques de diccionario:

- **Búnker JSON**: El historial de seguridad reside en archivos JSON aislados en el volumen Docker persistente `/app/data/security/history/`. 
- **Historial de Reuso**: El sistema computa las **últimas 5 contraseñas** utilizadas. No se permite la reactivación de ninguna clave presente en este registro.
- **Rotación de 90 Días**: Las credenciales caducan automáticamente cada trimestre. Al superar este plazo, el sistema activa un flag en el JWT que fuerza al usuario a renovar su clave antes de continuar.
- **Privacidad Local**: Esta carpeta nunca se sincroniza con GitHub, garantizando la soberanía de los datos de seguridad.

### 3.2 Exclusividad de Mando (Single Session Enforcement)
Para garantizar la integridad operativa de los invernaderos, SIRA implementa una política de **Sesión Única Activa**:
- **Rotación de SID**: Cada login genera un `session_id` (UUID) único que se almacena en la base de datos y se inyecta en el JWT.
- **Invalidación Instantánea**: Al iniciar sesión en un nuevo dispositivo, el `session_id` en la base de datos cambia, lo que provoca que cualquier token anterior sea rechazado inmediatamente por el Portero (Auth) con un error `401 SESSION_INVALIDATED`.
- **Prevención de Conflictos**: Esta medida evita que múltiples operadores envíen órdenes contradictorias a los actuadores desde diferentes terminales simultáneamente.
  
### 3.3 Control de Inactividad (Timeout Inteligente)
Para prevenir el acceso no autorizado en terminales desatendidos, SIRA implementa un control de inactividad basado en base de datos:
- **Expiración de JWT Extendida**: El token JWT en sí tiene una vida útil prolongada (12 horas) para evitar la expiración abrupta durante jornadas de trabajo intensivas.
- **Huella Digital Constante**: En cada petición, el sistema actualiza el campo `ultima_actividad` del usuario en la base de datos.
- **Cancelación Activa**: Si el portero detecta que han transcurrido más de **30 minutos** desde la última petición del usuario, rechaza la autorización con el error `401 SESSION_TIMEOUT`, forzando un nuevo login.

---

## 4. 🛡️ Blindaje de Interfaz y Lógica de Presentación

### Filosofía "Zero-JS" con Excepción Pragmática
SIRA minimiza el uso de JavaScript para reducir la superficie de ataque, pero permite un **1% de JS** por razones de seguridad crítica:
- **Validación en Tiempo Real**: Se usa JavaScript minimalista (`sira-security-ui.js`) para proporcionar feedback instantáneo sobre la complejidad de la contraseña (ticks verdes), asegurando robustez antes de que el dato viaje a la API.
- **Inmunidad XSS**: Al no delegar la lógica de negocio en scripts del cliente, se eliminan vectores comunes de inyección.
- **Escapado de Salida**: PHP aplica `htmlspecialchars()` en todos los puntos de renderizado de datos dinámicos.

### Patrones Anti-Autofill
Para evitar la interferencia de gestores de contraseñas y preservar la estética del Dashboard:
- **Dummy Inputs**: Uso de campos invisibles "señuelo" para capturar el autocompletado basura de los navegadores.
- **Renderizado Estático**: Las contraseñas de ejemplo o campos de visualización se muestran como bloques de texto (`div`), impidiendo que el navegador lo identifique como credenciales filtrables.

---

## 5. 📡 Seguridad en el Ecosistema IoT

### Token de Inyección de Telemetría
Para evitar la **Inyección de Datos Fantasma**, el sistema de recepción de datos IoT requiere un **IoT-Token privado** en la cabecera. Cualquier intento de enviar telemetría sin este secreto es bloqueado con un error `401 Unauthorized`.

### Protocolos Failsafe (Seguridad Estructural)
La lógica de control prioriza la seguridad física del invernadero sobre la optimización climática:
- **Prioridad Crítica**: El cierre de ventanas por viento fuerte (>45km/h) o lluvia anula cualquier comando de control climático para evitar daños estructurales.
- **Acción Coordinada**: Ante picos de calor con viento fuerte, se bloquean las ventanas y se activan los extractores al 100%.

---

## 🚩 Matriz de Riesgos y Mitigación (Consolidada)

| Riesgo Técnico | Impacto | Defensa SIRA (Iron Fortress) |
| :--- | :--- | :--- |
| **Inyección SQL** | Crítico | Uso de SQLAlchemy (ORM) con parámetros tipados y arquitectura desacoplada. |
| **Fuerza Bruta** | Alto | Complejidad obligatoria (10 chars), Bcrypt 12 rondas y bloqueo de reuso. |
| **Data Leak (Git)** | Crítico | Exclusión masiva de `.env` y búnkeres JSON en `.gitignore`. |
| **Session Hijacking** | Alto | JWT con expiración corta (30 min) y almacenamiento en sesión de servidor. |
| **Sabotaje IoT** | Alto | IoT-Token privado para la entrada de telemetría. |
| **Cross-Site Scripting** | Medio | Filosofía Zero-JS y Escapado de Salida (`htmlspecialchars`). |
| **Timezone Hell** | Bajo | Inyección de `TZ=Europe/Madrid` en Docker para coherencia de logs. |

---

### 🛡️ Nota sobre Cifrado de Transporte (HTTPS)
Aunque para la fase de prototipo y demostración académica SIRA opera bajo protocolo **HTTP** (Puerto 80), en un despliegue comercial se requiere la activación de **HTTPS**.
- **Requisito**: Vinculación de un nombre de dominio (FQDN).
- **Implementación**: Uso de **Certbot** y **Let's Encrypt** para la generación de certificados SSL/TLS automáticos, configurando Nginx para escuchar en el puerto `443` con cifrado de punto a punto.

---

> [!IMPORTANT]
> Este manifiesto consolida y sustituye a todos los archivos técnicos de infraestructura y seguridad previos. Es la referencia oficial para el mantenimiento y auditoría del sistema SIRA.

**Documentación Oficial SIRA**  
*Última actualización: 23 de Abril de 2026 (Versión 17.0 — Consolidación Maestra)*
