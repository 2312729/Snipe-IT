# Snipe-IT Milestone 2: Clean-machine onboarding

This guide rebuilds the source checkout on Windows with PowerShell. It targets a
disposable local development database. Never point migrations or seeders at
production, shared staging, or data that must be preserved.

## 1. Frozen source baseline

- Repository: `https://github.com/Bee-Max-Ne/Snipe-IT`
- Branch observed for this checkout: `main`
- Commit observed: `ec8eb741397bf11345f51ae8c7f3a89e1aff1ac5`
- Check the repository's own origin and pin the same revision when reproducing:

```powershell
git clone https://github.com/Bee-Max-Ne/Snipe-IT.git snipe-it
Set-Location .\snipe-it
git checkout ec8eb741397bf11345f51ae8c7f3a89e1aff1ac5
git rev-parse HEAD
```

Record the output of `git rev-parse HEAD` with the team's report. Do not use a
moving branch name as the reproducibility identifier.

## 2. Runtime and dependencies

Install these tools before setup:

- PHP 8.2 or newer, Composer 2, and the required PHP extensions: `curl`,
  `exif`, `fileinfo`, `iconv`, `json`, `mbstring`, `pdo`; for the complete
  project, also enable `gd`, `sodium`, `zip`, `openssl`, and `pdo_mysql`.
- Node.js 18 or newer and npm. The repository `.nvmrc` specifies Node.js
  `18.16.0`; the Testcontainers runner requires at least Node.js 18.
- Git and a local MySQL/MariaDB server. Docker Desktop is an alternative for
  the repository's development Compose stack and is required for Testcontainers.

The main dependencies are Laravel Framework `^12.0`, Laravel Passport `^12.0`,
PHPUnit `^11.0`, Laravel Mix `^6.0.49` / webpack, and `@testcontainers/mysql`
`^11.7.2`. Exact resolved versions are recorded in `composer.lock` and
`package-lock.json`.

Check versions and extensions:

```powershell
php -v
composer --version
node --version
npm --version
php -m
```

On XAMPP, select the PHP CLI matching Apache's PHP and enable extensions in
`C:\xampp\php\php.ini`. Reopen the terminal after changing `PATH` or `php.ini`.

## 3. Install and configure the application

From the repository root:

```powershell
Copy-Item .env.example .env
composer install
npm ci
```

Create a new, empty local database and a least-privilege local database user.
Edit `.env` with local-only values. Example keys (replace the password locally;
never commit `.env`):

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=snipeit
DB_USERNAME=snipeit
DB_PASSWORD=<local-only-password>

MAIL_MAILER=log
```

If choosing MariaDB, use `DB_CONNECTION=mariadb` and its actual local host and
port. The repository's `dev.docker-compose.yml` uses the source Dockerfile,
MariaDB `11.4.7`, Redis `7.4.3`, and MailHog `v1.0.1`; its app port is `8000`,
MariaDB is published on `3306`, and MailHog UI is on `8025`. It expects
`.env.dev.docker` and is an alternative to the native PHP workflow:

```powershell
docker compose -f dev.docker-compose.yml up -d --build
docker compose -f dev.docker-compose.yml ps
docker compose -f dev.docker-compose.yml logs -f snipeit
```

The root `docker-compose.yml` instead runs the published `snipe/snipe-it`
image and MariaDB `11.4.7`; it is useful for trying the released application,
not for verifying unbuilt source edits.

Initialize only the new local database:

```powershell
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run dev
```

To create demo data, review the seeders first and run `php artisan db:seed` only
against a disposable database. `DatabaseSeeder` truncates tables and deletes
some accumulated upload directories. Do not re-seed a database containing
valuable data. Complete first-run application setup in the browser as needed.

Run the source checkout:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000`. If port 8000 is occupied, select a free port and
update `APP_URL` to match. Use `npm run watch` for continuous frontend builds;
this project uses Laravel Mix, not Vite.

## 4. Testcontainers integration test

The canonical Node runner starts an isolated `mysql:8.0.36` container, uses a
random database name/password and mapped host port, passes those values into
PHPUnit, and stops the container afterward. It also generates ephemeral
Passport RSA keys for the test process. It does not use the local `.env`
database.

Requirements: Docker Engine/Desktop running, PHP 8.2+, PDO MySQL, Composer
development dependencies, Node.js 18+, and npm dependencies.

Run the whole test suite:

```powershell
npm run test:integration:testcontainers
```

Run the checkout/checkin acceptance test:

```powershell
npm run test:integration:testcontainers -- tests/Feature/Checkouts/Api/AssetCheckoutTest.php --filter=test_asset_can_be_checked_out_and_checked_back_in_through_api
```

On the recorded local run, the focused test passed with 1 test and 11
assertions in 2 minutes 8.481 seconds. A separate earlier run of the full
`AssetCheckoutTest.php` file had 29 tests, 134 assertions, and 1 incomplete
test; that was not the entire project suite.

## 5. Evidence capture checklist

The following evidence must be captured from the actual local application and
CI; it is intentionally not fabricated in this guide:

1. Save the successful application startup output and a browser screenshot of
   the local login/dashboard at `http://127.0.0.1:8000`.
2. Using a disposable seeded database and a local admin account, capture
   screenshots/log output for: login, asset checkout to a user, and checkin.
   Hide credentials, tokens, email addresses, and other personal data.
3. Save the focused PHPUnit output shown above.
4. Push the workflow and capture the GitHub Actions run URL/result for
   `Testcontainers Integration`. Local test success is not evidence that the
   remote workflow has passed.

Suggested evidence folder: `reports/milestone-2/evidence/`. Add actual,
sanitized screenshots and logs there after capture.
