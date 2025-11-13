"""
=============================================================================
               Archivo Principal de la API (main.py)
=============================================================================

Propósito:
Este archivo es el punto de entrada principal (entrypoint) para la API de SIRA,
construida con FastAPI.

Es el archivo que 'uvicorn' ejecuta (según el Dockerfile) para iniciar el servidor.

Responsabilidades Clave:
1.  Crear la instancia principal de la aplicación FastAPI (`app = FastAPI()`).
2.  Importar y "conectar" los diferentes módulos de 'routers' (ej. los
    endpoints para Clientes, Parcelas, Sensores) a la aplicación principal.
3.  Definir los 'schemas' (Pydantic) y los 'models' (SQLAlchemy) que
    utilizarán los endpoints (aunque estos se importan desde sus
    propios archivos: schemas.py y models.py).
4.  (En el futuro) Configurar 'middleware' de la aplicación, como CORS (para
    permitir peticiones desde el frontend) y la seguridad JWT (para la
    autenticación).
"""
from fastapi import FastAPI
from . import models
from .database import engine, SessionLocal

# Crea la instancia principal de la aplicación FastAPI
app = FastAPI()

@app.get("/")
def read_root():
    return {"mensaje": "SIRA API está funcionando correctamente!"}

@app.get("/test-clientes/")
def test_leer_clientes():
    db = SessionLocal() # Pide una sesión a la fábrica
    try:
        # --- ESTA ES LA PRUEBA ---
        # SQLAlchemy intentará mapear models.Cliente al 10-schema.sql
        clientes = db.query(models.Cliente).all()

        # Si tiene éxito, devolverá una lista vacía
        return {"status": "OK", "clientes": clientes}

    except Exception as e:
        # Si falla (ej. los nombres no coinciden), dará un error
        return {"status": "ERROR", "detalle": str(e)}
    finally:
        db.close()