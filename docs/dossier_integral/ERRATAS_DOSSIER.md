<style>
code {
    color: #000000 !important;
    background: transparent !important;
    font-weight: bold !important;
    font-family: inherit !important;
    font-size: 1em !important;
}
a {
    color: #000000 !important;
    text-decoration: none !important;
}
blockquote {
    border-left: 3px solid #000000 !important;
    color: #000000 !important;
    background: transparent !important;
}
</style>

## **ERRATAS — SIRA Dossier Integral v1.0**

---

> **Documento:** SIRA_DOSSIER_INTEGRAL.md (versión impresa, Mayo 2026)
> **Propósito:** Registro de discrepancias entre el dossier y la implementación real del sistema.

---

### **Errata 1 — Algoritmo de firma JWT**

| Campo | Valor |
|---|---|
| **Sección del dossier** | 9 "Protocolo Iron Fortress" — Diagrama Fase 1 |
| **Lo que dice el dossier** | `JWT (firma RS256)` |
| **Lo que hay implementado** | Algoritmo `HS256` (simétrico, clave secreta compartida) |
| **Archivo afectado** | `backend/app/auth.py` — línea 29 |
| **Código real** | `ALGORITHM = "HS256"` |

**Nota técnica:** RS256 es asimétrico (clave pública/privada). HS256 es simétrico (clave secreta compartida). Ambos son seguros; HS256 es la elección correcta para un despliegue single-server como SIRA. La discrepancia es únicamente de nomenclatura en el dossier.

---

### **Errata 2 — Nombre del archivo de seguridad del backend**

| Campo | Valor |
|---|---|
| **Sección del dossier** | 5.3 "Organización de Archivos del Backend" — tabla de archivos |
| **Lo que dice el dossier** | `security.py` — Lógica de generación y verificación de tokens JWT + Bcrypt |
| **Lo que hay implementado** | El archivo real se llama `auth.py` (no existe `security.py`) |
| **Archivo afectado** | `backend/app/auth.py` |

**Nota técnica:** El contenido y funcionalidad son exactamente los descritos en el dossier. Solo difiere el nombre del archivo.

---

<div style="page-break-before: always;"></div>

### **Errata 3 — Accesibilidad de `/docs` y `/redoc`**

| Campo | Valor |
|---|---|
| **Sección del dossier** | 5.4 "Documentación Automática (Swagger)" |
| **Lo que dice el dossier** | *"Estos endpoints están protegidos por Nginx para que solo sean accesibles desde la red interna o mediante VPN en producción."* |
| **Lo que hay implementado** | Los endpoints `/docs` y `/redoc` son **públicamente accesibles** sin restricción de IP ni autenticación adicional |
| **Archivo afectado** | `nginx/nginx.conf` — bloques `location ^~ /docs` y `location ^~ /redoc` |

**Nota técnica:** La ausencia de restricción es una decisión deliberada para el contexto del TFG, ya que permite demostrar la API interactiva durante la defensa. En un despliegue productivo real se añadiría `allow <IP>; deny all;` en la configuración de Nginx.

---

*Documento generado el 19 de Mayo de 2026 — Preparación defensa TFG ASIR*
