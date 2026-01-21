"""
===============================================================================================
            Router: Datos Maestros (Clientes, Localidades, Parcelas e Invernaderos)
===============================================================================================

Propósito:
Este archivo define los "Endpoints" (URLs) de la API relacionados con la
estructura base del sistema.

Aquí es donde FastAPI recibe las peticiones HTTP (GET, POST, PUT, DELETE), valida los datos
usando 'schemas.py', llama a la lógica de base de datos en 'crud.py',
y devuelve la respuesta al usuario.
"""

from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List

from .. import crud, models, schemas, auth
from ..database import SessionLocal

# --- Configuración del Router ---
router = APIRouter(
    prefix="/api/v1",
    tags=["Datos Maestros (Configuración)"]
)

# --- Dependencia para obtener la DB ---
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


# =============================================================================
# 1. GESTIÓN DE CLIENTES
# =============================================================================

# 1. POST (Crear) - PÚBLICO (Para registrarse)
@router.post("/clientes/", response_model=schemas.Cliente, status_code=status.HTTP_201_CREATED, summary="Crear Cliente")
def crear_cliente(cliente: schemas.ClienteCreate, db: Session = Depends(get_db)):
    """
    Registra una nueva empresa/agricultor en SIRA.
    """
    db_cliente = crud.get_cliente_by_cif(db, cif=cliente.cif)
    if db_cliente:
        raise HTTPException(status_code=400, detail="El cliente con este CIF ya está registrado.")
    return crud.create_cliente(db=db, cliente=cliente)

