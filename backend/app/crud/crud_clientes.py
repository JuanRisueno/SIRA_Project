"""
=============================================================================
            CRUD DE CLIENTES (crud/cliente.py)
=============================================================================
Propósito:
Gestionar las operaciones de base de datos exclusivas de la tabla 'Cliente'.
Aquí es donde ocurre la ENCRIPTACIÓN de contraseñas.
"""
from sqlalchemy.orm import Session
# Importamos models y schemas subiendo un nivel (..)
from .. import models, schemas
import bcrypt

# --- 1. LEER (SELECT) ---

def get_cliente(db: Session, cliente_id: int):
    """Busca un cliente por su ID interno (PK)."""
    return db.query(models.Cliente).filter(models.Cliente.cliente_id == cliente_id).first()

def get_cliente_by_cif(db: Session, cif: str):
    """
    Busca un cliente por su CIF.
    Esta función es CRÍTICA para el Login (Auth).
    """
    return db.query(models.Cliente).filter(models.Cliente.cif == cif).first()

def get_clientes(db: Session, skip: int = 0, limit: int = 100):
    """Devuelve un listado de clientes (paginado)."""
    return db.query(models.Cliente).offset(skip).limit(limit).all()


# --- 2. ESCRIBIR (INSERT) ---

def create_cliente(db: Session, cliente: schemas.ClienteCreate):
    """
    Registra un nuevo cliente en la base de datos.
    IMPORTANTE: Aquí convertimos la contraseña de texto plano a Hash seguro.
    """
    # 1. Generamos el Hash seguro usando bcrypt
    # .encode('utf-8') convierte el string en bytes, que es lo que pide bcrypt
    hashed_password = bcrypt.hashpw(cliente.password.encode('utf-8'), bcrypt.gensalt())
    
    # 2. Creamos el objeto del modelo SQL
    db_cliente = models.Cliente(
        nombre_empresa=cliente.nombre_empresa,
        cif=cliente.cif,
        email_admin=cliente.email_admin,
        telefono=cliente.telefono,
        persona_contacto=cliente.persona_contacto,
        # Guardamos el hash decodificado a string, NO la contraseña real
        hash_contrasena=hashed_password.decode('utf-8'),
        activa=True # Por defecto activamos la cuenta
    )
    
    # 3. Transacción en BBDD
    db.add(db_cliente)
    db.commit()
    db.refresh(db_cliente) # Recargamos para obtener el ID generado
    
    return db_cliente
