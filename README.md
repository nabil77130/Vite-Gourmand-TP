# Vite & Gourmand - Food Delivery App

This project is a Food Delivery Application developed with **Symfony 6.4/7.0**.

## Requirements
- PHP 8.2+
- Composer
- Symfony CLI
- SQLite (for dev) or MariaDB/MySQL (for prod)

## Installation (Quick Start)

Run the following command to install dependencies, set up the database, and load test data:

```bash
make install
```

Start the development server:

```bash
make start
```

## Admin Access
-   **URL**: `http://127.0.0.1:8000/admin` (to be implemented)
-   **Email**: `admin@julies.com`
-   **Password**: `password`

## User Access
-   **Email**: `user@julies.com`
-   **Password**: `password`

## Database
To reset the database and reload fixtures at any time:

```bash
make db
```

## Technical details
-   **Backend**: Symfony 6.4
-   **Database**: SQLite (Dev) / MariaDB (Prod)
-   **Frontend**: Twig + CSS
