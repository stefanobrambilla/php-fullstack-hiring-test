# WeRoad Checkout

Soluzione completa con:
- backend `Laravel` (REST + SQLite)
- frontend `Nuxt 4` + `Tailwind`

Regole implementate:
- max `5` posti per viaggio
- blocco posti per `15 minuti`
- pagamento fake per confermare

## Avvio backend
```bash
cd backend
cp .env.example .env
touch database/database.sqlite
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Avvio frontend
```bash
cd frontend
npm install
npm run dev
```
