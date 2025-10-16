# SIRA Project 🌱💧

> **Sistema Integral de Riego Automático (SIRA)** - Proyecto Fin de Grado para el ciclo de Administración de Sistemas Informáticos en Red (ASIR).

Implementación completa de la infraestructura backend para un sistema de gestión de riego automatizado para invernaderos.

## 🛠️ Stack Tecnológico

El proyecto utiliza un stack tecnológico moderno y estándar en la industria, desplegado íntegramente sobre contenedores Docker.

### Infraestructura y DevOps

| Tecnología | Propósito |
|------------|-----------|
| Git / GitHub | Control de versiones y colaboración mediante Pull Requests |
| Docker / Docker Compose | Entorno de desarrollo aislado y reproducible |
| Ubuntu Server 24.04 LTS | SO base para contenedores |

### Backend y Base de Datos

| Tecnología | Propósito |
|------------|-----------|
| Python / FastAPI | API de alto rendimiento |
| PostgreSQL | Base de datos relacional |
| Nginx | Proxy inverso y servidor web |

## 🚀 Puesta en Marcha

### Prerrequisitos

- Git
- Docker y Docker Compose

### Instalación

1. **Clonar el repositorio**

```bash
git clone https://github.com/tu-usuario/SIRA_Project.git
cd SIRA_Project
```

2. **Configurar variables de entorno**

```bash
cp .env.example .env
# Editar el archivo .env con tus credenciales
```

3. **Desplegar los servicios**

```bash
docker compose up --build -d
```

4. **Verificar la instalación**

```bash
curl http://localhost
# o visita http://localhost en tu navegador
```

Deberías ver:
```json
{"mensaje": "SIRA API está funcionando correctamente!"}
```

## 📋 Comandos Útiles

### Base de Datos

```bash
# Conectarse a PostgreSQL
docker exec -it sira_db psql -U tu_usuario -d sira_db

# Ejemplo práctico:
docker exec -it sira_db psql -U juan -d sira_db
```

> ⚠️ **IMPORTANTE**: El usuario es el que tengas configurado en el archivo `.env`

### Monitoreo y Logs

```bash
# Ver logs de la API
docker logs sira_api -f

# Ver logs de la base de datos
docker logs sira_db -f

# Ver estado de los contenedores
docker compose ps
```

### Gestión del Entorno

```bash
# Parar todos los servicios
docker compose down

# Parar y eliminar volúmenes (reinicio completo)
docker compose down -v

# Reiniciar servicios específicos
docker compose restart sira_api
```

## 📁 Estructura del Proyecto

```
SIRA_Project/
├── api/                # Código de FastAPI
├── database/          # Scripts y migraciones
├── nginx/             # Configuración de Nginx
├── docker-compose.yml
├── .env.example
└── README.md
```

## 🔧 Desarrollo

### Acceso a la Documentación de la API

Una vez ejecutando el proyecto, visita:
- Swagger UI: http://localhost/docs
- ReDoc: http://localhost/redoc

### Variables de Entorno Clave

```env
POSTGRES_USER=tu_usuario
POSTGRES_PASSWORD=tu_password
POSTGRES_DB=sira_db
DATABASE_URL=postgresql://user:pass@sira_db:5432/sira_db
```

## 🤝 Contribución

1. Fork del proyecto
2. Crear una rama feature (`git checkout -b feature/nueva-caracteristica`)
3. Commit de cambios (`git commit -am 'Añadir nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto es desarrollado como Proyecto Fin de Grado para ASIR.

## 🔗 Enlaces Rápidos

- [Documentación de FastAPI](https://fastapi.tiangolo.com/)
- [Documentación de PostgreSQL](https://www.postgresql.org/docs/)
- [Documentación de Docker](https://docs.docker.com/)

¡¡¡ IMPORTANTE !!!

Conectarse a PostgreSQL
docker exec -it sira_db psql -U tu_usuario -d sira_db

Ejemplo práctico:
docker exec -it sira_db psql -U juan -d sira_db

El usuario es el que tengas configurado en el archivo .env