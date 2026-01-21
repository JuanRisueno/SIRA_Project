"""
=============================================================================
            Archivo Principal de la API (main.py)
=============================================================================

Propósito:
Este archivo es el punto de entrada principal (entrypoint) para la API REST de SIRA.
Es el archivo que inicializa la aplicación y conecta las partes.

Responsabilidades Técnicas:
1.  Inicializar FastAPI.
2.  Gestionar la conexión a BBDD.
3.  Conectar los ROUTERS.
4.  ENDPOINT de verificación.
5.  GESTIÓN DE AUTENTICACIÓN (Login).
"""
# --- Importaciones de Framework y Utilidades ---
from datetime import timedelta
from fastapi import FastAPI, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session
import bcrypt  # Necesario para verificar la contraseña hasheada

# --- Importaciones Locales ---
from . import models, crud, schemas, auth
from .database import engine, get_db
from .routers import datos_maestros, jwt

# --- 1. CREACIÓN DE TABLAS ---
# Genera las tablas en PostgreSQL al arrancar si no existen.
models.Base.metadata.create_all(bind=engine)

# --- 2. INICIALIZACIÓN DE LA APP ---
app = FastAPI(
    title="SIRA API",
    description="Backend para el Sistema Integral de Riego Automático",
    version="1.0.0"
)

# --- 3. CONEXIÓN DE ROUTERS ---
# Habilitamos los endpoints de Clientes, Localidades, Parcelas e Invernaderos.
app.include_router(datos_maestros.router)
app.include_router(jwt.router)


# --- 4. ENDPOINT DE VERIFICACIÓN ---
@app.get("/")
def read_root():
    """Endpoint para comprobar desde Postman que el servidor arranca."""
    return {"mensaje": "SIRA API v1.0: Sistema Operativo y Escuchando."}