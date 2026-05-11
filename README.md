# Modern CBT Platform for High School

A comprehensive Computer-Based Testing (CBT) platform designed for Nigerian high schools to manage exams, track student performance, and generate detailed report cards with a complete teacher and admin management system.

**Repository**: https://github.com/michaeljohnsontweets-ui/advance_school_system.git

## 🎯 Core Features

### Student Features
- **Dashboard**: View available exams and past attempts
- **Exam Taking**: Interactive exam interface with timer and auto-save
- **Result Viewing**: Instant exam results, score breakdowns, and performance analytics
- **Result Export**: Download results as PDF or Word format
- **Progress Tracking**: View past attempts and performance history
- **Login Integration**: Register with email or ID

### Teacher Features
- **Score Entry**: Enter continuous assessment (CA1, CA2, CA3) and exam scores
- **Class Management**: Manage students in assigned classes
- **Result Compilation**: Compile and finalize exam results
- **Report Card Generation**: Create and manage student report cards (Nigerian standard)
- **Results Viewing**: Analyze class and student performance
- **Data Export**: Export results for further analysis
- **Dashboard**: Teacher-specific performance metrics

### Parent Features
- **Parent Portal**: Secure space where parents monitor every linked child (class, latest exams, report cards).
- **Exam Timeline**: Recent exam attempts and grades with quick status badges.
- **Report Card Access**: Preview report cards published for their children.
- **Admissions Comms**: Enquiry history + statuses tied to the same login so parents see when the school replies.
- **Notifications**: Badges surface new results, report card postings, and pending enquiries.

### Admin Features
- **Dashboard**: System overview with statistics and analytics
- **Exam Management**: Create, edit, and delete exams with time limits
- **Question Management**: Add questions with images to exams
- **Student Management**: Register and manage students
- **Teacher Management**: Assign teachers and manage roles
- **Class Management**: Create and organize school classes
- **Subject Management**: Manage subjects and assign teachers  
- **Form Teacher Assignment**: Assign form teachers to classes
- **Results Portal**: Analyze and export exam results (PDF/CSV/Word)
- **User Management**: Manage user accounts and permissions
- **Report Card Management**: Generate Nigerian standard report cards

## 🏗️ Technical Stack

- **Framework**: Laravel 10.x (PHP)
- **PHP**: ^8.1
- **Database**: MySQL/MariaDB
- **Frontend**: Blade Templates + TailwindCSS + Alpine.js
- **Build Tool**: Vite
- **PDF Export**: barryvdh/laravel-dompdf
- **Word Export**: phpoffice/phpword
- **Authentication**: Laravel Sanctum
- **Storage**: Local filesystem with S3 support

## 🚀 Installation Guide

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL/MariaDB 5.7+
- Node.js & npm (for asset compilation)
- Laragon or XAMPP (recommended for Windows)

### Quick Setup

