# Fix: Mailer [smto] is not defined

## Problem
There's a typo in your `.env` file. You probably have `smto` instead of `smtp`.

## Quick Fix

Edit your `.env` file and fix the mail configuration:

### Option 1: Use SMTP (for production with email)

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.sekimbi.com
MAIL_PORT=587
MAIL_USERNAME=noreply@sekimbi.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@sekimbi.com"
MAIL_FROM_NAME="ServiceMan"
```

### Option 2: Use Log Driver (for testing - emails go to log file)

If you don't have email configured yet, use the log driver:

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@serviceman.sekimbi.com"
MAIL_FROM_NAME="ServiceMan"
```

### Option 3: Use Sendmail (uses server's sendmail)

```env
MAIL_MAILER=sendmail
MAIL_FROM_ADDRESS="noreply@serviceman.sekimbi.com"
MAIL_FROM_NAME="ServiceMan"
```

## Steps to Fix

1. **Open cPanel File Manager**
2. **Navigate to:** `/serviceman.sekimbi.com/.env`
3. **Find the MAIL section** and fix any typos:
   - `MAIL_MAILER=smto` ❌ → `MAIL_MAILER=smtp` ✅
   - Check all `MAIL_*` variables for typos
4. **Clear config cache:**
   ```bash
   cd ~/serviceman.sekimbi.com
   php artisan config:clear
   php artisan config:cache
   ```
5. **Refresh your website**

## Correct Mail Configuration Examples

### For cPanel Email (Recommended)

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.sekimbi.com
MAIL_PORT=587
MAIL_USERNAME=noreply@sekimbi.com
MAIL_PASSWORD=your_email_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@sekimbi.com"
MAIL_FROM_NAME="ServiceMan"
```

**Note:** 
- `MAIL_HOST` is usually `mail.yourdomain.com` or `localhost`
- You can create email accounts in cPanel → Email Accounts
- Use that email's password for `MAIL_PASSWORD`

### For Development/Testing

```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@serviceman.sekimbi.com"
MAIL_FROM_NAME="ServiceMan"
```

With `log` driver, emails will be written to `storage/logs/laravel.log` instead of being sent.

## Common Mistakes to Avoid

❌ `MAIL_MAILER=smto` (typo)
✅ `MAIL_MAILER=smtp`

❌ `MAIL_MAILER=SMTP` (uppercase - use lowercase)
✅ `MAIL_MAILER=smtp`

❌ `MAIL_HOST=mail.yourdomain.com ` (trailing space)
✅ `MAIL_HOST=mail.yourdomain.com`

❌ `MAIL_PORT=587 ` (trailing space)
✅ `MAIL_PORT=587`

## Supported Mail Drivers

- `smtp` - Standard SMTP email
- `sendmail` - Use server's sendmail
- `log` - Write to log file (for testing)
- `array` - Store in array (for testing)
- `mailgun` - Mailgun service
- `ses` - Amazon SES
- `postmark` - Postmark service

## After Fixing

After updating `.env`, always clear the config cache:

```bash
php artisan config:clear
php artisan config:cache
```

Then test your website again!

