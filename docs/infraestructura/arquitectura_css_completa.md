# 🎨 Arquitectura CSS: Diseño Modular y Experiencia Inmersiva (SIRA V15.0)

Este documento detalla el sistema de diseño de SIRA, integrando la estructura modular original con el nuevo motor de efectos climatológicos avanzados.

---

## 1. Filosofía de Diseño
SIRA utiliza una arquitectura **CSS Modular** basada en la política de "Nombres Espejo": cada componente lógico en PHP tiene su correspondiente archivo de estilos. Esto garantiza un mantenimiento sencillo y evita colisiones de estilos.

---

## 2. Módulos Base (Arquitectura Original)

### 🧱 Estructura y Variables
*   **variables.css**: Centraliza los tokens de diseño (colores, sombras, radios, espaciados).
*   **base.css**: Reset global, tipografía (Inter/Roboto) y estilos fundamentales del `body`.
*   **layout.css**: Define la cuadrícula principal y la navegación superior.
*   **style.css**: El "índice" maestro que importa todos los módulos en el orden jerárquico correcto.

### 🍱 Componentes de Interfaz
*   **buttons.css**: Define la jerarquía de botones (`.submit-btn`, `.btn-back`). Incluye estados hover con transformaciones suaves.
*   **confirmations.css**: Modales de confirmación con efecto `backdrop-filter: blur(8px)` y `z-index: 9999`.
*   **menus.css (Checkbox Hack)**: Menús de opciones (⋮) implementados mediante checkboxes ocultos y el selector `:checked`. **0% JavaScript.**
*   **search_bar.css**: Buscador con efectos de enfoque (*glow*) y transparencia moderna.
*   **header.css**: Gestión de títulos, subtítulos y navegación por migas de pan (*breadcrumbs*).

### 📄 Vistas Específicas
*   **view_clients.css**: Estilizado de tablas responsivas con badges de estado y filas interactivas.
*   **view_infrastructure.css**: Grid dinámico de tarjetas para parcelas e invernaderos con efectos de elevación.
*   **sensores.css**: Interfaz IoT con valores de gran formato, unidades dinámicas y el indicador de conexión con animación de latido (`sensorPulse`).

---

## 3. Sistema VFX: Clima Dinámico (Novedad V15.0)

Hemos implementado un motor visual inmersivo que transforma la interfaz según el clima simulado. Estos estilos se cargan dinámicamente mediante `weather_engine.php`.

### 🌪️ Módulos Climatológicos (`css/weather/`)
*   **cloudy.css**: Genera un banco de nubes denso mediante múltiples capas de gradientes radiales y animaciones de deriva.
*   **rain.css**: Sistema de partículas CSS para la lluvia y ráfagas de relámpagos que iluminan toda la interfaz mediante cambios súbitos de opacidad.
*   **heat.css**: Aplica un filtro de deformación térmica y un destello solar (`fx-ideal-sun`) con resplandor dinámico.
*   **snow.css / sequia.css**: Efectos de partículas de nieve y neblina de calor con distorsión por movimiento.

### 🛠️ Innovaciones Técnicas en VFX
1.  **Capa Posterior (Background Layers)**: Los efectos se renderizan en capas con `z-index: -1` para actuar como fondos vivos sin ocultar la información.
2.  **Transparencia de Interacción**: Uso crítico de `pointer-events: none !important` en todos los contenedores de clima. Esto permite que el usuario pueda interactuar con los botones y sensores a través de la lluvia o las nubes.
3.  **Filtros Globales**: Uso de selectores hermanos (`~ *`) para aplicar filtros de saturación y brillo a toda la aplicación cuando hay climas extremos (como el efecto de "Ola de Calor").

---

## 4. Estándares de Diseño y UX
*   **Radio Estándar (Radius-10)**: Todos los contenedores siguen la regla de 10px para una estética industrial uniforme.
*   **Optimización de Rendimiento**: Las animaciones utilizan `transform` y `opacity` para garantizar 60 FPS al ser procesadas por la GPU.
*   **Modo Oscuro Nativo**: El sistema está diseñado sobre una base Navy Profundo (`#0f172a`), optimizado para entornos de control agrícola.

---
**Documentación de Diseño SIRA**  
*Fusionando el trabajo de equipo con la tecnología de vanguardia.*