1. **Clone Repository**
   ```bash
   git clone https://github.com/michaeljohnsontweets-ui/advance_school_system.git
   cd advance_school_system
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node Dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Configuration**
   
   Edit `.env` and configure:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=modern_cbt_platform
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed Sample Data** (Optional)
   ```bash
   php artisan db:seed
   ```

8. **Build Frontend Assets**
   ```bash
   npm run dev
   # or for production:
   npm run build
   ```

9. **Create Storage Symlink**
   ```bash
   php artisan storage:link
   ```

10. **Start Development Server**
    ```bash
    php artisan serve
    ```

Access application at: **http://localhost:8000**

## 🔐 Default Login Credentials

After running migrations and seeders:

### Admin Account
- **Email**: admin@school.com
- **Password**: password

### Teacher Accounts  
- **Email**: teacher@school.com
- **Password**: password

### Student Accounts
- **Registration No**: STD2024001
- **Password**: password

> ⚠️ **Security**: Change all default passwords immediately in production!

## Usage Guide

### For Students
1. Login with your registration number and password
2. View available exams on your dashboard
3. Click "Start Exam" to begin
4. Answer questions (answers auto-save every 30 seconds)
5. Submit exam before time expires
6. View your results after grading

### For Teachers/Admins
1. Login with email and password
2. Create new exams from the dashboard
3. Add questions to exams (multiple types supported)
4. Assign exams to classes
5. Monitor student attempts
6. Grade subjective questions (theory/coding)
7. Export results as PDF or Word
8. Print individual scripts

## 📚 Project Structure

```
modern-cbt-platform-for-highschool/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php
│   │   │   ├── AdminExamController.php
│   │   │   ├── AuthController.php
│   │   │   ├── StudentController.php
│   │   │   ├── SubjectController.php
│   │   │   ├── TeacherScoreController.php
│   │   │   ├── NigerianReportCardController.php
│   │   │   └── ResultsController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Exam.php
│   │   ├── Question.php
│   │   ├── ExamAttempt.php
│   │   ├── Answer.php
│   │   ├── SchoolClass.php
│   │   ├── Subject.php
│   │   ├── Score.php
│   │   ├── Session.php
│   │   ├── Term.php
│   │   ├── ReportCard.php
│   │   ├── FormTeacher.php
│   │   └── SchoolSettings.php
│   └── Exceptions/
├── database/
│   ├── migrations/
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── SessionSeeder.php
│   │   └── SubjectSeeder.php
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   │   ├── dashboard.blade.php
│   │   │   ├── exams/
│   │   │   ├── students/
│   │   │   ├── teachers/
│   │   │   ├── classes/
│   │   │   └── report-cards/
│   │   ├── student/
│   │   │   └── dashboard.blade.php
│   │   ├── teacher/
│   │   │   ├── scores/
│   │   │   ├── form-teacher-dashboard.blade.php
│   │   │   └── report-cards/
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   └── register.blade.php
│   │   └── layouts/
│   └── sass/
├── routes/
│   └── web.php
├── storage/
│   ├── app/
│   ├── logs/
│   └── framework/
├── public/
│   ├── storage/
│   └── index.php
├── tests/
├── .gitignore
├── composer.json
├── package.json
├── vite.config.js
└── README.md
```

## 📊 Database Schema

### Core Tables

**users**
- Students, Teachers, and Admins
- Role-based access control (admin, teacher, student)
- Photo/avatar storage

**school_classes**
- SS1, SS2, SS3, etc.
- Class capacity and information

**subjects**
- Curriculum subjects
- Subject codes and descriptions
- Linked to teachers

**exams**
- Title, description, duration, marks
- Start and end dates
- Subject association

**questions**
- Multiple choice or theory
- Question content with images support
- Linked to exams

**exam_attempts**
- Student submissions
- Scores and grading status
- Attempt start/end times

**answers**
- Student responses to questions
- Auto-grading for objective, manual for subjective

**scores**
- Continuous Assessment (CA1, CA2, CA3)
- Exam scores per subject
- Teacher and student linked

**sessions** & **terms**
- Academic session/year
- Terms per session (1st, 2nd, 3rd)

**report_cards**
- Nigerian standard report card format
- Scores, comments, ratings
- Student and form teacher linked

**form_teachers**
- Teachers assigned as form teachers
- Class management relationships

## 🔑 Key Features Explained

### Score Entry System
- Continuous Assessment scores (CA1, CA2, CA3) each out of 10
- Exam scores out of 70
- Total = 30 (CA) + 70 (Exam) = 100
- Teacher submits scores for final approval

### Nigerian Report Card Format
- Student name, class, session/term
- Subject-wise performance display
- Rating system (A1-F9)
- Teacher comments and overall rating
- Position/ranking in class
- PDF generation for printing

### Automatic Grading
- Multiple choice questions graded instantly
- Fill-in-the-blank (case-insensitive)
- Theory questions require manual teacher grading

### Export Features
- **PDF Export**: Formatted, printable results
- **Word Export**: Editable results document  
- **CSV Export**: Data analysis compatibility
- **Report Cards**: Nigerian standard format

## 🛡️ Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Authentication**: Laravel's built-in authentication
- **Authorization**: Role-based middleware for route protection
- **Password Hashing**: bcrypt with configurable rounds
- **SQL Injection Prevention**: Eloquent ORM parameterized queries
- **Input Validation**: Server-side validation on all forms
- **Session Management**: Secure session handling
- **Storage Security**: Symlinked private storage

## 🌐 Browser Compatibility

- Chrome/Chromium (Latest) ✅
- Firefox (Latest) ✅
- Safari (Latest) ✅
- Edge (Latest) ✅
- Opera (Latest) ✅

## 🚀 Deployment

### Production Checklist
- [ ] Set `.env` to `APP_DEBUG=false`
- [ ] Set `.env` to `APP_ENV=production`
- [ ] Run `composer install --no-dev`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Optimize: `php artisan optimize`
- [ ] Build frontend: `npm run build`
- [ ] Set proper file permissions on storage
- [ ] Configure SSL certificate

### Deployment Commands
```bash
# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear all caches
php artisan cache:clear
php artisan view:clear
```

### Docker Deployment
See `Dockerfile` for containerized deployment instructions.

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| "Target class does not exist" | Check controller imports in `routes/web.php` |
| Database connection error | Verify credentials in `.env`, ensure MySQL running |
| Permission denied on storage | Run: `chmod -R 775 storage bootstrap/cache` |
| Vite build errors | Delete `node_modules`, run `npm install` again |
| Blank page/500 error | Check `storage/logs/laravel.log` for details |
| Storage files not accessible | Run: `php artisan storage:link` |

## 🤝 Contributing

1. Fork repository
2. Create feature branch: `git checkout -b feature/YourFeature`
3. Commit changes: `git commit -m 'Add YourFeature'`
4. Push to branch: `git push origin feature/YourFeature`
5. Submit Pull Request

## 📝 Git Workflow

```bash
# Initial setup
git config user.name "Your Name"
git config user.email "your.email@example.com"

# Regular commits
git add .
git commit -m "Descriptive message"
git push origin main
```
