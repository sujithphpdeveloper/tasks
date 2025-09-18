# Advanced CRUD Assignment

A robust, production-ready RESTful API-based Task Management System built with Laravel.

The project demonstrates a strong understanding of modern backend architecture, data modeling, and API development best practices.



## Features

- Task Management with advanced filtering, sorting, pagination
- Models with different types of relationships
- Soft Deletes and restore for the Tasks
- Optimistic Locking using the versions
- Audit logging for the Tasks for create, updated and delete actions
- Authentication implemented using JWT
- Implemented Database migrations and seeders
- Role based authorizations
- Full text search and other table indexing
- Eager loading for performance
- Implemented tests using PHPUnit

---

# Project Setup

These instructions will get a copy of the project up and running on your local machine.

## Requirements

- PHP >= 8.3
- Composer
- MySQL on any equivalent

## Installation

### Clone the repository
```bash
git clone https://github.com/sujithphpdeveloper/tasks.git
cd tasks
```
### Install dependencies
```bash
composer install
```
### Setup environment
Create a copy of the .env.example file and rename it to .env.
```bash
cp .env.example .env
```
Update Database settings in .env
```bash
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=database_user_name
DB_PASSWORD=database_password
```
### Generate app key
Generate the application key for laravel
```bash
php artisan key:generate
```
### Database Migration and Seeding
Run the database migrations to set up the tables required by the application
```bash
php artisan migrate
```
Run the seed if you want to populate the database with dummy data
```bash
php artisan db:seed
```
Run the migration and seed in a single command
```bash
php artisan migrate:fresh --seed
```
### Set File Permissions
Make sure the web server have the enough permission for the laravel recommended folders
```bash
chmod -R 775 storage
```
### Run the Web Server
For development and real testing, use Laravel's built-in server.
```bash
php artisan serve
```
This will start the server at http://127.0.0.1:8000 and all APIs will be available at http://127.0.0.1:8000/api/v1

If the port 8000 is busy, the server will display the available URL after the command.

### JWT Package

The package is already added in the configuration file. 
#### Publishing the configuration for JWT
```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```
#### Generate secret key for JWT
```bash
php artisan jwt:secret
```
---

## API Endpoints
### Authentication Endpoints
Database seeders have dummy users, use the below credentials if you run the seeder.
#### Admin Credentials
```bash
Login: admin@example.com
Password: password
```

#### User Credentials
```bash
Login: demo@example.com
Password: password
```

#### Endpoints

| Method | Endpoint         | Description                                 |
| --- |------------------|---------------------------------------------|
| POST   | `/api/v1/login`  | Login the admin/user with their credentials |
| POST   | `/api/v1/logout` | Logout the admin/user                       |
| POST   | `/api/v1/me`     | Get the logged in admin/user details        |
---
### 1. Login
This API authenticates the admin/user by verifying their credentials and, issues a JWT token if its success.
This JWT token must include in the headers of all requests.

**Request**
```bash
POST /api/v1/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```
**Success Response (200)**
```bash
{
    "message": "User Login successfully",
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOi...",
    "token_type": "bearer",
    "expires_in": 3600
}
```
**Error Response (401)**
```bash
{
  "message": "Invalid credentials"
}
```
---
### 2. Me
Retrieve the details of the logged in admin/user
**Request**
```bash
GET /api/v1/me
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**
```bash
{
    "id": 40,
    "name": "Admin",
    "email": "admin@example.com",
    "role": "admin",
    "created_at": "2025-09-16T07:47:13.000000Z",
    "updated_at": "2025-09-16T07:47:13.000000Z"
}
```
**Error Response (401)**
```bash
{
    "message": "Unauthenticated."
}
```
---
### 3. Logout
Invalidate the JWT token and logged out the user
**Request**
```bash
POST /api/v1/logout
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**
```bash
{
    "message": "User Successfully Logged Out"
}
```
**Error Response (401)**
```bash
{
    "message": "Unauthenticated."
}
```
---
## Task Endpoints
Below table is the list of APIs available for the Tasks. All APIs are only allowed for the authenticated users.

