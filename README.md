# FlexQueue

A simple and lightweight job queue package for the Joomla Framework.

FlexQueue provides a flexible background job queue system for Joomla 4+ sites, supporting multiple queue backends (Database, Redis) and offering a clean job lifecycle API with `beforeHandle`, `handle`, and `afterHandle` hooks.

---

## Features

- **Multiple queue drivers** — Database and Redis backends supported out of the box
- **Job lifecycle hooks** — `beforeHandle` / `handle` / `afterHandle` for clean job management
- **Queue manager** — dispatch and consume jobs through a unified manager interface
- **Queue daemon** — CLI daemon (`QueueDaemon.php`) automatically deployed to the Joomla CLI directory on install
- **Auto-cleanup on uninstall** — database tables and CLI files are removed cleanly
- **Joomla native** — packaged as a standard Joomla library + system plugin combo
- **PHPUnit + Cypress** — unit tests and end-to-end smoke tests included

---

## Requirements

- Joomla 4.0 or later
- PHP 8.1 or later
- (Optional) Redis server, if using the Redis driver

---

## Installation

1. Download the latest release package (`pkg_FlexQueue_x.x.x.zip`) from the [Releases](https://github.com/Zhang-mason/FlexQueue/releases) page.
2. In the Joomla Administrator, go to **System → Install → Extensions**.
3. Upload and install the package.

On installation, the package will:
- Install the `lib_flexqueue` library under `JPATH_LIBRARIES`
- Install the `flexqueue` system plugin
- Deploy `QueueDaemon.php` to the Joomla CLI directory (`JPATH_CLI`)

> The package supports in-place upgrades via the built-in Joomla update server.

---

## Package Structure

```
FlexQueue/
├── flexqueue/          # Joomla system plugin
├── lib_flexqueue/      # Core library (Queue drivers, Job classes, CLI daemon)
│   └── src/
│       └── Cli/
│           └── QueueDaemon.php
├── tests/
│   ├── unit/           # PHPUnit unit tests
│   └── cypress/        # Cypress E2E specs
├── pkg_FlexQueue.xml   # Joomla package manifest
├── pkg_script.php      # Install/update/uninstall script
├── changelog.xml       # Release changelog
└── update.xml          # Joomla update server manifest
```

---

## Usage

### Dispatching a Job

Dispatch jobs through the queue manager provided by the `lib_flexqueue` library:

```php
use Mason\FlexQueue\QueueManager;

$manager = new QueueManager($config);
$manager->dispatch(new MyJob($payload));
```

### Consuming Jobs

Run the queue daemon from the Joomla CLI directory:

```bash
php cli/QueueDaemon.php
```

The daemon will continuously consume and process queued jobs using the configured driver.

### Creating a Custom Job

Extend the base job class and implement the `handle` method. Optionally override `beforeHandle` and `afterHandle`:

```php
use Mason\FlexQueue\Contracts\BaseJob;

class SendEmailJob extends BaseJob
{
    public function beforeHandle(): void
    {
        // Preparation logic
    }

    public function handle(): void
    {
        // Core job logic
    }

    public function afterHandle(): void
    {
        // Cleanup or follow-up logic
    }
}
```

---

## Queue Drivers

| Driver   | Description                                        |
|----------|----------------------------------------------------|
| Database | Stores jobs in `#__flexqueue_jobs` table (default) |
| Redis    | Uses a Redis server for high-throughput queuing    |

The driver is configured through the `flexqueue` system plugin settings in the Joomla Administrator.

---

## Testing

See [TESTING.md](TESTING.md) for full details. A summary is provided below.

### Unit Tests (PHPUnit)

```bash
composer install
./vendor/bin/phpunit -c phpunit.xml.dist
```

The unit test suite covers:

- Base job lifecycle hooks (`beforeHandle` / `handle` / `afterHandle`)
- Queue manager dispatch and consume behaviour
- Queue factory unsupported driver guard

### E2E Tests (Cypress)

```bash
npm install
npx cypress install

# Run headless
npx cypress run

# Open interactive runner
npx cypress open
```

Set the following environment variables before running backend tests:

```bash
export CYPRESS_BASE_URL=http://localhost:8080
export CYPRESS_ADMIN_USER=your-admin-user
export CYPRESS_ADMIN_PASSWORD=your-admin-password
```

The Cypress smoke spec verifies that the `flexqueue` plugin appears in the plugin manager .

---

## Uninstallation

When the package is uninstalled, the script will automatically:

- Drop the `#__flexqueue_jobs` database table
- Drop the `#__flexqueue_job_errors` database table
- Remove `QueueDaemon.php` from the CLI directory

---

## License

This project is licensed under the [MIT License](LICENSE).

Copyright (c) 2026 Mason
