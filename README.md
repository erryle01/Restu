# Restu

**Restaurant operations and workforce management built with PHP and MySQL.** Restu brings restaurant point of sale, menu and stock management, dine-in tables, sales reporting, employee attendance, and payroll workflows together in one web application.

> Restu is an actively developed project. See [Known limitations](#known-limitations) before using it for live operations or payroll.

## Features

- **Restaurant POS:** dine-in and take-out orders, selectable tables, product search, cart quantities, discounts, taxes, payment methods, and change calculation.
- **Keyboard-friendly checkout:** F1-F12 shortcuts, arrow-key navigation, quantity selection, and POS fullscreen mode.
- **Products and inventory:** product catalog, local product photos, stock levels, low-stock indicators, adjustments, and movement history.
- **Sales reporting:** date-filtered income, payment-method totals, top products, and paginated order ledger.
- **Restaurant tables:** add, edit, disable, view availability, and release occupied tables.
- **Workforce tools:** employee records, schedules, attendance clock-in/out, attendance history, and payroll periods.
- **Access control:** role-based permissions for application pages and actions.
- **Offline UI assets:** Bootstrap and icon libraries are bundled locally; uploaded product photos are stored locally.

## Technology

- PHP 8.1+
- MySQL or MariaDB
- Apache (XAMPP supported)
- PDO
- Bootstrap 5, Bootstrap Icons, and Lucide (local vendor files)
- Vanilla JavaScript

## Setup

### Requirements

- PHP 8.1 or newer with PDO MySQL enabled
- Apache and MySQL/MariaDB
- A compatible Restu base database

### Run with XAMPP

1. Place the project at `C:\xampp\htdocs\Restu`.
2. Create or select the MySQL database named `pos_payroll`.
3. Configure the application URL and timezone in [`config/config.php`](config/config.php).
4. Set your connection values in [`config/database.php`](config/database.php).
5. Apply the applicable SQL files in [`database/migrations/`](database/migrations/) to your compatible base schema, in order, only once.
6. Open `http://localhost/Restu`.

The URL prefix must match the folder where the app is served. For example, if deployed at the domain root, change `BASE_URL` from `/Restu` to an empty string or your configured mount path.

### Database setup note

The repository currently contains **incremental migrations**, not a complete initial schema dump. A fresh empty database cannot be initialized from these migrations alone; start from a compatible Restu base schema before applying them. Back up the database before running schema changes. Some migrations use `ALTER TABLE` and are not intended to be rerun.

### Create an administrator

The legacy helper is [`create_admin.php`](create_admin.php). It uses a known development password and should only be run in a trusted local environment. Remove or restrict this helper before deployment, and immediately change any generated/default credentials.

### Demo menu data

Run this from the project directory to add sample menu products, categories, opening stock, and local photos:

```powershell
php seed_demo_food.php
```

It is safe to rerun: existing demo SKUs are skipped and existing stock is not reset. View the sample menu at `http://localhost/Restu/pos`.

For local role demos, the CLI-only helper below creates one random-password user per current role and prints the credentials once:

```powershell
php create_demo_role_accounts.php --create-demo-accounts
```

Treat the printed passwords as secrets. Delete or disable these accounts before production use.

## POS Shortcuts

| Key | Action |
|---|---|
| `F1` | Take-out |
| `F2` | Dine-in |
| `F3` | Focus product search |
| `F4` | Toggle fullscreen |
| `F5` | Clear order |
| `F6` | Cash payment |
| `F7` | Card payment |
| `F8` | GCash payment |
| `F9` | Checkout |
| `F10` | Focus discount |
| `F11` | Exit fullscreen, remain on POS |
| `F12` | Focus amount tendered |
| `Alt+T` | Choose dine-in table |
| `Alt+N` | Add a table |
| `Alt+M` | Manage tables |
| `Arrow keys` / `Enter` | Navigate and select products/tables; adjust quantity in the quantity dialog |

## Tests

Run the CLI test suite from the project root:

```powershell
php tests\testRunner.php
```

The suite covers database connectivity/schema, authentication, role permissions, employees, schedules, attendance, and payroll calculation unit cases.

Check PHP syntax for an individual file:

```powershell
php -l modules\POS\Controllers\POSController.php
```

## Project Structure

```text
config/                 App, database, and route configuration
core/                   Router, controller, database, auth, and session classes
modules/
  Attendance/            Attendance records and employee time tracking
  Dashboard/             Operational and sales overview
  Employees/             Employee management
  Inventory/             Stock overview and adjustments
  Payroll/               Payroll periods, calculators, and payslips
  POS/                   Restaurant checkout
  Products/              Menu catalog and product photos
  Reports/               Sales and income reports
  Settings/              System and navigation settings
  Tables/                 Restaurant table management
database/migrations/     Incremental SQL migrations
assets/vendor/           Locally bundled frontend libraries
uploads/products/        Uploaded and demo product images
tests/                   CLI regression and calculation tests
```

## Security and Deployment

- Configure database credentials and `BASE_URL` for the deployment environment.
- Set `display_errors` off in production and send errors to protected server logs.
- Serve over HTTPS and protect database backups and uploaded files.
- Remove or restrict development account/setup scripts.
- Use least-privilege application accounts and review role permissions.
- Do not expose the development installation directly to the public internet.

## Known Limitations

- **Payroll is not certified for production compliance.** Philippine SSS, PhilHealth, Pag-IBIG, and BIR calculations require verified, effective-dated configuration profiles. Review rates, eligibility, payroll frequency, and business-specific rules with a qualified payroll/accounting professional before relying on net-pay results.
- The initial database schema is not bundled yet; migrations require a compatible base schema.
- Product images are local files; production deployment should include upload size/type checks, backups, and an appropriate web-server policy for the uploads directory.

## License

No project license is currently specified. Add a `LICENSE` file before distributing or reusing this project.
