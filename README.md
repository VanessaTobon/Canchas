# Aplicación web para reserva de canchas

Sistema web desarrollado para la administración y reserva de canchas deportivas.  
Incluye autenticación de usuarios, roles (Administrador / Cliente), gestión de canchas, reserva con validación de disponibilidad, historial de reservas, cancelaciones justificadas y panel administrativo.

---

## Descripción del Sistema

Reserva de Canchas es una aplicación web construida en PHP bajo una arquitectura MVC.  
El sistema permite:

- Registro e inicio de sesión de usuarios.
- Panel de administración para gestionar canchas (CRUD).
- Panel de cliente para reservar canchas.
- Asignación de ubicación por país, departamento y municipio.
- Creación, validación y cancelación de reservas.
- Gestión de reservas por parte del administrador.
- Visualización de reservas personales para usuarios clientes.

Se implementaron múltiples patrones de diseño que fortalecen la escalabilidad, modularidad y mantenibilidad del sistema.

---

## Arquitectura Utilizada

El proyecto sigue la Arquitectura MVC (Modelo – Vista – Controlador):

- Modelos: Acceso a la base de datos, reglas y validaciones.
- Controladores: Manejo de solicitudes del usuario, lógica de negocio.
- Vistas: Interfaz gráfica construida con HTML, CSS y PHP.

---

## Patrones de Diseño Aplicados

### 1. Builder (Creacional) – Construcción de Reservas
Permite crear objetos reserva de forma controlada, estableciendo usuario, cancha, fecha, horarios y estado paso a paso.

### 2. Facade (Estructural) – Simplificación del sistema
Centraliza la lógica compleja (validación de disponibilidad, actualización de estado, creación de reservas) en una única interfaz: ReservaFacade.

### 3. Proxy (Estructural) – Control de permisos
UsuarioProxy se encarga de validar:

- Autenticación del usuario.
- Permisos para crear reservas.
- Rol de administrador para eliminar canchas.

Todo sin modificar los modelos originales del sistema.

### 4. Decorator (Estructural) – Extensión de características de canchas
Permite agregar funcionalidades adicionales sin cambiar la clase base:

- Cancha con promoción.
- Cancha con iluminación.
- Cancha con servicios adicionales.

El precio final se ajusta dinámicamente con cada decorador aplicado.

### 5. Composite (Estructural) – Jerarquía de Ubicaciones
La ubicación se organiza como un árbol jerárquico:

País → Estado → Municipio → Cancha

Todos implementan la interfaz UbicacionComponent, permitiendo consultas y recorridos uniformes.

---
### Integrantes del Proyecto
- Maria Andrea Nieto Valencia
- Vanessa Tobón Pérez

## Instrucciones para Ejecutar el Proyecto

### Requisitos Previos
- PHP 8+
- MariaDB o MySQL
- XAMPP
- Navegador web

### Instalación

1. Clonar o descargar el proyecto.
2. Colocarlo en:

/xampp/htdocs/Canchas/

sql
Copiar código

3. Crear la base de datos:

```sql
CREATE DATABASE reserva_canchas;
Importar:

pgsql
Copiar código
reserva_canchas.sql
Configurar la conexión en:

arduino
Copiar código
config/config.php
Ejemplo:

php
Copiar código
$conexion = new mysqli("localhost", "root", "", "reserva_canchas");
Iniciar Apache y MySQL desde XAMPP.

Abrir en el navegador:

bash
Copiar código
http://localhost/Canchas/public
Credenciales de Prueba
Administrador
Correo: pepe@gmail.com
Contraseña: pepe12345

Cliente
Correo: lopera@gmail.com
Contraseña: lopera12345


Capturas del Sistema
Para mostrar imágenes en GitHub, guardarlas en la carpeta /screenshots/ y referenciarlas así:

scss
Copiar código
![Login](./screenshots/login.jpg)
![Registro](./screenshots/registro.jpg)
![Gestión de Canchas](./screenshots/canchas.jpg)
![Reservas](./screenshots/reservas.jpg)
Asegúrate de que los nombres coincidan exactamente con los archivos.

Estructura del Proyecto
arduino
Copiar código
Canchas/
│── config/
│── controllers/
│── models/
│── public/
│── services/
│── assets/
│── README.md
│── reserva_canchas.sql
Licencia
Este proyecto se entrega únicamente con fines académicos.

