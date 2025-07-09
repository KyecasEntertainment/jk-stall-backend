# 🛒 Inventory & Sales Management Backend (Laravel)

A Laravel backend system to manage products, stock batches, inventory activities, sales tracking, and real-time analytics. It is designed to be consumed by a Unity-based frontend.

---

## 🧩 Tech Stack

- **Backend**: Laravel 10+
- **Frontend**: Unity (via API calls)
- **Database**: Apache / MySQL
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

```
---
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
=======