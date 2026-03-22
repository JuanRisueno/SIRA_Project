# Justificación Técnica: Nginx como Proxy Inverso

En un trabajo de Fin de Grado (ASIR), la justificación de cada componente de red introducido en la capa de arquitectura debe tener fundamentos técnicos medibles. La API ya incluye internamente Uvicorn como servidor puro de Python. Esta es la justificación técnica de la necesidad de incluir **Nginx** por delante.

## 1. El Rol de Uvicorn vs Nginx

*   **Uvicorn (Servidor ASGI)**: Es extremadamente rápido procesando el código asíncrono puro de Python de nuestra aplicación `SIRA`. Sin embargo, es un servidor de "aplicación" y **no está diseñado para enfrentarse a Internet de forma directa.** No procesa bien peticiones lentas o corruptas ("Slowloris attacks") y no tiene un manejo eficiente de miles de conexiones inactivas.
*   **Nginx (Proxy Inverso)**: Es un servidor web maduro y robusto con años de consolidación. Está construido en C y procesa miles de conexiones concurrentes en la capa de red con un uso muy bajo de CPU/Memoria. Su función es recibir "el escrutinio de Internet", frenar peticiones incorrectas y solo encapsular y reenviar al entorno asíncrono (Uvicorn) las peticiones correctas.

## 2. Ventajas del Proxy Inverso en la topología de SIRA

1.  **Aislamiento y Seguridad (Blindaje de Puertos)**: El puerto de la base de datos (5432) y el de Uvicorn (8000) nunca se exponen ni contactan la tarjeta de red externa de la máquina. Permanecen completamente aislados en la red interna de Docker (`sira-network`). Solo Nginx actúa de portero bajo el puerto público común de HTTP (80).
2.  **Manejo de Cifrado y Descifrado / SSL Termination**: Añadir certificados cifrados *Let's Encrypt* en Uvicorn deteniendo el flujo normal de Python es errático. En SIRA, Nginx será el único encargado de procesar todo el tráfico cifrado TLS/HTTPS. Nginx lo descifra y envía el JSON plano mediante protocolo interno a la API, quintándole la carga computacional destructiva a Python y logrando mejor velocidad.
3.  **Flexibilidad de Balanceo y Estáticos (Escalado)**: De cara a futuras ampliaciones, si el sistema requiere entregar contenido web completo (aplicación React/Angular de UI/UX para el móvil), Nginx serviría esos archivos minificados *sin siquiera pasar por las reglas de routing de la API en Python*. Esto es la definición misma de desacoplamiento tecnológico eficiente.
