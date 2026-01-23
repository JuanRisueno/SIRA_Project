"""
=============================================================================
            Router de Login/JWT (routers/jwt.py)
=============================================================================

Propósito:
Este archivo expone el endpoint '/token'. Es la "puerta" de entrada.
Recibe las credenciales, consulta a la BBDD y, si todo va bien,
usa las herramientas de '../auth.py' para fabricar el token.
"""
from datetime import timedelta
from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session
import bcrypt

# --- Importaciones Relativas (Subimos un nivel con ..) ---
# Importamos 'auth' (las herramientas) y lo demás
from .. import database, schemas, models, crud, auth

router = APIRouter(
    tags=["Autenticación"] # Etiqueta para Swagger
)

@router.post("/token", response_model=schemas.Token)
def login_for_access_token(
    form_data: OAuth2PasswordRequestForm = Depends(), 
    db: Session = Depends(database.get_db)
):
    """
    Recibe usuario (CIF) y contraseña. 
    Si son correctos, devuelve un Token JWT de acceso.
    """
    # 1. Buscar al cliente en la BBDD por su CIF
    cliente = crud.get_cliente_by_cif(db, cif=form_data.username)
    
    login_exception = HTTPException(
        status_code=status.HTTP_401_UNAUTHORIZED,
        detail="Credenciales incorrectas (CIF o Contraseña inválidos)",
        headers={"WWW-Authenticate": "Bearer"},
    )

    # 2. Verificar si el cliente existe
    if not cliente:
        raise login_exception
    
    # 3. Verificar la contraseña
    try:
        # Verificamos usando bcrypt
        if not bcrypt.checkpw(form_data.password.encode('utf-8'), cliente.hash_contrasena.encode('utf-8')):
            raise login_exception
    except Exception:
        raise login_exception

    # 4. Generar el Token
    # Aquí llamamos al archivo 'auth.py' que está en la carpeta padre
    access_token_expires = timedelta(minutes=auth.ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = auth.create_access_token(
        data={"sub": cliente.cif}, 
        expires_delta=access_token_expires
    )
    
    return {"access_token": access_token, "token_type": "bearer"}