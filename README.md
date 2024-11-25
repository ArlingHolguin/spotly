<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


# Spotly
Produccion: https://whatfy.com/
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




# Spotly - Despliegue en AWS con Apache y GitHub Actions

Este documento explica cómo configurar un servidor Apache en AWS para ejecutar el proyecto Laravel y desplegar automáticamente usando GitHub Actions.

---

## Pasos para Configuración en AWS

### 1. Crear una Instancia EC2 en AWS
1. Accede a la consola de AWS y selecciona **EC2**.
2. Crea una nueva instancia con las siguientes especificaciones:
   - **AMI:** Ubuntu 20.04 o Amazon Linux 2.
   - **Tipo de instancia:** t2.micro (o según tus necesidades).
   - **Grupo de seguridad:** Asegúrate de abrir los puertos:
     - **22** (para SSH).
     - **80** (para HTTP).
3. Descarga la clave privada (`.pem`) que será usada para conectarte al servidor.

---

### 2. Conectarse a la Instancia desde Git Bash
1. Abre Git Bash en tu máquina local.
2. Conéctate al servidor usando el siguiente comando:
   ```bash
   ssh -i "C:/Users/arlin/Downloads/sshpot.pem" ubuntu@ec2-3-135-244-31.us-east-2.compute.amazonaws.com
   ```

3. Actualiza los paquetes del sistema:
   ```bash
   sudo apt update
   sudo apt upgrade -y
   ```

---

### 3. Instalar Dependencias
1. **Instalar Apache:**
   ```bash
   sudo apt install apache2 -y
   sudo systemctl enable apache2
   sudo systemctl start apache2
   ```

2. **Instalar PHP y módulos necesarios:**
   ```bash
   sudo apt install php8.2 libapache2-mod-php php-mysql php-mbstring php-xml php-bcmath unzip curl git composer -y
   ```

3. **Instalar MySQL:**
   ```bash
   sudo apt install mysql-server -y
   sudo systemctl start mysql
   sudo mysql_secure_installation
   ```

---

### 4. Configurar Apache para Laravel
1. **Crear un Virtual Host para el proyecto:**
   Abre o crea un archivo de configuración para el sitio:
   ```bash
   sudo nano /etc/apache2/sites-available/spotly.conf
   ```

   Agrega lo siguiente al archivo:
   ```apache
   <VirtualHost *:80>
       ServerName ec2-3-135-244-31.us-east-2.compute.amazonaws.com
       DocumentRoot /var/www/spotly/public

       <Directory /var/www/spotly>
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/spotly_error.log
       CustomLog ${APACHE_LOG_DIR}/spotly_access.log combined
   </VirtualHost>
   ```

2. **Habilitar el sitio y el módulo `mod_rewrite`:**
   ```bash
   sudo a2ensite spotly
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Configurar permisos:**
   ```bash
   sudo chown -R www-data:www-data /var/www/spotly
   sudo chmod -R 775 /var/www/spotly/storage /var/www/spotly/bootstrap/cache
   ```

---

### 5. Configurar el Proyecto en el Servidor
1. **Clonar el repositorio:**
   ```bash
   cd /var/www
   sudo git clone https://github.com/ArlingHolguin/spotly.git
   cd spotly
   ```

2. **Instalar dependencias con Composer:**
   ```bash
   sudo composer install
   ```

3. **Configurar el archivo `.env`:**
   Copia el archivo `.env.example` y configúralo:
   ```bash
   cp .env.example .env
   sudo nano .env
   ```

   Configura las credenciales de base de datos y otras configuraciones según sea necesario.

4. **Migrar la base de datos:**
   ```bash
   php artisan migrate --seed
   ```

5. **Generar la clave de la aplicación:**
   ```bash
   php artisan key:generate
   ```

---

## Pasos para Configurar GitHub Actions

### 1. Crear un Fork del Proyecto
1. Ve al repositorio original en GitHub.
2. Haz clic en **Fork** para crear una copia en tu cuenta de GitHub.

---

### 2. Configurar Secretos en GitHub
1. Ve a `Settings > Secrets and variables > Actions` en tu repositorio.
2. Agrega los siguientes secretos:
   - **`SERVER_HOST`**: Dirección pública de tu instancia EC2 (ejemplo: `ec2-3-135-244-31.us-east-2.compute.amazonaws.com`).
   - **`SERVER_USER`**: Usuario SSH del servidor (normalmente `ubuntu`).
   - **`SERVER_SSH_KEY`**: Contenido de la llave privada descargada (`sshpot.pem`).

---

### 3. Probar el Despliegue Automático
1. Realiza algún cambio en el código del proyecto y haz un **push** al repositorio:
   ```bash
   git add .
   git commit -m "Test automatic deploy"
   git push origin main
   ```

2. Ve a la sección de **Actions** en tu repositorio para verificar que el pipeline se ejecuta correctamente.

---

### Si Todo Sale Bien
El despliegue será automático en cada **push** a la rama `main`. Solo necesitas seguir desarrollando y el sistema actualizará el servidor en AWS automáticamente.

---




Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