| Method | Endpoint                           | Description                                                                          |
| --- |---|---|
| GET    | `/api/v1/tasks`                    | List all tasks with filters, search, pagination, sorting and based on the user role. |
| GET    | `/api/v1/tasks/{id}`               | Get single task details with relations|
| POST   | `/api/v1/tasks`                    | Create new task|
| PUT    | `/api/v1/tasks/{id}`               | Update existing task with optimistic locking using versions|
| DELETE | `/api/v1/tasks/{id}`               | Soft delete task|
| PATCH  | `/api/v1/tasks/{id}/restore`       | Restore the deleted task|
| PATCH  | `/api/v1/tasks/{id}/toggle-status` | Toggle the status of a task|

**All of these endpoints require a valid Bearer JWT token in the Authorization header for authentication.**
### 1. List All Tasks
This API will retrieve all tasks based on the user role, filters and pagination parameters.

**Request**
```bash
GET /api/v1/tasks
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Parameters**

| Parameter | Type | Required | Description                                                           |
| ---  | --- | --- |-----------------------------------------------------------------------|
| **Filters** | | |                                                                       |
| `status` | enum | No | Filter by task status                                                 |
| `priority` | enum | No | Filter by priority                                                    |
| `assigned_to` | integer | No | Show tasks assigned to a specific user                                |
| `tags` | comma-list | No | Filter by multiple tag IDs (e.g., `tags=1,3`)                         |
| `due_date_from` | date | No | Tasks due date on or after this date                                  |
| `due_date_to` | date | No | Tasks due date on or before this date                                 |
| `keyword` | string | No | Search in `title` and `description`                                   |
| **Pagination** | | |                                                                       |
| `page` | integer | No     | Page number, default is 1                                             |
| `per_page` | integer | No     | No of items for the pagination, default is 10 and maximum is 100      |
| `cursor` | string | No     | Cursor for the pagination                                             |
| **Sorting** | | |                                                                       |
| `sort_by` | string     | No     | Sort the tasks by `created_at`/`due_date`/`priority`/`title` |
| `sort_direction` | string     | No     | `asc` or `desc` (default: `asc`)                                      |


Filtering, Sorting and Pagination parameters can pass through the URL, view some examples

```bash
/api/v1/tasks?status=pending
/api/v1/tasks?status=pending&priority=low
/api/v1/tasks?keyword=deadline&due_date_to=2025-11-25
```
**Success Response (200)**

Response with all task details based on the request
```bash
    {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "Dolores eius magni quae voluptatem officiis.",
                "description": "Assumenda vitae expedita d...",
                "status": "in_progress",
                "priority": "medium",
                "due_date": null,
                "assigned_to": 42,
                "version": 1,
                "metadata": {
                    "note": "This is a sample note"
                },
                "deleted_at": null,
                "created_at": "2025-09-16T07:47:13.000000Z",
                "updated_at": "2025-09-16T07:47:13.000000Z"
            }
        ],
        "first_page_url": "http://127.0.0.1:8000/api/v1/tasks?page=1",
        "from": 1,
        "last_page": 20,
        "last_page_url": "http://127.0.0.1:8000/api/v1/tasks?page=20",
        "links": [...],
        "next_page_url": "http://127.0.0.1:8000/api/v1/tasks?page=2",
        "path": "http://127.0.0.1:8000/api/v1/tasks",
        "per_page": 1,
        "prev_page_url": null,
        "to": 1,
        "total": 20
    }
```
**Error Response (401)**
```bash
{
    "message": "Unauthenticated."
}
```
---

### 2. Single Task Details
Get the details of Task using ID

**Request**
```bash
GET /api/v1/tasks/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**

Return the Task with its relation values
```bash
{
    "id": 64,
    "title": "Ratione culpa eaque quia porro tenetur.",
    "description": "Non neque sit om ...",
    "status": "in_progress",
    "priority": "low",
    "due_date": "2025-10-03T00:00:00.000000Z",
    "assigned_to": 44,
    "version": 1,
    "metadata": {
        "note": "This is a sample note"
    },
    "deleted_at": null,
    "created_at": "2025-09-16T07:47:13.000000Z",
    "updated_at": "2025-09-16T07:47:13.000000Z",
    "user": {
        "id": 44,
        "name": "Rocky Toy",
        ...
      },
    "tags": [
        {
            "id": 8,
            "name": "Social Media Marketing",
            "color": "#832e7c",
            ...
        },
        {
            "id": 10,
            "name": "Email Marketing",
            "color": "#76ec37",
            ...
            }
        }
    ]
}
```
**Error Response (404)**
```bash
{
  "message": "No query results for model [App\\Models\\Task] 6"
}
```
---

