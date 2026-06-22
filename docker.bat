@echo off
REM Docker Helper Script for RailSync Booking System (Windows)

setlocal enabledelayedexpansion

if "%1"=="" (
    echo Usage: %0 {up^|down^|build^|test^|logs^|shell^|mysql^|redis^|clear}
    echo.
    echo Commands:
    echo   up         - Start all docker services
    echo   down       - Stop all docker services
    echo   build      - Build docker images
    echo   test       - Run all tests
    echo   test FILE  - Run specific test file
    echo   logs       - Show logs from all services
    echo   shell      - Access PHP container shell
    echo   mysql      - Access MySQL database
    echo   redis      - Access Redis CLI
    echo   clear      - Clear cache, config, and routes
    echo   migrate    - Run database migrations
    echo   seed       - Seed database with data
    exit /b 1
)

if "%1"=="up" (
    echo Starting docker services...
    docker-compose up -d
    echo Services started successfully!
    echo - App: http://localhost
    echo - MySQL: localhost:3306
    echo - Redis: localhost:6379
    goto :eof
)

if "%1"=="down" (
    echo Stopping docker services...
    docker-compose down
    echo Services stopped!
    goto :eof
)

if "%1"=="build" (
    echo Building docker images...
    docker-compose build
    echo Build completed!
    goto :eof
)

if "%1"=="test" (
    if "%2"=="" (
        echo Running all tests...
        docker-compose run testing
    ) else (
        echo Running tests for %2...
        docker-compose exec app php artisan test --filter=%2
    )
    goto :eof
)

if "%1"=="logs" (
    echo Fetching logs...
    docker-compose logs -f
    goto :eof
)

if "%1"=="shell" (
    echo Accessing PHP container...
    docker-compose exec app bash
    goto :eof
)

if "%1"=="mysql" (
    echo Accessing MySQL database...
    docker-compose exec mysql mysql -u railsync -psecret railsync_test
    goto :eof
)

if "%1"=="redis" (
    echo Accessing Redis CLI...
    docker-compose exec redis redis-cli
    goto :eof
)

if "%1"=="clear" (
    echo Clearing cache and config...
    docker-compose exec app php artisan cache:clear
    docker-compose exec app php artisan config:clear
    docker-compose exec app php artisan route:clear
    echo Cache and config cleared!
    goto :eof
)

if "%1"=="migrate" (
    echo Running migrations...
    docker-compose exec app php artisan migrate
    echo Migrations completed!
    goto :eof
)

if "%1"=="seed" (
    echo Seeding database...
    docker-compose exec app php artisan db:seed
    echo Database seeded!
    goto :eof
)

echo Unknown command: %1
exit /b 1
