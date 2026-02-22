#!/bin/bash
# Heroku and Railway inject $PORT at runtime — not available at Docker build time.
# This script updates Apache config with the correct port before starting.

PORT=${PORT:-80}

# Update Apache to listen on the dynamic port
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

echo "Starting Apache on port $PORT..."
exec apache2-foreground
