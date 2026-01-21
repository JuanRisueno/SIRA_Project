"""
=============================================================================
            Módulo de Autenticación y Seguridad (auth.py)
=============================================================================

Propósito:
Este archivo maneja toda la lógica de seguridad "invisible" de la API:
1. Configuración de encriptación (Clave secreta y algoritmo).
2. Funciones para crear Tokens JWT (Las "pulseras" de acceso).
3. Dependencia 'get_current_user' para proteger rutas.

Flujo:
Login -> Crea Token -> Usuario guarda Token -> Usuario envía Token en cada petición -> API verifica Token.
"""

# --- Importaciones Estándar ---
from datetime import datetime, timedelta
from typing import Optional

# --- Importaciones de Terceros ---
from jose import JWTError, jwt  # Para crear y leer tokens
from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer # El estándar de seguridad web
from sqlalchemy.orm import Session

# --- Importaciones Locales ---
from . import crud, models, schemas
from .database import get_db

# =============================================================================
# 1. CONFIGURACIÓN (VARIABLES DE ENTORNO SIMULADAS)
# =============================================================================
# IMPORTANTE: En producción, esto debería leerse de un archivo .env
# SECRET_KEY: Es la "firma" del servidor. Si cambia, todos los tokens antiguos dejan de valer.
# Puedes generar una segura ejecutando en terminal: openssl rand -hex 32
SECRET_KEY = "SIRA_SECRET_KEY_SUPER_SECRETA_PARA_DESARROLLO" 
ALGORITHM = "HS256" # El algoritmo estándar de encriptación
ACCESS_TOKEN_EXPIRE_MINUTES = 30 # Las "pulseras" caducan a los 30 minutos

# OAuth2PasswordBearer: Le dice a FastAPI que el cliente enviará el token
# en la cabecera "Authorization: Bearer <token>"
# "tokenUrl" es la ruta donde el usuario debe ir a loguearse (la crearemos luego).
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="token")


# =============================================================================
# 2. FUNCIÓN GENERADORA DE TOKENS (El Fabricante de Pulseras)
# =============================================================================
def create_access_token(data: dict, expires_delta: Optional[timedelta] = None):
    """
    Recibe los datos del usuario (ej: CIF) y crea un string encriptado (JWT).
    """
    to_encode = data.copy()
    
    # Calcular fecha de caducidad
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(minutes=15)
    
    # Añadimos la caducidad al contenido del token
    to_encode.update({"exp": expire})
    
    # "Firmamos" el token con nuestra clave secreta
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    
    return encoded_jwt


# =============================================================================
# 3. DEPENDENCIA DE VERIFICACIÓN (El Portero)
# =============================================================================
async def get_current_user(token: str = Depends(oauth2_scheme), db: Session = Depends(get_db)):
    """
    Esta función se usará en los endpoints protegidos (ej: ver parcelas).
    1. Lee el token de la petición.
    2. Lo decodifica y busca el usuario (CIF) dentro.
    3. Verifica si ese usuario existe en la BBDD.
    Si todo es OK, devuelve el objeto 'usuario'. Si no, lanza error 401.
    """
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Credenciales no válidas o token expirado",
        headers={"WWW-Authenticate": "Bearer"},
    )
    
    try:
        # Intentamos decodificar el token
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        
        # En el login (que haremos luego), guardaremos el CIF en el campo "sub" del token
        cif: str = payload.get("sub")
        
        if cif is None:
            raise credentials_exception
            
        # Creamos un pequeño esquema temporal con los datos del token
        token_data = schemas.TokenData(cif=cif)
        
    except JWTError:
        # Si el token es falso, ha caducado o la firma no coincide
        raise credentials_exception
        
    # Validar contra la Base de Datos real
    user = crud.get_cliente_by_cif(db, cif=token_data.cif)
    
    if user is None:
        raise credentials_exception
        
    return user