# Control de Inscripciones a Eventos Académicos

Parcial 2 - Programación para Internet (UES).

> **Para que quede en los libros de historia:** este repositorio es el testimonio vivo de aquel tormentoso parcial de **Cheyo**. Arrancamos a la **1:00 PM** con toda la fe del mundo y terminamos saliendo a las **6:00 PM**, en equipo, con el estrés por las nubes, los nervios a flor de piel y peleando hasta el último minuto contra conexiones, sesiones, `mysqli` vs `PDO`, phpMyAdmin y el hosting. Nos sacó canas verdes a todos, pero el equipo aguantó el vendaval y lo sacó adelante.
>
> **Mención de honor al banquillo de IAs:** en el momento más crítico de la tormenta, **Claude Opus 5.5** se puso la 10 y acarreó cuando todo parecía caerse a pedazos; pero que nunca se ignore ni se borre de la memoria el arduo trabajo, la constancia y el lomo que puso **Gemini** remando durante toda la jornada. Sin todo el equipo —humanos e IAs— esta hazaña no se contaba.

## Estructura del proyecto
- `config/` - Conexión a base de datos (`config.php`, `db.php`, `conexion.php`) y control de sesiones/roles (`auth.php`)
- `database/schema.sql` - Script SQL base con `CREATE DATABASE`
- `database/restauracion.sql` - Script SQL de restauración listo para phpMyAdmin (tablas + usuarios iniciales)
- `auth/` - Login, logout y registro
- `estudiantes/` - Registro de estudiantes
- `eventos/` - Gestión de eventos
- `inscripciones/` - Inscripción a eventos
- `asistencia/` - Registro de asistencia
- `informes/` - Informe de eventos inscritos y asistencia por estudiante
- `assets/` - CSS y JS compartidos

## Restauración de la base de datos
Para levantar la base desde cero (tanto en local como en hosting compartido como InfinityFree), selecciona tu base de datos en **phpMyAdmin** y ejecuta [`database/restauracion.sql`](database/restauracion.sql).

El script crea todas las tablas con `IF NOT EXISTS` (sin `CREATE DATABASE` ni `USE` para evitar errores de permisos en hosting) y deja listos dos usuarios de prueba:

| Rol | Usuario | Clave |
|---|---|---|
| Administrador | `admin` | `password` |
| Estudiante (`PE25001`) | `estudiante1` | `password` |

*(Cambia estas claves en cualquier entorno que no sea de pruebas.)*

## Flujo de trabajo en Git
1. Clonar el repo:
   ```bash
   git clone https://github.com/ralex1945/parcial.git
   cd parcial
   ```
2. Crear rama propia (`auth`, `eventos`, `inscripciones`, `informes`):
   ```bash
   git checkout -b mi-rama
   ```
3. Subir cambios:
   ```bash
   git add .
   git commit -m "Descripcion del cambio"
   git push -u origin mi-rama
   ```
