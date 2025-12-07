# Base de Datos - Sistema de Control Escolar

Este directorio contiene los archivos SQL necesarios para configurar la base de datos del Sistema de Control Escolar en WampServer.

## Archivos incluidos

### 1. `database_schema.sql`
Contiene la estructura completa de la base de datos:
- Creación de la base de datos `sistema_escolar`
- Definición de todas las tablas con sus relaciones
- Índices para optimizar el rendimiento
- Restricciones de integridad referencial

### 2. `sample_data.sql`
Contiene datos de ejemplo para probar el sistema:
- Usuarios del sistema (admin, profesores, estudiantes)
- Grados y grupos escolares
- Aulas y materias
- Horarios de clases
- Calificaciones y asistencias de ejemplo
- Configuraciones del sistema
- Notificaciones de prueba

## Instalación en WampServer

### Paso 1: Iniciar WampServer
1. Inicie WampServer en su computadora
2. Asegúrese de que el servicio MySQL esté ejecutándose (ícono verde)

### Paso 2: Acceder a phpMyAdmin
1. Abra su navegador web
2. Navegue a `http://localhost/phpmyadmin`
3. Inicie sesión (usuario: `root`, contraseña: vacía por defecto)

### Paso 3: Importar la base de datos
1. En phpMyAdmin, haga clic en la pestaña "Importar"
2. Haga clic en "Seleccionar archivo" y elija `database_schema.sql`
3. Haga clic en "Continuar" para ejecutar el script
4. Repita el proceso para `sample_data.sql`

### Paso 4: Verificar la instalación
1. En el panel izquierdo, debería ver la base de datos `sistema_escolar`
2. Al expandirla, debería ver todas las tablas creadas
3. Verifique que los datos de ejemplo se hayan insertado correctamente

## Configuración de la aplicación

### Archivo de configuración
El archivo `config/database.php` contiene la configuración de conexión a la base de datos:

```php
private $host = 'localhost';
private $db_name = 'sistema_escolar';
private $username = 'root';
private $password = '';
```

### Credenciales por defecto
- **Usuario administrador**: `admin`
- **Contraseña**: `admin123` (o la contraseña que configure)

## Estructura de la base de datos

### Tablas principales:
- `usuarios` - Usuarios del sistema (admin, profesores, estudiantes)
- `estudiantes` - Información de los estudiantes
- `profesores` - Información de los profesores
- `materias` - Materias escolares por grado
- `grados` - Grados escolares (1° a 6°)
- `grupos` - Grupos por grado (A, B, C)
- `aulas` - Aulas y espacios físicos
- `horarios` - Horarios de clases
- `calificaciones` - Calificaciones de estudiantes
- `asistencias` - Registro de asistencias
- `notificaciones` - Sistema de notificaciones
- `configuraciones` - Configuraciones del sistema

## Solución de problemas

### Error de conexión
- Verifique que WampServer esté ejecutándose
- Confirme que MySQL esté activo (ícono verde)
- Revise las credenciales en `config/database.php`

### Error de permisos
- Asegúrese de que el usuario `root` tenga permisos completos
- En WampServer, use la contraseña vacía por defecto

### Error de codificación
- La base de datos usa UTF-8 (utf8mb4_unicode_ci)
- Asegúrese de que phpMyAdmin esté configurado para UTF-8

## Respaldos

### Crear respaldo
```sql
mysqldump -u root -p sistema_escolar > backup_escolar.sql
```

### Restaurar respaldo
```sql
mysql -u root -p sistema_escolar < backup_escolar.sql
```

## Mantenimiento

### Optimizar tablas
```sql
OPTIMIZE TABLE estudiantes, profesores, calificaciones, asistencias;
```

### Verificar integridad
```sql
CHECK TABLE estudiantes, profesores, calificaciones, asistencias;
```

## Contacto

Para soporte técnico o consultas sobre la base de datos, contacte al administrador del sistema.

