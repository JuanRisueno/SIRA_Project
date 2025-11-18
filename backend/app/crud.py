"""
=============================================================================
             Lógica CRUD (Create, Read, Update, Delete) - Tarea 8
=============================================================================

Propósito:
Este archivo contiene funciones reutilizables para interactuar con la base de datos.
Actúa como puente entre la API (Routers) y los datos (Models).

Separar esta lógica permite:
1.  Reutilizar código: La función 'get_cliente' sirve para el login y para el perfil.
2.  Testear fácil: Podemos probar estas funciones sin levantar el servidor web.
3.  Limpieza: Los endpoints en 'main.py' o 'routers/' quedan limpios y legibles.

Convención de Nombres:
- get_item(db, id): Obtener uno por ID.
- get_items(db, skip, limit): Obtener lista paginada.
- create_item(db, schema): Crear nuevo.
"""