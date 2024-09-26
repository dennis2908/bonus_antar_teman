Laravel, PGSQL, php, composer </br>

copy isi .env ke .env lalu di cmd ketik :

```
composer install
```

Tunggu hingga akhir lalu :

```
php artisan optimize:clear
```

Tunggu hingga akhir lalu :

```
php artisan serve
```

Lalu buka di browser :

```
http://localhost:8000/api/bonusteman
```

Lalu buka di postman :

```
GET localhost:8000/api/bonusteman
```