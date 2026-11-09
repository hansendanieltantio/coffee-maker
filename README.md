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

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Cloud–edge simulation

The same Laravel codebase can run as two separate nodes with separate databases. WFQ is calculated on the edge during normal operation; the cloud only runs its scheduler when the ESP32 falls back to the cloud:

- Set `ORDER_NODE_ROLE=cloud` on the public cloud instance. The website writes orders here, and this node serves the ESP32 fallback API.
- Set `ORDER_NODE_ROLE=edge`, `ORDER_NODE_ID=edge-1`, `ORDER_CLOUD_URL=https://your-cloud-host`, and the same `ORDER_NODE_TOKEN` on the local edge instance.
- Set the same long random `ORDER_DEVICE_TOKEN` on both instances and in `deviceApiToken` in the ESP32 sketch.
- Run migrations on both instances. Start the edge API on the LAN, then run `php artisan edge:work` on the edge instance to poll the cloud for new orders and retry status synchronization every `ORDER_SYNC_INTERVAL` seconds.
- Configure `edgeServerAddress` and `cloudServerAddress` in `esp32_coffee_maker.ino`. The ESP32 tries the edge first and uses the cloud when the edge API is unavailable.

Generate long random values for `ORDER_NODE_TOKEN` and `ORDER_DEVICE_TOKEN` and keep them private. The cloud uses the first to authenticate edge synchronization and order-claim requests; the second protects the ESP32 queue and status APIs. The cloud is the source of truth; an edge claim is recorded there before the order is returned to the ESP32, preventing the cloud fallback from claiming the same pending order. The edge calculates WFQ finish times from its synchronized local copy. Run the cloud over HTTPS when it is reachable from outside the LAN.

An order already claimed by an edge is deliberately not automatically reassigned if that edge stops mid-order; this avoids brewing the same physical order twice. Such an interrupted order needs manual status recovery before it can be retried.
