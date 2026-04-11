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
    """Devuelve un listado de clientes (paginado) ordenado por ID."""
    return db.query(models.Cliente).order_by(models.Cliente.cliente_id).offset(skip).limit(limit).all()


# --- 2. ESCRIBIR (INSERT) ---

def create_cliente(db: Session, cliente: schemas.ClienteCreate, rol: str = "cliente"):
    """
    Registra un nuevo cliente en la base de datos.
    MODO DEV: Guardamos la contraseña en texto plano.
    """
    # Creamos el objeto del modelo SQL
    db_cliente = models.Cliente(
        nombre_empresa=cliente.nombre_empresa,
        cif=cliente.cif,
        email_admin=cliente.email_admin,
        telefono=cliente.telefono,
        persona_contacto=cliente.persona_contacto,
        # MODO DEV: Guardamos la contraseña plana
        hash_contrasena=cliente.password,
        rol=rol, # <--- ROL DINÁMICO
        activa=True # Por defecto activamos la cuenta
    )
    
    # Transacción en BBDD
    db.add(db_cliente)
    db.commit()
    db.refresh(db_cliente) # Recargamos para obtener el ID generado
    
    return db_cliente

def set_cliente_status(db: Session, cliente_id: int, activa: bool):
    """Activa o desactiva (borrado lógico) un cliente."""
    db_cliente = db.query(models.Cliente).filter(models.Cliente.cliente_id == cliente_id).first()
    if db_cliente:
        db_cliente.activa = activa
        db.commit()
        db.refresh(db_cliente)
        return db_cliente
    return None