### 3. Create a new Task
**Request**
```bash
POST /api/v1/tasks/
Content-Type: application/json
Authorization: Bearer <jwt_token_received>

Sample Task Content
{
    "title": "Title of the Task",
    "description": "Some description will go",
    "status": "pending",
    "priority": "low",
    "due_date": "2025-12-08",
    "assigned_to": 42,
    "metadata": {
        "note": "This is a sample note"
    }
}
```
**Parameter List**

| Field | Type | Required | Notes|
| --- | --- | --- |----|
| `title` | string | Yes | Minimum 5 characters |
| `description` | string | No | Optional task details |
| `status` | enum | No | Any of from `pending`, `in_progress`, `completed` (default: `pending`) |
| `priority` | enum | No | Any of from  `low`, `medium`, `high` (default: `medium`) |
| `due_date` | date | No | Must not be in the past if `status = pending` or `in_progress` |
| `assigned_to` | integer (user\_id) | No | Must be a valid user id |
| `tags` | array\[int] | No | Array of tag IDs |
| `metadata` | JSON | No | Array of additional information |


**Success Response (200)**

Will return the created task details with user and tags
```bash
{
    "message": "Task created successfully",
    "task": {
        "title": "title of the task",
        "description": null,
        "status": "pending",
        "priority": "low",
        "due_date": "2025-12-08T00:00:00.000000Z",
        "assigned_to": 42,
        "version": 1,
        ...
    }
}
```
**Error Response (422)**
```bash
{
    "message": "The title field is required.",
    "errors": {
        "title": [
            "The title field is required."
        ]
    }
}
```
---

### 4. Update the Task
**Request**
```bash
PUT /api/v1/tasks/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token_received>

Sample Task Content
{
    "title": "This is new title",
    "priority": "high",
    "assigned_to": 42,
    "version": 1
}
```
Each update request should have a version, this version is using for the optimistic locking.
If there is any mismatch the version sending and the existing one in the database won't allow to update the task.

**Parameter List**

| Field         | Type | Required | Notes                                                                            |
|---------------| --- | --- |----------------------------------------------------------------------------------|
| `title`       | string | Yes | Minimum 5 characters                                                             |
| `description` | string | No | Optional task details                                                            |
| `status`      | enum | No | Any of from `pending`, `in_progress`, `completed` (default: `pending`)           |
| `priority`    | enum | No | Any of from  `low`, `medium`, `high` (default: `medium`)                         |
| `due_date`    | date | No | Must not be in the past if `status = pending` or `in_progress`                   |
| `assigned_to` | integer (user\_id) | No | Must be a valid user id                                                          |
| `tags`        | array\[int] | No | Array of tag IDs                                                                 |
| `metadata`    | JSON | No | Array of additional information                                                  |
| `version`     | integer | Yes | This field is using for optimistic locking, value should be same as the Database |


**Success Response (200)**

Will return the updated task details with user and tags
```bash
{
    "message": "Task updated successfully",
    "task": {
        "title": "title of the task",
        "status": "pending",
        "priority": "high",
        "due_date": "2025-12-08T00:00:00.000000Z",
        "version": 2,
        ...
    }
}
```
**Error Response -  Validation Errors(422)**
```bash
{
    "message": "The title field is required.",
    "errors": {
        "title": [
            "The title field is required."
        ]
    }
}
```
**Error Response - Optimistic Locking(409)**
```bash
{
    "message": "Task has been modified by another user. Please refresh and try again."
}
```
---

### 5. Delete a Task
Soft Delete the task using the ID

