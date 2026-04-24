# RentDress - Setup Guide

## Prerequisites

- XAMPP (PHP 8.2+, MySQL, Apache)
- Node.js (v18+)
- npm

---

## Step 1: Install XAMPP

1. Download from: https://www.apachefriends.org/download.html
2. Install with components: Apache, MySQL, PHP, phpMyAdmin
3. Install to: `C:\xampp`

---

## Step 2: Link Project to XAMPP

**Option A: Symbolic Link (Recommended)**

Open **Command Prompt as Administrator**:

```cmd
mklink /D "C:\xampp\htdocs\RentDress" "C:\Users\TANG\Desktop\RentDress"
```

**Option B: Copy Project**

```cmd
xcopy "C:\Users\TANG\Desktop\RentDress" "C:\xampp\htdocs\RentDress" /E /I
```

---

## Step 3: Setup Database

1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**
3. Open browser: http://localhost/phpmyadmin
4. Create database: `rentdress`
5. Import file: `database/rentdress.sql`

**Database Config** (`api/config/database.php`):
- Host: `localhost`
- Database: `rentdress`
- Username: `root`
- Password: `` (empty)

---

## Step 4: Run Frontend

```bash
cd C:\Users\TANG\Desktop\RentDress\frontend

# Install dependencies (first time only)
npm install

# Run development server
npm run dev
```

Frontend URL: http://localhost:5173

---

## Step 5: Access the Application

| Service | URL |
|---------|-----|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost/RentDress/api |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@rentdress.com | admin123 |
| Customer | somchai@email.com | admin123 |

---

## Troubleshooting

### Port 80 already in use
- Check if Skype or other apps use port 80
- Or change Apache port in `C:\xampp\apache\conf\httpd.conf`

### MySQL won't start
- Check if another MySQL instance is running
- Or change MySQL port in `C:\xampp\mysql\bin\my.ini`

### CORS errors
- Make sure API is accessed via `http://localhost/RentDress/api`
- Check `.htaccess` file has correct CORS headers

### Frontend can't connect to API
- Verify XAMPP Apache is running
- Check `frontend/src/services/api.js` for correct API URL
