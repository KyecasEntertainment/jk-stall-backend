# 🛒 Inventory & Sales Management Backend (Laravel)

A Laravel backend system to manage products, stock batches, inventory activities, sales tracking, and real-time analytics. It is designed to be consumed by a Unity-based frontend.

---

## 🧩 Tech Stack

- **Backend**: Laravel 10+
- **Frontend**: Unity (via API calls)
- **Database**: MySQL / MariaDB
- **Primary Models**: 
  - `ProductsList`
  - `StockBatches`
  - `TotalProductQuantity`
  - `SalesHistory`
  - `DailyStockActivity`

---

## 🎯 Features

- ✅ Product & Batch creation/update/deletion
- 📦 Display, return, discard, or replace product quantities
- 📊 Daily and monthly sales/profit calculations
- 📈 Real-time analytics comparing today vs. yesterday
- 🔄 Full integration with Unity via HTTP API

---

## 📡 API Endpoints

Base URL: `/api/inventory/`

### 🔧 Product Management
| Endpoint | Method | Description |
|---------|--------|-------------|
| `products/create` | POST | Create a new product |
| `products/view` | GET | View all products |
| `products/update/{id}` | POST | Update a product name |
| `products/delete/{id}` | DELETE | Delete a product |

### 📦 Batch Management
| Endpoint | Method | Description |
|---------|--------|-------------|
| `batch/create` | POST | Create a new stock batch |
| `batch/view` | GET | View all batches |
| `batch/update/{batch_id}/{product_id}` | POST | Update a specific batch |
| `batch/delete/{id}` | DELETE | Delete a batch |
| `batch/delete-product/{id}` | DELETE | Remove a product from a batch (may affect totals) |

### 📈 Product Quantities
| Endpoint | Method | Description |
|---------|--------|-------------|
| `view-product-status` | GET | View current product stock quantities |
| `display-product` | POST | Display product (deduct from stock) |
| `return-product` | POST | Return displayed product back to stock |
| `discard-product` | POST | Discard damaged/expired product |
| `replace-discarded-product` | POST | Replace previously discarded product |

### 💰 Calculations
| Endpoint | Method | Description |
|---------|--------|-------------|
| `calculate/sold-quantity` | GET | Calculate today's sold products & profit |
| `calculate-profit` | POST | Calculate selected month’s total profit |

### 📊 Analytics
| Endpoint | Method | Description |
|---------|--------|-------------|
| `analytics/fetch` | GET | Retrieve per-product & daily analytics |

### 🧪 Test
| Endpoint | Method | Description |
|---------|--------|-------------|
| `test/request` | POST | Endpoint for testing raw request payload |

---

## 🕹️ Unity Integration

Unity serves as the **frontend UI**, sending HTTP requests to the Laravel backend using `UnityWebRequest`. All data such as product stock, trends, and sales/profit info is rendered in real-time using Unity's interface.

---

## 🚀 Setup Instructions

```bash
# 1. Clone the repository
git clone <your-repo-url>
cd <your-project-folder>

# 2. Install dependencies
composer install

# 3. Set up environment
cp .env.example .env
php artisan key:generate

# 4. Configure .env (DB connection, etc.)

# 5. Run migrations
php artisan migrate

# 6. Serve the application
php artisan serve