**Request**
```bash
DELETE /api/v1/tasks/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**
```bash
{
    "message": "Task deleted successfully"
}
```
**Error Response (404)**
```bash
{
    "message": "No query results for model [App\\Models\\Task] 68"
}
```
---
### 6. Restore the deleted Task
Restore the deleted task using the API. Now this is allowed for Admin and User can restore their own deleted tasks too.
**Request**
```bash
PATCH /api/v1/tasks/{id}/restore
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**
```bash
{
    "message": "Task restored successfully",
    "task": {
        "id": 68,
        "title": "this is new title",
        "description": null,
        "status": "pending",
        "priority": "low",
        "due_date": "2025-12-08T00:00:00.000000Z",
        ...
    }
}
```
**Error Response (404)**
```bash
{
    "message": "No query results for model [App\\Models\\Task] 68"
}
```
---
### 7. Toggle the status of the Task
This API will toggle the status of the task as below

- pending --> in_progress
- in_progress --> completed
- completed --> pending

**Request**
```bash
PATCH /api/v1/tasks/{id}/toggle-status
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```

**Success Response (200)**
```bash
{
    "message": "Task status updated successfully",
    "task": {
        "id": 68,
        "title": "this is new title",
        "description": null,
        "status": "completed",
        ...
    }
}
```

**Error Response (404)**
```bash
{
    "message": "No query results for model [App\\Models\\Task] 68"
}
```
---

## Tag Endpoints
Below table is the list of APIs available for the Tags. All APIs are only allowed for the authenticated users.

| Method | Endpoint            | Description         |
| --- |---------------------|---------------------|
| GET    | `/api/v1/tags`      | List all tags       |
| POST   | `/api/v1/tags`      | Create new Tag      |
| PUT    | `/api/v1/tags/{id}` | Update existing tag |
| DELETE | `/api/v1/tags/{id}` | Soft delete tag     |

**All of these endpoints require a valid Bearer JWT token in the Authorization header for authentication.**
### 1. List All Tags
This API will retrieve all tags.

**Request**
```bash
GET /api/v1/tags
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**

Response with all tags
```bash
[
    {
        "id": 6,
        "name": "Content Marketing",
        "color": "#35cbfa",
        "deleted_at": null,
        "created_at": "2025-09-16T07:47:13.000000Z",
        "updated_at": "2025-09-16T07:47:13.000000Z"
    },
    {
        "id": 7,
        "name": "Search Engine Optimization",
        "color": "#6f8550",
        "deleted_at": null,
        "created_at": "2025-09-16T07:47:13.000000Z",
        "updated_at": "2025-09-16T07:47:13.000000Z"
    },
    ...
]
```
**Error Response (401)**
```bash
{
    "message": "Unauthenticated."
}
```
---

### 2. Create a new Tag
**Request**
```bash
POST /api/v1/tags/
Content-Type: application/json
Authorization: Bearer <jwt_token_received>

Sample Task Content
{
    "name": "Accounts",
    "color": "#ff00ff",
}
```
**Parameter List**

| Field         | Type | Required | Notes                                      |
|---------------| --- | --- |--------------------------------------------|
| `name`        | string | Yes | Name of the Tag. The name should be Unique |
| `color`       | string | No | Color value of the Tag                     |


**Success Response (200)**

```bash
{
    "message": "Tag created successfully",
    "tag": {
        "name": "Account",
        "color": "#ff00ff",
        "updated_at": "2025-09-16T17:30:50.000000Z",
        "created_at": "2025-09-16T17:30:50.000000Z",
        "id": 16
    }
}
```
**Error Validation (422)**
```bash
{
    "message": "The name has already been taken.",
    "errors": {
        "name": [
            "The name has already been taken."
        ]
    }
}
```
---

### 4. Update the Tag
**Request**
```bash
PUT /api/v1/tags/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token_received>

