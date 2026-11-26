# Control de Inscripciones a Eventos Académicos

Parcial 2 - Programación para Internet (UES).

## Estructura del proyecto
- `config/` - Conexión a base de datos y utilidades (cifrado, sesiones)
- `database/schema.sql` - Script SQL de las tablas
- `auth/` - Login y auto-registro
- `estudiantes/` - Registro de estudiantes
- `eventos/` - Registro de eventos
- `inscripciones/` - Inscripción a eventos
- `asistencia/` - Registro de asistencia
- `informes/` - Informe de eventos inscritos y asistencia por estudiante
- `assets/` - CSS y JS compartidos

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
