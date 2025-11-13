# 1. Usar una imagen oficial de Python como base. 'slim' es una versión ligera.
FROM python:3.11-slim

# 2. Establecer el directorio de trabajo dentro del contenedor.
# A partir de aquí, todos los comandos se ejecutan en /app.
WORKDIR /app

# 3. Copiar el fichero de dependencias y luego instalarlas.
# Se hace en dos pasos para aprovechar la caché de Docker y acelerar futuras builds.
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# 4. Copiar todo el código de tu aplicación a la carpeta /app del contenedor.
COPY ./app .

# 5. El comando que se ejecutará para iniciar la API cuando arranque el contenedor.
# El host 0.0.0.0 es crucial para que la API sea accesible desde fuera de su propio contenedor.
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]