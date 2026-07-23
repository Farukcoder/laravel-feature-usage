# 🔥 Laravel Feature Heatmap

<p align="center">
  <img src="https://img.shields.io/packagist/v/farukcoder/laravel-feature-heatmap?style=flat-square&color=06b6d4" alt="Latest Version">
  <img src="https://img.shields.io/packagist/php-v/farukcoder/laravel-feature-heatmap?style=flat-square&color=777BB4" alt="PHP Version">
  <img src="https://img.shields.io/packagist/l/farukcoder/laravel-feature-heatmap?style=flat-square&color=22c55e" alt="License">
  <img src="https://img.shields.io/packagist/dt/farukcoder/laravel-feature-heatmap?style=flat-square&color=f59e0b" alt="Downloads">
</p>

<p align="center">
  <strong>A Powerful Laravel Feature Usage Tracking & Heatmap Dashboard Package by Farukcoder</strong><br>
  Track controllers, routes, users, and feature adoption in your Laravel application with zero configuration.
</p>

---

## ✨ Features

- ⚡ **Zero-Setup Global Tracking** — Automatically registers middleware to track all application routes with zero manual configuration in `web.php` or `Kernel.php`
- 🔥 **Interactive Heatmap Dashboard** — Visualise Feature × Date and Feature × User usage patterns with interactive ApexCharts
- 👤 **User-Wise Tracking & Drill-Down** — Track individual user activity across modules and features with detailed modal drill-downs
- 📥 **CSV Activity Reports** — Export per-user feature interaction reports directly from the dashboard with a single click
- 💤 **Unused Feature Discovery** — Detect features dormant for 30+ days to clean up technical debt and refactor safely
- ⚡ **Non-Blocking Queue Logger** — Dispatches logging jobs asynchronously using Laravel queues to maintain ultra-fast HTTP responses
- 🧹 **Daily Summary & Auto-Pruning** — Aggregates high-volume raw logs into lightweight daily summary records to keep your database fast and lean
- ⚙️ **Highly Configurable** — Easily exclude debug/admin routes, configure authorization gates, user models, and table schema mappings

---

## 📋 What It Tracks

The package focuses on **code-level feature adoption** — identifying exactly which controllers, methods, and routes are used by whom.

| Tracked Data | Source / Description |
|--------------|----------------------|
| **Controller & Action** | e.g. `App\Http\Controllers\OrderController@store` |
| **Route Name** | e.g. `orders.store` |
| **HTTP Method** | `GET`, `POST`, `PUT`, `DELETE`, etc. |
| **Request URI** | Full endpoint URI path |
| **User Identity** | Authenticated `user_id`, resolved with User Name & Email |
| **Timestamps** | High-precision hit timestamps & daily usage frequency |

---

## 📦 Installation

```bash
composer require farukcoder/laravel-feature-heatmap
```

### 🖥️ CLI Output on Installation

```
  ███████╗ █████╗ ██████╗ ██╗  ██╗██╗  ██╗  ██████╗  ██████╗ ██████╗ ███████╗██████╗ 
  ██╔════╝██╔══██╗██╔══██╗██║  ██║██║ ██╔╝ ██╔════╝ ██╔═══██╗██╔══██╗██╔════╝██╔══██╗
  █████╗  ███████║██████╔╝██║  ██║█████╔╝  ██║      ██║   ██║██║  ██║█████╗  ██████╔╝
  ██╔══╝  ██╔══██╗██╔══██╗██║  ██║██╔═██╗  ██║      ██║   ██║██║  ██║██╔══╝  ██╔══██╗
  ██║     ██║  ██║██║  ██║╚██████╔╝██║  ██╗ ╚██████╗ ╚██████╔╝██████╔╝███████╗██║  ██║
  ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═╝ ╚═════╝ ╚═╝  ╚═╝  ╚═════╝  ╚═════╝ ╚═════╝ ╚══════╝╚═╝  ╚═╝

        Laravel Feature Heatmap — Route & Feature Usage Tracker
                  by Farukcoder | github.com/farukcoder
```

> **Note:** The package auto-discovers the service provider and middleware. No manual provider registration is needed for Laravel 10+.

### Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=feature-heatmap-config
```

### Run Migrations

```bash
php artisan migrate
```

This creates two optimized database tables:
- `feature_usage_logs`: Stores raw per-request interaction logs.
- `feature_usage_summaries`: Stores aggregated daily usage statistics for fast heatmap rendering.

---

## 🚀 Usage

### Step 1 — Automatic Global Tracking (Zero Setup)

By default, the package **automatically registers and attaches** the tracking middleware to all `web` and `api` route groups inside the `FeatureHeatmapServiceProvider`. 

**No manual edits to `web.php`, `api.php`, or `Kernel.php` are required!**

> **Optional Manual Mode:**
> If you prefer manual middleware control, set `'auto_track' => false` in `config/feature-heatmap.php` or `FEATURE_HEATMAP_AUTO_TRACK=false` in `.env`.
> You can then apply the named middleware `track.feature` to specific routes or groups:
> ```php
> Route::middleware(['web', 'auth', 'track.feature'])->group(function () {
>     Route::get('/orders', [OrderController::class, 'index']);
> });
> ```

---

### Step 2 — Schedule Daily Aggregation & Pruning

Raw request logs build up quickly on high-traffic sites. Schedule the built-in Artisan command to aggregate raw logs daily and prune old entries.

**Laravel 10 (`app/Console/Kernel.php`):**

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('feature-heatmap:aggregate --prune')->dailyAt('01:00');
}
```

