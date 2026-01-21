from sqlalchemy.orm import Session
from .. import models, schemas

# --- MEDICIONES ---
def create_medicion(db: Session, medicion: schemas.MedicionCreate):
    db_medicion = models.Medicion(**medicion.dict())
    db.add(db_medicion)
    db.commit()
    db.refresh(db_medicion)
    return db_medicion

def get_mediciones(db: Session, skip: int = 0, limit: int = 1000):
    # Limitamos a 1000 por defecto porque pueden haber millones
    return db.query(models.Medicion).order_by(models.Medicion.fecha_hora.desc()).offset(skip).limit(limit).all()

# --- ACCIONES DE ACTUADORES ---
def create_accion(db: Session, accion: schemas.AccionActuadorCreate):
    db_accion = models.AccionActuador(**accion.dict())
    db.add(db_accion)
    db.commit()
    db.refresh(db_accion)
    return db_accion

# --- RECOMENDACIONES DE RIEGO ---
def create_recomendacion(db: Session, recomendacion: schemas.RecomendacionRiegoCreate):
    db_rec = models.RecomendacionRiego(**recomendacion.dict())
    db.add(db_rec)
    db.commit()
    db.refresh(db_rec)
    return db_rec