Sample Tag Content
{
    "name": "Finance",
    "color": "#00ff00",
}
```
**Parameter List**

| Field         | Type | Required | Notes                                                                                        |
|---------------| --- |----------|----------------------------------------------------------------------------------------------|
| `name`        | string | No       | The field is not mandatory for udpate other values and name field is not allowed NULL value. |
| `color`       | string | No       | Color value of the Tag                                                                       |


**Success Response (200)**

Will return the updated tag details
```bash
{
    "message": "Tag updated successfully",
    "tag": {
        "id": 16,
        "name": "Finance",
        "color": "#00FF00",
        "deleted_at": null,
        "created_at": "2025-09-16T17:30:50.000000Z",
        "updated_at": "2025-09-16T17:41:42.000000Z"
    }
}
```
**Error Response -  Validation Errors(422)**
```bash
{
    "message": "The name field is required.",
    "errors": {
        "name": [
            "The name field is required."
        ]
    }
}
```
---

### 4. Delete a Tag
Soft Delete the tag using the ID

**Request**
```bash
DELETE /api/v1/tags/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**
```bash
{
    "message": "Tag deleted successfully"
}
```
**Error Response (404)**
```bash
{
    "message": "No query results for model [App\\Models\\Task] 68"
}
```
---
## User Endpoints
For the forms and filters we need the user list

| Method | Endpoint            | Description         |
| --- |---------------------|---------------------|
| GET    | `/api/v1/users`     | List all users      |


**This endpoints require a valid Bearer JWT token in the Authorization header for authentication.**
### 1. List All Users
This API will retrieve all Users.

**Request**
```bash
GET /api/v1/users
Content-Type: application/json
Authorization: Bearer <jwt_token_received>
```
**Success Response (200)**

Response with all tags
```bash
[
    {
        "id": 1,
        "name": "Admin"
    },
    {
        "id": 2,
        "name": "Demo User"
    },
    {
        "id": 3,
        "name": "Micaela Heathcote"
    },
    ...
]
```
**Error Response (401)**
```bash
{
    "message": "Unauthenticated."
}
```
---

## Test Instructions

The project includes automated PHPUnit tests for controllers, models, and custom behaviors.

### Configure the Test Environment

Copy the configuration file to .env.testing and update the Database connections for testing. 
```bash
cp .env .env.testing
```
Change the Database in the configuration if need different database for testing
```bash
DB_DATABASE=new_testing_database
```

### Running the Tests
**Run All Tests**
```bash
php  artisan test
```
**Run only the TaskControllerTest**
```bash
php artisan test --filter=TaskControllerTest
```
**Run only specific single test** 
```bash
php artisan test --filter=TaskControllerTest::test_unauthenticated_users_cannot_access_tasks
```
**Run only TaskTest for the model**
```bash
php artisan test --filter=TaskTest
```
### Available Tests
**Tests available in TaskControllerTest - Featured Test**
- Unauthenticated users cannot access tasks  
- Admin can view all Tasks 
- Creating a Task with invalid data  
- Updating a Task with invalid data 
- User can only view their own Tasks   
- Task due date cannot be a past date for pending or in_progress status  
- Toggle Task status     
- Optimistic locking of Task   
- Task can be soft deleted   
- Task can be restored   
- Tasks can be filtered by status  
- Tasks can be filtered by priority  
- Tasks can be filtered by date range  
- Tasks can be searched by keyword   
- Tasks can be filtered by tags  
- Tasks can be sorted by field with direction 

**Tests available in TaskTest for the Model - Unit Test**
- Task can be soft Deleted  
- Exclude deleted tasks from queries  
- Deleted tasks can be restored  
- Task belongs to assigned User   
- Task has many Tags
---
## Minimal Frontend
A very simple single page application for implementing the APIs. 

This application is built using the Laravel Blade, Bootstrap, JS and Axios.

### Key Features
- Login Form
- JWT Token based authentication
- Listing tasks with filters
- Create/update/delete tasks
- Toggle status
- Filter by tag or assigned user
- Restore soft-deleted tasks

### How to Run the application
This application is built as part of the same Laravel project. To run it locally, navigate to your project root directory and execute the following command:

```bash
php artisan serve
```
By default, it will run on `http://localhost:8000`. If that port is already in use, the command will automatically find and provide an alternative, available URL for you to access the application.

You can use the same login credentials mentioned in the Authentication Endpoints to access the application.
## Postman Collection
The requested Postman Collection is included in the project root directory

```bash
File Name: tasks.postman_collection.json
```
