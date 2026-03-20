# Testing Guide

This repository now includes a starter testing setup inspired by Joomla Weblinks.

## 1) Unit Tests (PHPUnit)

### Install dependencies

```bash
composer install
```

### Run tests

```bash
./vendor/bin/phpunit -c phpunit.xml.dist
```

Current suite covers:

- Base job lifecycle hooks (`beforeHandle` / `handle` / `afterHandle`)
- Queue manager dispatch/consume behavior
- Queue factory unsupported driver guard

## 2) E2E Tests (Cypress)

### Install dependencies

```bash
npm install
npx cypress install
```

### Run headless

```bash
npx cypress run
```

### Open runner UI

```bash
npx cypress open
```

### Env vars (optional)

Administrator credentials are required for backend tests. Do not rely on placeholder defaults.

```bash
export CYPRESS_BASE_URL=http://localhost:8080
export CYPRESS_ADMIN_USER=your-real-admin-user
export CYPRESS_ADMIN_PASSWORD=your-real-admin-password
```

If the credentials are missing or invalid, backend Cypress tests should fail explicitly.

Current smoke spec:

- Open plugin manager and find `flexqueue`
- Validate `com_ajax` endpoint does not return HTTP 500

## 3) File Layout

- `tests/unit/*` - PHPUnit tests
- `tests/cypress/*` - Cypress specs/support
- `phpunit.xml.dist` - PHPUnit config
- `cypress.config.js` - Cypress config
