# NTI Backend — Nitriansky technologický inkubátor

REST API backend for the NTI platform — a process and registration system for managing programs, applications, projects, mentoring, and evaluation.

**Stack:** Laravel 13 · MySQL 8 · Redis · Docker · Sanctum · Spatie Permissions · OpenAPI/Swagger

---

## Quick Start (Docker)

### 1. Clone and configure environment

```bash
git clone <repository-url>
cd BE_NTI
cp .env.example .env
```

Edit `.env` — set database, mail, Redis, and frontend URL:

```env
DB_HOST=db
DB_DATABASE=nti_database
DB_USERNAME=root
DB_PASSWORD=root

REDIS_HOST=redis

MAIL_HOST=mailpit
MAIL_PORT=1025

APP_FRONTEND_URL=http://localhost:5173
```

### 2. Start containers

```bash
docker compose up -d --build
```

| Service | URL |
|---|---|
| API | http://localhost:8000/api |
| Swagger UI | http://localhost:8000/api/documentation |
| phpMyAdmin | http://localhost:8080 |
| Mailpit (email preview) | http://localhost:8025 |

### 3. Install dependencies

```bash
docker exec nti-app composer install
docker exec nti-app php artisan key:generate
```

### 4. Run migrations and seeders

```bash
docker exec nti-app php artisan migrate:fresh --seed
```

### 5. Run queue worker (for email jobs)

```bash
docker exec nti-app php artisan queue:work
```

### 6. Run scheduled tasks (deadline reminders)

Add to server crontab:

```bash
* * * * * docker exec nti-app php artisan schedule:run
```

Or run manually:

```bash
docker exec nti-app php artisan notifications:deadline-reminders --days=3
```

---

## Running Tests

```bash
docker exec nti-app php artisan test
```

Test coverage includes unit tests, feature/integration tests, and security tests (401/403, XSS, SQL injection, rate limiting).

---

## API Documentation

Swagger UI is available at `/api/documentation` after starting the server.

To regenerate the OpenAPI spec from annotations:

```bash
docker exec nti-app php artisan l5-swagger:generate
```

---

## Features

### Authentication & Accounts
- Email registration with OTP verification and password reset
- 9 RBAC roles: `visitor`, `student`, `team_leader`, `company`, `mentor`, `evaluator`, `content_editor`, `admin`, `super_admin`
- Sanctum Bearer token authentication
- Student onboarding profile (study program, year, skills, GPA)

### Organizations & Teams
- Company organization management with member roles (owner / manager / member)
- Student team creation, invitations, and membership management

### Programs & Calls
- Configurable programs (A — grant incubation, B — live practice)
- Calls with open/close lifecycle management
- Configurable form fields per program and call

### Applications
- Full application lifecycle: draft → submitted → in review → approved / rejected
- 11-status state machine with history and audit trail
- Required field validation before submission
- Program B pairing submissions (CV, motivation letter, solution proposal)

### Evaluation & Decisions
- Evaluation templates with scoring criteria
- Committee scoring and approval/rejection workflow
- Automatic average score calculation on decision

### Projects & Milestones
- Project management linked to approved applications
- Milestone tracking with approval and deadline monitoring
- Daily deadline reminders via scheduled artisan command

### Mentorship & Consultations
- Mentor assignment to projects with email notification
- Consultation records with scheduling

### Documents
- Versioned document uploads with classification (public / internal / confidential)
- MIME type validation (magic bytes check) — PDF, DOC, DOCX, JPG, PNG
- Document access codes, preview, and download
- Template-based document generation (internship agreements)

### Notifications
- In-app notification center (accept/reject/read/delete)
- Transactional emails: registration, application submitted, approved, rejected, mentor assigned
- Deadline reminder emails (3 days before milestone deadline)
- Admin-managed email templates with variable substitution
- Bulk messaging to user groups via queue

### CMS & Public Web
- Pages, posts (articles / FAQ / success stories), and partners CRUD
- SEO fields: `meta_title`, `meta_description`, `og_image`, `slug`
- Sitemap.xml endpoint
- Content filterable by type: `?type=faq`

### Admin & Reporting
- Admin dashboard with pending applications overview
- CSV / XLSX / PDF exports with audit log
- GDPR data export and erasure for any user
- Audit trail for all critical operations (approvals, role changes, exports, GDPR)

### Specializations (Program A)
- Qualification stacks 01–05 per spec
- Stack filter: `GET /api/specializations?stack=01`

---

## Project Structure

```
app/
├── Actions/          # Single-responsibility business actions
├── Console/Commands/ # Artisan commands (deadline reminders)
├── Http/
│   ├── Concerns/     # HasApiResponse trait
│   ├── Controllers/  # API controllers
│   ├── Requests/     # Form request validation
│   └── Resources/    # API resource transformers
├── Jobs/             # Queue jobs (exports, bulk messages)
├── Mail/             # Mailable classes
├── Models/           # Eloquent models
├── Policies/         # Authorization policies
└── Services/         # Business logic services
```

---

## Authors

| # | Name |
|---|---|
| 1 | Vladyslav Svider |
| 2 | Vladyslav Shcherbyna |
| 3 | Davyd Shapovalov |
