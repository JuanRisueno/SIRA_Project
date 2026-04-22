# 🔐 Arquitectura de Seguridad: Ecosistema JWT & Bcrypt

Este documento detalla la implementación, justificación y mantenimiento del sistema de seguridad industrial de la plataforma **SIRA**.

---

## 📋 Resumen Ejecutivo
SIRA ha migrado de un modelo de autenticación basado en texto plano (Modo Desarrollo) a una arquitectura de **Seguridad de Grado Industrial**. Esta transición garantiza la protección de los datos de los clientes mediante hashing criptográfico y sesiones *stateless* de alta escalabilidad.

---

## 🎯 ¿Por qué esta tecnología?

### 1. Bcrypt (Hashing de Contraseñas)
A diferencia de otros algoritmos, **Bcrypt** está diseñado específicamente para proteger contraseñas:
*   **Salt Adaptativo**: Protege contra ataques de tablas arcoíris.
*   **Factor de Trabajo**: Es resistente a la computación masiva (fuerza bruta).
*   **Unidireccional**: Es físicamente imposible revertir un hash para obtener la contraseña original.

### 2. JSON Web Tokens (JWT)
Hemos elegido JWT para gestionar las sesiones por sus ventajas arquitectónicas:
*   **Independencia (Stateless)**: El servidor no necesita almacenar sesiones. Esto reduce la carga en la base de datos y memoria.
*   **Escalabilidad**: Permite que el ecosistema SIRA crezca sin cuellos de botella en la autenticación.
*   **Payload Enriquecido**: El token transporta de forma segura el `rol`, `id` y `nombre_empresa`, permitiendo una UI dinámica y permisos granulares.

---

## 🏗️ Implementación Técnica

### Flujo de Autenticación
1.  **Identificación**: El usuario provee su CIF (como username) y contraseña.
2.  **Verificación**: El backend valida el hash en la DB mediante `bcrypt.checkpw()`.
3.  **Emisión**: Se genera un token firmado con una clave secreta (`SECRET_KEY`) y algoritmo `HS256`.
4.  **Autorización**: El Frontend adjunta el token en el Header `Authorization: Bearer <token>` para todas las peticiones protegidas.

### Estructura de Datos (Claims)
```json
{
  "sub": "B04123456",  // Identificador único (CIF)
  "rol": "admin",      // Control de acceso (RBAC)
  "id": 1,             // Referencia interna
  "exp": 1713784800    // Expiración de seguridad (30 min)
}
```

---

## 🛡️ Persistencia y "Zero-Config"

Para asegurar que SIRA sea seguro desde el primer despliegue, hemos implementado una estrategia de **Seguridad por Defecto**:

> [!IMPORTANT]
> **Semilla Segura (Seed Data)**: El archivo `20-data.sql` ha sido actualizado. Todos los usuarios de prueba (root, admin, etc.) se insertan directamente como hashes Bcrypt. El sistema es seguro desde el primer `docker-compose up`.

### Gestión de Reinicios
*   **Volúmenes de Datos**: Gracias al volumen `postgres_data`, los hashes persisten aunque se reinicien o borren los contenedores.
*   **Persistencia de Clave**: La clave de firma se gestiona mediante variables de entorno en el archivo `.env`.

---

## 🚑 Mantenimiento y Recuperación

Si en el futuro se importan datos antiguos en texto plano, SIRA incluye una herramienta de saneamiento automático:

**Procedimiento de Migración Manual:**
```bash
docker exec sira_api python3 /app/scripts/migrate_passwords.py
```
Este script detectará cualquier contraseña vulnerable y la elevará al estándar Bcrypt de forma atómica.

---

> [!NOTE]
> Esta arquitectura cumple con los estándares de seguridad requeridos para proyectos de nivel TFG e industrial, asegurando la integridad de la información del sector agrícola.

**Documentación de Infraestructura SIRA**  
*Última actualización: 22 de Abril de 2026*
