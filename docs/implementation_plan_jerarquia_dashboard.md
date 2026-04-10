# Mejora de la Jerarquía Visual y Calidad Profesional (TFG ASIR)

Este documento detalla los pasos para transformar el proyecto en una entrega altamente profesional para un tribunal de ASIR (Administración de Sistemas Informáticos en Red), manteniendo la simplicidad operativa (basada en PHP puro y contenedores) que caracteriza a un buen administrador de sistemas, pero elevando enormemente la calidad percibida del Software.

## 1. Nueva Jerarquía de Navegación (Localidad -> Parcela -> Invernadero)

El dashboard actual agrupa todos los invernaderos provocando confusión si hay múltiples localidades. Se estructurará una navegación guiada **exclusivamente mediante PHP y parámetros URL (`$_GET`)**.

### Backend (FastAPI)
- **Nuevos Schemas Pydantic**: `LocalidadJerarquia` -> `ParcelaJerarquia` -> `InvernaderoJerarquia`.
- **Nuevo Endpoint (`GET /api/v1/clientes/me/jerarquia`)**: Devolverá el árbol estructurado del usuario actual. Incluirá metadatos útiles como el conteo de parcelas e invernaderos por localidad.

### Frontend (PHP Puro)
- **Vista Dinámica controlada por PHP**: 
  - *Sin parámetros*: Muestra Localidades (con insignias/badges que indiquen "XX Parcelas").
  - `?localidad=X`: Muestra Parcelas de esa localidad.
  - `?parcela=Y`: Muestra Invernaderos de esa parcela.
- **Saltos Inteligentes**: Si un usuario solo tiene 1 localidad, PHP se salta esa vista directamente y carga las parcelas (o los invernaderos si solo hay 1).
- **Migas de pan (Breadcrumbs)**: Ej: "Mis Cultivos > El Ejido > Parcela Los Llanos", permitiendo retroceder sin utilizar el botón "Atrás" del navegador.

---

## 2. Mejoras Profesionales Adicionales Propuestas

Para garantizar que el proyecto deslumbre al tribunal como una aplicación "empresarial" sin requerir librerías complejas como React o JS, añadiremos las siguientes mejoras arquitectónicas y visuales:

### 2.1 Reestructuración Arquitectónica del Frontend (Includes PHP)
- **Problema actual:** El código del menú de navegación (`<nav>`), el `<header>` y los estilos están copiados y pegados en todos los archivos (`index.php`, `dashboard.php`, `sensores.php`).
- **Solución Profesional:** Crear una carpeta `includes/` con archivos `header.php` y `footer.php`. Todas las pantallas cargarán estos módulos centrales. Esto demuestra ante el tribunal principios de **código limpio, modularidad y fácil mantenimiento**.

### 2.2 Modernización Estética (Vanilla CSS Premium)
- **Lavado de Cara Visual:** Implementar un diseño CSS moderno enfocado en la usabilidad y la estética (fuentes como *Inter* o *Roboto*, sombras suaves "glassmorphism", transiciones limpias y bordes redondeados).
- Se diseñará una paleta de colores agronómica profesional (Verdes esmeralda, blancos crudos y grises sutiles).
- Se añadirán efectos `hover` en las tarjetas (que se levanten ligeramente al pasar el ratón por encima) para dar la sensación de una web interactiva y dinámica, aunque sea PHP estático.

### 2.3 Manejo de Errores a Prueba de Bombas (Resiliencia ASIR)
- Los tribunales de Sistemas suelen probar la **tolerancia a fallos** (ej: apagan el contenedor de la API o la Base de Datos para ver qué pasa).
- En lugar de que el PHP escupa un error crudo de cURL o la página se quede en blanco, el PHP detectará la caída del backend y mostrará una **página de error amistosa**: *"Servicio temporalmente no disponible. El equipo de sistemas está trabajando en ello"*. 

### 2.4 Documentación Swagger Enriquecida (Backend API)
- Aprovechando que FastAPI auto-genera el Swagger, añadiremos una descripción profesional al inicio de la API, un título personalizado, información de contacto de los desarrolladores (vosotros) e iconos en las etiquetas (`Tags`). Esto da una impresión espectacular a nivel de producto terminado.

## Open Questions

> [!IMPORTANT]
> El plan ha sido ampliado con mejoras sustanciales enfocadas en garantizar la mejor nota posible frente a un tribunal de ASIR, asegurando al mismo tiempo que el mantenimiento siga siendo súper sencillo para vosotros (solo PHP y HTML).
> 
> ¿Tienes alguna de duda sobre alguna de las mejoras propuestas o podemos considerar el plan listo para que proceda a ejecutarlo sobre el código real?

## Verification Plan

1. **Prueba de Jerarquía**: Al iniciar sesión con un cliente multi-parcela, se navegará por niveles hasta llegar al invernadero utilizando los nuevos breadcrumbs (migas de pan).
2. **Prueba Visual Modular**: Se verificará que el código de múltiples páginas requiera a `header.php`, reduciendo el tamaño total del código y estandarizando la apariencia moderna.
3. **Prueba de Resiliencia (Simulada)**: Validaremos que si la API se apaga, el Frontend muestra un error gráfico amistoso.
