# Guía de Backups y Recuperación (PostgreSQL en Docker)

Dado que la base de datos corre dentro de un contenedor Docker aislado (`sira_db`), las copias de seguridad no se realizan con los comandos clásicos del host, sino ejecutándolos **a través del sistema Docker**.

Esta guía indica cómo realizar copias de la información contenida en el volumen persistente `postgres_data`.

## 1. Crear una Copia de Seguridad (Dump) de la Información Actual

Para extraer absolutamente todos los datos (esquemas e información real) de la base de datos en funcionamiento y guardarlos en tu máquina física:

```bash
docker exec -t sira_db pg_dump -U usuario -d sira_db > backup_sira_$(date +%F).sql
```
*(Asegúrate de cambiar `usuario` y `sira_db` por los valores reales de tus variables en el fichero `.env` si los has modificado)*.

Esto generará un archivo SQL en tu carpeta actual con el nombre `backup_sira_YYYY-MM-DD.sql`.

## 2. Restaurar una Copia de Seguridad

Si ocurre un desastre, pierdes el volumen, o ejecutas por error `docker compose down -v`, puedes restaurar los datos de manera limpia.

1. Asegúrate de que los contenedores están arrancados limpios y existiendo la BBDD:
   ```bash
   docker compose up -d
   ```
2. Inyecta el archivo de backup en el contenedor de base de datos de manera directa:
   ```bash
   cat backup_sira_2026-03-22.sql | docker exec -i sira_db psql -U usuario -d sira_db
   ```

## 3. Notas Importantes sobre el Volumen
Los datos persisten en el Host (tu ordenador o servidor en la nube) de manera transparente gracias al volumen nombrado en el `docker-compose.yml`. Si quieres borrar por completo la base de datos para empezar desde cero:

1. Borrar contenedores Y volúmenes:
   ```bash
   docker compose down -v
   ```
2. Volver a levantar todo (PostgreSQL leerá de nuevo la estructura vacía desde el inicio para crear tablas nuevas usando los scripts o auto-generación de SQLAlchemy):
   ```bash
   docker compose up --build -d
   ```
