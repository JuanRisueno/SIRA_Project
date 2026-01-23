# backend/app/auth.py

from datetime import datetime, timedelta, timezone
from typing import Optional
import jwt # Asegúrate de que python-jose o pyjwt esté instalado

# --- MODO DEV: LIBRERÍAS DE SEGURIDAD COMENTADAS ---
# from passlib.context import CryptContext

# Configuración JWT (Esto sí lo mantenemos para generar tokens)
SECRET_KEY = "tu_clave_secreta_super_segura_para_el_tfg"
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

# --- MODO DEV: CONTEXTO DE HASH COMENTADO ---
# pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")

# 1. FUNCIÓN DE VERIFICACIÓN (TRUCADA)
def verify_password(plain_password, hashed_password):
    """
    MODO DEV: Compara texto plano con texto plano.
    """
    return plain_password == hashed_password

    # --- MODO PRO (Comentado) ---
    # return pwd_context.verify(plain_password, hashed_password)

# 2. FUNCIÓN DE HASH (TRUCADA)
def get_password_hash(password):
    """
    MODO DEV: Devuelve la contraseña sin tocar.
    """
    return password

    # --- MODO PRO (Comentado) ---
    # return pwd_context.hash(password)

# 3. GENERACIÓN DE TOKEN (ESTO SIGUE IGUAL PARA QUE FUNCIONE EL LOGIN)
def create_access_token(data: dict, expires_delta: Optional[timedelta] = None):
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.now(timezone.utc) + expires_delta
    else:
        expire = datetime.now(timezone.utc) + timedelta(minutes=15)
    
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt

# 4. AUTENTICACIÓN DE USUARIO (USANDO LA VERIFICACIÓN TRUCADA)
from sqlmodel import Session, select
from app.models import Cliente 

def authenticate_user(session: Session, username: str, password: str):
    # Buscamos al usuario
    statement = select(Cliente).where(Cliente.username == username)
    user = session.exec(statement).first()
    
    if not user:
        return False
    
    # Aquí llamamos a nuestra verify_password trucada
    if not verify_password(password, user.password):
        return False
    
    return user