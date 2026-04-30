# backend/app/auth.py

"""
=============================================================================
            Módulo de Autenticación y Seguridad (auth.py)
=============================================================================
Propósito:
Gestionar la seguridad de la API mediante JWT y hashing de contraseñas Bcrypt.
"""

import os
from datetime import datetime, timedelta, timezone
from typing import Optional, Annotated

import bcrypt
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import jwt, JWTError
from sqlalchemy.orm import Session

# --- IMPORTACIONES LOCALES ---
from .database import get_db
from .models import Cliente
from . import schemas

# --- CONFIGURACIÓN ---
# Prioridad: Variable de entorno > Valor por defecto seguro
SECRET_KEY = os.getenv("JWT_SECRET_KEY", "SIRA_SECRET_KEY_SUPER_SECRETA_PARA_DESARROLLO")
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 1440 # 24 horas (El timeout real por inactividad de 30m se controla en DB)
# ... (rest of the file until get_current_user)
    # CONTROL DE CONCURRENCIA (Iron Fortress)
    # Comparamos el SID del token con el guardado en la base de datos.
    if not token_sid or token_sid != user.session_id:
        raise session_invalidated_exception
    
    # SLIDING WINDOW TIMEOUT (30 Minutos de Inactividad)
    # Comprobamos si la última actividad fue hace más de 30 minutos
    from datetime import datetime, timezone, timedelta
    if user.ultima_actividad:
        ahora = datetime.now(timezone.utc)
        ultima_act = user.ultima_actividad
        if ultima_act.tzinfo is None:
            ultima_act = ultima_act.replace(tzinfo=timezone.utc)
        
        if (ahora - ultima_act) > timedelta(minutes=30):
            # Sesión expirada por inactividad
            user.session_id = None
            db.commit()
            raise session_invalidated_exception

    # MONITOR DE ACTIVIDAD (Iron Fortress)
    # Actualizamos la huella digital del usuario en cada interacción (renovando el timeout)
    from sqlalchemy.sql import func
    user.ultima_actividad = func.now()
    db.commit()
        
    return user
        
    return user

async def get_current_user_optional(token: Optional[str] = Depends(oauth2_scheme_optional), db: Session = Depends(get_db)):
    """Permite el paso aunque no haya token, pero identifica al usuario si existe."""
    if not token:
        return None
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        cif_usuario: str = payload.get("sub")
        if cif_usuario is None:
            return None
    except JWTError:
        return None

    return db.query(Cliente).filter(Cliente.cif == cif_usuario).first()


# ==========================================
# 5. CONTROL DE ROLES
# ==========================================

def require_admin(current_user: Cliente = Depends(get_current_user)):
    """Solo permite el paso a administradores o root."""
    if current_user.rol not in ["admin", "root"]:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Operación no permitida. Requiere permisos de administrador."
        )
    return current_user

def require_root(current_user: Cliente = Depends(get_current_user)):
    """Solo permite el paso al superusuario root."""
    if current_user.rol != "root":
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Operación no permitida. Requiere permisos de superusuario (root)."
        )
    return current_user