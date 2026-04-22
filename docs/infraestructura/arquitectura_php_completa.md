# 🐘 Arquitectura PHP: Lógica de Gestión y Seguridad (SIRA V15.0)

Este documento describe la estructura lógica del frontend de SIRA, detallando cómo se integra con la API y gestiona la experiencia del usuario.

---

## 1. Núcleo de la Aplicación (Arquitectura Original)

### 🗺️ Gestión de Infraestructura
*   **view_localidades.php**: Punto de entrada jerárquico. Agrupa las posesiones del cliente por municipio y provincia.
*   **view_infrastructure.php**: Motor de renderizado dinámico. Según la navegación, muestra Localidades, Parcelas o Invernaderos, manteniendo el contexto mediante variables de sesión y GET.
*   **Gating System (Validación Geográfica)**: Implementado en los formularios de alta (`add_parcela.php`, `add_localidad.php`). Fuerza al usuario a validar el Código Postal contra una API externa antes de permitir el guardado, garantizando la integridad de los datos.

### 📝 Módulos de Gestión (CRUD)
*   **Alta/Edición de Usuarios**: Gestión de clientes y administradores con validación de roles en el servidor.
*   **Gestión de Parcelas e Invernaderos**: Formularios inteligentes que detectan el rol del usuario para bloquear o permitir la edición de campos críticos (como dimensiones de naves).
*   **Modales sin JS**: Uso de lógica PHP para procesar confirmaciones y mostrar overlays de éxito/error, manteniendo el sistema ligero y compatible.

---

## 2. Seguridad y Autenticación (Novedad V15.0)

Hemos sustituido el sistema de sesiones tradicional por una implementación de **Seguridad de Grado Industrial**.

### 🔑 Autenticación JWT (JSON Web Token)
*   **Desacoplamiento Total**: El frontend ya no maneja contraseñas tras el login. Se comunica con la API mediante un Token Bearer.
*   **Payload Decodificado**: En cada carga de página (`header.php`), el sistema extrae el `rol`, `id_cliente` y `nombre_empresa` directamente del token (Base64). Esto elimina consultas redundantes a la base de datos y mejora la velocidad de carga.
*   **Validación de Sesión**: Si el token expira o es inválido, PHP redirige automáticamente al `index.php`.

### 🛡️ Integración con Bcrypt
*   Toda la lógica de gestión de usuarios (`add_user.php`, `edit_user.php`) ha sido actualizada para delegar el hasheo a la API. El frontend nunca envía ni recibe contraseñas en texto plano, solo flujos de datos protegidos.

---

## 3. Motor de Experiencia IoT y Simulación

### 🌤️ Weather Engine (`weather_engine.php`)
Es el componente encargado de la "Inmersión SIRA".
*   **Detección de Escenario**: Lee el estado de la simulación desde la base de datos o sesión.
*   **Inyección de Recursos**: Decide qué archivos CSS de clima cargar y qué elementos del DOM (nubes, lluvia, destellos) inyectar en la página.
*   **Persistencia de Simulación**: Mantiene los efectos visuales activos de forma coherente mientras el usuario navega por diferentes invernaderos.

### 📊 Panel de Sensores (`sensores.php`)
*   **Consumo de Telemetría**: Realiza llamadas a los endpoints de sensores de la API.
*   **Lógica de Actuadores**: Gestiona el envío de comandos (Encender/Apagar dispositivos) mediante peticiones autenticadas con el token del usuario actual.

---

## 4. Gestión de Estado y Visibilidad
*   **Sistema de Soft-Delete**: Implementado a través del campo `activa`. El dashboard permite alternar entre "Vista Activa" y "Vista Archivada", permitiendo la recuperación de elementos sin pérdida de datos históricos.
*   **Redirección de Seguridad**: Un sistema de control de acceso en cada script de `management/` asegura que un cliente nunca pueda acceder a funciones reservadas para administradores.

---
**Documentación de Lógica SIRA**  
*Un sistema robusto, escalable y preparado para entornos industriales.*
