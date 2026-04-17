import os
import json
from fastapi import APIRouter, Depends, HTTPException, status
from typing import List
from .. import auth, schemas

router = APIRouter(
    prefix="/api/v1/config",
    tags=["Configuración IoT"],
)

CONFIG_DIR = os.path.join(os.path.dirname(os.path.dirname(__file__)), "config_clientes")

def get_client_config_path(cliente_id: int):
    """Devuelve la ruta al fichero JSON del cliente."""
    return os.path.join(CONFIG_DIR, f"jornada_{cliente_id}.json")

@router.get("/jornada/{cliente_id}", response_model=schemas.ConfigJornada)
def obtener_jornada(
    cliente_id: int,
    current_user: schemas.ClienteRead = Depends(auth.get_current_user)
):
    """Obtiene la configuración de jornada de un cliente."""
    # Seguridad: Solo root/admin o el propio cliente
    if current_user.rol not in ["root", "admin"] and current_user.cliente_id != cliente_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="No tienes permiso para ver esta configuración"
        )

    path = get_client_config_path(cliente_id)
    if not os.path.exists(path):
        # Devolver una estructura vacía/default si no existe
        return schemas.ConfigJornada(default=[])

    try:
        with open(path, "r", encoding="utf-8") as f:
            data = json.load(f)
            return schemas.ConfigJornada(**data)
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error al leer la configuración: {str(e)}"
        )

@router.post("/jornada/{cliente_id}", status_code=status.HTTP_200_OK)
def guardar_jornada(
    cliente_id: int,
    config: schemas.ConfigJornada,
    current_user: schemas.ClienteRead = Depends(auth.get_current_user)
):
    """Guarda la configuración de jornada de un cliente."""
    # Seguridad: Solo root/admin o el propio cliente
    if current_user.rol not in ["root", "admin"] and current_user.cliente_id != cliente_id:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="No tienes permiso para modificar esta configuración"
        )

    # Asegurar que el directorio existe
    if not os.path.exists(CONFIG_DIR):
        os.makedirs(CONFIG_DIR)

    path = get_client_config_path(cliente_id)
    
    try:
        # Convertir a dict usando alias para que las claves sean "0", "1", etc. en el JSON
        data_to_save = config.model_dump(by_alias=True, exclude_none=False)
        
        with open(path, "w", encoding="utf-8") as f:
            json.dump(data_to_save, f, indent=2, ensure_ascii=False)
        
        return {"mensaje": "Configuración guardada correctamente"}
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error al guardar la configuración: {str(e)}"
        )
