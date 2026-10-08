CONTROL ESCOLAR (PHP 8.2 + MySQL/MariaDB, XAMPP)

INSTALACIÓN
1. Abre XAMPP y enciende Apache y MySQL.
2. Copia la carpeta "control_escolar" a C:\xampp\htdocs\
3. Abre http://localhost/control_escolar/install.php  (crea la BD, tablas y usuario)
4. Entra en http://localhost/control_escolar/login.php
   Usuario: admin   Contraseña: admin123
5. BORRA install.php y cambia la contraseña.

Si tu MySQL tiene contraseña para root, cámbiala en config.php e install.php.

MÓDULOS
- Alumnos: alta, edición, baja y búsqueda.
- Materias: catálogo por grado.
- Calificaciones: 3 parciales por materia (0-10), promedio y aprobado (>= 6).

PORTAL DEL ALUMNO
- URL: http://localhost/control_escolar/login_alumno.php
- Entran con su matrícula. Contraseña inicial = su matrícula (o la que captures en Alumnos).
- Si ya habías instalado antes: entra como admin y abre migrar_alumnos.php una vez, luego bórralo.
- Si instalas desde cero, install.php ya incluye todo.
