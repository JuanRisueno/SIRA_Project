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
"""
from fastapi import FastAPI

# Importaciones locales
from . import models
from .database import engine

# --- IMPORTACIÓN DE ROUTERS ---
# Único router existente a fecha de hoy.
from .routers import datos_maestros

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

# --- 4. ENDPOINT DE VERIFICACIÓN ---
@app.get("/")
def read_root():
    """Endpoint para comprobar desde Postman que el servidor arranca."""
    return {"mensaje": "SIRA API v1.0: Sistema Operativo y Escuchando."}