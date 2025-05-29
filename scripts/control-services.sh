#!/bin/bash

# Service control script for Todo List Application

show_help() {
    echo "Todo List Service Controller"
    echo "============================"
    echo ""
    echo "Usage: $0 [action] [service]"
    echo ""
    echo "Actions:"
    echo "  start    - Start service(s)"
    echo "  stop     - Stop service(s)"
    echo "  restart  - Restart service(s)"
    echo "  status   - Show service status"
    echo "  logs     - Show service logs"
    echo ""
    echo "Services:"
    echo "  all      - All services"
    echo "  db       - PostgreSQL database"
    echo "  backend  - PHP backend API"
    echo "  frontend - Frontend web interface"
    echo "  nginx    - Reverse proxy"
    echo ""
    echo "Examples:"
    echo "  $0 start all          # Start all services"
    echo "  $0 stop backend       # Stop only backend"
    echo "  $0 restart db         # Restart database"
    echo "  $0 logs frontend      # Show frontend logs"
    echo "  $0 status            # Show all service status"
}

check_service() {
    local service=$1
    if [[ ! " all db backend frontend nginx " =~ " $service " ]]; then
        echo "❌ Invalid service: $service"
        echo "Valid services: all, db, backend, frontend, nginx"
        exit 1
    fi
}

ACTION=${1:-help}
SERVICE=${2:-all}

case $ACTION in
    start)
        check_service $SERVICE
        echo "🚀 Starting $SERVICE..."
        if [ "$SERVICE" = "all" ]; then
            docker-compose up -d
        else
            docker-compose up -d $SERVICE
        fi
        echo "✅ Started $SERVICE"
        ;;
    
    stop)
        check_service $SERVICE
        echo "🛑 Stopping $SERVICE..."
        if [ "$SERVICE" = "all" ]; then
            docker-compose down
        else
            docker-compose stop $SERVICE
        fi
        echo "✅ Stopped $SERVICE"
        ;;
    
    restart)
        check_service $SERVICE
        echo "🔄 Restarting $SERVICE..."
        if [ "$SERVICE" = "all" ]; then
            docker-compose restart
        else
            docker-compose restart $SERVICE
        fi
        echo "✅ Restarted $SERVICE"
        ;;
    
    status)
        echo "📊 Service Status:"
        echo "=================="
        docker-compose ps
        ;;
    
    logs)
        if [ "$SERVICE" = "all" ]; then
            echo "📝 Showing logs for all services (Ctrl+C to exit):"
            docker-compose logs -f
        else
            check_service $SERVICE
            echo "📝 Showing logs for $SERVICE (Ctrl+C to exit):"
            docker-compose logs -f $SERVICE
        fi
        ;;
    
    help|--help|-h)
        show_help
        ;;
    
    *)
        echo "❌ Unknown action: $ACTION"
        echo ""
        show_help
        exit 1
        ;;
esac 