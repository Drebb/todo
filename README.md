# Todo List Application

A simple to-do list application with PHP and PostgreSQL 16, running in Docker containers.

## Features

- Create, view, toggle completion, and delete to-do items
- Persistent storage with PostgreSQL
- Docker containerization for easy setup
- Nginx web server with PHP-FPM

## Requirements

- Docker
- Docker Compose

## Setup and Running

1. Clone this repository
2. Navigate to the project directory
3. Build and start the containers:

```bash
docker-compose up -d
```

4. Access the application at http://localhost:80

## Project Structure

- `docker-compose.yml` - Docker Compose configuration
- `frontend/` - PHP application
  - `Dockerfile` - PHP-FPM Docker image configuration
  - `src/` - PHP source code
- `nginx/` - Nginx configuration
- `db/` - PostgreSQL initialization scripts

## Stopping the Application

To stop the containers:

```bash
docker-compose down
```

To stop and remove all data (including the database volume):

```bash
docker-compose down -v
```
