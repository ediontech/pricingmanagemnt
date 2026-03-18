# Pricing Management Portal

A PHP/MySQL pricing management portal inspired by the provided design, now organized with a CodeIgniter-style configuration layout.

## What is included

- A dashboard-style rate management interface.
- JSON endpoints for rate grids, bulk updates, history, copy-forward, and integrity checks.
- MySQL schema and seed data.
- CodeIgniter-style config files such as `app/Config/App.php`, `app/Config/Database.php`, `app/Config/Routes.php`, plus a root `env` template.
- GitHub Actions CI for PHP syntax and smoke tests.

## Important config files

- `app/Config/App.php` — application defaults such as app name, base URL, default year/month, and environment.
- `app/Config/Database.php` — database group configuration used by the PDO connection layer.
- `app/Config/Routes.php` — route table for the page and JSON endpoints.
- `env` — CodeIgniter-style environment template you can copy to `.env`.

## Run locally

1. Copy the environment template:
   ```bash
   cp env .env
   ```
   Or, if you prefer the simpler example file:
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

## Environment examples

### CodeIgniter-style

```env
CI_ENVIRONMENT = development
app.name = Pricing Management Portal
app.baseURL = 'http://127.0.0.1:8000/'
app.debug = true
pricing.defaultYear = 2026
pricing.defaultMonth = 2
pricing.defaultAcriss = CCAR,ECAR,FFAR

database.default.DBDriver = MySQLi
database.default.hostname = 127.0.0.1
database.default.port = 3306
database.default.database = pricing_management
database.default.username = root
database.default.password =
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci
```

### Flat fallback variables

```env
APP_NAME="Pricing Management Portal"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=MySQLi
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
- `app/Config/` CodeIgniter-style application configuration.
- `app/Controllers/` request handlers.
- `app/Models/` database access.
- `app/Views/` templates.
- `database/` MySQL schema and seed files.
- `tests/` smoke checks used by CI.
