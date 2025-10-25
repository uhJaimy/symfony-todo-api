# Symfony Todo API

A small, clean Symfony REST API for managing tasks (CRUD) with validation and simple API-key authentication.

## Features
- CRUD endpoints for Task entity
- Validation (title required, min 3 chars)
- API-key authentication via X-API-KEY header
- JSON responses and proper HTTP status codes
- Doctrine ORM + migrations

## Requirements
- PHP 8+
- Composer
- MySQL (or compatible DB)
- Symfony CLI (optional)

## Setup
1. Clone repository
2. Copy environment file and set secrets:
  cp .env .env.local
  Set DATABASE_URL and APP_API_KEY in .env.local, e.g.:
  DATABASE_URL="mysql://user:password@127.0.0.1:3306/symfony_api?serverVersion=8.0"
  APP_API_KEY="changeme-super-secret"
3. Install dependencies:
  composer install
4. Create database and run migrations:
  php bin/console doctrine:database:create
  php bin/console make:migration
  php bin/console doctrine:migrations:migrate -n

## Run
Start the local server:
php -S 127.0.0.1:8000 -t public
(or use symfony server:start)

## API Endpoints
All endpoints require the X-API-KEY header.

- POST /api/tasks
  - Create a task
  - Body: { "title": string, "description": string|null, "status": "open"|"done" }

- GET /api/tasks
  - List tasks

- GET /api/tasks/{id}
  - Get single task

- PUT /api/tasks/{id}
  - Update task (partial or full)

- DELETE /api/tasks/{id}
  - Delete task

## Example curl
Create:
curl -X POST http://127.0.0.1:8000/api/tasks \
  -H "X-API-KEY: changeme-super-secret" \
  -H "Content-Type: application/json" \
  -d '{"title":"Buy milk","description":"2L whole milk","status":"open"}'

List:
curl -X GET http://127.0.0.1:8000/api/tasks \
  -H "X-API-KEY: changeme-super-secret"

Update:
curl -X PUT http://127.0.0.1:8000/api/tasks/1 \
  -H "X-API-KEY: changeme-super-secret" \
  -H "Content-Type: application/json" \
  -d '{"title":"Buy milk (updated)","status":"done"}'

Delete:
curl -X DELETE http://127.0.0.1:8000/api/tasks/1 \
  -H "X-API-KEY: changeme-super-secret"

## Validation & Errors
- Title: required, min 3 characters → 422 Unprocessable Entity on validation failure
- Missing/wrong API key → 401 Unauthorized
- Not found → 404 Not Found
- Bad request → 400 Bad Request

## Tests
Manual testing via curl or Postman is supported. Add automated tests under tests/ as needed.

## Contributing
- Follow PSR standards
- Add tests for new features
- Commit messages: use conventional commits (feat, fix, docs, chore)

## License
Specify a license in LICENSE.md