**Laravel 11+ (`routes/console.php`):**

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('feature-heatmap:aggregate --prune')->dailyAt('01:00');
```

---

### Step 3 — Access the Heatmap Dashboard

Navigate to the dashboard in your web browser:

```
http://your-domain.test/feature-heatmap
```

---

## 👤 User-Wise Tracking & CSV Export

The dashboard includes a dedicated **User-Wise Tracking Table**:

1. **User Summary Table**: Lists all active users, total interaction count, distinct features used, and last active date.
2. **Module Drill-Down**: Click on any user row to launch an interactive breakdown showing exact features accessed by that specific user across dates.
3. **CSV Export**: Click the **Download CSV Report** button on any user row to generate a complete report file (`heatmap-report-{username}-{date}.csv`).

---

## ⚙️ Configuration

After publishing, edit `config/feature-heatmap.php`:

```php
return [
    // Enable or disable the package globally
    'enabled' => env('FEATURE_HEATMAP_ENABLED', true),

    // Automatically attach middleware to web/api routes
    'auto_track' => env('FEATURE_HEATMAP_AUTO_TRACK', true),

    // Use queues for non-blocking database writes
    'use_queue' => env('FEATURE_HEATMAP_USE_QUEUE', true),
    'queue_name' => env('FEATURE_HEATMAP_QUEUE', 'default'),

    // Retention period for raw interaction logs (in days)
    'raw_log_retention_days' => 14,

    // Excluded URIs (regex patterns)
    'excluded_patterns' => [
        '^_debugbar',
        '^horizon',
        '^telescope',
        '^feature-heatmap',
    ],

    // Dashboard route prefix & middleware
    'route_prefix' => 'feature-heatmap',
    'route_middleware' => ['web', 'auth'],

    // User model column resolution
    'user_name_column' => 'name',
    'user_email_column' => 'email',
];
```

### Environment Variables

Add these to your `.env` file to customize behavior:

```env
FEATURE_HEATMAP_ENABLED=true
FEATURE_HEATMAP_AUTO_TRACK=true
FEATURE_HEATMAP_USE_QUEUE=true
FEATURE_HEATMAP_QUEUE=default
```

---

## 🛠️ Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan feature-heatmap:aggregate` | Aggregates raw logs into daily summary statistics |
| `php artisan feature-heatmap:aggregate --prune` | Aggregates raw logs and deletes entries older than `raw_log_retention_days` |

---

## 🌐 API Endpoints

The dashboard exposes internal JSON & CSV endpoints under the configured route prefix (`/feature-heatmap`):

| Method | URI | Description |
|--------|-----|-------------|
| `GET` | `/feature-heatmap` | Renders the HTML Dashboard UI |
| `GET` | `/feature-heatmap/data` | Returns global heatmap data for charts |
| `GET` | `/feature-heatmap/users` | Returns user summary table data |
| `GET` | `/feature-heatmap/users/{userId}` | Returns module-wise heatmap detail for a user |
| `GET` | `/feature-heatmap/users/{userId}/report` | Streams downloadable CSV activity report for a user |

---

## 🔧 Laravel & PHP Compatibility

| Laravel Version | PHP Version | Status |
|-----------------|-------------|--------|
| 10.x | 8.1+ | ✅ Supported |
| 11.x | 8.2+ | ✅ Supported |
| 12.x | 8.2+ | ✅ Supported |
| 13.x | 8.3+ | ✅ Supported |

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request:

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📝 Changelog

### v1.0.0 (2026-07-23)
- 🎉 Initial release of `farukcoder/laravel-feature-heatmap`
- ⚡ Zero-setup automatic global route tracking
- 🔥 Interactive Feature × Date and Feature × User ApexCharts heatmaps
- 👤 User-wise tracking table with modal drill-downs
- 📥 Streamed CSV user activity report exporter
- 🧹 Automated log aggregation and pruning Artisan command
- 💤 Unused feature detection engine

---

## 🛡️ Security

If you discover any security-related issues, please report them directly to the author instead of using the public issue tracker.

---

## 📄 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

---

## 👨‍💻 Author

**Md. Omar Faruk** — Full Stack Software Engineer

- GitHub: [@farukcoder](https://github.com/farukcoder)

---

<p align="center">
  Made with ❤️ by <a href="https://github.com/farukcoder">Farukcoder</a>
</p>
