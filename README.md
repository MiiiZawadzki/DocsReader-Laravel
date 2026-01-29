# DocsReader API

![lines](https://github.com/user-attachments/assets/6613ad3d-eecb-4ee7-9d2c-7ff480577374)

This project is a backend API for the **DocsReader** app, built with the Laravel framework. The API provides all the features required by the DocsReader frontend application. The project follows a modular architecture and is currently under active development.

## Authentication

The API uses Laravel Sanctum to handle cookie-based authentication, providing secure session management for client applications.

## Modules

The project is organized into distinct modules located in the `/modules` directory to maintain clean separation of concerns and improve maintainability. Each module encapsulates specific functionality and can be developed independently.

Modules communicate with each other through dedicated API classes located in `/modules/*/Api`, ensuring loose coupling and clear interfaces between different parts of the application.

## Setup

To set up the Laravel application, navigate to the root directory and follow these steps:

1. Copy the `.env.example` file to `.env` and configure your environment variables

2. Install dependencies:
```bash
composer install
```
3. Generate application key:
```bash
php artisan key:generate
```

4. Run database migrations:
```bash
php artisan migrate
```

5. Start the development server:
```bash
php artisan serve
```

## Testing
Run PHPUnit tests using:
```bash
php artisan test
```
