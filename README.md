# 📚 Book Finder API

A RESTful Laravel API for searching, saving, and managing favorite books using the Google Books API.

## 🚀 Features

- ✅ User authentication with Laravel Sanctum
- ✅ Google Books API integration
- ✅ Book search and import (Admin only)
- ✅ Favorite books management
- ✅ Role-based access control (Admin/User)
- ✅ Pagination support
- ✅ RESTful API design
- ✅ Unit tests
- ✅ Swagger Documentation

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Laravel 12.x

## 🔧 Installation

1. **Clone the repository**
```bash
git clone https://github.com/Najisadek/book-finder.git
cd book-finder
```

2. **Install dependencies**
```bash
composer install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Update `.env` file**
```env
DB_DATABASE=book_finder
DB_USERNAME=root
DB_PASSWORD=your_password

GOOGLE_BOOKS_API_KEY=your_api_key
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Create admin user**
```bash
php artisan admin:create
```

7. **Start the server**
```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`

## 📚 API Documentation

### Authentication
#### Register
```bash
POST /api/register
Content-Type: application/json

{
  "name": "Sadek Naji",
  "email": "naji@sadek.site",
  "password": "password",
  "password_confirmation": "password"
}
```

#### Login
```bash
POST /api/login
Content-Type: application/json

{
  "email": "naji@sadek.site",
  "password": "password"
}

Response:
{
  "access_token": "1|token...",
  "token_type": "Bearer"
}
```

For more documentation details navigate to `http://127.0.0.1:8000/api/documentation`

## 🧪 Testing
```bash
php artisan test
php artisan test --filter ImportBookTest
```

## 👨‍💻 Author

Naji 