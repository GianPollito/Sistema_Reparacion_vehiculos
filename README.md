# 🚗 MOTOR EXPRESS - Sistema de Gestión para Taller de Reparación de Vehículos
**Proyecto de Desarrollo Web / Ingeniería de Software - SENATI (Caso B)**
**Tecnologías:** Python 3.11+ / Django 5 / MariaDB (MySQL) / Bootstrap 5.3 / Chart.js

Sistema web moderno, responsivo e integral diseñado para la administración técnica, operativa y contable del taller automotriz **"Motor Express"**. Desarrollado con el framework **Django** y base de datos relacional **MariaDB**, cumpliendo con todas las especificaciones y rúbricas del **Caso B**, garantizando la protección de datos mediante el **ORM de Django con consultas parametrizadas**, arquitectura modular y diseño adaptable a cualquier pantalla (computadoras, laptops, tabletas y celulares).

---

## 🌟 Características Principales

### 1. 🔐 Módulo de Autenticación y Seguridad
* **Inicio de Sesión (Login)** con verificación segura mediante hashes PBKDF2 / Argon2. Permite login por usuario o por correo.
* **Registro de Usuarios** con roles automotrices: *Administrador*, *Recepcionista* y *Mecánico Jefe*.
* **Control de Sesiones Activas** en todas las vistas con decoradores `@login_required`.
* **Protección CSRF** nativa en todos los formularios contra ataques de falsificación de petición en sitios cruzados.
* **Consultas Parametrizadas y Prepared Statements** garantizadas por el ORM de Django contra inyecciones SQL.

### 2. 👥 Administración de Clientes (CRUD Completo)
* Registro detallado de clientes y empresas (DNI/RUC, Nombres, Apellidos, Teléfono, Correo, Dirección).
* Búsqueda en tiempo real por nombres, DNI o teléfono.
* Ficha individual del cliente con historial completo de vehículos de su propiedad y órdenes de trabajo ejecutadas.

### 3. 🚙 Registro y Gestión de Vehículos (CRUD Completo)
* Ficha técnica automotriz con **Placa**, **Marca**, **Modelo**, **Año de fabricación**, **Color**, **Kilometraje** y **N° de Chasis/VIN**.
* Vinculación directa con el cliente/propietario.
* **Historial de Mantenimiento:** Consulta cronológica de reparaciones previas, diagnósticos y repuestos instalados en cada vehículo.

### 4. 👨‍🔧 Gestión de Mecánicos según su Especialidad (CRUD Completo)
* Registro del equipo técnico con su especialidad: *Diagnóstico y Electrónica Automotriz*, *Motores Gasolina y Diésel*, *Frenos, Dirección y Suspensión*, *Mantenimiento Preventivo*, *Aire Acondicionado*, etc.
* Monitoreo de órdenes asignadas activas por mecánico y estado laboral (*Activo / Inactivo*).

### 5. 📅 Programación de Citas para Mantenimiento (CRUD Completo)
* Agenda de citas con código correlativo (ej. `CIT-001`), cliente, vehículo, fecha y hora programada, y motivo de visita.
* Flujo de estados: *Pendiente*, *Confirmada*, *En Atención*, *Completada* y *Cancelada*.
* Conversión directa de una cita programada a una Orden de Trabajo con un solo clic.

### 6. 📋 Generación de Órdenes de Trabajo (CRUD Completo)
* Código único de orden (ej. `OT-2026-001`), fechas de ingreso y entrega estimada, nivel de combustible en tanque y kilometraje de recepción.
* Asignación de mecánico especialista y descripción de problemas reportados por el cliente.
* Asignación dinámica de servicios y repuestos con recálculo automático de costos en tiempo real.
* **Formato de Impresión Oficial:** Hoja técnica lista para imprimir o guardar como PDF con resumen de mano de obra, repuestos y firmas de conformidad.

### 7. 🩺 Diagnóstico de Fallas Mecánicas (CRUD Completo)
* Informe técnico con vehículo, orden asociada, mecánico responsable, síntoma observado y causa raíz de la avería.
* Clasificación por nivel de criticidad o gravedad: *Baja*, *Media*, *Alta* y *Crítica*.
* Recomendaciones técnicas y plan de acción para el cliente.

### 8. 🔧 Catálogo de Servicios de Reparación (CRUD Completo)
* Tarifario de servicios clasificados por categoría (*Motor y Transmisión*, *Frenos y Suspensión*, *Mantenimiento General*, *Sistema Eléctrico*, *Diagnóstico Computarizado*).
* Definición de costos de mano de obra (S/.) y tiempos estimados de ejecución.

