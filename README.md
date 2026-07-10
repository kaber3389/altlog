### Запуск проект после клонирования репозитория
docker compose up -d

composer install

настраиваем файл .env см. .env.example

php artisan migrate

php artisan db:seed --class=SlotSeeder

php artisan serve

### Запросы

Получить все слоты

curl --request GET \
--url http://127.0.0.1:8000/api/slots/availability

Создать hold

curl --request POST \
--url http://127.0.0.1:8000/api/slots/1/hold \
--header 'idempotency-key: 1bfd2e04-0ff3-4d22-8763-84f8481cecba'

Подтвердить hold

curl --request POST \
--url http://127.0.0.1:8000/api/holds/2/confirm \
--header 'idempotency-key: 1bfd2e04-0ff3-4d22-8763-84f8481cecba'

Отменить hold

curl --request DELETE \
--url http://127.0.0.1:8000/api/holds/3 \
--header 'idempotency-key: 1bfd2e04-0ff3-4d22-8763-84f8481cecba'
