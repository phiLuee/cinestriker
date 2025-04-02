# Cinestriker

**Cinestriker** is a modern film rating portal built with [Laravel](https://laravel.com/), [Livewire](https://laravel-livewire.com/), and [Volt](https://github.com/livewire/volt). The project also leverages additional packages such as [Spatie Laravel Data](https://spatie.be/docs/laravel-data/v4/introduction), [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6/introduction), and [robsontenorio/mary](https://github.com/robsontenorio/mary) to efficiently handle data transformation and user permissions.

## Features

-   **Film Ratings & Reviews:** Users can rate films and write reviews.
-   **Dynamic Interface:** Real-time UI updates with Livewire and Volt without full page reloads.
-   **Filtering & Sorting:** Search by film title and filter options like "only rated films" or "only my reviews".
-   **User Management:** Robust role and permission management using Spatie Laravel Permission.
-   **Modern Frontend:** Utilizes Vite for fast and modern asset management.

## Installation

### Prerequisites

-   **PHP 8.2** or higher
-   **Composer**
-   **Node.js** and **npm**
-   A database (e.g. MySQL, PostgreSQL, or SQLite for local development)
-   **Git** (optional, for cloning the repository)

### Steps

1. **Clone the Repository or Create a New Laravel Project:**

    ```bash
    git clone https://github.com/your-username/cinestriker.git
    cd cinestriker
    ```

2. **Set Up Environment Variables:**
   cp .env.example .env

    Update the `.env` file with your database and application settings.

3. **Install Dependencies:**

    ```bash
    composer install
    php artisan key:generate
    npm install
    npm run build
    ```

4. **Run Migrations:**

    ```bash
    touch database/database.sqlite
    php artisan migrate
    ```

5. **Seed the Database:**

    ```bash
    php artisan db:seed
    ```

6. **Run the Application:**

    ```bash
    php artisan serve
    ```

7. **Access the Application:**
   Open your web browser and navigate to `http://localhost:8000`.
   You should see the Cinestriker homepage.
   You can log in with the following credentials:

    User: admin@example.com/user@example.com
    Pass: same as Field User

## Docker Compose (Experimental)

You can also run this project using Docker Compose. Please note that the provided Docker Compose configuration is experimental and has not been extensively tested. In particular, there may be issues with Xdebug integration. Use this setup at your own risk and feel free to adjust the configuration as needed for your environment.

**License**
This project is licensed under the MIT License.

**Credits**

-   Developed with [Laravel](https://laravel.com), [Livewire](https://laravel-livewire.com), and [Volt](https://github.com/livewire/volt).
-   Additional packages provided by [Spatie](https://spatie.be) and [robsontenorio/mary](https://github.com/robsontenorio/mary).
