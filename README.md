<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## как запустить у себя 

php должен работать (если его нет, запуск внутри докер контейнера с php)

старт 
    
    php artisan serve --host=0.0.0.0 --port=8000

смотрим сайт

    http://0.0.0.0:8000

установить композер пакеты

    composer i

добавить конфиг

    cp .env.example .env

засеять данными бд

    php artisan migrate:fresh --seed

свагер (тесты апи)
    
    http://0.0.0.0:8000/api/documentation

генерация свагера если не доступен

    php artisan l5-swagger:generate

### **готово!!**

Сергей php-cat.com 

звоните 89-222-6-222-89
