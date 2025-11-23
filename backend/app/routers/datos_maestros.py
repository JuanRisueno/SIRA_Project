"""
=============================================================================
             Router: Datos Maestros (Estructurales)
=============================================================================

Propósito:
Gestionar los endpoints HTTP para las entidades "estáticas" o estructurales
del sistema: Clientes, Localidades y Parcelas.

Responsabilidades:
1.  Recibir la petición HTTP (GET, POST...).
2.  Validar datos extra (ej. comprobar si el CIF ya existe).
3.  Llamar al 'Cocinero' (crud.py) para que ejecute la operación.
4.  Devolver la respuesta con el Código de Estado adecuado (200, 201, 404...).
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List

# Importaciones de tu proyecto
from .. import crud, schemas, models
from ..database import SessionLocal

# 1. DEFINICIÓN DEL ROUTER
# prefix="/datos-maestros": (Opcional) Podríamos agrupar URLs, pero aquí
# dejaremos las rutas limpias (/clientes, /parcelas).
# tags=["Datos Maestros"]: Para agruparlo bonito en la documentación automática.
router = APIRouter(
    tags=["Datos Maestros (Clientes/Parcelas)"]
)

# 2. DEPENDENCIA DE BASE DE DATOS
# Copiamos esta lógica aquí para evitar dependencias circulares con main.py
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# =============================================================================
#                                 CLIENTES
# =============================================================================

# --- CREAR (POST) ---
@router.post("/clientes/", response_model=schemas.Cliente, status_code=status.HTTP_201_CREATED)
def create_cliente(cliente: schemas.ClienteCreate, db: Session = Depends(get_db)):
    """
    Registra un nuevo cliente.
    - Verifica si el CIF ya existe (Integridad).
    - Hashea la contraseña (vía CRUD).
    """
    # 1. Validación de Negocio: ¿Existe ya este CIF?
    # Usamos la función de lectura que creamos en crud.py
    db_cliente = crud.get_cliente_by_cif(db, cif=cliente.cif)
    if db_cliente:
        # Si existe, devolvemos error 400 (Bad Request) o 409 (Conflict)
        raise HTTPException(status_code=409, detail="El cliente con este CIF ya existe.")
    
    # 2. Llamada al CRUD
    return crud.create_cliente(db=db, cliente=cliente)

# --- LEER UNO (GET) ---
@router.get("/clientes/{cliente_id}", response_model=schemas.Cliente)
def read_cliente(cliente_id: int, db: Session = Depends(get_db)):
    """
    Obtiene la ficha de un cliente por su ID interno.
    """
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return db_cliente

# --- LEER LISTA (GET) ---
@router.get("/clientes/", response_model=List[schemas.Cliente])
def read_clientes(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """
    Obtiene el listado de clientes registrados (paginado).
    """
    clientes = crud.get_clientes(db, skip=skip, limit=limit)
    return clientes