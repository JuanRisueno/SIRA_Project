from sqlalchemy.orm import Session
from sqlalchemy import or_, func
from typing import Optional, List
from .. import models, schemas

# --- LOCALIDADES ---
def get_localidad(db: Session, codigo_postal: str):
    return db.query(models.Localidad).filter(models.Localidad.codigo_postal == codigo_postal).first()

def get_localidades(db: Session, skip: int = 0, limit: int = 1000, q: Optional[str] = None):
    query = db.query(models.Localidad)
    if q:
        search_filter = f"%{q}%"
        query = query.filter(
            or_(
                func.unaccent(models.Localidad.municipio).ilike(func.unaccent(search_filter)),
                func.unaccent(models.Localidad.provincia).ilike(func.unaccent(search_filter)),
                models.Localidad.codigo_postal.ilike(search_filter)
            )
        )
    return query.offset(skip).limit(limit).all()

def create_localidad(db: Session, localidad: schemas.LocalidadCreate):
    db_localidad = models.Localidad(**localidad.dict())
    db.add(db_localidad)
    db.commit()
    db.refresh(db_localidad)
    return db_localidad

def update_localidad(db: Session, codigo_postal: str, localidad_update: schemas.LocalidadUpdate):
    db_localidad = db.query(models.Localidad).filter(models.Localidad.codigo_postal == codigo_postal).first()
    if not db_localidad:
        return None
    for var, value in vars(localidad_update).items():
        if value is not None:
            setattr(db_localidad, var, value)
    db.commit()
    db.refresh(db_localidad)
    return db_localidad

def delete_localidad(db: Session, codigo_postal: str):
    db_localidad = db.query(models.Localidad).filter(models.Localidad.codigo_postal == codigo_postal).first()
    if db_localidad:
        db.delete(db_localidad)
        db.commit()
        return True
    return False

# --- CULTIVOS ---
def get_cultivo(db: Session, cultivo_id: int):
    return db.query(models.Cultivo).filter(models.Cultivo.cultivo_id == cultivo_id).first()

def get_cultivos(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Cultivo).offset(skip).limit(limit).all()

def create_cultivo(db: Session, cultivo: schemas.CultivoCreate):
    db_cultivo = models.Cultivo(**cultivo.dict())
    db.add(db_cultivo)
    db.commit()
    db.refresh(db_cultivo)
    return db_cultivo

# --- PARCELAS ---
def get_parcela(db: Session, parcela_id: int):
    return db.query(models.Parcela).filter(models.Parcela.parcela_id == parcela_id).first()

def get_parcelas(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Parcela).offset(skip).limit(limit).all()

def create_parcela(db: Session, parcela: schemas.ParcelaCreate):
    db_parcela = models.Parcela(**parcela.dict())
    db.add(db_parcela)
    db.commit()
    db.refresh(db_parcela)
    return db_parcela

def get_parcelas_por_cliente(db: Session, cliente_id: int):
    return db.query(models.Parcela).filter(models.Parcela.cliente_id == cliente_id).all()

def get_parcelas_por_localidad(db: Session, codigo_postal: str):
    """Recupera todas las parcelas asociadas a un código postal específico."""
    return db.query(models.Parcela).filter(models.Parcela.codigo_postal == codigo_postal).all()

def update_parcela(db: Session, parcela_id: int, parcela_update: schemas.ParcelaUpdate):
    db_parcela = db.query(models.Parcela).filter(models.Parcela.parcela_id == parcela_id).first()
    if not db_parcela:
        return None
    for var, value in vars(parcela_update).items():
        if value is not None and var != "confirmar_cambio_ref":
            setattr(db_parcela, var, value)
    db.commit()
    db.refresh(db_parcela)
    return db_parcela

def delete_parcela(db: Session, parcela_id: int):
    db_parcela = db.query(models.Parcela).filter(models.Parcela.parcela_id == parcela_id).first()
    if db_parcela:
        db.delete(db_parcela)
        db.commit()
        return True
    return False

# --- INVERNADEROS ---
def get_invernadero(db: Session, invernadero_id: int):
    return db.query(models.Invernadero).filter(models.Invernadero.invernadero_id == invernadero_id).first()

def get_invernaderos(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Invernadero).offset(skip).limit(limit).all()

def create_invernadero(db: Session, invernadero: schemas.InvernaderoCreate):
    db_invernadero = models.Invernadero(**invernadero.dict())
    db.add(db_invernadero)
    db.commit()
    db.refresh(db_invernadero)
    return db_invernadero

def get_invernaderos_por_cliente(db: Session, cliente_id: int):
    return db.query(models.Invernadero).join(models.Parcela).filter(models.Parcela.cliente_id == cliente_id).all()

def update_invernadero(db: Session, invernadero_id: int, invernadero_update: schemas.InvernaderoUpdate):
    db_invernadero = db.query(models.Invernadero).filter(models.Invernadero.invernadero_id == invernadero_id).first()
    if not db_invernadero:
        return None
    for var, value in vars(invernadero_update).items():
        if value is not None:
            setattr(db_invernadero, var, value)
    db.commit()
    db.refresh(db_invernadero)
    return db_invernadero

def delete_invernadero(db: Session, invernadero_id: int):
    db_invernadero = db.query(models.Invernadero).filter(models.Invernadero.invernadero_id == invernadero_id).first()
    if db_invernadero:
        db.delete(db_invernadero)
        db.commit()
        return True
    return False