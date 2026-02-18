# WeRoad Checkout - Fullstack Hiring Test

Checkout flow implementation for WeRoad users:
- the user can select a travel to book
- the user enters email and number of seats
- the user confirms a fake payment

Business rules:
- max 5 seats per travel
- reserved seats are held for 15 minutes before cart expiration

## Tech stack
- Backend: PHP 8.2+, Laravel 12, REST API, SQLite
- Frontend: Nuxt 4, Vue 3, Tailwind CSS

## Repository structure
- `backend/`: Laravel API (routes, models, migrations, tests)
- `frontend/`: Nuxt app
- `samples/`: seed data (`travels.json`)

## Quick start (from clone)
Prerequisites:
- PHP 8.2+
- Composer 2+
- Node.js 20+
- npm 10+

## Clone repository
```bash
git clone https://github.com/stefanobrambilla/php-fullstack-hiring-test.git
cd php-fullstack-hiring-test
```

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
