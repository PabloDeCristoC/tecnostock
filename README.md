# TecnoStock — Sistema de Inventario

Sistema web de inventario para una tienda de accesorios tecnológicos. Permite iniciar sesión, gestionar productos (crear, listar, buscar, editar, desactivar) y registrar movimientos de stock (entradas/salidas) con actualización automática y alertas de stock bajo.

## Tecnologías

- **Frontend:** HTML5, Bootstrap 5, JavaScript
- **Backend:** PHP 8 (PDO)
- **Base de datos:** MySQL / MariaDB
- **Entorno local:** XAMPP

## Estructura del proyecto

```
tecnostock/
├── index.html                 # Formulario de login
├── conexion.php                # Conexión PDO a la base de datos
├── procesar_login.php          # Autenticación de usuario
├── logout.php                  # Cierre de sesión
├── dashboard.php               # Listado, búsqueda y alertas de stock
├── crear_producto.php          # Formulario de nuevo producto
├── guardar_producto.php        # Inserta un producto nuevo
├── editar_producto.php         # Formulario de edición
├── actualizar_producto.php     # Actualiza un producto existente
├── desactivar_producto.php     # Desactiva un producto (sin eliminarlo)
├── movimiento.php              # Formulario de entrada/salida de stock
├── guardar_movimiento.php      # Registra el movimiento y actualiza el stock (transacción)
└── .gitignore
```

## Modelo de datos

4 tablas relacionadas: `usuario`, `categoria`, `producto`, `movimiento`.

- `categoria` 1:N `producto`
- `producto` 1:N `movimiento`
- `usuario` 1:N `movimiento`

Restricciones de integridad: `PRIMARY KEY` autoincremental en cada tabla, `UNIQUE` en `codigo_producto` y `email_usuario`, y `FOREIGN KEY` en las relaciones descritas arriba. Los productos nunca se eliminan físicamente, solo se desactivan (`est_producto = 0`).

## Instalación local

1. Clonar este repositorio dentro de `htdocs` de XAMPP.
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Crear la base de datos `tecnostock` en phpMyAdmin y ejecutar los scripts SQL de estructura, restricciones y datos (ver carpeta de scripts / documentación entregada aparte).
4. Ajustar en `conexion.php` el host, puerto, usuario y clave según tu configuración local.
5. Acceder a `http://localhost/tecnostock/`.

**Usuario de prueba:**
- Correo: `admin@tecnostock.cl`
- Clave: `admin123`

## Plan de pruebas

| # | Módulo | Caso | Datos de prueba | Resultado esperado | Resultado obtenido |
|---|---|---|---|---|---|
| 1 | Login | Credenciales válidas | `admin@tecnostock.cl` / `admin123` | Redirige a `dashboard.php` | ✅ Correcto |
| 2 | Login | Contraseña incorrecta | Correo válido, clave errónea | "Correo o contraseña incorrectos" | ✅ Correcto |
| 3 | Login | Formato de correo inválido | `admin` (sin @) | "El formato del correo no es válido" | ✅ Correcto |
| 4 | Sesión | Acceso directo sin sesión | Ir a `dashboard.php` sin login | Redirige a `index.html` | ✅ Correcto |
| 5 | Sesión | Cierre de sesión | Clic en "Cerrar sesión" | Vuelve al login y bloquea acceso posterior | ✅ Correcto |
| 6 | Productos | Crear producto válido | Código `TEC-005`, datos completos | Se guarda y aparece en el listado | ✅ Correcto |
| 7 | Productos | Código duplicado | Código `TEC-001` (ya existe) | "El código ya existe" | ✅ Correcto |
| 8 | Productos | Editar producto | Cambiar precio/stock de `TEC-004` | Cambios reflejados en el listado | ✅ Correcto |
| 9 | Productos | Desactivar producto | Producto `TEC-005` | Desaparece del listado (no se borra de la BD) | ✅ Correcto |
| 10 | Movimientos | Entrada de stock | +5 unidades a un producto | Stock aumenta correctamente | ✅ Correcto |
| 11 | Movimientos | Salida con stock insuficiente | Sacar 100 de un producto con 2 | "Stock insuficiente para esta salida", no modifica nada | ✅ Correcto |
| 12 | Búsqueda | Buscar por nombre | "mouse" | Filtra solo productos que contienen "mouse" | ✅ Correcto |
| 13 | Búsqueda | Buscar por código | "TEC-00" | Filtra por coincidencia de código | ✅ Correcto |
| 14 | Búsqueda | Buscar por categoría | "Accesorios" | Filtra productos de esa categoría | ✅ Correcto |
| 15 | Alertas | Stock bajo | Producto con stock < stock mínimo | Fila en rojo + badge "Stock bajo" | ✅ Correcto |

## Seguridad implementada

- Contraseñas almacenadas con `password_hash()` / verificadas con `password_verify()` (nunca en texto plano).
- Todas las consultas SQL usan sentencias preparadas de PDO (`prepare()` + `execute()`), evitando inyección SQL.
- Salida de datos escapada con `htmlspecialchars()` en todas las vistas, evitando XSS.
- Rutas protegidas mediante verificación de sesión (`$_SESSION['id_usuario']`) en cada archivo sensible.
- Método HTTP controlado (`POST` para operaciones que modifican datos, `GET` solo para búsqueda/lectura).
- Movimientos de inventario ejecutados dentro de una transacción (`beginTransaction` / `commit` / `rollBack`), evitando datos inconsistentes entre el stock y el historial.

## Autor

Pablo De Cristo C. — Caso 1, Sistemas de Información.
