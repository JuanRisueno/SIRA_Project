# backend/app/auth.py

from datetime import datetime, timedelta, timezone
from typing import Optional, Annotated

from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer
from jose import jwt, JWTError
from sqlalchemy.orm import Session

# --- IMPORTACIONES DE TU PROYECTO ---
from app.database import get_db
from app.models import Cliente  # Asegúrate de que Cliente está en models.py

# --- CONFIGURACIÓN ---
# En producción, esto debería ir en el .env
SECRET_KEY = "tu_clave_secreta_super_segura_para_el_tfg"
ALGORITHM = "HS256"
ACCESS_TOKEN_EXPIRE_MINUTES = 30

# Definimos dónde tiene que mirar FastAPI para buscar el token
oauth2_scheme = OAuth2PasswordBearer(tokenUrl="auth/token")


# ==========================================
# 1. FUNCIONES DE SEGURIDAD (MODO DEV)
# ==========================================

def verify_password(plain_password, hashed_password):
    """
    MODO DEV: Compara la contraseña tal cual (texto plano).
    Si estuvieras en producción, aquí usaríamos bcrypt.verify()
    """
    return plain_password == hashed_password

def get_password_hash(password):
    """
    MODO DEV: Devuelve la contraseña sin encriptar.
    Si estuvieras en producción, aquí usaríamos bcrypt.hash()
    """
    return password


# ==========================================
# 2. GESTIÓN DE TOKENS (JWT)
# ==========================================

def create_access_token(data: dict, expires_delta: Optional[timedelta] = None):
    to_encode = data.copy()
    if expires_delta:
        expire = datetime.now(timezone.utc) + expires_delta
    else:
        expire = datetime.now(timezone.utc) + timedelta(minutes=15)
    
    to_encode.update({"exp": expire})
    encoded_jwt = jwt.encode(to_encode, SECRET_KEY, algorithm=ALGORITHM)
    return encoded_jwt


# ==========================================
# 3. LÓGICA DE AUTENTICACIÓN (LOGIN)
# ==========================================

def authenticate_user(db: Session, username: str, password: str):
    """
    Recibe las credenciales del formulario de Swagger/Frontend.
    
    NOTA IMPORTANTE DE MAPEO:
    - El formulario envía un campo llamado 'username'.
    - Nosotros lo buscamos en la columna 'cif' de la base de datos.
    """
    # Buscamos por CIF
    user = db.query(Cliente).filter(Cliente.cif == username).first()
    
    if not user:
        return False
    
    # Comprobamos contraseña (campo 'hash_contrasena' de la BBDD)
    if not verify_password(password, user.hash_contrasena):
        return False
    
    return user


# ==========================================
# 4. DEPENDENCIA: OBTENER USUARIO ACTUAL
# ==========================================

async def get_current_user(token: Annotated[str, Depends(oauth2_scheme)], db: Session = Depends(get_db)):
    """
    Esta función protege las rutas.
    1. Lee el token.
    2. Extrae el CIF (que guardamos en el campo 'sub').
    3. Busca al cliente en la BBDD.
    """
    credentials_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="No se pudieron validar las credenciales",
        headers={"WWW-Authenticate": "Bearer"},
    )
    
    try:
        # Decodificamos el token
        payload = jwt.decode(token, SECRET_KEY, algorithms=[ALGORITHM])
        
        # Extraemos el CIF (el estándar JWT usa 'sub' para el ID del usuario)
        cif_usuario: str = payload.get("sub")
        
        if cif_usuario is None:
            raise credentials_exception
            
    except JWTError:
        raise credentials_exception

    # Buscamos al usuario en la BBDD usando el CIF recuperado
    user = db.query(Cliente).filter(Cliente.cif == cif_usuario).first()
    
    if user is None:
        raise credentials_exception
        
    return user