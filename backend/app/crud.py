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
# --- 1. IMPORTACIONES DE TERCEROS (Librerías externas) ---

# Session: No es para ejecutar código, es para "Type Hinting" (Pistas de Tipo).
# Sirve para que VS Code sepa que la variable 'db' es una conexión a BBDD
# y te autocomplete métodos como .add(), .commit() o .query().
from sqlalchemy.orm import Session

# CryptContext: Es la herramienta de seguridad.
# Nos permite gestionar el encriptado (hashing) de contraseñas.
# Nunca guardamos texto plano en la BBDD por seguridad (Regla ASIR/RGPD).
from passlib.context import CryptContext

# --- 2. IMPORTACIONES LOCALES (Tu propio código) ---

# Importamos los 'models' (Tablas SQL) para saber DÓNDE guardar los datos.
# Importamos los 'schemas' (Validación Pydantic) para saber QUÉ datos recibimos.
# El punto (.) significa "busca en esta misma carpeta".
from . import models, schemas

# --- 3. CONFIGURACIÓN DE SEGURIDAD ---

# Configuramos el contexto de criptografía.
# schemes=["bcrypt"]: Elegimos el algoritmo 'bcrypt' porque es el estándar actual.
# Es lento a propósito para dificultar ataques de fuerza bruta.
# deprecated="auto": Si en el futuro bcrypt se vuelve obsoleto, esta librería
# gestionará la actualización automáticamente.
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")