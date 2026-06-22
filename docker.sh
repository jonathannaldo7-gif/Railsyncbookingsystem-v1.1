#!/bin/bash

# Docker Helper Script for RailSync Booking System

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
usage() {
    echo "Usage: $0 {up|down|build|test|logs|shell|mysql|redis|clear}"
    echo ""
    echo "Commands:"
    echo "  up         - Start all docker services"
    echo "  down       - Stop all docker services"
    echo "  build      - Build docker images"
    echo "  test       - Run all tests"
    echo "  test FILE  - Run specific test file"
    echo "  logs       - Show logs from all services"
    echo "  shell      - Access PHP container shell"
    echo "  mysql      - Access MySQL database"
    echo "  redis      - Access Redis CLI"
    echo "  clear      - Clear cache, config, and routes"
    echo "  migrate    - Run database migrations"
    echo "  seed       - Seed database with data"
    exit 1
}

# Main command
case "$1" in
    up)
        echo -e "${GREEN}Starting docker services...${NC}"
        docker-compose up -d
        echo -e "${GREEN}Services started successfully!${NC}"
        echo "- App: http://localhost"
        echo "- MySQL: localhost:3306"
        echo "- Redis: localhost:6379"
        ;;
    down)
        echo -e "${YELLOW}Stopping docker services...${NC}"
        docker-compose down
        echo -e "${GREEN}Services stopped!${NC}"
        ;;
    build)
        echo -e "${GREEN}Building docker images...${NC}"
        docker-compose build
        echo -e "${GREEN}Build completed!${NC}"
        ;;
    test)
        if [ -z "$2" ]; then
            echo -e "${GREEN}Running all tests...${NC}"
            docker-compose run testing
        else
            echo -e "${GREEN}Running tests for $2...${NC}"
            docker-compose exec app php artisan test --filter=$2
        fi
        ;;
    logs)
        echo -e "${GREEN}Fetching logs...${NC}"
        docker-compose logs -f
        ;;
    shell)
        echo -e "${GREEN}Accessing PHP container...${NC}"
        docker-compose exec app bash
        ;;
    mysql)
        echo -e "${GREEN}Accessing MySQL database...${NC}"
        docker-compose exec mysql mysql -u railsync -psecret railsync_test
        ;;
    redis)
        echo -e "${GREEN}Accessing Redis CLI...${NC}"
        docker-compose exec redis redis-cli
        ;;
    clear)
        echo -e "${YELLOW}Clearing cache and config...${NC}"
        docker-compose exec app php artisan cache:clear
        docker-compose exec app php artisan config:clear
        docker-compose exec app php artisan route:clear
        echo -e "${GREEN}Cache and config cleared!${NC}"
        ;;
    migrate)
        echo -e "${GREEN}Running migrations...${NC}"
        docker-compose exec app php artisan migrate
        echo -e "${GREEN}Migrations completed!${NC}"
        ;;
    seed)
        echo -e "${GREEN}Seeding database...${NC}"
        docker-compose exec app php artisan db:seed
        echo -e "${GREEN}Database seeded!${NC}"
        ;;
    *)
        echo -e "${RED}Unknown command: $1${NC}"
        usage
        ;;
esac
