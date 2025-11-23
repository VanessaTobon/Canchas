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

3. Crear la base de datos:

```sql
CREATE DATABASE reserva_canchas;
Importar:
creation.txt

Configurar la conexión en:
config/config.php

Ejemplo:

php
Copiar código
$conexion = new mysqli("localhost", "root", "", "reserva_canchas");
Iniciar Apache y MySQL desde XAMPP.

Abrir en el navegador:

http://localhost/Canchas/public

Credenciales de Prueba
Administrador
Correo: pepe@gmail.com
Contraseña: pepe12345

Cliente
Correo: lopera@gmail.com
Contraseña: lopera12345

Sin embargo pueden crear el administrador y cliente que deseen al clonar y abrir el proyecto
```

## Funcionalidades

### Para Usuarios Registrados

| Función | Descripción | Archivo | Imagen |
|---------|-------------|---------|---------|
| **Login** | Iniciar sesión en el sistema | `public/login.php` | ![Pantalla Login](./assets/img/login.jpeg) 
| **Registro** | Crear una cuenta nueva |  `public/registro.php` | ![Registro Usuario](./assets/img/registrodeusuario.jpeg)
| **Realizar Reservas** | Reservar canchas con selección de fecha y horario | `public/reservar.php` | ![Realizar Reserva](./assets/img/realizarreserva.png)
| **Mis Reservas** | Gestionar y visualizar todas las reservas propias | `cliente/mis_reservas.php` |![Pantalla de reservas](./assets/img/misreservas.png)
| **Cancelar Reservas** | Cancelar reservas existentes con motivo específico | `cliente/mis_reservas.php` | ![Mis Reservas](./assets/img/misreservas.png)

### Para Administradores

| Función | Descripción | Archivo | Imagen |
|---------|-------------|---------|--------|
| **Gestionar Canchas** | CRUD completo para administrar todas las canchas | `admin/gestionar_canchas.php` |![Gestión de Canchas](./assets/img/gestionarcanchas.png)
| **Gestionar Reservas** | Administrar y supervisar todas las reservas del sistema | `admin/gestionar_reservas.php` |![Gestión de Reservas](./assets/img/gestionarreservas.png)
| **Confirmar/Completar** | Cambiar estados de reservas y gestionar su ciclo de vida | `admin/gestionar_reservas.php` |

### Estados de Reservas

| Estado | Descripción |
|--------|-------------|
| **Pendiente** | Reserva creada, esperando confirmación del administrador |
| **Confirmada** | Reserva aprobada y confirmada por el administrador |
| **Completada** | Reserva utilizada exitosamente y marcada como finalizada |
| **Cancelada** | Reserva cancelada (requiere motivo de cancelación) |

### Decisiones arquitectónicas criticas
- Separar la lógica del sistema en MVC, evitando mezclar PHP con SQL o HTML.
- Agregar una capa de servicios para evitar controladores excesivamente grandes.
- Uso de patrones estructurales para garantizar extensibilidad futura.
- Permitir cancelaciones con motivo, manteniendo historial confiable.
- Interfaz limpia y uniforme, centrada en la usabilidad.
- Validación estricta de disponibilidad para evitar reservas duplicadas.
  
### Retos técnicos y de aprendizaje
- Manejo de multiples capas MVC.
- Incluir los patrones de diseño a un proyecto ya existente.
- Uso real de patrones en un proyecto funcional, no solo teórico.
- Manejo de sesiones y roles.
- Validación de traslape de horarios.
  
### Licencia
Este proyecto se entrega únicamente con fines académicos.

