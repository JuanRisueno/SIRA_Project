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

# bcrypt: Librería oficial para hashear contraseñas de forma segura.
import bcrypt


# --- 2. IMPORTACIONES LOCALES (Tu propio código) ---

# Importamos los 'models' (Tablas SQL) para saber DÓNDE guardar los datos.
# Importamos los 'schemas' (Validación Pydantic) para saber QUÉ datos recibimos.
# El punto (.) significa "busca en esta misma carpeta".
from . import models, schemas

# =============================================================================
# 1. LÓGICA PARA CLIENTE
# =============================================================================

def get_cliente(db: Session, cliente_id: int):
    """Busca un cliente por su ID (PK)."""
    return db.query(models.Cliente).filter(models.Cliente.cliente_id == cliente_id).first()

def get_cliente_by_cif(db: Session, cif: str):
    """
    Busca un cliente por su CIF.
    IMPORTANTE: Esta función se usará para el LOGIN (Regla de Negocio).
    """
    return db.query(models.Cliente).filter(models.Cliente.cif == cif).first()

def get_clientes(db: Session, skip: int = 0, limit: int = 100):
    """Lista paginada de clientes."""
    return db.query(models.Cliente).offset(skip).limit(limit).all()

def create_cliente(db: Session, cliente: schemas.ClienteCreate):
    """
    Crea un nuevo cliente en la base de datos.
    PASO CLAVE: Hashear la contraseña usando bcrypt puro.
    """
    # 1. Convertir la contraseña a bytes (utf-8)
    password_bytes = cliente.hash_contrasena.encode('utf-8')
    
    # 2. Generar un "Salt" (ruido aleatorio) y hashear la contraseña.
    # bcrypt requiere bytes, por eso hacemos .encode('utf-8')
    salt = bcrypt.gensalt()
    hashed_bytes = bcrypt.hashpw(cliente.hash_contrasena.encode('utf-8'), salt)
    
    # 3. Convertir el hash (que es bytes) a string para guardarlo en PostgreSQL (VARCHAR).
    hashed_password_str = hashed_bytes.decode('utf-8')
    
    # 4. Crear objeto ORM
    db_cliente = models.Cliente(
        nombre_empresa=cliente.nombre_empresa,
        cif=cliente.cif,
        email_admin=cliente.email_admin,
        telefono=cliente.telefono,
        persona_contacto=cliente.persona_contacto,
        hash_contrasena=hashed_password_str # Guardamos el string seguro
    )
    
    # 5. Transacción SQL
    db.add(db_cliente)
    db.commit()
    db.refresh(db_cliente)
    
    return db_cliente