### 9. 📦 Control de Inventario y Repuestos Utilizados (CRUD Completo)
* Catálogo de repuestos con código, nombre, categoría, marca (*Bosch, Brembo, Mobil 1, Mann Filter, etc.*), precio unitario y control de stock mínimo.
* **Asignación a Órdenes:** Descuento automático del stock de almacén al agregar una pieza a una reparación y reposición automática si se anula.
* Semáforo de alerta para repuestos en stock crítico.

### 10. 💵 Registro de Pagos (Efectivo, Tarjetas y Yape) (CRUD Completo)
* Emisión de Boletas de Venta, Facturas Electrónicas y Recibos de Caja de Taller.
* Registro de pagos con todos los métodos requeridos:
  - 💵 **Efectivo**
  - 💳 **Tarjeta de Crédito** (Visa, Mastercard, etc.)
  - 💳 **Tarjeta de Débito**
  - 🟣 **Yape** / Billeteras Digitales (y Plin)
* Registro de número de operación o referencia POS para auditorías.
* Formato de Ticket imprimible y exportable a PDF.

### 11. 📊 Módulo de Reportes Estadísticos Avanzados
Cumplimiento estricto de los cuatro reportes solicitados con filtros y exportación a **Excel (CSV)**:
1. **Reporte de Servicios Realizados:** Servicios ejecutados por período, categoría y recaudación generada.
2. **Reporte de Vehículos Atendidos:** Frecuencia de visitas por marca, modelo y total invertido por cliente.
3. **Reporte de Reparaciones Pendientes:** Monitoreo de autos en bahía, mecánicos asignados y demoras de entrega.
4. **Reporte de Ingresos Obtenidos:** Desglose financiero global y comparativo por método de cobro (Efectivo, Tarjetas, Yape).

### 12. 🎨 Interfaz de Usuario Moderna y Adaptable (Responsive)
* **Header de Dos Niveles:**
  - *Nivel 1 (Top Bar):* Teléfonos, dirección, horario de atención, indicador de puerto activo desocupado y perfil de usuario activo.
  - *Nivel 2 (Navbar):* Logo automotriz de Motor Express, accesos directos a todos los módulos y botón de acción rápida.
* **Menú Vertical Derecho (Offcanvas):** Panel lateral interactivo con accesos directos para nuevas citas, órdenes, clientes, estado de bahías y cierre de sesión.
* **Dashboard con Tarjetas e Imágenes:** Tarjetas KPI con contadores en tiempo real, ilustraciones automotrices vectoriales SVG de alta definición (`hero-car.svg`, `engine.svg`, `brakes.svg`, `electric.svg`, `oil.svg`), gráficos interactivos de Chart.js y tablas de actividad reciente.
* **Footer Profesional:** Información institucional de Motor Express, horarios de atención, enlaces rápidos y distintivo de SENATI.
* **Diseño 100% Adaptable:** Diseñado con Bootstrap 5.3 y CSS3 para visualizarse perfectamente en pantallas de escritorio, laptops, tablets y smartphones.

---

## 🚀 Cómo Iniciar el Sistema (Uso de Puertos Desocupados)

> [!IMPORTANT]
> El sistema cumple estrictamente la restricción: **"No utilices puertos que ya están ocupados, utiliza otros puertos desocupados"**.
> El sistema **NO** utiliza los puertos 80, 443, 3306 ni 8080 (que están ocupados por otros proyectos). En su lugar, el servidor Django inicia automáticamente en un **puerto seguro y desocupado** como el **`8095`** (o 8096, 8585, etc.).

### Opción 1: Inicio Rápido con 1 Clic (Recomendada)
Haga doble clic en el archivo:
```text
iniciar_servidor.bat
```
O ejecute en PowerShell:
```powershell
.\iniciar_servidor.ps1
```
El script verificará automáticamente la disponibilidad de puertos, seleccionará el puerto libre `8095`, levantará el servidor Django y abrirá inmediatamente su navegador en:
`http://127.0.0.1:8095/`

### Opción 2: Inicio Manual por Consola
Si desea iniciarlo manualmente por consola:
```powershell
# 1. Aplicar migraciones (si es la primera vez)
python manage.py migrate

# 2. Iniciar servidor Django en el puerto desocupado 8095
python manage.py runserver 127.0.0.1:8095
```
Luego abra su navegador web en `http://127.0.0.1:8095/`.

---

## 🔑 Credenciales de Acceso Demo

