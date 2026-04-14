from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List, Optional
from .. import crud, models, schemas
from ..database import get_db

router = APIRouter(
    prefix="/api/v1/iot",
    tags=["Telemetría e IoT"]
)

# --- SENSORES ---
@router.get("/sensores/invernadero/{invernadero_id}", response_model=List[schemas.Sensor])
def listar_sensores_invernadero(invernadero_id: int, db: Session = Depends(get_db)):
    return db.query(models.Sensor).filter(models.Sensor.invernadero_id == invernadero_id).all()

# --- MEDICIONES ---
@router.get("/mediciones/sensor/{sensor_id}", response_model=List[schemas.Medicion])
def listar_mediciones_sensor(sensor_id: int, limit: int = 20, db: Session = Depends(get_db)):
    return db.query(models.Medicion)\
             .filter(models.Medicion.sensor_id == sensor_id)\
             .order_by(models.Medicion.fecha_hora.desc())\
             .limit(limit).all()

@router.post("/mediciones/", response_model=schemas.Medicion, status_code=status.HTTP_201_CREATED)
def crear_medicion(medicion: schemas.MedicionCreate, db: Session = Depends(get_db)):
    from ..crud import crud_operaciones
    return crud_operaciones.create_medicion(db=db, medicion=medicion)
