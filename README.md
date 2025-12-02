# Officer Appointment & Activity Management System
A Laravel-based web application for managing officers, their working schedules, visitor appointments, and activities.  
This system ensures proper visitor flow, officer availability tracking, and automatic status updates for appointments and activities.

---

## 🚀 Features

### 🧑‍💼 Officer Management
- Create and manage officer profiles
- Assign posts/roles
- Define daily working hours
- Set available working days

### 📅 Appointment Management
- Visitors can book appointments based on officer availability
- Only future appointments are shown (today + upcoming)
- Appointment conflicts are prevented automatically
- Auto-mark appointments as **completed** when end time passes

### 📋 Activity Management
- Officers can take **breaks** or **leave**
- System checks overlap conflicts before approving an activity
- Automatic status update when activity end time passes

### 🕒 Working Days
- Only officers with registered workdays are considered "Available"
- Officers working today are dynamically detected

### 🧑‍🤝‍🧑 Visitor Management
- Register new visitors
- Track visitor history
- Visitors linked automatically with their appointments

## 🛠️ Tech Stack

| Component | Technology                        |
|----------|-----------------------------------|
| Backend  | Laravel 10+ (Laravel 12 recommended)                    |
| Database | MySQL / MariaDB                   |
| Frontend | Blade Templates (No Vite, No NPM) |
| UI | Bootstrap                         |
| Timezone | Asia/Kathmandu                    |

---

## 📦 Installation Guide

### 1️⃣ Clone the Project
```bash
git clone https://github.com/your-repository/appointment-system.git
cd appointment-system
```

### 2️⃣ Install PHP Dependencies
```bash
composer install
```

### 3️⃣ Configure Environment File
```bash
cp .env.example .env
```

Update your `.env` values:

```
APP_NAME="Officer Appointment System"
APP_ENV=local
APP_KEY=
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

TIMEZONE=Asia/Kathmandu
```

Generate key:

```bash
php artisan key:generate
```

### 4️⃣ Run Migrations & Seeders
```bash
php artisan migrate --seed
```

(Optional) If you want fresh install:
```bash
php artisan migrate:fresh --seed
```

### 5️⃣ Run the Application
```bash
php artisan serve
```

---

## ⚙️ Scheduler Setup (For Auto-Complete Feature)


To auto-update expired appointments & activities, add this cron:


```

* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1

```


---

## 📚 Project Structure (Simplified)

```
app/
  Models/
    Officer.php
    Appointment.php
    Activity.php
    Visitor.php
    WorkDay.php
  
  Http/Controllers/
    OfficerController.php
    AppointmentController.php
    ActivityController.php
    VisitorController.php

  Services/
    OfficerService.php
    AppointmentService.php
    ActivityService.php
    VisitorService.php

resources/views/
  officers/
  appointments/
  activities/
  visitors/
  layout.blade.php
```

---

## 🛡️ Validation Rules Summary

### Appointment Rules
- Officer must exist
- Appointment must be future-based
- No overlap with break/leave
- Follows officer working hours

### Activity Rules
- Break/Leave cannot overlap with existing activities
- Must be within officer working hours

---

## ✔️ Automatic Status Update


The scheduler updates:

- Activities: from **active → completed** when end_date & end_time pass
- Activities: from **inactive → canceled** when end_date & end_time pass

- Appointments: from **active → completed** automatically
- Appointments: converted to **canceled** if its related activity is inactive when end_date & end_time pass


This avoids manual work and ensures clean system records.


---

## 🙋‍♂️ Developed By
**Puskar**
- BCA Student
- Laravel Developer
- Passionate about backend systems

---

## 📄 License
This project is open-source and free to use.

