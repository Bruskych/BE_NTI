<div align="center">

# NTI ```Backend```
### Nitriansky technologický inkubátor

REST API for the NTI platform that powers all business processes of the system.

It handles authentication, program management, applications, evaluations, mentoring, organizations, and reporting.

```Main responsibilities:```
* user authentication and role-based access control
* program and challenge management (Program A & B)
* application workflow and document handling
* evaluation and scoring system
* mentoring and project tracking
* organization and partner management
* notifications and audit logs
* data export and reporting

🤖 [Backend Repository](https://github.com/Bruskych/BE_NTI) · 🎨 [Frontend Repository](https://github.com/Bruskych/FE_NTI)

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Swagger](https://img.shields.io/badge/Swagger-85EA2D?style=for-the-badge&logo=swagger&logoColor=black)](https://swagger.io)
[![Mailpit](https://img.shields.io/badge/Mailpit-3482A4?style=for-the-badge&logo=go&logoColor=white)](https://github.com/axllent/mailpit)
[![Nginx](https://img.shields.io/badge/Nginx-009639?style=for-the-badge&logo=nginx&logoColor=white)](https://nginx.org)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)

</div>

---

### Tech Stack & Tools
* **Core:** Laravel 13 & PHP 8.4
* **Database & Caching:** MySQL 8, Redis
* **Dev Environment:** Docker Compose, Mailpit (SMTP Testing)
* **Auth & Security:** Secure token authentication via Laravel Sanctum and middleware-enforced RBAC using Spatie.
* **Documentation:** OpenAPI / Swagger (L5-Swagger)

---

### 📥 1. Quick Start

```Clones the repository``` with the backend code from GitHub to your local computer. The command must be executed in an empty folder where you want to see your project.
```bash
git clone https://github.com/Bruskych/BE_NTI
```

You need to go inside the ```project folder```
```bash
cd YOUR_PROJECT_FOLDER_NAME
```

Creates a copy ````(.env)```` of the template file .env.example
```bash
cp .env.example .env
```

---

### 🐳 2. Start Containers

Since Composer dependencies are baked into the Dockerfile, the initial build might take a few minutes. Run the following command to build and start all microservices in the background:

```bash
docker compose up -d --build
```

---

### 🔐 3. Generate Application Key

Once the containers are running, generate a unique encryption key for your Laravel application. This key is used to securely encrypt user sessions, cookies, and tokens. Without it, the app will throw a 500 error.

```bash
php artisan key:generate
```
```OR```
```bash
docker compose exec app php artisan key:generate
```

---

### 📄 4. Run Migrations and Seeders

Set up the database structure and populate it with initial data, roles, and administrative accounts:

```bash
docker compose exec app php artisan migrate --seed
```

---

### ⚙️ Service Architecture & Ports

All services running inside the Docker network are pre-configured. You can access them via the following local URLs:

| Service | 🌐 Context / UI URL                     | Internal Port | External Port |
|---|-----------------------------------------|---|---|
| API Endpoint | http://localhost:8000/api               | — | 8000 |
| Swagger UI | http://localhost:8000/api/documentation | — | 8000 |
| phpMyAdmin | http://localhost:8080                   | 80 | 8080 |
| Mailpit (Email Hub) | http://localhost:8025                   | 8025 | 8025 |

### ⚙️ Background Processing & Automation

Thanks to the dedicated infrastructure containers, you don't need to configure host crontabs or run manual processing commands.

* **Asynchronous Queues (`nti-queue`):** Processes emails, notifications, and heavy background tasks automatically.
* **Task Scheduler (`nti-scheduler`):** Automatically runs Laravel's internal loop (`schedule:work`) every minute to trigger deadline reminders and maintenance tasks.

To manually trigger the deadline reminders immediately for debugging, run:

```bash
docker compose exec app php artisan notifications:deadline-reminders --days=3
```

---

### 🧪 Running Tests

Execute the comprehensive test suite (Unit, Integration, and Security test coverage for XSS, SQL injection, and rate limiting) directly inside the containerized environment:

```bash
docker compose exec app php artisan test
```

---

## 📦 API Documentation (Swagger)

Swagger UI is available at http://localhost:8000/api/documentation after starting the server.

If you modify annotations in Controllers or DTOs, regenerate the OpenAPI specification file using the following command:

```bash
docker compose exec app php artisan l5-swagger:generate
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
├── Http/                    # TRANSPORT LAYER (Requests & Responses)
│   ├── Controllers/         # API Controllers (handle endpoints, delegate to Actions/Services)
│   ├── Requests/            # Incoming data validation (Form Requests for applications, programs)
│   ├── Resources/           # API Resource transformers (standardizing JSON output for Vue)
│   ├── Middleware/          # HTTP filters (Role-based access, security headers)
│   └── Concerns/            # Shared traits (e.g., HasApiResponse for unified API outputs)
│
├── Actions/                 # CORE BUSINESS LOGIC (Single-Responsibility Principle)
│   └── [Domain]/            # Atomic action classes (e.g., UpdateUserProfileAction)
│
├── Services/                # COMPLEX BUSINESS LOGIC (Coarse-Grained Services)
│   └── [Domain]/            # Heavy lifting services (PDF generation, Swagger integrations, third-party APIs)
│
├── Models/                  # DATA LAYER (Database Entities)
│   └── [Model].php          # Eloquent models (User, Program, Application, Project, Mentor)
│
├── Policies/                # ACCESS CONTROL (Fine-Grained Authorization)
│   └── [Model]Policy.php    # Resource authorization gates coupled with Spatie Permissions
│
├── Console/                 # AUTOMATION (Artisan CLI & Cron Tasks)
│   └── Commands/            # Custom Artisan commands (e.g., deadline & notification reminders)
│
├── Events/                  # EVENT-DRIVEN ARCHITECTURE (System Lifecycle Triggers)
│   └── *.php                # DTOs signaling that a specific business event has occurred (e.g., ApplicationSubmitted)
│
├── Jobs/                    # ASYNC PROCESSING (Background Queues via Redis)
│   └── [Job].php            # Heavy async tasks (e.g., Excel report generation, bulk email dispatches)
│
├── Listeners/               # ASYNC & SYNC EVENT HANDLERS (Decoupled Logic Consumers)
│   └── *.php                # Classes reacting to Events and triggering downstream processes (e.g., calling NotificationService)
│
├── Mail/                    # NOTIFICATIONS (Email Blueprints)
│   └── [Mailable].php       # Mail layout configurations captured locally by Mailpit
│
└── Providers/               # INFRASTRUCTURE (Bootstrapping Core System Services)
```

## Database structure in the project

```
database/
├── factories/                      # DATA GENERATORS (Testing & Development)
│   └── [Model]Factory.php          # Blueprints for generating realistic fake records (User, Application)
│
├── migrations/                     # SCHEMA DEFINITIONS (Database Version Control)
│   └── [Time]create[name]table.php # Table schemas (programs, applications, projects, permissions)
│
└── seeders/                        # DATA POPULATION (System Configuration & Mock State)
    ├── DatabaseSeeder.php          # Main entry point that orchestrates the seeding process
    ├── RolePermissionSeeder.php    # Initial configuration for Spatie roles & permissions (Admin, Student)
    └── [Domain]Seeder.php          # Dummy data injection for local development
```

## Testing Structure & Coverage

```
tests/
├── Feature/                          # INTEGRATION & API TESTS (End-to-End HTTP Workflows)
│   └── [Domain]Test.php              # Feature & endpoint validations (Auth, Application flows, RBAC security, Exports)
│
├── Unit/                             # ISOLATED UNIT TESTS (Core Business Logic & Models)
│   └── [Model/Service]Test.php       # Isolated assertions for Eloquent models, relations, and standalone services
│
└── TestCase.php                      # TESTING BASEMENT (Global Setup & Bootstrapping)
```

---

## 🗺️ Database Schema

<p align="center">
  <br>
  <a href="https://dbdiagram.io/d/6a28a9ee25fc5bf036cf0a5b">
    <img src=".github/assets/schema.svg" alt="NTI Database Schema" width="100%">
  </a>
  <br>
  <br>
  <span>💡 <i>Click on the image to open the interactive schema and explore relationships.</i></span>
</p>

---

## 📋 Project Documentation (NTI Platform Specification)

This section covers the core business, architectural, and operational framework of the Nitriansky technologický inkubátor (NTI) central system, aligning with the academic and technical requirements.

### 1. Executive Summary
* **Problem:** Brain drain of technological talent from the Nitra region and a lack of structured, real-world practical experience for IT students during their studies.
* **Solution:** NTI is a centralized web information system that bridges the gap between academia and the private sector. It provides a presentation portal, handles application workflows for grant incubation (Program A), and manages real-world company-assigned projects (Program B).
* **Market & Impact:** The platform serves university students, regional IT companies, mentors, and the NTI administration, driving regional retention of tech talent and accelerating new tech startups.

### 2. Technical Architecture Overview
The NTI platform is currently implemented using a **Layered Monolith** architecture, leveraging the native, battle-tested structure of the Laravel framework. To prevent the codebase from turning into a "big ball of mud," the system heavily relies on the **Service-Action Pattern**, establishing a solid foundation for future scalability.

* **Current Architecture (Layered Monolith):** The codebase is separated by technical concerns (`Controllers`, `Actions`, `Services`, `Models`, `Requests`, ...). Instead of bloating controllers, all core business logic is encapsulated into isolated, single-responsibility classes inside `app/Actions` and `app/Services`. This ensures loose coupling, strict testability, and clear separation of duties.
* **Future Architectural Evolution (Target: Modular Monolith):** As the platform scales beyond the initial MVP phase (Phase 2 & 3), the codebase is prepared to transition into a strict **Modular Monolith**. Because the business logic is already isolated within independent `Actions` and `Services`, they can be easily refactored into self-contained domain modules (e.g., `app/Modules/Auth`, `app/Modules/Programs`, `app/Modules/Evaluations`) without breaking the core infrastructure. This architecture ensures low operational complexity for university hosting while keeping the system ready for future microservices extraction if required.
* **Frontend:** Vue.js + TypeScript (Responsive SPA tailored for different user roles).
* **Backend:** Laravel 13 (PHP 8.4) exposing a secure REST API documented via OpenAPI/Swagger.
* **Database & Cache:** MySQL 8 for robust relational integrity (audit logs, application tracking) and Redis for high-performance background queueing (notifications, rate-limiting).
* **Infrastructure:** Containerized environment via Docker Compose and Nginx reverse proxy.

### 3. Project Roadmap
The implementation is divided into four strategic phases to ensure an expandable MVP:
* **Phase 0 (Discovery & UX/IA):** Process refinement, user role definition, wireframing, and branding rules.
* **Phase 1 (MVP Core - Current):** Public web CMS, user authentication, student/team registration, and Program A workflow.
* **Phase 2 (Program B & Workflows):** Company onboarding, project backlog management, mentor assignment, and performance tracking.
* **Phase 3 (BI & Advanced Features):** KPI dashboards, automated reporting, and advanced analytical export layers.

### 4. Budget & Resource Allocation (MVP Estimate)
The estimated initial development and maintenance budget for the NTI platform MVP is structured as follows:

| Category | Allocation / Description | Estimated Cost Model |
| --- | --- | --- |
| **Development** | Backend (Laravel) & Frontend (Vue.js) core architecture | ~350 Engineering Hours |
| **Infrastructure** | Self-hosted university servers or standard cloud VPS setup | Minimal (Open-source friendly) |
| **QA & Security** | Unit/Integration testing & lightweight penetration testing | Internal academic / peer review |

### 5. Risk Analysis & Mitigation
* **Risk 1: Data Leaks & GDPR Violations (High Impact).** The system stores sensitive academic records and company contract details.
    * *Mitigation:* Strict Role-Based Access Control (RBAC) via Spatie, password hashing using Argon2id, and comprehensive automated audit logging for all admin actions.
* **Risk 2: High System Load During Deadlines (Medium Impact).** Massive traffic spikes when application call deadlines approach.
    * *Mitigation:* Redis rate-limiting on forms, client-side draft autosaving, and shifting heavy email notifications to asynchronous background queues (`nti-queue`).
* **Risk 3: Malicious File Uploads (High Impact).** Students upload mandatory PDFs or project documents containing malware.
    * *Mitigation:* Strict server-side MIME-type/extension validation and file size constraints enforced by backend middleware.

### 6. Monetization & Sustainability Model
As an institutional and regional platform, NTI ensures long-term operational sustainability through a hybrid ecosystem model:
* **Program A (Incubation):** Funded by regional development grants, university structural funds, and innovation subsidies.
* **Program B (Live Practice):** B2B partnership model where external companies provide student rewards/budgets. A small administrative percentage can be retained by NTI to cover platform maintenance, server costs, and mentor compensations.

---

## 👨🏽‍💻 Authors

| Name | GitHub                                         |
|---|--------------------------------------------------|
| Vladyslav Svider | [Link to git](https://github.com/Versus1478)     |
| Vladyslav Shcherbyna | [Link to git](https://github.com/Bruskych)       |
| Davyd Shapovalov | [Link to git](https://github.com/davidshapovalov) |
