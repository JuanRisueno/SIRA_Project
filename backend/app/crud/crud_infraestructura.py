from sqlalchemy.orm import Session
from .. import models, schemas

# --- LOCALIDADES ---
def get_localidad(db: Session, codigo_postal: str):
    return db.query(models.Localidad).filter(models.Localidad.codigo_postal == codigo_postal).first()

def get_localidades(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Localidad).offset(skip).limit(limit).all()

def create_localidad(db: Session, localidad: schemas.LocalidadCreate):
    db_localidad = models.Localidad(**localidad.dict())
    db.add(db_localidad)
    db.commit()
    db.refresh(db_localidad)
    return db_localidad

# --- CULTIVOS ---
def get_cultivos(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Cultivo).offset(skip).limit(limit).all()

def create_cultivo(db: Session, cultivo: schemas.CultivoCreate):
    db_cultivo = models.Cultivo(**cultivo.dict())
    db.add(db_cultivo)
    db.commit()
    db.refresh(db_cultivo)
    return db_cultivo

# --- PARCELAS ---
def get_parcelas(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Parcela).offset(skip).limit(limit).all()

def create_parcela(db: Session, parcela: schemas.ParcelaCreate):
    db_parcela = models.Parcela(**parcela.dict())
    db.add(db_parcela)
    db.commit()
    db.refresh(db_parcela)
    return db_parcela

# --- INVERNADEROS ---
def get_invernaderos(db: Session, skip: int = 0, limit: int = 100):
    return db.query(models.Invernadero).offset(skip).limit(limit).all()

def create_invernadero(db: Session, invernadero: schemas.InvernaderoCreate):
    db_invernadero = models.Invernadero(**invernadero.dict())
    db.add(db_invernadero)
    db.commit()
    db.refresh(db_invernadero)
    return db_invernadero