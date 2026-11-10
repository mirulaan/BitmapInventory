# BitmapInventory

<p align="center">
  <strong>Enterprise Hardware Lifecycle, Vision Equipment & Asset Inventory Management System</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4+" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS v4" />
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Ready" />
  <img src="https://img.shields.io/badge/Tests-76%20Passed-brightgreen?style=for-the-badge" alt="Tests" />
</p>

---

## Overview

**BitmapInventory** is an end-to-end asset and inventory management application designed specifically for industrial vision systems, cameras, IPCs, networking hardware, and serialized enterprise equipment. 

It covers complete hardware lifecycles—from procurement and warehouse receiving to field loan dispatching, client-site deployments, RMA repairs, cycle count reconciliation, and financial cost accounting.

> 📖 **User Manual**: For an in-depth operational walkthrough covering every role, workflow, and action, see the **[Operations Guide & User Manual](GUIDE.md)**.

---

## Key Features & Functional Modules

### 1. Hardware Lifecycle & Workflow Engine
- **Stock-In & Serial Tracking**: Receive items with individual serial numbers, batch numbers, and purchase costs.
- **Rack-to-Rack Transfers**: Move assets between internal warehouses, branches, floors, and specific racks.
- **Client Loan Operations**: Dispatch equipment on loan to client sites with loan agreements, person-in-charge tracking, and return due dates.
- **Early Warnings & Overdue Alerts**:
  - 🟠 *Expiring Loan Warning*: Visual badges for loans due within 7 days.
  - 🔴 *Overdue Alert*: Prominent alerts and indicators for overdue hardware.
- **Convert to Sale**: Seamlessly transition items on loan into permanent client sales with customer & supplier PO references.
- **Direct Sales**: Sell warehouse items directly to clients.
- **Reservations**: Hold hardware for upcoming engineering projects and release when completed.
- **RMA & Defective Repair**: Track hardware sent to suppliers for RMA repair and handle returns.
- **Decommissioning**: Safely scrap obsolete or end-of-life hardware with audit logs.

### 2. Procurement & Purchase Orders
- **Purchase Order Management**: Create and track POs through status lifecycles (`Draft` → `Approved` → `Ordered` → `Received` → `Cancelled`).
- **Auto-Draft from Low Stock**: Automatically draft new POs when catalog items fall below minimum stock thresholds.
- **Receiving Workflow**: Convert ordered PO items directly into active serialized inventory items upon physical delivery.

### 3. Cycle Counts & Physical Stock Audits
- **Barcode & Serial Scanning**: Perform physical warehouse counts by scanning serial numbers against registered stock.
- **Discrepancy Calculation**: Automatically compute expected vs. counted variance.
- **Reconciliation**: Finalize audits and adjust inventory state with an immutable audit ledger.

### 4. Financial Valuation & Cost Accounting
- **Inventory Valuation Models**: Supports **FIFO** (First-In, First-Out), **LIFO** (Last-In, First-Out), and **Weighted Average** costing methods.
- **COGS (Cost of Goods Sold)**: Real-time calculation of inventory value and sold goods expense.
- **CSV Data Export**: Export valuation reports for finance and accounting audits.

### 5. Audit Trail & Ledger
- **Complete Traceability**: Every transaction logs timestamps, user responsible, transaction type, source/destination locations, serial number, and reference documentation numbers.
- **Advanced Filtering**: Filter by transaction type, search by model/brand/form number, and export filtered logs to CSV.

### 6. Role-Based Access Control (RBAC)
| Role | Identifier | Key Capabilities |
| :--- | :--- | :--- |
| **Super Admin** | `admin` | Full system access, user management, and superadmin password reset codes. |
| **Warehouse Manager** | `warehouse` | Stock In, transfers, decommissioning, PO creation/receiving, stock counts, catalog management. |
| **Field Engineer** | `engineer` | Loan dispatches, loan returns, sale conversions, direct sales, reservations, RMA. |
| **Auditor / Viewer** | `viewer` | Read-only access to dashboard, inventory, valuation, reports, and audit trail. |

---

## Default Demo Credentials

When running the database seeders, the following pre-configured accounts are created:

| Role | Username | Email | Password |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin` | `admin@bitmap.com` | `password` |
| **Warehouse Manager** | `warehouse` | `warehouse@bitmap.com` | `password` |
| **Field Engineer** | `engineer` | `engineer@bitmap.com` | `password` |
| **Auditor / Viewer** | `viewer` | `viewer@bitmap.com` | `password` |

---

## Quick Start (Docker Deployment)

BitmapInventory is fully containerized and production-ready with Docker Compose (includes Nginx, PHP 8.4-FPM, PostgreSQL connectivity, Redis, Supervisor, Queue Worker, and Scheduler):

```bash
# 1. Clone the repository
git clone https://github.com/mirulaan/BitmapInventory.git
cd BitmapInventory

# 2. Copy the Docker environment configuration
cp .env.docker.example .env

# 3. Build and launch all services in detached mode
docker compose up -d --build
```

The application is now accessible at:
- **Web Application**: [http://localhost:8000](http://localhost:8000)
- **Health Check**: [http://localhost:8000/up](http://localhost:8000/up)

For full Docker configuration details, cloud deployments (AWS, Cloud Run, VPS), and development mounts, see [DOCKER.md](DOCKER.md).

---

## Local Development Setup (Without Docker)

### Prerequisites
- **PHP** >= 8.4 with extensions (`pdo_pgsql`, `pdo_sqlite`, `curl`, `mbstring`, `intl`, `gd`, `bcmath`, `zip`)
- **Composer** >= 2.x
- **Node.js** >= 20.x & **npm**

### Step-by-Step Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Setup environment file & encryption key
cp .env.example .env
php artisan key:generate

# 3. Run database migrations & seed demo scenarios
php artisan migrate --seed

# 4. Install npm packages and build frontend assets
npm install
npm run build

# 5. Start the local development server
php artisan serve
```

Run Vite for live front-end hot module reloading:
```bash
npm run dev
```

---

## Running Automated Tests

BitmapInventory includes a comprehensive test suite covering authentication, RBAC authorization, inventory workflows, PO procurement, cycle counts, valuation, and catalog settings:

```bash
# Run all PHPUnit tests
php artisan test
```

All 76 tests run against an in-memory SQLite database and execute in seconds.

---

## Tech Stack & Architecture

- **Backend Framework**: [Laravel 13](https://laravel.com) (PHP 8.4+)
- **Frontend / UI**: Laravel Blade Components & [Tailwind CSS v4](https://tailwindcss.com) compiled with [Vite](https://vite.dev)
- **Database**: PostgreSQL (`pgsql`) / SQLite (test suite & dev)
- **Cache & Session & Queue**: Redis / In-memory / Database
- **Process Supervisor**: Supervisord & PHP-FPM
- **Web Server**: Nginx Alpine
- **Containerization**: Multi-stage Dockerfile & Docker Compose

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
