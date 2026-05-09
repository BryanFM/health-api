# Health API

![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Sanctum](https://img.shields.io/badge/Sanctum-Auth-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Swagger](https://img.shields.io/badge/Swagger-Docs-85EA2D?style=for-the-badge&logo=swagger&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

A professional **RESTful API** for a Health Management System built with Laravel 8. It provides complete management of patients, doctors, medical specialties, appointments, and clinical history — with role-based access control and interactive API documentation.

---

## Table of Contents

- [About the Project](#about-the-project)
- [Tech Stack](#tech-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Environment Variables](#environment-variables)
- [Default Users](#default-users)
- [API Endpoints](#api-endpoints)
- [Roles & Permissions](#roles--permissions)
- [Project Structure](#project-structure)
- [Running Tests](#running-tests)
- [API Documentation](#api-documentation)

---

## About the Project

Health API is a mid-level REST API designed to power health management platforms. It handles the core workflows of a medical system:

- Secure authentication via **Laravel Sanctum** (token-based)
- Full **CRUD** for patients, doctors, specialties, appointments, and medical records
- **Role-based access control** with three roles: `admin`, `doctor`, and `patient`
- **Conflict detection** when scheduling appointments (no double-booking per doctor)
- **Soft deletes** across all main entities to preserve data integrity
- Standardized JSON responses across all endpoints
- Interactive documentation via **Swagger UI**

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 8 |
| Language | PHP 7.4+ |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel Permission v5 |
| Database | MySQL 8 |
| API Docs | L5-Swagger (OpenAPI 3.0) |
| Testing | PHPUnit (SQLite in-memory) |

---

## Prerequisites

Make sure you have the following installed:

- PHP >= 7.4
- Composer >= 2.x
- MySQL >= 8.0

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/your-username/health-api.git
cd health-api
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Copy and configure the environment file

```bash
cp .env.example .env
```

Edit `.env` with your database credentials (see [Environment Variables](#environment-variables)).

### 4. Generate the application key

```bash
php artisan key:generate
```

### 5. Create the database

```sql
CREATE DATABASE health_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run migrations and seeders

```bash
php artisan migrate --seed
```

### 7. Generate API documentation

```bash
php artisan l5-swagger:generate
```

### 8. Start the development server

```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000`.

---

## Environment Variables

| Variable | Description | Example |
|---|---|---|
| `APP_URL` | Application base URL | `http://127.0.0.1:8000` |
| `DB_CONNECTION` | Database driver | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `3306` |
| `DB_DATABASE` | Database name | `health_api` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | *(empty)* |

---

## Default Users

After running the seeders, the following users are available:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@healthapi.com` | `password` |
| Doctor | `doctor@healthapi.com` | `password` |
| Patient | `patient@healthapi.com` | `password` |

> **Note:** Change these credentials before deploying to production.

---

## API Endpoints

All protected endpoints require the header:
```
Authorization: Bearer <token>
```

### Auth

| Method | Endpoint | Description | Auth |
|---|---|---|---|
| `POST` | `/api/auth/login` | Login and obtain token | Public |
| `POST` | `/api/auth/logout` | Revoke current token | Required |
| `GET` | `/api/auth/me` | Get authenticated user info | Required |

### Patients

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/patients` | List all patients (filterable) |
| `POST` | `/api/patients` | Create a new patient |
| `GET` | `/api/patients/{id}` | Get a patient by ID |
| `PUT` | `/api/patients/{id}` | Update a patient |
| `DELETE` | `/api/patients/{id}` | Soft delete a patient |

### Doctors

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/doctors` | List all doctors (filterable by specialty, availability) |
| `POST` | `/api/doctors` | Create a new doctor |
| `GET` | `/api/doctors/{id}` | Get a doctor by ID |
| `PUT` | `/api/doctors/{id}` | Update a doctor |
| `DELETE` | `/api/doctors/{id}` | Soft delete a doctor |

### Specialties

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/specialties` | List all specialties |
| `POST` | `/api/specialties` | Create a specialty |
| `GET` | `/api/specialties/{id}` | Get a specialty by ID |
| `PUT` | `/api/specialties/{id}` | Update a specialty |
| `DELETE` | `/api/specialties/{id}` | Delete a specialty |

### Appointments

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/appointments` | List appointments (filterable by doctor, patient, status, date) |
| `POST` | `/api/appointments` | Schedule a new appointment |
| `GET` | `/api/appointments/{id}` | Get an appointment by ID |
| `PUT` | `/api/appointments/{id}` | Update an appointment |
| `POST` | `/api/appointments/{id}/cancel` | Cancel an appointment |
| `DELETE` | `/api/appointments/{id}` | Soft delete an appointment |

### Medical Records

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/medical-records` | List all medical records |
| `POST` | `/api/medical-records` | Create a medical record |
| `GET` | `/api/medical-records/{id}` | Get a record by ID |
| `PUT` | `/api/medical-records/{id}` | Update a medical record |
| `DELETE` | `/api/medical-records/{id}` | Soft delete a record |
| `GET` | `/api/patients/{id}/medical-records` | List records for a specific patient |

### Standardized Response Format

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {}
}
```

**Paginated:**
```json
{
  "success": true,
  "message": "Success",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Roles & Permissions

| Permission | admin | doctor | patient |
|---|:---:|:---:|:---:|
| Manage patients (CRUD) | ✅ | 👁 view | ❌ |
| Manage doctors (CRUD) | ✅ | ❌ | ❌ |
| Manage specialties (CRUD) | ✅ | 👁 view | 👁 view |
| Manage appointments | ✅ | 👁 update | ✅ create/cancel |
| Manage medical records | ✅ | ✅ create/edit | 👁 view |
| Manage users | ✅ | ❌ | ❌ |

---

## Project Structure

```
app/
├── Exceptions/
│   └── Handler.php               # Global JSON error handling
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       ├── AuthController.php
│   │       ├── PatientController.php
│   │       ├── DoctorController.php
│   │       ├── SpecialtyController.php
│   │       ├── AppointmentController.php
│   │       └── MedicalRecordController.php
│   ├── Middleware/
│   │   └── Authenticate.php      # API-aware auth redirect
│   └── Requests/
│       ├── Auth/
│       ├── Patient/
│       ├── Doctor/
│       ├── Specialty/
│       ├── Appointment/
│       └── MedicalRecord/
├── Models/
│   ├── User.php
│   ├── Patient.php
│   ├── Doctor.php
│   ├── Specialty.php
│   ├── Appointment.php
│   └── MedicalRecord.php
├── Services/                     # Business logic layer
│   ├── PatientService.php
│   ├── DoctorService.php
│   ├── SpecialtyService.php
│   ├── AppointmentService.php
│   └── MedicalRecordService.php
└── Traits/
    └── ApiResponse.php           # Standardized JSON responses

database/
├── factories/
├── migrations/
└── seeders/
    ├── DatabaseSeeder.php
    ├── RolesAndPermissionsSeeder.php
    ├── SpecialtySeeder.php
    └── UserSeeder.php

tests/
└── Feature/
    └── API/
        ├── AuthTest.php
        ├── PatientTest.php
        ├── DoctorTest.php
        ├── SpecialtyTest.php
        ├── AppointmentTest.php
        └── MedicalRecordTest.php
```

---

## Running Tests

Tests use an **SQLite in-memory database** — no extra configuration needed.

```bash
# Run all tests
php artisan test

# Run a specific test file
php artisan test tests/Feature/API/PatientTest.php

# Run with coverage report
php artisan test --coverage
```

Current test suite: **61 tests, 61 passing**.

---

## API Documentation

Interactive Swagger UI is available at:

```
http://127.0.0.1:8000/api/documentation
```

To authenticate in Swagger UI:
1. Call `POST /api/auth/login` with your credentials
2. Copy the returned `token`
3. Click **Authorize** (top right)
4. Enter `Bearer <your_token>` and confirm

---

## License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).
