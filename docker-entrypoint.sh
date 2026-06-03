#!/bin/sh
set -e

# Migrations are handled by Railway's preDeployCommand ("php artisan migrate --force")
# which runs in a separate container before the app starts. This ensures Apache
# launches immediately and the healthcheck succeeds without any startup delay.

exec apache2-foreground
