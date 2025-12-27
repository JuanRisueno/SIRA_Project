"""
===============================================================================================
            Router: Datos Maestros (Clientes, Localidades, Parcelas e Invernaderos)
===============================================================================================

Propósito:
Este archivo define los "Endpoints" (URLs) de la API relacionados con la
estructura base del sistema.

Aquí es donde FastAPI recibe las peticiones HTTP (GET, POST), valida los datos
usando 'schemas.py', llama a la lógica de base de datos en 'crud.py',
y devuelve la respuesta al usuario.
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List

# Importaciones locales del proyecto SIRA
from .. import crud, models, schemas
from ..database import SessionLocal

# --- Configuración del Router ---
# prefix: Todas las URLs empezarán con /api/v1 (convención REST)
# tags: Para agrupar estos endpoints en la documentación automática (Swagger)
router = APIRouter(
    prefix="/api/v1",
    tags=["Datos Maestros (Configuración)"]
)

# --- Dependencia para obtener la DB ---
# Esta función abre una "tubería" a la BBDD para cada petición y la cierra al terminar.
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


# =============================================================================
# 1. GESTIÓN DE CLIENTES
# =============================================================================

@router.post("/clientes/", response_model=schemas.Cliente, status_code=status.HTTP_201_CREATED, summary="Crear Cliente")
def crear_cliente(cliente: schemas.ClienteCreate, db: Session = Depends(get_db)):
    """
    Registra una nueva empresa/agricultor en SIRA.
    - Verifica si el CIF ya existe.
    - Hashea la contraseña automáticamente (vía crud).
    """
    # 1. Validar duplicados (Regla de Negocio)
    db_cliente = crud.get_cliente_by_cif(db, cif=cliente.cif)
    if db_cliente:
        raise HTTPException(status_code=400, detail="El cliente con este CIF ya está registrado.")
    
    # 2. Crear si no existe
    return crud.create_cliente(db=db, cliente=cliente)


@router.get("/clientes/", response_model=List[schemas.Cliente], summary="Listar Clientes")
def listar_clientes(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """Devuelve un listado paginado de clientes registrados."""
    return crud.get_clientes(db, skip=skip, limit=limit)


@router.get("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Leer Cliente")
def leer_cliente(cliente_id: int, db: Session = Depends(get_db)):
    """Busca un cliente específico por su ID interno."""
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return db_cliente


# =============================================================================
# 2. GESTIÓN DE LOCALIDADES
# =============================================================================

@router.post("/localidades/", response_model=schemas.Localidad, status_code=status.HTTP_201_CREATED, summary="Crear Localidad")
def crear_localidad(localidad: schemas.LocalidadCreate, db: Session = Depends(get_db)):
    """
    Registra una nueva localidad (Municipio/Provincia).
    La PK es el Código Postal.
    """
    # 1. Verificar si el CP ya existe
    db_localidad = crud.get_localidad(db, codigo_postal=localidad.codigo_postal)
    if db_localidad:
        raise HTTPException(status_code=400, detail="Este Código Postal ya existe en la base de datos.")
        
    return crud.create_localidad(db=db, localidad=localidad)


@router.get("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Leer Localidad")
def leer_localidad(codigo_postal: str, db: Session = Depends(get_db)):
    """Obtiene datos de una localidad dado su CP."""
    db_localidad = crud.get_localidad(db, codigo_postal=codigo_postal)
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return db_localidad


# =============================================================================
# 3. GESTIÓN DE PARCELAS
# =============================================================================

@router.post("/parcelas/", response_model=schemas.Parcela, status_code=status.HTTP_201_CREATED, summary="Crear Parcela")
def crear_parcela(parcela: schemas.ParcelaCreate, db: Session = Depends(get_db)):
    """
    Registra un terreno físico.
    Requiere que el Cliente (ID) y la Localidad (CP) existan previamente.
    """
    # 1. Validar integridad referencial (¿Existe el cliente?)
    if not crud.get_cliente(db, cliente_id=parcela.cliente_id):
        raise HTTPException(status_code=404, detail=f"Cliente {parcela.cliente_id} no encontrado. No se puede asignar la parcela.")

    # 2. Validar integridad referencial (¿Existe la localidad?)
    if not crud.get_localidad(db, codigo_postal=parcela.codigo_postal):
        raise HTTPException(status_code=404, detail=f"Código Postal {parcela.codigo_postal} no registrado. Créalo primero.")

    # 3. Crear parcela
    try:
        return crud.create_parcela(db=db, parcela=parcela)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Error al crear parcela. Verifique que la Ref. Catastral no esté duplicada.")


@router.get("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Leer Parcela")
def read_parcela(parcela_id: int, db: Session = Depends(get_db)):
    """
    Busca una parcela específica por su ID.
    """
    db_parcela = crud.get_parcela(db, parcela_id=parcela_id)
    if db_parcela is None:
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return db_parcela


@router.get("/parcelas/", response_model=List[schemas.Parcela], summary="Listar Parcelas")
def listar_parcelas(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """
    Lista todas las parcelas.
    Gracias al schema 'Parcela' (con orm_mode), traerá automáticamente
    los datos anidados del Cliente y la Localidad (Rich Response).
    """
    return crud.get_parcelas(db, skip=skip, limit=limit)


# =============================================================================
# 4. GESTIÓN DE INVERNADEROS
# =============================================================================

@router.post("/invernaderos/", response_model=schemas.Invernadero, status_code=status.HTTP_201_CREATED, summary="Crear Invernadero")
def crear_invernadero(invernadero: schemas.InvernaderoCreate, db: Session = Depends(get_db)):
    """
    Registra un invernadero.
    """
    # 1. Validar que la parcela existe
    db_parcela = crud.get_parcela(db, parcela_id=invernadero.parcela_id)
    if not db_parcela:
        raise HTTPException(status_code=404, detail=f"La parcela {invernadero.parcela_id} no existe. Crea la parcela antes que el invernadero.")

    # 2. Validar cultivo si viene informado
    if invernadero.cultivo_id:
        db_cultivo = crud.get_cultivo(db, cultivo_id=invernadero.cultivo_id)
        if not db_cultivo:
            raise HTTPException(status_code=404, detail=f"El cultivo {invernadero.cultivo_id} no existe.")

    # 3. Crear
    return crud.create_invernadero(db=db, invernadero=invernadero)


@router.get("/invernaderos/", response_model=List[schemas.Invernadero], summary="Listar Invernaderos")
def listar_invernaderos(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """
    Lista los invernaderos registrados.
    """
    return crud.get_invernaderos(db, skip=skip, limit=limit)