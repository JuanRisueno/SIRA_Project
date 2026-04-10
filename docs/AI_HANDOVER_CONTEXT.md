# 🤖 SIRA PROJECT - SYSTEM PROMPT & HANDOVER STATE
**IMPORTANTE PARA LA IA:** Lee este documento entero antes de tomar ninguna acción. Este es tu estado mental y contexto de la sesión transferido.

## 1. Contexto del Proyecto Educativo (TFG ASIR)
- **Nombre:** SIRA (Sistema Integral de Riego Automático).
- **Stack Tecnológico:** FastAPI (Python), PostgreSQL, PHP Puro + Vanilla CSS, Docker + Docker Compose, Nginx (Proxy inverso).
- **Restricción Tecnológica Front:** **PROHIBIDO USAR JAVASCRIPT** asíncrono para manipulación del DOM.
- **Naturaleza:** Proyecto fin de grado de ASIR. Se premia la arquitectura (modularidad web, robustez ante fallos, documentación SWAGGER) por encima de florituras.
- **Roles del Equipo:** Juan (El usuario activo: Arquitecto Backend/DevOps), Alfonso (SQL/Lógica/CSS), Jorge (Hardware/Frontend).

## 2. Estado de Seguridad & Autenticación (¡Aviso Crítico!)
- **JWT Activo pero MODO DEV:** El sistema emplea protecciones JWT reales (`auth.py` y `routers/jwt.py`), pero para agilizar el trabajo del equipo, se evalúa la **contraseña en texto plano** estricto sin encriptar.
- **Archivos `*_final.py`:** Contienen la seguridad criptográfica real (`bcrypt`). NO deben renombrarse ni activarse. Su activación queda diferida deliberadamente para la etapa futura "Fase V: Hardening" ya que la Base de datos en `20-data.sql` contiene contraseñas planas ("sol1234"). 

## 3. ¿Qué código se acaba de fabricar? (Estado Actualizada)
Pertenecemos a la Tarea "T.15: Jerarquía de Navegación del Dashboard".
- **Backend COMPLETADO [x]:** Esquemas Pydantic anidados y persistencia en `backend/app/schemas.py`.
- **Endpoint COMPLETADO [x]:** Ruta `GET /api/v1/clientes/me/jerarquia` en `backend/app/routers/datos_maestros.py`.
- **Frontend Modular [x]:** Creación de `frontend/includes/` (header/footer). Ahora todo el diseño es centralizado.
- **Dashboard Premium [x]:** Implementación de navegación pura PHP (Localidad -> Parcela -> Invernadero) con:
    - **Glassmorphism:** Estética moderna con desenfoques y gradientes.
    - **Saltos Inteligentes:** Si solo hay una localidad, el sistema salta automáticamente a parcelas.
    - **Breadcrumbs:** Migas de pan dinámicas en la barra superior.
    - **Resiliencia:** Manejo de errores gráfico si la API está caída.

## 4. PRÓXIMO PASO INMEDIATO
El sistema de navegación y visualización jerárquica está terminado y validado. Quedan pendientes las siguientes fases del proyecto ASIR:
1. **Fase IV: IoT y Sensores:** Integración de gráficas y datos en tiempo real en `sensores.php` manteniendo la nueva estética.
2. **Fase V: Hardening de Seguridad:** Activar los archivos `*_final.py` (Bcrypt) y limpiar la base de datos de contraseñas en texto plano.
3. **Optimización DevOps:** Revisión de los contenedores Docker y Nginx para el despliegue final.

**Firma:** Antigravity (JuanRisueno Edition). Listo para el siguiente reto. Pide al usuario qué fase priorizar ahora.
