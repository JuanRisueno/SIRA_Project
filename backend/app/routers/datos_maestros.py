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

# Endpoint para registrar un nuevo cliente en el sistema
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


# Endpoint para obtener el listado completo de clientes (paginado)
@router.get("/clientes/", response_model=List[schemas.Cliente], summary="Listar Clientes")
def listar_clientes(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """Devuelve un listado paginado de clientes registrados."""
    return crud.get_clientes(db, skip=skip, limit=limit)


# Endpoint para consultar los datos de un único cliente mediante su ID
@router.get("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Leer Cliente")
def leer_cliente(cliente_id: int, db: Session = Depends(get_db)):
    """Busca un cliente específico por su ID interno."""
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if db_cliente is None:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return db_cliente


# Endpoint para modificar datos de un cliente (Update)
@router.put("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Actualizar Cliente")
def actualizar_cliente(cliente_id: int, cliente_update: schemas.ClienteUpdate, db: Session = Depends(get_db)):
    """
    Modifica los datos de un cliente. 
    Para cambiar el CIF, es obligatorio enviar 'confirmar_cambio_cif': true.
    """
    try:
        db_cliente = crud.update_cliente(db=db, cliente_id=cliente_id, cliente_update=cliente_update)
        
        if db_cliente is None:
            raise HTTPException(status_code=404, detail="Cliente no encontrado")
            
        return db_cliente

    except ValueError as e:
        # Capturamos el error de CIF duplicado o falta de confirmación que lanzamos en crud.py
        raise HTTPException(status_code=400, detail=str(e))


# Endpoint para Borrado Lógico (Soft Delete)
@router.delete("/clientes/{cliente_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Cliente (Lógico)")
def borrar_cliente(cliente_id: int, db: Session = Depends(get_db)):
    """
    Desactiva un cliente. No borra los datos físicos.
    Devuelve 204 No Content si se realizó correctamente.
    """
    exito = crud.delete_cliente(db, cliente_id=cliente_id)
    if not exito:
        raise HTTPException(status_code=404, detail="Cliente no encontrado o ya borrado")
    return None


# =============================================================================
# 2. GESTIÓN DE LOCALIDADES
# =============================================================================

# Endpoint para dar de alta una nueva localidad
@router.post("/localidades/", response_model=schemas.Localidad, status_code=status.HTTP_201_CREATED, summary="Crear Localidad")
def crear_localidad(localidad: schemas.LocalidadCreate, db: Session = Depends(get_db)):
    """
    Registra una nueva localidad. La PK es el Código Postal.
    """
    # 1. Verificar si el CP ya existe
    db_localidad = crud.get_localidad(db, codigo_postal=localidad.codigo_postal)
    if db_localidad:
        raise HTTPException(status_code=400, detail="Este Código Postal ya existe en la base de datos.")
        
    return crud.create_localidad(db=db, localidad=localidad)

# Endpoint para consultar información de una localidad
@router.get("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Leer Localidad")
def leer_localidad(codigo_postal: str, db: Session = Depends(get_db)):
    """Obtiene datos de una localidad dado su CP."""
    db_localidad = crud.get_localidad(db, codigo_postal=codigo_postal)
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
    return db_localidad

# Endpoint para corregir errores en localidad
@router.put("/localidades/{codigo_postal}", response_model=schemas.Localidad, summary="Actualizar Localidad")
def actualizar_localidad(codigo_postal: str, localidad_update: schemas.LocalidadUpdate, db: Session = Depends(get_db)):
    """
    Permite cambiar el nombre del Municipio o Provincia.
    El CP es inmutable.
    """
    db_localidad = crud.update_localidad(db=db, codigo_postal=codigo_postal, localidad_update=localidad_update)
    
    if db_localidad is None:
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
        
    return db_localidad

# Endpoint para listar TODAS las localidades
@router.get("/localidades/", response_model=List[schemas.Localidad], summary="Listar Localidades")
def listar_localidades(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """
    Devuelve la lista completa de localidades.
    Útil para que el Frontend muestre un selector de municipios.
    """
    return crud.get_localidades(db, skip=skip, limit=limit)

# Endpoint para borrar localidad
@router.delete("/localidades/{codigo_postal}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Localidad")
def borrar_localidad(codigo_postal: str, db: Session = Depends(get_db)):
    """
    Elimina una localidad físicamente.
    
    PROTECCIÓN DE INTEGRIDAD:
    Si la localidad tiene parcelas asignadas, la base de datos bloqueará el borrado
    y devolveremos un error 400 explicativo.
    """
    # 1. Verificar existencia
    if not crud.get_localidad(db, codigo_postal):
        raise HTTPException(status_code=404, detail="Localidad no encontrada")
        
    # 2. Intentar borrar con red de seguridad
    try:
        crud.delete_localidad(db, codigo_postal=codigo_postal)
    except Exception as e:
        # Aquí capturamos el error de PostgreSQL si hay parcelas vinculadas
        raise HTTPException(
            status_code=400, 
            detail="No se puede borrar esta localidad porque existen parcelas registradas en ella. Elimine o mueva las parcelas antes."
        )
    
    return None

# =============================================================================
# 3. GESTIÓN DE PARCELAS
# =============================================================================

# Endpoint para registrar una nueva parcela
@router.post("/parcelas/", response_model=schemas.Parcela, status_code=status.HTTP_201_CREATED, summary="Crear Parcela")
def crear_parcela(parcela: schemas.ParcelaCreate, db: Session = Depends(get_db)):
    """
    Registra un terreno físico.
    Requiere que el Cliente (ID) y la Localidad (CP) existan previamente.
    """
    # 1. Validar integridad referencial (Cliente)
    if not crud.get_cliente(db, cliente_id=parcela.cliente_id):
        raise HTTPException(status_code=404, detail=f"Cliente {parcela.cliente_id} no encontrado.")

    # 2. Validar integridad referencial (Localidad)
    if not crud.get_localidad(db, codigo_postal=parcela.codigo_postal):
        raise HTTPException(status_code=404, detail=f"Código Postal {parcela.codigo_postal} no registrado.")

    # 3. Crear parcela
    try:
        return crud.create_parcela(db=db, parcela=parcela)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Error al crear parcela. Verifique Ref. Catastral.")


# Endpoint para obtener el detalle de una parcela
@router.get("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Leer Parcela")
def read_parcela(parcela_id: int, db: Session = Depends(get_db)):
    """Busca una parcela específica por su ID."""
    db_parcela = crud.get_parcela(db, parcela_id=parcela_id)
    if db_parcela is None:
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return db_parcela


# Endpoint para listar parcelas
@router.get("/parcelas/", response_model=List[schemas.Parcela], summary="Listar Parcelas")
def listar_parcelas(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """Lista todas las parcelas."""
    return crud.get_parcelas(db, skip=skip, limit=limit)


# Endpoint para actualizar parcela
@router.put("/parcelas/{parcela_id}", response_model=schemas.Parcela, summary="Actualizar Parcela")
def actualizar_parcela(parcela_id: int, parcela_update: schemas.ParcelaUpdate, db: Session = Depends(get_db)):
    """
    Modifica una parcela (Traspaso de dueño o corrección Catastro).
    Dirección y CP son inmutables.
    """
    try:
        db_parcela = crud.update_parcela(db=db, parcela_id=parcela_id, parcela_update=parcela_update)
        
        if db_parcela is None:
            raise HTTPException(status_code=404, detail="Parcela no encontrada")
            
        return db_parcela

    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))


# Endpoint para Borrado Lógico de Parcela
@router.delete("/parcelas/{parcela_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Parcela (Lógico)")
def borrar_parcela(parcela_id: int, db: Session = Depends(get_db)):
    """
    Desactiva una parcela.
    """
    if not crud.delete_parcela(db, parcela_id):
        raise HTTPException(status_code=404, detail="Parcela no encontrada")
    return None


# =============================================================================
# 4. GESTIÓN DE INVERNADEROS
# =============================================================================

# Endpoint para registrar un nuevo invernadero
@router.post("/invernaderos/", response_model=schemas.Invernadero, status_code=status.HTTP_201_CREATED, summary="Crear Invernadero")
def crear_invernadero(invernadero: schemas.InvernaderoCreate, db: Session = Depends(get_db)):
    """
    Registra un invernadero. Valida Parcela y Cultivo.
    """
    # 1. Validar Parcela
    db_parcela = crud.get_parcela(db, parcela_id=invernadero.parcela_id)
    if not db_parcela:
        raise HTTPException(status_code=404, detail=f"La parcela {invernadero.parcela_id} no existe.")

    # 2. Validar Cultivo
    if invernadero.cultivo_id:
        db_cultivo = crud.get_cultivo(db, cultivo_id=invernadero.cultivo_id)
        if not db_cultivo:
            raise HTTPException(status_code=404, detail=f"El cultivo {invernadero.cultivo_id} no existe.")

    return crud.create_invernadero(db=db, invernadero=invernadero)


# Endpoint para listar invernaderos
@router.get("/invernaderos/", response_model=List[schemas.Invernadero], summary="Listar Invernaderos")
def listar_invernaderos(skip: int = 0, limit: int = 100, db: Session = Depends(get_db)):
    """Lista los invernaderos registrados."""
    return crud.get_invernaderos(db, skip=skip, limit=limit)

# Endpoint para consultar un invernadero específico por ID
@router.get("/invernaderos/{invernadero_id}", response_model=schemas.Invernadero, summary="Leer Invernadero")
def leer_invernadero(invernadero_id: int, db: Session = Depends(get_db)):
    """
    Recupera los datos de un invernadero concreto.
    Devuelve 404 si no existe o si ha sido borrado (soft delete).
    """
    db_invernadero = crud.get_invernadero(db, invernadero_id=invernadero_id)
    
    if db_invernadero is None:
        raise HTTPException(status_code=404, detail="Invernadero no encontrado")
        
    return db_invernadero

# Endpoint para actualizar invernadero
@router.put("/invernaderos/{invernadero_id}", response_model=schemas.Invernadero, summary="Actualizar Invernadero")
def actualizar_invernadero(invernadero_id: int, invernadero_update: schemas.InvernaderoUpdate, db: Session = Depends(get_db)):
    """
    Modifica nombre, medidas o cultivo (rotación).
    """
    try:
        db_invernadero = crud.update_invernadero(db=db, invernadero_id=invernadero_id, invernadero_update=invernadero_update)
        
        if db_invernadero is None:
            raise HTTPException(status_code=404, detail="Invernadero no encontrado")
            
        return db_invernadero

    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))


# Endpoint para Borrado Lógico de Invernadero
@router.delete("/invernaderos/{invernadero_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Invernadero (Lógico)")
def borrar_invernadero(invernadero_id: int, db: Session = Depends(get_db)):
    """
    Desactiva un invernadero.
    """
    if not crud.delete_invernadero(db, invernadero_id):
        raise HTTPException(status_code=404, detail="Invernadero no encontrado")
    return None