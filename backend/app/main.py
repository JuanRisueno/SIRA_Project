"""
=============================================================================
               Archivo Principal de la API (main.py)
=============================================================================

Propósito:
Este archivo es el punto de entrada principal (entrypoint) para la API REST de SIRA,
construida con el framework FastAPI. Es el archivo que 'uvicorn' ejecuta (según
el Dockerfile) para iniciar el servidor.

¿QUÉ ES ESTA API Y POR QUÉ LA NECESITAMOS?
Una API (Interfaz de Programación de Aplicaciones) actúa como un
intermediario seguro entre nuestros datos (PostgreSQL) y el mundo exterior.
Es fundamental para el proyecto SIRA por tres razones:
1.  Seguridad: Impide el acceso directo a la Base de Datos. La API actúa como
    un "filtro" que valida quién entra y qué datos envía.
2.  Interoperabilidad: Permite que diferentes sistemas (la Web de Gestión,
    la Raspberry Pi con los sensores, o una futura App móvil) se comuniquen
    con la misma base de datos usando un lenguaje común (JSON).
3.  Lógica de Negocio: Centraliza las decisiones inteligentes (ej. cuándo regar)
    en un solo lugar, independientemente de desde dónde se consulte.

Responsabilidades Técnicas de este archivo:
1.  Crear la instancia principal de la aplicación FastAPI (`app = FastAPI()`).
2.  Gestionar el ciclo de vida de la conexión a la BBDD (crear tablas si no existen).
3.  Importar y "conectar" los diferentes módulos de 'routers' (ej. los
    endpoints para Clientes, Parcelas, Sensores) a la aplicación principal.
4.  Definir y configurar 'middleware' de la aplicación, como CORS (para
    permitir peticiones desde el frontend) y la seguridad JWT.
"""
from fastapi import FastAPI, Depends, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session

# Importaciones locales
from . import models
from .database import SessionLocal, engine

# --- 1. CREACIÓN DE TABLAS (Sincronización) ---
# Esta línea le dice a SQLAlchemy: "Mira models.py y asegúrate de que todas
# las tablas definidas allí existan en la BBDD".
# Si ya existen (gracias a 10-schema.sql), no hace nada. Es una red de seguridad.
models.Base.metadata.create_all(bind=engine)

# --- 2. INICIALIZACIÓN DE LA APP ---
app = FastAPI(
    title="SIRA API",
    description="Backend para el Sistema Integral de Riego Automático",
    version="1.0.0"
)

# --- 3. CONFIGURACIÓN CORS (Seguridad) ---
# Definimos quién tiene permiso para hablar con esta API.
# En desarrollo permitimos todo ("*"), pero en producción esto se restringe.
origins = ["*"]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_credentials=True,
    allow_methods=["*"], # Permitir GET, POST, PUT, DELETE...
    allow_headers=["*"],
)

# --- 4. DEPENDENCIA DE BASE DE DATOS (Clave para el examen) ---
# Esta función es la "llave maestra". En lugar de abrir/cerrar conexiones manuales
# en cada endpoint, usamos esto con 'Depends(get_db)'.
#
# Funcionamiento (yield):
# 1. Cuando llega una petición, crea una sesión (db).
# 2. Entrega la sesión al endpoint para que trabaje.
# 3. Cuando el endpoint termina (aunque falle), ejecuta el 'finally' y cierra la sesión.
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# --- 5. ENDPOINTS GENERALES ---

@app.get("/")
def read_root():
    """Endpoint de prueba para verificar que la API está viva."""
    return {"mensaje": "SIRA API v1.0: Sistema Operativo y Escuchando."}

# NOTA: Aquí abajo importaremos los 'routers' cuando los creemos en la Tarea 8.
# Ejemplo: app.include_router(clientes.router)
