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

1. Clone repository
```bash
git clone https://github.com/stefanobrambilla/php-fullstack-hiring-test.git
cd php-fullstack-hiring-test
```

2. Setup and run backend
```bash
cd backend
composer run setup
php artisan serve --host=127.0.0.1 --port=8000
```

3. Setup and run frontend (new terminal)
```bash
cd frontend
npm install
npm run dev
```

4. Open app
- Frontend: `http://localhost:3000`
- Backend API: `http://127.0.0.1:8000/api`

`NUXT_PUBLIC_API_BASE` defaults to `http://127.0.0.1:8000/api` in `frontend/nuxt.config.ts`.

## Tests
```bash
cd backend
php artisan test
```
