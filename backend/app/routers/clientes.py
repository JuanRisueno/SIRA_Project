from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List, Optional
from .. import crud, models, schemas, auth
from ..database import SessionLocal

router = APIRouter(
    prefix="/api/v1",
    tags=["Gestión de Clientes"]
)

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

@router.post("/clientes/", response_model=schemas.Cliente, status_code=status.HTTP_201_CREATED, summary="Crear Cliente")
def crear_cliente(
    cliente: schemas.ClienteCreate, 
    db: Session = Depends(get_db),
    current_user: Optional[models.Cliente] = Depends(auth.get_current_user_optional)
):
    """Registra una nueva empresa/agricultor en SIRA."""
    if cliente.rol != "cliente":
        if not current_user or current_user.rol not in ["admin", "root"]:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, 
                detail="No tienes permisos para asignar roles especiales."
            )
        if cliente.rol == "root":
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN, 
                detail="No se pueden crear más usuarios de tipo 'root'."
            )

    db_cliente = crud.get_cliente_by_cif(db, cif=cliente.cif)
    if db_cliente:
        raise HTTPException(status_code=400, detail="El cliente con este CIF ya está registrado.")
    
    return crud.create_cliente(db=db, cliente=cliente, rol=cliente.rol)

@router.get("/clientes/", response_model=List[schemas.Cliente], summary="Listar Clientes")
def listar_clientes(
    skip: int = 0, 
    limit: int = 1000, 
    q: Optional[str] = None,
    db: Session = Depends(get_db),
    current_user: models.Cliente = Depends(auth.require_admin)
):
    """Lista todos los clientes registrados. Solo para administradores."""
    return crud.get_clientes(db, skip=skip, limit=limit)

@router.patch("/clientes/{cliente_id}/status", response_model=schemas.Cliente, summary="Activar/Desactivar Cliente")
def cambiar_estado_cliente(
    cliente_id: int, 
    activa: bool, 
    db: Session = Depends(get_db),
    current_user: models.Cliente = Depends(auth.require_admin)
):
    """Cambia la visibilidad de un cliente (Borrado Lógico)."""
    db_cliente = crud.get_cliente(db, cliente_id=cliente_id)
    if not db_cliente:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    
    if current_user.rol == "admin" and db_cliente.rol != "cliente":
        raise HTTPException(status_code=403, detail="Un administrador no puede cambiar el estado de otros administradores.")
    
    return crud.set_cliente_status(db, cliente_id=cliente_id, activa=activa)

@router.put("/clientes/{cliente_id}", response_model=schemas.Cliente, summary="Actualizar Cliente")
def actualizar_cliente(
    cliente_id: int, 
    cliente_update: schemas.ClienteUpdate, 
    db: Session = Depends(get_db),
    current_user: models.Cliente = Depends(auth.get_current_user)
):
    """Permite a un usuario editar su propia información o al Admin editar perfiles."""
    try:
        db_cliente = crud.update_cliente(db=db, cliente_id=cliente_id, cliente_update=cliente_update)
        if db_cliente is None:
            raise HTTPException(status_code=404, detail="Cliente no encontrado")
        return db_cliente
    except ValueError as e:
        raise HTTPException(status_code=400, detail=str(e))

@router.delete("/clientes/{cliente_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Borrar Cliente Definitivamente")
def borrar_cliente(
    cliente_id: int, 
    db: Session = Depends(get_db),
    current_user: models.Cliente = Depends(auth.require_admin)
):
    """Elimina permanentemente un cliente (Uso restringido)."""
    exito = crud.delete_cliente(db, cliente_id=cliente_id)
    if not exito:
        raise HTTPException(status_code=404, detail="Cliente no encontrado")
    return None
