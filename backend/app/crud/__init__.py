# Importamos las funciones de cada archivo específico
# Fíjate que usamos el punto (.) seguido del nombre EXACTO del archivo (sin .py)

from .crud_clientes import (
    get_cliente, 
    get_cliente_by_cif, 
    get_clientes, 
    create_cliente
)

from .crud_infraestructura import (
    get_localidad, get_localidades, create_localidad,
    get_parcelas, create_parcela,
    get_invernaderos, create_invernadero,
    get_cultivos, create_cultivo
)

from .crud_dispositivos import (
    get_sensores, create_sensor,
    get_actuadores, create_actuador,
    get_tipos_sensor, create_tipo_sensor,
    get_tipos_actuador, create_tipo_actuador
)

from .crud_operaciones import (
    create_medicion, get_mediciones,
    create_accion,
    create_recomendacion
)
