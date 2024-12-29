FROM php:7.3-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy your application files to the container
COPY . /var/www/html/

# Set the working directory
WORKDIR /var/www/html/

# Expose port 80
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]