"""
=============================================================================
               Archivo Principal de la API (main.py)
=============================================================================

Propósito:
Este archivo es el punto de entrada principal (entrypoint) para la API REST de SIRA.
Es el "Director de Orquesta" que inicializa la aplicación y conecta las partes.

Responsabilidades Técnicas:
1.  Inicializar FastAPI.
2.  Configurar CORS.
3.  Gestionar la conexión a BBDD.
4.  CONECTAR LOS ROUTERS (NUEVO).
"""

from fastapi import FastAPI, Depends, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session

# Importaciones locales
from . import models
from .database import SessionLocal, engine

# --- IMPORTACIÓN DE ROUTERS (NUEVO) ---
# Importamos el archivo que acabamos de crear en la carpeta routers
from .routers import datos_maestros

# --- 1. CREACIÓN DE TABLAS ---
models.Base.metadata.create_all(bind=engine)

# --- 2. INICIALIZACIÓN DE LA APP ---
app = FastAPI(
    title="SIRA API",
    description="Backend para el Sistema Integral de Riego Automático",
    version="1.0.0"
)

# --- 3. CONFIGURACIÓN CORS ---
origins = ["*"]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- 4. DEPENDENCIA DE BASE DE DATOS ---
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# --- 5. CONEXIÓN DE ROUTERS (NUEVO) ---
# Aquí "enchufamos" el departamento de Clientes/Parcelas a la API principal.
# Ahora la API ya sabe responder a /clientes/ y /parcelas/
app.include_router(datos_maestros.router)


# --- 6. ENDPOINTS GENERALES ---
@app.get("/")
def read_root():
    """Endpoint de prueba para verificar que la API está viva."""
    return {"mensaje": "SIRA API v1.0: Sistema Operativo y Escuchando."}