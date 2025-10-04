# Taskati API

A comprehensive Laravel 11 API for task management with JWT authentication and automatic email verification.

## Overview

Taskati API is a robust backend service designed for task management applications. Built with Laravel 11, it provides secure authentication, comprehensive task CRUD operations, and user profile management with automatic email verification.

## Features

### Authentication System
- JWT-based authentication using tymon/jwt-auth
- User registration with automatic email verification
- Secure login/logout functionality
- Password reset capability
- Profile management (info, password, profile image)

### Task Management
- Complete CRUD operations for tasks
- Task status management (todo, doing, done)
- Color-coded task organization
- Time-based task scheduling
- User-specific task isolation
- Task filtering and search capabilities

### Email System
- Automatic email verification (no SMTP required)
- Log-based email handling for development
- Simplified verification process

## API Endpoints

### Authentication
```
POST /api/register          - User registration
POST /api/login             - User login
POST /api/logout            - User logout
POST /api/refresh           - Refresh JWT token
POST /api/profile           - Get user profile
PUT  /api/profile/info      - Update profile information
PUT  /api/profile/password  - Update password
POST /api/profile/image     - Update profile image
POST /api/resend-verification - Resend verification email
```

### Tasks
```
GET    /api/tasks          - Get all user tasks
POST   /api/tasks          - Create new task
GET    /api/tasks/{id}     - Get specific task
PUT    /api/tasks/{id}     - Update task
DELETE /api/tasks/{id}     - Delete task
```

## Installation

### Requirements
- PHP 8.1 or higher
- Composer
- MySQL/MariaDB
- Laravel 11

### Setup Instructions

1. Clone the repository
```bash
git clone https://github.com/mahmoud-y0usef/Taskati-Api.git
cd Taskati-Api
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. Setup database
```bash
php artisan migrate
```

5. Start development server
```bash
php artisan serve
```

## Configuration

### Environment Variables
```env
APP_NAME="Taskati API"
APP_ENV=local
APP_KEY=base64:generated_key
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskati_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

JWT_SECRET=generated_jwt_secret
JWT_TTL=60

MAIL_MAILER=log
```

## Database Schema

### Users Table
- id (primary key)
- name
- email (unique)
- email_verified_at
- password
- image (nullable)
- created_at
- updated_at

### Tasks Table
- id (primary key)
- user_id (foreign key)
- title
- description (nullable)
- date
- start_time
- end_time
- color (0-5 index)
- status (todo, doing, done)
- created_at
- updated_at

## Task Model Structure

Tasks follow this JSON structure for Flutter compatibility:
```json
{
  "id": 1,
  "title": "Task Title",
  "description": "Task Description",
  "date": "2025-10-04",
  "start_time": "09:00:00",
  "end_time": "10:00:00",
  "color": 2,
  "status": "todo",
  "created_at": "2025-10-04T12:00:00.000000Z",
  "updated_at": "2025-10-04T12:00:00.000000Z"
}
```

## Security Features

- JWT token-based authentication
- Password hashing using bcrypt
- SQL injection protection via Eloquent ORM
- CORS configuration for cross-origin requests
- Input validation and sanitization
- Automatic email verification

## Development Notes

- Email verification is automatic (no SMTP configuration required)
- All emails are logged for development purposes
- JWT tokens have configurable TTL (default: 60 minutes)
- Task colors use index system (0-5) for consistency with Flutter
- Time validation ensures end_time is after start_time

## Testing

Run the test suite:
```bash
php artisan test
```

## Production Deployment

1. Set `APP_ENV=production` in .env
2. Set `APP_DEBUG=false`
3. Configure proper database credentials
4. Run `php artisan config:cache`
5. Run `php artisan route:cache`
6. Set up proper web server configuration

## License

This project is open-sourced software licensed under the MIT license.
