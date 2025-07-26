# Reservations API (Laravel Backend)

This is the backend API for a space reservation system, built with Laravel 12. It provides a robust RESTful API to manage users, spaces, and reservations.

## Features

* **Authentication:** Secure token-based authentication using Laravel Sanctum.
* **User Management:** Full CRUD operations for users with distinct 'user' and 'admin' roles.
* **Space Management:** CRUD functionality for managing reservable spaces (rooms, desks), restricted to administrators.
* **Reservation Management:** Endpoints for creating, viewing, updating, and canceling reservations.
* **API Documentation:** Comprehensive documentation available through a Postman collection and auto-generated Swagger UI.
* **Testing:** A full suite of PHPUnit tests to ensure API reliability.

## Prerequisites

* Docker
* Docker Compose

## Installation

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/freeman1610/reservationesapp.git
    cd reservationesapp
    ```

2.  **Create Environment File**
    Copy the example environment file and create your own `.env` file.
    ```bash
    cp .env.example .env
    ```
    *Note: The default configuration is set up to work with the provided Docker Compose setup. You shouldn't need to change the `DB_` variables.*

3.  **Build and Start Docker Containers**
    This command will build the images and run the application, Nginx, and database containers in the background.
    ```bash
    docker-compose up -d --build
    ```

4.  **Install Composer Dependencies**
    Install the required PHP packages inside the `app` container.
    ```bash
    docker-compose exec app composer install
    ```

5.  **Generate Application Key**
    ```bash
    docker-compose exec app php artisan key:generate
    ```

6.  **Run Migrations and Seeders**
    This will set up the database schema and populate it with initial data, including an admin and a regular user.
    ```bash
    docker-compose exec app php artisan migrate --seed
    ```
    **Test Credentials:**
    * **Admin:** `admin@example.com` / `password`
    * **User:** `user@example.com` / `password`

7.  **Create Storage Link**
    This makes the `storage/app/public` directory accessible from the web.
    ```bash
    docker-compose exec app php artisan storage:link
    ```

The application is now running and accessible at `http://localhost:8000`.

## Testing

To run the full test suite, execute the following command. The tests run on an in-memory SQLite database to avoid affecting your development data.

```bash
docker-compose exec app php artisan test
```
## API Documentation
The API is documented using both Postman and Swagger.

## Postman

1. **Import Collection:**

    Import the PostmanReservationsAPI.json file located in the root of the project into your Postman client.

    2. **Set Base URL:** 
    The collection uses a {{baseUrl}} variable. Make sure it is set to http://localhost:8000.

    3. **Authentication:**
*  First, run the Authentication > Login request with valid credentials (e.g., the admin user).
* The request includes a test script that automatically saves the received access_token to a collection variable named {{token}}.
*  All protected endpoints are pre-configured to use this {{token}} for Bearer Token authentication.

## Swagger (OpenAPI)
The Swagger UI provides interactive API documentation directly in your browser.

1. **Access the Documentation:**

    Navigate to http://localhost:8000/api/documentation.

2. **Authorize Requests:**
*  To test protected endpoints, click the Authorize button at the top of the page.
*  In the popup, enter your access token in the format Bearer <your_token>. You can get a token from the Login endpoint.
* You can now test all endpoints directly from the UI.

3. **Generate Documentation:**

    If you make changes to the API annotations in the controller files, you can regenerate the documentation with this command:

    ```bash
    docker-compose exec app php artisan l5-swagger:generate
    ```