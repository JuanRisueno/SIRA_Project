# backend/app/routers/jwt.py

from datetime import timedelta
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session

# Importaciones de TU estructura
from app.database import get_db
from app.models import Cliente
from app.schemas import ClienteCreate, ClienteRead, Token
from app.auth import (
    authenticate_user,
    create_access_token,
    get_password_hash,
    ACCESS_TOKEN_EXPIRE_MINUTES,
)

router = APIRouter(prefix="/auth", tags=["Autenticación"])

# 1. REGISTRO
@router.post("/register", response_model=ClienteRead, status_code=status.HTTP_201_CREATED)
def register_user(cliente: ClienteCreate, db: Session = Depends(get_db)):
    # 1. Verificar existencia usando CIF (que es tu usuario real)
    user_existente = db.query(Cliente).filter(Cliente.cif == cliente.cif).first()
    if user_existente:
        raise HTTPException(status_code=400, detail="El usuario con este CIF ya existe")

    # 2. Hashear contraseña (o dejarla plana en modo dev)
    hashed_password = get_password_hash(cliente.password)
    
    # 3. Crear el objeto Cliente mapeando los campos del Schema a la BBDD
    nuevo_cliente = Cliente(
        cif=cliente.cif,                   # Usamos CIF como identificador
        hash_contrasena=hashed_password,   # Columna correcta de la BBDD
        nombre_empresa=cliente.nombre_empresa,
        email_admin=cliente.email_admin,
        telefono=cliente.telefono,
        persona_contacto=cliente.persona_contacto
    )
    
    db.add(nuevo_cliente)
    db.commit()
    db.refresh(nuevo_cliente)
    
    return nuevo_cliente

# 2. LOGIN
@router.post("/token", response_model=Token)
def login_for_access_token(
    form_data: Annotated[OAuth2PasswordRequestForm, Depends()],
    db: Session = Depends(get_db)
):
    # La función authenticate_user ya busca por CIF internamente (gracias al cambio en auth.py)
    user = authenticate_user(db, form_data.username, form_data.password)
    
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Usuario (CIF) o contraseña incorrectos",
            headers={"WWW-Authenticate": "Bearer"},
        )
    
    # CREACIÓN DEL TOKEN
    access_token_expires = timedelta(minutes=ACCESS_TOKEN_EXPIRE_MINUTES)
    
    # ¡AQUÍ ESTABA EL ERROR 500!
    # user.username NO existe. Tenemos que guardar el CIF en el token.
    access_token = create_access_token(
        data={"sub": user.cif},  # <--- CORREGIDO: Usamos user.cif
        expires_delta=access_token_expires
    )
    
    return {"access_token": access_token, "token_type": "bearer"}