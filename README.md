<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# Spotly

## Requisitos para Deploy Local

Asegúrate de tener los siguientes requisitos instalados en tu máquina para ejecutar el proyecto correctamente:

- **XAMPP** (para Apache y MySQL)
- **PHP 8.2** (compatible con Laravel)
- **Node.js 18** y **npm** (Node Package Manager)
- **Composer** (administrador de dependencias PHP)

---

## Pasos para Instalar y Configurar

### 1. Clonar el Repositorio
Descarga el código fuente del proyecto ejecutando el siguiente comando en tu terminal:
```bash
git clone https://github.com/ArlingHolguin/spotly.git
```

Accede al directorio del proyecto:
```bash
cd spotly
```

---

### 2. Crear la Base de Datos
1. Abre **phpMyAdmin** (incluido en XAMPP).
2. Crea una base de datos llamada `spotly`. Esto será utilizado en la configuración del proyecto.

---

### 3. Configurar el Archivo `.env`
1. Duplica el archivo de configuración `.env.example` y renómbralo a `.env`:
   ```bash
   cp .env.example .env
   ```
2. Abre el archivo `.env` en un editor de texto y configura las credenciales de la base de datos según tu entorno local:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=spotly
   DB_USERNAME=root
   DB_PASSWORD=
   ```

---

### 4. Instalar Dependencias
1. Instala las dependencias de PHP usando Composer:
   ```bash
   composer install
   ```

2. Si el proyecto tiene un frontend, instala las dependencias de Node.js:
   ```bash
   npm install
   ```

---

### 5. Migrar y Poblar la Base de Datos
Ejecuta las migraciones para crear las tablas y los seeders para poblar la base de datos con datos iniciales:
```bash
php artisan migrate:fresh --seed
```

---

### 6. Ejecutar el Proyecto
1. Inicia el servidor de desarrollo Laravel:
   ```bash
   php artisan serve
   ```

2. Si el proyecto incluye frontend, ejecuta:
   ```bash
   npm run dev
   ```

3. Abre tu navegador y accede al proyecto en [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## Notas Adicionales

1. **Permisos en Carpetas (Linux/MacOS):**
   Si estás en un entorno Unix/Linux, asegúrate de establecer permisos de escritura para las carpetas `storage` y `bootstrap/cache`:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

2. **Generar la Llave de la Aplicación:**
   Si Laravel no tiene una clave de aplicación configurada, genera una con el siguiente comando:
   ```bash
   php artisan key:generate
   ```

3. **Compilar Archivos para Producción:**
   Si necesitas un entorno de producción, asegúrate de compilar los assets frontend:
   ```bash
   npm run build
   ```

---

Con esta guía, deberías tener tu proyecto listo para ejecutarse localmente. Si encuentras algún error o necesitas más ayuda, ¡no dudes en contactarme! 😊


Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
