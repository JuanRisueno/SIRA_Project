# SIRA Project 🌱💧

> Sistema Integral de Riego Automático (SIRA) — Proyecto Fin de Grado (ASIR).

> Backend en Python (FastAPI) para gestionar sensores, zonas y actuadores en invernaderos. Preparado para ejecutarse con Docker Compose (PostgreSQL) y servirse detrás de Nginx.

[![status: draft](https://img.shields.io/badge/status-draft-orange)](#) [![docker](https://img.shields.io/badge/docker-enabled-blue)](#) [![license](https://img.shields.io/badge/license-MIT-lightgrey)](#)

---

## Contenido
- [Descripción](#descripción)
- [Requisitos](#requisitos)
- [Inicio rápido — Docker Compose (recomendado)](#inicio-rápido--docker-compose-recomendado)
- [Ejecución local (sin Docker)](#ejecución-local-sin-docker)
- [Variables de entorno](#variables-de-entorno)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Documentación de la API](#documentación-de-la-api)
- [Comandos útiles](#comandos-útiles)
- [Pruebas rápidas](#pruebas-rápidas)
- [Contribuir](#contribuir)
- [Licencia y contacto](#licencia-y-contacto)
- [Próximos pasos sugeridos](#próximos-pasos-sugeridos)

---

## Descripción

SIRA (Sistema Integral de Riego Automático) es un backend REST (FastAPI) para la gestión de sensores, zonas y actuadores en invernaderos. Está pensado para ser usado en desarrollo local y desplegado en contenedores con Docker Compose.

## Requisitos

- Git
- Docker (>= 20.x) y Docker Compose (subcomando `docker compose` recomendado)
- Python 3.10+ (solo si ejecutas en local sin Docker)

## Inicio rápido — Docker Compose (recomendado)

1. Clona el repositorio y entra en la raíz:

`bash
git clone [https://github.com/](https://github.com/)<tu-usuario>/SIRA_Project.git
cd SIRA_Project


2.  Crea el archivo de entorno a partir del ejemplo y edítalo:

<!-- end list -->

`bash
cp .env.example .env
# Rellena DB_USER, DB_PASSWORD, DB_NAME, etc.
`

3.  Arranca los servicios (background):

<!-- end list -->

`bash
docker compose up --build -d
`

4.  Comprueba estado y logs:

<!-- end list -->

`bash
docker compose ps
docker compose logs -f
`

5.  Accede a la API y su documentación:

<!-- end list -->

  - Nginx (puerto 80): http://localhost/ (según `nginx/nginx.conf`)
  - Swagger UI (FastAPI): http://localhost/docs
  - ReDoc: http://localhost/redoc

Parar/limpiar:

`bash
docker compose down
`

Reiniciar solo la API:

`bash
docker compose restart api
`

## Ejecución local (sin Docker)

Para desarrollo rápido sin contenedores:

`bash
cd backend
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
`

Docs locales: http://localhost:8000/docs (Swagger) y /redoc (ReDoc).

## Variables de entorno

Usa `.env` en la raíz (añádelo a `.gitignore`). Ejemplo mínimo en `.env.example`:

`env
DB_USER=tu_usuario
DB_PASSWORD=tu_password
DB_NAME=sira_db
# La API suele usar: DATABASE_URL=postgresql://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}
`

Notas:

  - El servicio de base de datos en `docker-compose.yml` se llama `db`.
  - En el contenedor `api` la variable `DATABASE_URL` está construida apuntando a `db`.

## Estructura del proyecto

`
SIRA_Project/
├─ backend/               # Código del backend (FastAPI)
│  ├─ Dockerfile
│  ├─ requirements.txt
│  └─ app/
│     └─ main.py
├─ nginx/
│  └─ nginx.conf
├─ docker-compose.yml
├─ .env.example
└─ README.md
`

## Documentación de la API

FastAPI expone documentación automática cuando la app está en ejecución:

  - Swagger UI: `/docs`
  - ReDoc: `/redoc`

Si Nginx hace proxy en el puerto 80, usa `http://localhost/docs`.

## Comandos útiles

  - Levantar (foreground):

<!-- end list -->

`bash
docker compose up
`

  - Levantar (detached):

<!-- end list -->

`bash
docker compose up -d
`

  - Estado de servicios:

<!-- end list -->

`bash
docker compose ps
`

  - Logs del API:

<!-- end list -->

`bash
docker compose logs -f api
`

  - Acceso a Postgres (contenedor `sira_db`):

<!-- end list -->

`bash
docker exec -it sira_db psql -U ${DB_USER} -d ${DB_NAME}
`

## Pruebas rápidas

  - Comprobar que la API responde (si has añadido un endpoint /health):

<!-- end list -->

`bash
curl -sS http://localhost:8000/health || echo "API no responde"
`

  - Probar docs:

<!-- end list -->

`bash
curl -s http://localhost:8000/docs | head -n 20
`

## Contribuir

1.  Haz fork y crea una rama: `git checkout -b feature/<nombre>`.
2.  Realiza commits pequeños y descriptivos.
3.  Añade tests para cambios importantes.
4.  Abre PR describiendo los cambios y cómo probarlos.

## Licencia

Por defecto: MIT — ajústala si procede.

Copyright (c) 2025 Juan Risueno

## Autor / Contacto

  - Juan Risueno
  - Email: risu.profesional@gmail.com

-----

## Próximos pasos sugeridos

  - Añadir badges CI / coverage en la cabecera.
  - Crear `.github/ISSUE_TEMPLATE` y `.github/PULL_REQUEST_TEMPLATE`.
  - Añadir un workflow de GitHub Actions (lint + tests).
  - Incluir ejemplos de requests en `examples/`.

<!-- end list -->
