# Administrador de eventos en PHP

Proyecto desarrollado en el módulo de DWES (DAW/DAM) para practicar PHP, sesiones, validación de formularios y acceso a base de datos mediante una pequeña aplicación de calendario / gestor de eventos.

## Descripción

La aplicación permite a un usuario registrarse, iniciar sesión y gestionar un listado de eventos personales almacenados en una base de datos SQLite. Incluye gestión de perfil y cambio de contraseña con contraseñas cifradas.

## Funcionalidades principales

- Registro e inicio de sesión de usuarios.
- Autenticación mediante sesiones y cierre de sesión.
- Visualización de un listado de eventos.
- Creación, edición y borrado de eventos.
- Edición del perfil de usuario (email, nombre, apellidos, fecha de nacimiento, about).
- Cambio de contraseña con validación y uso de `password_hash` / `password_verify`.

## Tecnologías utilizadas

- PHP 8
- SQLite
- HTML5, CSS3, Bootstrap 5

## Requisitos

- PHP 8 instalado.
- Extensión de SQLite habilitada.
- Servidor embebido de PHP o servidor web compatible (Apache, Nginx, etc.).

## Puesta en marcha

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/HugoPalacioAndres/base-code.git
   cd base-code
   
## Autor

- Hugo Palacio Andrés  
- Grupo: DA2D1A  
- IES Clara del Rey
