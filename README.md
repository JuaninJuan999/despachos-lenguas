📦 Despachos - Lenguas

Aplicación web construida con Laravel para gestionar procesos relacionados con despachos, idiomas y/o recursos asociados (PDF, Excel, imágenes y permisos de usuario).

Este repositorio contiene la base de un proyecto Laravel con funcionalidades extendidas mediante paquetes de generación de PDF, manipulación de imágenes, exportación/lectura de Excel y control de permisos.

🔍 Descripción

Despachos-Lenguas es un proyecto esqueleto basado en Laravel (v12) que sirve como punto de partida para una solución web con:

Gestión de usuarios y roles (con permisos avanzados).

Generación de documentos PDF.

Procesamiento y redimensionamiento de imágenes.

Exportación e importación de datos en formatos como Excel.

Estructura modular y escalable lista para desarrollar módulos de negocio.

🚀 Tecnologías y dependencias

El proyecto está construido con:

🧠 Laravel Framework — arquitectura MVC para aplicaciones PHP.

📄 barryvdh/laravel-dompdf — generación de PDF.

📊 maatwebsite/excel — exportación e importación de Excel.

🖼 intervention/image — manipulación de imágenes.

🔐 spatie/laravel-permission — roles y permisos avanzados.

📦 Construcción frontend con Vite + TailwindCSS/JS.

🛠️ Instalación

Asegúrate de tener instalado PHP 8.2+, Composer, Node.js y un servidor de bases de datos (MySQL, SQLite, etc.).

# Clonar el repositorio
git clone https://github.com/JuaninJuan999/despachos-lenguas.git
cd despachos-lenguas

# Instalar dependencias backend
composer install

# Copiar env y generar clave de aplicación
cp .env.example .env
php artisan key:generate

# Configurar la base de datos en .env y migrar
php artisan migrate

# Instalar dependencias frontend
npm install
npm run dev

# Iniciar servidor
php artisan serve

🔧 Scripts útiles
Comando	Descripción
composer setup	Instala dependencias, genera key y migra DB
npm run dev	Compila assets para desarrollo
npm run build	Compila assets para producción
php artisan test	Ejecuta tests automáticos
📁 Estructura principal
app/           → Código principal (Modelos, Controladores)
config/        → Configuraciones de Laravel y paquetes
database/      → Migraciones y seeders
public/        → Archivos públicos (CSS, imágenes, JS compilado)
resources/     → Vistas, assets sin compilar
routes/        → Definición de rutas web y API
tests/         → Pruebas automáticas

🧩 Uso de Roles y Permisos

Este proyecto incorpora control de acceso basado en roles usando Spatie Laravel Permission.

Crea roles (admin, editor, etc.).

Asigna permisos según necesidades.

Protege rutas usando middleware role: y/o permission:.

📄 Licencia

Este proyecto está bajo MIT License — libre para usar, modificar y distribuir.

❤️ Contribuir

Si quieres colaborar:

Haz un fork del repositorio.

Crea una rama con una feature o fix.

Envía un pull request describiendo tus cambios.