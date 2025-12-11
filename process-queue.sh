#!/bin/bash
# Queue Worker Script - Processes email notifications

echo "Starting queue worker..."
echo "Press Ctrl+C to stop"
echo ""

php artisan queue:work --tries=3 --timeout=300

