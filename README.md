# Pricing Management Portal

A PHP/MySQL pricing management portal inspired by the provided design. The app includes:

- A dashboard-style rate management interface.
- A JSON API for rate grids, bulk updates, history, copy-forward, and integrity checks.
- MySQL schema and seed data.
- GitHub Actions CI for PHP syntax and smoke tests.

## Stack

- PHP 8.2+
- MySQL 8+
- Vanilla HTML/CSS/JavaScript

## Run locally

1. Copy the environment file:
   ```bash
   cp .env.example .env
   ```
2. Update the database values in `.env`.
3. Create the schema:
   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p < database/seed.sql
   ```
4. Start the app:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
5. Open `http://127.0.0.1:8000`.

## Environment variables

```env
APP_NAME="Pricing Management Portal"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pricing_management
DB_USERNAME=root
DB_PASSWORD=
```

## CI

The GitHub Actions workflow is in `.github/workflows/ci.yml` and runs:

- PHP linting on all PHP files.
- A smoke test that boots the app, checks the home page, and validates the JSON API shape.

## Project structure

- `public/` web entry point and static assets.
- `app/Controllers/` request handlers.
- `app/Models/` database access.
- `app/Views/` templates.
- `database/` MySQL schema and seed files.
- `tests/` smoke checks used by CI.