# 2. GET (Listar Todos) - PROTEGIDO POR JWT
@router.get("/clientes/", response_model=List[schemas.Cliente], summary="Listar Clientes")
def listar_clientes(
    skip: int = 0, 
    limit: int = 100, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    """Devuelve un listado paginado de clientes registrados. Requiere Login."""
    return crud.get_clientes(db, skip=skip, limit=limit)

# 3. GET (Buscar por CIF) - PROTEGIDO POR JWT
@router.get("/clientes/buscar/{cif}", response_model=schemas.Cliente, summary="Buscar Cliente por CIF")
def buscar_cliente_por_cif(
    cif: str, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    db_cliente = crud.get_cliente_by_cif(db, cif=cif)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="No existe ningún cliente con este CIF")
    return db_cliente

# 4. GET (Leer por ID) - PROTEGIDO POR JWT
@router.get("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Leer Cliente")
def leer_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return db_cliente

# 5. PUT (Actualizar) - PROTEGIDO POR JWT
@router.put("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Actualizar Cliente")
def actualizar_cliente(
    cliente_id: int, 
    cliente_update: schemas.ClienteUpdate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    try:
        db_cliente = crud.update_cliente(db=db, cliente_id=cliente_id, cliente_update=cliente_update)
        if db_cliente is None:
            raise HTTPException(status_code=404, detail="Cliente no encontrado")
        return db_cliente
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

# 6. DELETE (Borrar) - PROTEGIDO POR JWT
@router.delete("/clientes/{cliente_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Cliente (Lógico)")
def borrar_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # CANDADO
):
    exito = crud.delete_cliente(db, cliente_id=cliente_id)
    if not exito:
        raise HTTPException(status_code=404, detail="Cliente no encontrado o ya borrado")
    return None


# =============================================================================
# 2. GESTIÓN DE LOCALIDADES (PÚBLICO)
# =============================================================================
# Las localidades se dejan públicas para facilitar desplegables en el Frontend

@router.post("/localidades/", response_model=schemas.Localidad, status_code=status.HTTP_201_CREATED, summary="Crear Localidad")
def crear_localidad(localidad: schemas.LocalidadCreate, db: Session = Depends(get_db)):
    db_localidad = crud.get_localidad(db, codigo_postal=localidad.codigo_postal)
    if db_localidad:
        raise HTTPException(status_code=400, detail="Este Código Postal ya existe en la base de datos.")
    return crud.create_localidad(db=db, localidad=localidad)

@router.get("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Leer Localidad")
def leer_localidad(codigo_postal: str, db: Session = Depends(get_db)):
    db_localidad = crud.get_localidad(db, codigo_postal=codigo_postal)
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return db_localidad

@router.put("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Actualizar Localidad")
def actualizar_localidad(codigo_postal: str, localidad_update: schemas.LocalidadUpdate, db: Session = Depends(get_db)):
    db_localidad = crud.update_localidad(db=db, codigo_postal=codigo_postal, localidad_update=localidad_update)
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return db_localidad

@router.get("/localidades/", response_model=List[schemas.Localidad], summary="Listar Localidades")
def listar_localidades(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    return crud.get_localidades(db, skip=skip, limit=limit)

@router.delete("/localidades/{codigo_postal}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Localidad")
def borrar_localidad(codigo_postal: str, db: Session = Depends(get_db)):
    if not crud.get_localidad(db, codigo_postal):
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    try:
        crud.delete_localidad(db, codigo_postal=codigo_postal)
    except Exception as e:
        raise HTTPException(
            status_code=400, 
            detail="No se puede borrar esta localidad porque existen parcelas registradas en ella."
        )
    return None

# =============================================================================
# 3. GESTIÓN DE PARCELAS
# =============================================================================

@router.post("/parcelas/", response_model=schemas.Parcela, status_code=status.HTTP_201_CREATED, summary="Crear Parcela")
def crear_parcela(
    parcela: schemas.ParcelaCreate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.get_cliente(db, cliente_id=parcela.cliente_id):
        raise HTTPException(status_code=404, detail=f"Cliente {parcela.cliente_id} no encontrado.")
    if not crud.get_localidad(db, codigo_postal=parcela.codigo_postal):
        raise HTTPException(status_code=404, detail=f"Código Postal {parcela.codigo_postal} no registrado.")
    try:
        return crud.create_parcela(db=db, parcela=parcela)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Error al crear parcela. Verifique Ref. Catastral.")

@router.get("/parcelas/", response_model=List[schemas.Parcela], summary="Listar Parcelas")
def listar_parcelas(
    skip: int = 0, 
    limit: int = 100, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    return crud.get_parcelas(db, skip=skip, limit=limit)

@router.get("/parcelas/cliente/{cliente_id}", response_model=List[schemas.Parcela], summary="Listar Parcelas de un Cliente")
def listar_parcelas_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.get_cliente(db, cliente_id):
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return crud.get_parcelas_por_cliente(db, cliente_id=cliente_id)

@router.get("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Leer Parcela")
def read_parcela(
    parcela_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    db_parcela = crud.get_parcela(db, parcela_id=parcela_id)
    if db_parcela is None:
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return db_parcela

@router.put("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Actualizar Parcela")
def actualizar_parcela(
    parcela_id: int, 
    parcela_update: schemas.ParcelaUpdate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    try:
        db_parcela = crud.update_parcela(db=db, parcela_id=parcela_id, parcela_update=parcela_update)
        if db_parcela is None:
            raise HTTPException(status_code=404, detail="Parcela no encontrada")
        return db_parcela
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

@router.delete("/parcelas/{parcela_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Parcela (Lógico)")
def borrar_parcela(
    parcela_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.delete_parcela(db, parcela_id):
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return None


# =============================================================================
# 4. GESTIÓN DE INVERNADEROS
# =============================================================================

@router.post("/invernaderos/", response_model=schemas.Invernadero, status_code=status.HTTP_201_CREATED, summary="Crear Invernadero")
def crear_invernadero(
    invernadero: schemas.InvernaderoCreate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    db_parcela = crud.get_parcela(db, parcela_id=invernadero.parcela_id)
    if not db_parcela:
        raise HTTPException(status_code=404, detail=f"La parcela {invernadero.parcela_id} no existe.")
    if invernadero.cultivo_id:
        db_cultivo = crud.get_cultivo(db, cultivo_id=invernadero.cultivo_id)
        if not db_cultivo:
            raise HTTPException(status_code=404, detail=f"El cultivo {invernadero.cultivo_id} no existe.")
    return crud.create_invernadero(db=db, invernadero=invernadero)

@router.get("/invernaderos/", response_model=List[schemas.Invernadero], summary="Listar Invernaderos")
def listar_invernaderos(
    skip: int = 0, 
    limit: int = 100, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    """Lista los invernaderos registrados. Requiere Login."""
    return crud.get_invernaderos(db, skip=skip, limit=limit)

@router.get("/invernaderos/cliente/{cliente_id}", response_model=List[schemas.Invernadero], summary="Listar Invernaderos de un Cliente")
def listar_invernaderos_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.get_cliente(db, cliente_id):
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return crud.get_invernaderos_por_cliente(db, cliente_id=cliente_id)

@router.get("/invernaderos/{invernadero_id}", response_model=schemas.Invernadero, summary="Leer Invernadero")
def leer_invernadero(
    invernadero_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    db_invernadero = crud.get_invernadero(db, invernadero_id=invernadero_id)
    if db_invernadero is None:
        raise HTTPException(status_code=404, detail="Invernadero no encontrado")
    return db_invernadero

@router.put("/invernaderos/{invernadero_id}", response_model=schemas.Invernadero, summary="Actualizar Invernadero")
def actualizar_invernadero(
    invernadero_id: int, 
    invernadero_update: schemas.InvernaderoUpdate, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    try:
        db_invernadero = crud.update_invernadero(db=db, invernadero_id=invernadero_id, invernadero_update=invernadero_update)
        if db_invernadero is None:
            raise HTTPException(status_code=404, detail="Invernadero no encontrado")
        return db_invernadero
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

@router.delete("/invernaderos/{invernadero_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Invernadero (Lógico)")
def borrar_invernadero(
    invernadero_id: int, 
    db: Session = Depends(get_db),
    current_user: schemas.Cliente = Depends(auth.get_current_user) # PROTEGIDO POR JWT
):
    if not crud.delete_invernadero(db, invernadero_id):
        raise HTTPException(status_code=404, detail="Invernadero no encontrado")
    return None