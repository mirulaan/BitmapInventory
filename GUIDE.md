# BitmapInventory — User Manual & Operations Guide

Welcome to the **BitmapInventory Operations Guide**. This manual provides comprehensive step-by-step instructions for managing serialized vision hardware, industrial cameras, IPCs, networking equipment, client loan lifecycles, procurement, cycle counts, and financial valuation.

---

## Table of Contents

1. [Getting Started & Authentication](#1-getting-started--authentication)
2. [User Roles & Permissions Matrix](#2-user-roles--permissions-matrix)
3. [Dashboard & Command Center](#3-dashboard--command-center)
4. [Master Data & System Settings](#4-master-data--system-settings)
5. [Product Catalog Management](#5-product-catalog-management)
6. [Inventory Receiving & Stock-In](#6-inventory-receiving--stock-in)
7. [Warehouse Transfers & Decommissioning](#7-warehouse-transfers--decommissioning)
8. [Field Operations & Loan Lifecycle](#8-field-operations--loan-lifecycle)
9. [Defective Equipment & RMA Workflow](#9-defective-equipment--rma-workflow)
10. [Procurement & Purchase Orders (PO)](#10-procurement--purchase-orders-po)
11. [Cycle Counts & Physical Stock Audits](#11-cycle-counts--physical-stock-audits)
12. [Financial Valuation & Cost Accounting](#12-financial-valuation--cost-accounting)
13. [Audit Trail & Transaction Ledger](#13-audit-trail--transaction-ledger)
14. [User Administration & Emergency Password Resets](#14-user-administration--emergency-password-resets)

---

## 1. Getting Started & Authentication

### 1.1 Accessing the Application
Navigate to your deployed URL in any modern browser:
- Local / Docker: `http://localhost:8000` (or your configured server IP, e.g. `http://192.168.100.134:8000`)

### 1.2 Logging In
1. Enter your **Username** (or Email) and **Password**.
2. Click **Sign In to System**.
3. *Quick Demo Accounts*: On test environments, use the one-click demo buttons at the bottom of the login card to auto-fill credentials for `admin`, `warehouse`, `engineer`, or `viewer` (default password: `password`).

### 1.3 Emergency Password Reset with Superadmin Code
If a team member forgets their password:
1. Request a one-time 8-character **Reset Code** from the Super Administrator.
2. Click the **Reset with Code** link on the login page (or go to `/reset-password`).
3. Enter your **Username**, the provided **Reset Code**, and your **New Password**.
4. Click **Reset Password** to immediately regain access.

---

## 2. User Roles & Permissions Matrix

BitmapInventory enforces strict Role-Based Access Control (RBAC):

| Capability / Module | Super Admin (`admin`) | Warehouse Manager (`warehouse`) | Field Engineer (`engineer`) | Auditor / Viewer (`viewer`) |
| :--- | :---: | :---: | :---: | :---: |
| **Dashboard Metrics & Alerts** | Full | Full | Full | View Only |
| **Product Catalog Management** | Full | Full | View Only | View Only |
| **Inventory Stock-In & Serial Entry** | Full | Full | No | View Only |
| **Internal Rack Transfers** | Full | Full | No | View Only |
| **Loan Dispatch & Return** | Full | Full | Full | View Only |
| **Loan-to-Sale Conversion & Direct Sale** | Full | Full | Full | View Only |
| **Hardware Reservations & Release** | Full | Full | Full | View Only |
| **RMA Dispatch & Supplier Return** | Full | Full | Full | View Only |
| **Decommission / Scrap Asset** | Full | Full | No | View Only |
| **Purchase Orders & Procurement** | Full | Full | No | View Only |
| **Cycle Counts & Reconciliation** | Full | Full | No | View Only |
| **Financial Valuation & CSV Export** | Full | Full | Full | Full |
| **Audit Ledger & CSV Export** | Full | Full | Full | Full |
| **User Management & Reset Codes** | Full | No | No | No |

---

## 3. Dashboard & Command Center

The Dashboard (`/dashboard`) serves as the operational command center:

- **Total SKUs**: Number of distinct catalog products registered.
- **In-Stock Units**: Serialized hardware available in warehouse racks ready for dispatch.
- **Active Loans**: Equipment currently deployed to customer facilities on loan.
- **Overdue Loans**: Hardware whose loan due date has elapsed. Highlights with a pulsing red warning.
- **Low Stock Alerts**: Catalog items whose in-stock quantity has reached or breached their minimum threshold.
- **Total Inventory Valuation**: Real-time monetary value of all owned inventory.
- **Early Warning Badges**:
  - 🟠 **Expiring Soon**: Loans due within the next 7 days.
  - 🔴 **Overdue Alert**: Loans past their due date requiring urgent recovery or sales conversion.
- **Quick Action Bar**: Shortcuts for Stock-In, New Loan, Stock Count, and New Purchase Order.

---

## 4. Master Data & System Settings

Before operating inventory, configure master data under **Settings**:

### 4.1 Locations (`/settings/locations`)
1. Click **Add Location**.
2. Select **Location Type**:
   - **Internal Rack**: Represents warehouse shelves (e.g., `Rack A-01`, Branch: `HQ Warehouse`, Floor: `Level 1`).
   - **Client Site**: Represents a customer premises for equipment deployment (e.g., `Factory Line 3`).
3. Click **Save Location**.

### 4.2 Brands & Categories (`/settings/brands` & `/settings/categories`)
- Define hardware manufacturers (e.g., *Basler*, *Cognex*, *Advantech*, *Cisco*).
- Define equipment types (e.g., *Industrial Cameras*, *Lenses*, *IPCs*, *Switches*).

### 4.3 Clients (`/settings/clients`)
Register customer organizations, primary contact persons, phone numbers, and billing/notification emails.

### 4.4 Suppliers (`/settings/suppliers`)
Register hardware distributors and vendors for procurement and RMA warranty returns.

---

## 5. Product Catalog Management

Manage SKUs and hardware models under **Products** (`/products`):

1. Click **Create Product**.
2. Fill in the required specifications:
   - **SKU**: Unique catalog code (e.g., `CAM-BASLER-01`).
   - **Name**: Model name (e.g., `Basler ace 2 Pro 5MP GigE`).
   - **Brand** & **Category**: Select from predefined dropdowns.
   - **Unit Price**: Default retail or catalog value.
   - **Minimum Stock Alert**: Threshold number of units. When warehouse stock hits this level, automatic alerts trigger on the dashboard and auto-draft PO generation activates.
   - **Product Image**: Upload an image or datasheet icon (PNG/JPG/WEBP).
3. Click **Save Product**.

---

## 6. Inventory Receiving & Stock-In

When new hardware arrives at the receiving dock:

### 6.1 Manual Stock-In (`/inventory/create`)
1. Navigate to **Inventory** → **Receive / Stock In**.
2. Select the **Catalog Product**.
3. Enter the manufacturer **Serial Number** (unique physical barcode/tag).
4. Enter the **Batch / Lot Number** and the exact **Unit Cost** (for FIFO/LIFO financial valuation).
5. Choose the destination **Internal Warehouse Rack** (e.g., `Rack A-01`).
6. Optionally enter a **Supplier PO Reference** (e.g., `PO-2026-001`).
7. Click **Confirm Stock In**. The item status becomes `in_stock`.

### 6.2 Receiving from Purchase Orders
Hardware can also be received directly from the Procurement module (see [Section 10](#10-procurement--purchase-orders-po)).

---

## 7. Warehouse Transfers & Decommissioning

### 7.1 Internal Rack-to-Rack Transfer
Move inventory items between internal racks, floors, or branches:
1. Locate the item in **Inventory** (`/inventory`).
2. Click the actions menu → **Transfer Rack**.
3. Select the **Destination Rack**.
4. Enter notes or a transfer authorization reference.
5. Click **Process Transfer**. The audit ledger records the movement with timestamp.

### 7.2 Decommissioning / Scrapping Obsolete Hardware
When hardware reaches end-of-life or irreparable damage:
1. Open item actions → **Decommission**.
2. Enter the decommission reason (e.g., *Water damage during testing*, *Beyond economical repair*).
3. Click **Decommission Asset**. The item transitions to `decommissioned` and is deducted from active valuation.

---

## 8. Field Operations & Loan Lifecycle

Field Engineers deploy equipment to client sites for proof-of-concept (POC), testing, or temporary backup:

### 8.1 Dispatch on Loan
1. Navigate to **Inventory** and locate an `in_stock` item.
2. Click **Loan Out**.
3. Fill in loan details:
   - **Client**: Target company.
   - **Destination Location**: Client site or factory location.
   - **Loan Form Number**: Physical document or tracking code (e.g., `LF-2026-101`).
   - **Person in Charge (PIC)**: Field engineer or client representative.
   - **Loan Start Date** & **Expected Return Date**.
4. Click **Dispatch Hardware**. Status changes to `on_loan`.

### 8.2 Monitoring & Early Warnings
- If the return date is within **7 days**, an **Orange Badge** displays on the dashboard and inventory table.
- If the return date passes without return, a **Pulsing Red Overdue Badge** appears.

### 8.3 Returning Hardware from Loan
1. Open the item's action menu → **Return from Loan**.
2. Select the receiving **Internal Warehouse Rack**.
3. Inspect physical condition and enter return notes.
4. Click **Complete Return**. Status reverts to `in_stock`.

### 8.4 Converting Loan to Permanent Sale
When a customer decides to purchase the loaned hardware:
1. Open item actions → **Convert to Sale**.
2. Enter the **Customer Purchase Order Number** (`PO Customer`) and **Supplier Reference** (`PO Supplier`).
3. Confirm selling price.
4. Click **Convert to Sale**. Status transitions to `sold`. The transaction logs into COGS financial accounting.

### 8.5 Direct Sales
Sell warehouse stock directly without an intermediate loan:
1. Open item actions → **Sell Item**.
2. Select **Client**, enter **Customer PO**, and confirm transaction.

### 8.6 Hardware Reservations
Prevent other engineers from dispatching hardware needed for scheduled projects:
1. Open item actions → **Reserve Item**.
2. Enter project code, expected deployment date, and notes.
3. Status changes to `reserved`.
4. When ready, click **Release Reservation** to make it available for dispatch.

---

## 9. Defective Equipment & RMA Workflow

When hardware malfunctions under warranty:

### 9.1 Dispatching for RMA
1. Open item actions → **Dispatch RMA**.
2. Select the **Supplier / Vendor**.
3. Enter RMA tracking number, fault description, and supplier dispatch date.
4. Click **Dispatch RMA**. Status changes to `rma`.

### 9.2 Receiving from RMA
1. When the vendor ships the repaired hardware or warranty replacement:
2. Open item actions → **Return from RMA**.
3. Select whether the same serial number was repaired or a replacement unit was issued (with updated serial number).
4. Select the receiving internal rack.
5. Click **Accept RMA Return**. Status returns to `in_stock`.

---

## 10. Procurement & Purchase Orders (PO)

Manage stock replenishment under **Purchase Orders** (`/purchase-orders`):

### 10.1 Creating a Purchase Order
1. Click **Create Purchase Order**.
2. Select **Supplier**, expected delivery date, and add catalog line items with quantities and estimated unit costs.
3. Save as `Draft`.

### 10.2 Auto-Draft from Low Stock
When multiple products breach their minimum stock thresholds:
1. Navigate to **Purchase Orders**.
2. Click **Auto-Draft from Low Stock**.
3. The system automatically groups low-stock items by preferred supplier and creates pre-filled draft POs.

### 10.3 PO Lifecycle & Receiving
1. **Approve PO**: Manager approves draft (`Draft` → `Approved`).
2. **Order PO**: Vendor confirms order (`Approved` → `Ordered`).
3. **Receive Hardware**: When the shipment arrives at the dock:
   - Click **Receive Items**.
   - Input the physical serial numbers for each delivered line item.
   - Confirm receipt. Status moves to `Received`, and new `in_stock` serialized inventory items are instantly created in the designated warehouse rack.

---

## 11. Cycle Counts & Physical Stock Audits

Maintain inventory accuracy without shutting down the warehouse:

### 11.1 Creating a Cycle Count (`/stock-counts`)
1. Click **New Stock Count**.
2. Choose audit scope:
   - **By Location** (e.g., audit all items in `Rack A-01`).
   - **By Category** (e.g., audit all `Industrial Cameras`).
3. Click **Start Count**. The count enters `In Progress` status.

### 11.2 Barcode & Serial Scanning
1. Use a handheld USB/Bluetooth barcode scanner or manual keyboard entry.
2. Scan each physical item's serial number into the scan input.
3. The system matches scanned serials against registered expected inventory in real time.

### 11.3 Reviewing Variance & Reconciliation
1. Review the variance table:
   - **Expected vs. Counted**: Highlights missing items or unexpected items found in the rack.
2. Click **Reconcile Stock Count**.
3. Enter audit reconciliation notes and finalize. The system updates inventory states and logs an audit ledger transaction.

---

## 12. Financial Valuation & Cost Accounting

Navigate to **Valuation** (`/valuation`) for managerial accounting and balance sheet audits:

### 12.1 Costing Methods
Toggle between three standard financial costing algorithms:
- **FIFO (First-In, First-Out)**: Assumes oldest acquired hardware is sold first.
- **LIFO (Last-In, First-Out)**: Assumes newest acquired hardware is sold first.
- **Weighted Average Cost**: Blends unit costs across active inventory batches.

### 12.2 Cost of Goods Sold (COGS)
- View total acquisition costs of sold hardware versus current stock value.
- Breakdown by product category and brand.

### 12.3 Exporting Financial Data
Click **Export Valuation to CSV** to download complete inventory valuation spreadsheets for finance audits.

---

## 13. Audit Trail & Transaction Ledger

Every movement and lifecycle mutation in BitmapInventory is immutable and permanently recorded:

### 13.1 Viewing the Audit Ledger (`/audit`)
Access the chronological timeline of all activities:
- Timestamp & Responsible User
- Action Type (`Stock In`, `Transfer`, `Loan Out`, `Return`, `Sold`, `RMA`, `Decommission`)
- Serial Number & Model
- Origin & Destination Locations
- Document references (Loan Form No, Customer PO, Supplier PO, Reference No)

### 13.2 Advanced Search & Filters
- Filter by **Transaction Type**.
- Filter by **Date Range**.
- Full-text search across **Model Name**, **Brand**, **Serial Number**, and **Loan Form Number**.

### 13.3 Exporting Audit Trail
Click **Export Audit to CSV** to generate a compliance report.

---

## 14. User Administration & Emergency Password Resets

*(Super Administrator Role Only)*

Manage team credentials under **Settings** → **Users** (`/settings/users`):

### 14.1 Creating Users
1. Click **Add User**.
2. Enter **Full Name**, unique **Username**, **Email Address**, temporary **Password**, and assign a **Role** (`admin`, `warehouse`, `engineer`, `viewer`).
3. Click **Create User**.

### 14.2 Deactivating / Activating Accounts
Click **Toggle Status** next to any user to immediately revoke or restore system access without deleting historical audit trail relationships.

### 14.3 Generating Superadmin Emergency Reset Codes
When a user forgets their password and cannot receive emails:
1. Find the user in `/settings/users`.
2. Click **Generate Reset Code**.
3. Provide the generated 8-character code to the user.
4. The user navigates to `/reset-password` on the login screen, enters the code along with their username, and sets a new password.
5. Once used or if compromised, click **Revoke Code** to invalidate it immediately.

---

## Quick Reference Summary

| Need to... | Go to... | Recommended Role |
| :--- | :--- | :--- |
| Check overdue equipment & low stock | `/dashboard` | All |
| Register new hardware arrival | `/inventory/create` | Warehouse / Admin |
| Send demo camera to customer | `/inventory` → `Loan Out` | Engineer / Admin |
| Return camera from customer | `/inventory` → `Return from Loan` | Engineer / Admin |
| Convert POC loan to purchase | `/inventory` → `Convert to Sale` | Engineer / Admin |
| Send broken unit to vendor | `/inventory` → `Dispatch RMA` | Engineer / Admin |
| Reorder depleted items | `/purchase-orders` → `Auto-Draft` | Warehouse / Admin |
| Physical inventory stock take | `/stock-counts/create` | Warehouse / Admin |
| Export asset valuation report | `/valuation` → `Export CSV` | Auditor / Admin |
| Export compliance audit ledger | `/audit` → `Export CSV` | Auditor / Admin |
| Issue password reset code | `/settings/users` | Super Admin |
