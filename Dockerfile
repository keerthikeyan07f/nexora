# Multi-stage production container for NEXORA PHP + Python Flask ML App
FROM php:8.2-apache

# Install System Dependencies & Python 3
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    supervisor \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Working Directory
WORKDIR /var/www/html

# Copy Project Files
COPY . /var/www/html/

# Ensure web permissions
RUN chown -R www-data:www-data /var/www/html

# Install Python ML Dependencies inside a virtualenv
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"

RUN pip install --no-cache-dir gunicorn
RUN pip install --no-cache-dir -r /var/www/html/python_ml/requirements.txt

# Configure Supervisor to manage Apache & Flask ML Service
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose HTTP port
EXPOSE 80 5000

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
