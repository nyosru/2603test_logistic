go:
	php artisan serve --host=0.0.0.0 --port=8000

migrate:
	php artisan migrate:fresh --seed

swagger:
	php artisan l5-swagger:generate