Para facilitar la evaluación y navegación inmediata, la pantalla de inicio de sesión incluye botones de autocompletado con un solo clic:

| Rol / Usuario | Nombre | Correo Electrónico | Contraseña |
| :--- | :--- | :--- | :--- |
| **Administrador** | Juan Silva | `admin@motorexpress.pe` | `admin123` |
| **Mecánico Jefe** | Carlos Mendoza | `carlos@motorexpress.pe` | `carlos123` |
| **Recepción / Caja** | Valeria Rivas | `valeria@motorexpress.pe` | `valeria123` |

*(También puede iniciar sesión ingresando el nombre de usuario directo: `admin`, `carlos`, `valeria`).*

---

## 🗄️ Configuración de la Base de Datos MariaDB

* **Servidor:** `127.0.0.1` (localhost)
* **Puerto:** `3307` (Instancia dedicada MariaDB 13.0)
* **Base de datos:** `motorexpress_django`
* **Usuario:** `root`
* **Contraseña:** `gian123`
* **Conexión en MySQL Workbench:** Acceso directo **Gian MariaDB** (`127.0.0.1:3307`)

### Carga de Datos Iniciales (Seed Data)
El proyecto incluye un comando de gestión para repoblar la base de datos con registros reales de prueba en cualquier momento:
```powershell
python manage.py seed_data
```

---

## 📁 Estructura del Proyecto

```text
Sistema_reparacion_vehiculos/
│
├── manage.py                          # Gestor de comandos de Django
├── iniciar_servidor.bat               # Script ejecutable en Windows (1 clic)
├── iniciar_servidor.ps1               # Script PowerShell con detección de puertos
├── README.md                          # Documentación técnica del proyecto
│
├── motorexpress/                      # Configuración central del proyecto Django
│   ├── __init__.py                    # Conexión PyMySQL y compatibilidad MariaDB
│   ├── settings.py                    # Configuración de base de datos, apps y templates
│   ├── urls.py                        # Enrutamiento principal
│   ├── wsgi.py / asgi.py
│
├── taller/                            # Aplicación principal del taller automotriz
│   ├── models.py                      # Modelos ORM: Clientes, Vehículos, Órdenes, etc.
│   ├── forms.py                       # Formularios Bootstrap 5 con validaciones
│   ├── views.py                       # Lógica de controladores y vistas CRUD
│   ├── urls.py                        # Rutas de todos los módulos
│   ├── admin.py                       # Panel administrativo de Django
│   ├── context_processors.py          # Variables globales (Top bar, info del taller)
│   ├── management/commands/seed_data.py # Seeder con datos de prueba realistas
│   └── migrations/                    # Historial de migraciones para MariaDB
│
├── templates/                         # Plantillas HTML con Django Template Language
│   ├── base.html                      # Layout base (Header 2 niveles, Menú offcanvas, Footer)
│   └── taller/
│       ├── dashboard.html             # Panel principal con tarjetas e imágenes
│       ├── auth/                      # Login y Registro
│       ├── clientes/                  # Listado, Formulario y Ficha Detalle
│       ├── vehiculos/                 # Listado, Formulario e Historial
│       ├── mecanicos/                 # Listado y Formulario
│       ├── citas/                     # Agenda de citas y Formulario
│       ├── ordenes/                   # Listado, Detalle, Formulario e Impresión
│       ├── diagnosticos/              # Listado y Formulario
│       ├── servicios/                 # Catálogo y Formulario
│       ├── repuestos/                 # Inventario, Control de Stock y Formulario
│       ├── pagos/                     # Caja, Comprobantes, Detalle e Impresión
│       └── reportes/                  # Dashboard de Reportes y 4 reportes con CSV
│
└── static/                            # Archivos estáticos
    ├── css/custom.css                 # Estilos automotrices personalizados
    └── img/banners/                   # Banners e ilustraciones SVG automotrices
        ├── hero-car.svg
        ├── engine.svg
        ├── brakes.svg
        ├── electric.svg
        └── oil.svg
```

---

## 👥 Cumplimiento de Rúbricas SENATI
* **Arquitectura:** Patrón MVT (Modelo - Vista - Template) con Django.
* **Seguridad:** Consultas parametrizadas por ORM, protección contra inyecciones SQL y CSRF.
* **Base de Datos:** MariaDB con integridad referencial y claves foráneas.
* **Red:** Cero colisión con puertos ocupados (`80`, `443`, `3306`, `8080`), ejecutándose en puerto libre `8095`.
* **Interfaz:** Adaptable, con encabezado de dos niveles, menú offcanvas derecho y diseño moderno automotriz.
