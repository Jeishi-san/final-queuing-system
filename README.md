## PROJECT SETUP QUICK GUIDE

### Backend Setup Using Laragon terminal

1. git clone https://github.com/Jeishi-san/final-queuing-system.git
2. composer install
3. cp .env.example .env
4. open and edit .env:

    APP_NAME=Laravel<br>
    APP_URL=http://127.0.0.1:8000<br>
    DB_CONNECTION=mysql<br>
    DB_HOST=127.0.0.1<br>
    DB_PORT=3306<br>
    DB_DATABASE=your_database_name<br>
    DB_USERNAME=root<br>
    DB_PASSWORD=<br>

5. Create the database
6. php artisan key:generate
7. php artisan migrate
8. php artisan serve

### Frontend SetUp Using another Laragon terminal

1. npm install
2. npm install vue @vitejs/plugin-vue\
3. npm install @fortawesome/fontawesome-svg-core @fortawesome/free-solid-svg-icons @fortawesome/vue-fontawesome
4. npm run dev
