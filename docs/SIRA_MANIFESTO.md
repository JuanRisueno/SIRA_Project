# 📜 MANIFIESTO SIRA — Guía de Desarrollo y Estándares

Este documento define la esencia técnica y visual del proyecto **SIRA (Sistema Integral de Riego Automático)**. Debe servir como referencia absoluta para mantener la coherencia en el desarrollo del Backend y Frontend.

---

## 1. 🎨 FILOSOFÍA VISUAL (Frontend - "Premium Cinematic UI")

SIRA no es un software convencional; es una experiencia inmersiva industrial.

### 🔳 Geometría "Standard-10"
- **Contenedores y Tarjetas**: `border-radius: 10px` (variable `--radius-container`).
- **Botones e Inputs**: `border-radius: 4px` o `pill` (redondeado total) según contexto.
- **Espaciado y Tipografía**: Uso estricto de variables `--spacing-*` y `--font-size-*` para mantener el ritmo visual. **Prohibido el uso de valores hardcoded (ej: 20px) en los módulos CSS.**
- **Sistema de Tokens**: Todo valor estético (color, sombra, radio) debe nacer de `modules/variables.css`. Si no existe, se crea el token allí primero.

### 🌑 Paleta de Colores y Texturas
- **Tema Oscuro (Primario)**: Fondo Navy profundo (`#0f172a`), tarjetas translúcidas con `backdrop-filter: blur()`.
- **Destellos (Highlights)**: Verde esmeralda (`#10b981`) para acciones positivas y Azul eléctrico para sistemas globales.
- **Glassmorphism**: Opacidad de headers al **0.95** con blur de `12px`. El contenido debe "morir" visualmente al pasar bajo el header.

### 🎥 Factor WOW & Micro-animaciones
- Toda interacción (hover, click, carga) debe tener una transición suave (`transition: 0.3s cubic-bezier`).
- Los elementos deben elevarse o iluminarse sutilmente al interactuar con ellos.

---

## 2. 🏛️ ARQUITECTURA TÉCNICA

### ⚙️ Backend (FastAPI / Python)
- **Modelos**: Uso de SQLAlchemy para la base de datos relacional y Pydantic para la validación de esquemas API.
- **Persistencia Híbrida**: 
    - Datos estructurales en DB.
    - Configuraciones dinámicas (Jornadas, Sensores) en archivos **JSON por cliente/invernadero** para máxima flexibilidad.
- **Seguridad**: Autenticación vía JWT. Los roles (`root`, `admin`, `cliente`) determinan estrictamente el acceso a los datos.
- **Variables de Entorno (.env)**: Es obligatorio el uso de archivos `.env` para gestionar secretos, claves JWT, credenciales de DB y la URL base de la API. Jamás se debe subir información sensible o URLs estáticas al control de versiones.

### 🖥️ Frontend (PHP / CSS Puro)
- **Cero CSS en PHP**: Está terminantemente prohibido escribir estilos inline o etiquetas `<style>` dentro de archivos `.php`. **Sin excepciones.**
- **Independencia de Capas**: El PHP gestiona la estructura y los datos; el CSS (en archivos externos `.css`) gestiona la estética. Mezclarlos se considera un fallo grave de arquitectura.
- **Estructura Espejo**: Cada archivo PHP debe tener su correspondiente módulo CSS en `frontend/css/modules/` (ej: `view_jornadas.php` -> `view_jornadas.css`).
- **Lógica Zero-JS**: Priorizar el uso de lógica PHP para estados y CSS para animaciones. El JavaScript se reserva exclusivamente para interacciones en tiempo real o validaciones complejas de UI que no puedan resolverse con CSS.

---

## 3. 🧬 REGLAS DE ORO DE DESARROLLO

### 🔗 Herencia Jerárquica (Maestro-Esclavo)
- El sistema admite una **Política Global** (Maestro) por cliente.
- Al configurar el Maestro, se debe ofrecer (o forzar) la sincronización de todas las naves.
- Cada nave puede "romper" la herencia mediante un switch de **Heredar Global**, volviéndose independiente.

### 👻 Gestión de Ciclo de Vida (Archivado)
- **Nunca Borrar**: Los elementos (Parcelas, Invernaderos) no se eliminan físicamente de la base de datos.
- **Estado 'Activo'**: Se usa un flag para ocultarlos. Esto permite la restauración de activos y mantiene la integridad del historial de sensores.

### 📐 Profesionalismo y No-Repetición (DRY - Don't Repeat Yourself)
- **Prohibida la Redundancia**: Si un bloque de código o un estilo se usa en más de un sitio, **debe** ser extraído a un componente modular (`includes/`, `componentes/`) o a una variable global.
- **Componentes Atómicos**: Componentes comunes (Header, Navbar, Footer, SearchBar) deben estar en `dashboard/componentes/` e incluirse mediante `require_once`.
- **Contexto Persistente**: No se debe duplicar lógica de redirección; se usan parámetros `GET` (como `from` y `cliente_id`) para mantener el hilo de navegación del usuario.

### 📜 Ética de Programación "Industrial"
- **Código Documentado**: Cada sección clave debe llevar comentarios que expliquen el "por qué", no solo el "qué".
- **Limpieza Absoluta**: No se dejan fragmentos de código comentados, ni variables "basura" o `console.log` en el código final. 
- **Escalabilidad**: Cada ajuste debe pensarse para que funcione igual de bien con 1 invernadero que con 10.000.

---

## 4. 📂 ORGANIZACIÓN DE ARCHIVOS

- `backend/app/routers/`: Lógica de API por módulos.
- `frontend/dashboard/vistas/`: El "corazón" visual de la aplicación.
- `frontend/formularios/`: Formularios premium con validaciones compartidas.
- `frontend/css/modules/`: El alma estética, organizada por componentes.

---
