# Archivos SQL de GesTIck

Esta carpeta contiene los entregables de persistencia de la segunda entrega.

## Archivos

- `gestick_ddl.sql`: crea la base `gestick`, sus 27 tablas, claves primarias, índices y 35 claves foráneas.
- `gestick_datos_prueba.sql`: carga datos relacionados en las 27 tablas.

## Orden de importación

1. Abrir MySQL Workbench y conectarse al servidor.
2. Abrir y ejecutar `gestick_ddl.sql`.
3. Abrir y ejecutar `gestick_datos_prueba.sql`.
4. Actualizar la lista de esquemas y comprobar la base `gestick`.

El DDL contiene instrucciones para recrear las tablas. No debe ejecutarse sobre una base que se quiera conservar sin realizar antes una copia de respaldo.

## Cuentas de prueba

Todas utilizan la contraseña `GesTIck2026!`, almacenada mediante `password_hash`.

| Rol | Correo |
|---|---|
| Administrador | `admin@gestick.test` |
| Técnico | `tecnico1@gestick.test` |
| Docente | `docente1@gestick.test` |

Los nombres, correos y contraseñas de las cuentas reales no se incluyen en el archivo DML.

## Justificación de la tercera forma normal (3FN)

El esquema se organizó para evitar datos repetidos y dependencias innecesarias:

- Cada tabla posee una clave primaria que identifica sus registros.
- Los campos contienen valores simples y atómicos.
- Los datos de catálogo están separados en tablas como `estado_ticket`, `estado_solicitud`, `estado_equipo`, `estado_prestamo`, `prioridad`, `turno`, `marca` y `tipo_equipo`.
- Las relaciones de muchos a muchos utilizan tablas intermedias como `docente_asignatura`, `docente_grupo`, `equipo_componente` y `prestamo_equipo`.
- La información común de acceso se guarda en `usuario`, mientras que `administrador`, `docente` y `tecnico` almacenan la información propia de cada rol.
- Tickets, solicitudes, planillas y préstamos guardan identificadores relacionados mediante claves foráneas en lugar de repetir nombres o descripciones de otras entidades.

De esta manera, cada atributo depende de la clave de su tabla y los cambios en catálogos o usuarios se realizan en un único lugar.
