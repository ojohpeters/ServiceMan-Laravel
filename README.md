# ServiceMan Laravel

Professional on-demand service platform connecting clients with skilled service professionals. Built with Laravel, React, and modern web technologies.

## 🚀 Features

- **Multi-User System**: Clients, Servicemen, and Admins with role-based access
- **Service Booking**: Easy service request booking with real-time status tracking
- **Category Management**: Organized service categories (Electrical, Plumbing, HVAC, etc.)
- **Rating & Reviews**: Client feedback system with admin-approved testimonials
- **Payment Integration**: Secure Paystack payment gateway integration
- **Admin Dashboard**: Comprehensive admin panel for managing platform operations
- **Real-time Notifications**: Notification system for all users
- **Profile Management**: User profiles with picture uploads
- **Mobile Responsive**: Fully responsive design for all devices

## 📋 Requirements

- PHP 8.1 or higher
- Composer
- Node.js 18+ and npm
- MySQL 5.7+ or MariaDB
- Web server (Apache/Nginx)

## 🛠️ Installation

### Local Development Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd ServiceManLaravel
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies:**
   ```bash
   npm install
   ```

4. **Environment configuration:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

   Configure your `.env` file with database credentials and other settings.

5. **Database setup:**
   ```bash
   php artisan migrate
   php artisan db:seed --class=CategorySeeder
   php artisan db:seed --class=AdminUserSeeder
   ```

6. **Build frontend assets:**
   ```bash
   npm run build
   ```

7. **Start development servers:**
   ```bash
   # Terminal 1: Laravel server
   php artisan serve
   
   # Terminal 2: Vite dev server (for hot reload)
   npm run dev
   ```

## 📦 Production Deployment

### cPanel Deployment

1. **Upload files to cPanel:**
   - Via Git (recommended) or File Manager
   - Extract to your domain's `public_html` directory

2. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with production settings
   ```

3. **Run deployment script:**
   ```bash
   bash deploy-cpanel.sh
   ```

   Or manually:
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   php artisan key:generate
   php artisan jwt:secret
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Set file permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache public/uploads
   chmod -R 755 public
   ```

5. **Configure database:**
   - Create database in cPanel MySQL Databases
   - Update `.env` with database credentials
   - Run migrations

### Environment Variables

Key configuration in `.env`:

```env
APP_NAME=ServiceMan
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

JWT_SECRET=your_jwt_secret

# Paystack Configuration
PAYSTACK_PUBLIC_KEY=your_public_key
PAYSTACK_SECRET_KEY=your_secret_key
PAYSTACK_MERCHANT_EMAIL=your_email

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 📁 Project Structure

```
ServiceManLaravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # API controllers
│   │   │   └── Web/          # Web controllers
│   │   └── Middleware/
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── uploads/              # User uploaded files
│   ├── images/               # Default images
│   └── index.php
├── resources/
│   ├── views/                # Blade templates
│   └── js/                   # React components
├── routes/
│   ├── api.php               # API routes
│   └── web.php               # Web routes
└── storage/
    └── app/
        └── public/           # Public storage
```

## 🔐 File Uploads

Profile pictures are stored in `public/uploads/profile_pictures/`. Ensure proper permissions:

```bash
mkdir -p public/uploads/profile_pictures
chmod -R 775 public/uploads
```

The storage symlink must be created:
```bash
php artisan storage:link
```

## 🎯 Key Features Explained

### User Roles
- **Clients**: Book services, make payments, rate servicemen
- **Servicemen**: Accept jobs, provide estimates, complete work
- **Admins**: Manage platform, approve users, control testimonials

### Service Workflow
1. Client books a service and pays booking fee
2. Admin assigns serviceman
3. Serviceman inspects and provides estimate
4. Admin reviews and sets final cost
5. Client pays final amount
6. Serviceman completes work
7. Client rates and reviews

### Testimonials Management
Admins can control which testimonials appear on the landing page via the Admin Dashboard → Testimonials section.

## 🔧 Common Commands

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create storage link
php artisan storage:link

# Generate keys
php artisan key:generate
php artisan jwt:secret
```

## 🔒 Security Features

- JWT authentication
- Password hashing (bcrypt)
- CSRF protection
- Input validation
- SQL injection prevention (Eloquent ORM)
- XSS protection
- Secure file uploads

## 📱 API Documentation

### Authentication Endpoints
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `POST /api/auth/refresh` - Refresh token

### Public Endpoints
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get category details
- `GET /api/categories/{id}/servicemen` - Get servicemen by category

### Protected Endpoints
- `GET /api/auth/me` - Get current user
- `PUT /api/users/client-profile` - Update client profile
- `PUT /api/users/serviceman-profile` - Update serviceman profile
- `GET /api/service-requests` - List service requests
- `POST /api/service-requests` - Create service request

See `routes/api.php` for complete API routes.

## 🐛 Troubleshooting

### Images Not Displaying
1. Check directory permissions: `chmod -R 775 public/uploads`
2. Verify storage symlink: `php artisan storage:link`
3. Check file paths in database

### 500 Internal Server Error
1. Check error logs: `storage/logs/laravel.log`
2. Verify file permissions
3. Check `.env` configuration
4. Clear caches: `php artisan cache:clear`

### Database Connection Issues
1. Verify database credentials in `.env`
2. Check database server status
3. Ensure database exists
4. Check user permissions

### Permission Errors
```bash
chmod -R 775 storage bootstrap/cache
chmod -R 775 public/uploads
chown -R your_user:your_group storage bootstrap/cache public/uploads
```

## 📝 Quick Login Credentials

After running `AdminUserSeeder`, you can log in with:
- **Email**: admin@serviceman.com
- **Password**: Service123!

(Change this immediately in production!)

## 🚀 Performance Optimization

### Enable OPcache (Production)
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
```

### Database Optimization
- Add proper indexes
- Optimize queries
- Use connection pooling

### CDN Setup
- Use CDN for static assets
- Enable gzip compression
- Set proper cache headers

## 📞 Support

For deployment or technical issues:
- Check `storage/logs/laravel.log`
- Review Laravel documentation
- Contact development team

## 📄 License

Proprietary - All rights reserved

## 👨‍💻 Developed By

**SACS Computers - IT in your palms**

Website: https://www.sacscomputers.com/

---

For detailed cPanel deployment instructions, see the inline deployment steps above or refer to the deployment script: `deploy-cpanel.sh`
# ServiceMan-Laravel
