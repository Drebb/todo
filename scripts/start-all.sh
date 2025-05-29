#!/bin/bash

echo "🚀 Starting Todo List Application..."
echo "=================================="

# Start all services
docker-compose up -d

echo ""
echo "✅ All services started!"
echo ""
echo "📊 Service Status:"
docker-compose ps

echo ""
echo "🌐 Access Points:"
echo "  • Main Application: http://localhost"
echo "  • Frontend Only: http://localhost:3000"
echo "  • Database: localhost:5432"
echo ""
echo "📝 View logs: docker-compose logs -f [service-name]"
echo "🛑 Stop all: docker-compose down" 