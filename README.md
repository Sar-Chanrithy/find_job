# DevHire Cambodia

React frontend + Laravel 12 REST API + PostgreSQL job recruitment learning project.

## Run locally

Backend terminal:

```powershell
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

Frontend terminal:

```powershell
cd frontend
npm install
npm run dev
```

Open http://127.0.0.1:5173. Vite forwards `/api` to Laravel on port 8000.

## Database and API

The local database is `devhire_db`; `devhire_test` is isolated for tests. Credentials belong only in `backend/.env` (ignored by Git). `.env.example` contains no real password. PostgreSQL must be running.

```powershell
cd backend
php artisan config:clear
php artisan migrate
php artisan db:seed
```

The seeder creates five illustrative companies and nine sample jobs without duplicate records or a default user account. It does not overwrite existing jobs. The previous `devhire_cambodia` database is preserved.

- `GET /api/jobs`: validated search, category/location/type/remote filters, featured/newest ordering, pagination (maximum 50 per page), optional saved-ID filtering.
- `GET /api/jobs/{id}`: published, non-expired job details.
- Public API rate limit: 120 requests per minute per IP.
- No public write endpoints. Authentication, applications, employer CRUD, and dashboards are the next phase.

## Frontend

- `/#/`: homepage with API-loaded jobs
- `/#/jobs`: server-side search, filters, sorting and pagination
- `/#/jobs?saved=true`: locally saved IDs resolved against published API jobs
- `/#/jobs/1`: API-loaded detail page
- `/#/login` and `/#/register`: UI-only forms; no authentication yet

Axios client: `frontend/src/services/api.js`. Request lifecycle hooks: `useJobs.js`. UI categories are static; job records live in PostgreSQL. The interface includes loading, retry, empty and unavailable-job states. Saved IDs use localStorage, not account synchronization. Fonts and the hero photo require internet access.

`VITE_API_BASE_URL` defaults to `/api`. For a production frontend, configure a same-origin reverse proxy for `/api`, or set an HTTPS API URL and the corresponding Laravel CORS origins. Never put secrets into any `VITE_*` variable. Vite's development proxy is not included in the production build.

## Verification

```powershell
cd backend
php artisan test
```

The test DB must already have the migrations. Tests use rollback transactions, and `phpunit.xml` forces `devhire_test`. On another machine, create this separate database and migrate it with `DB_DATABASE=devhire_test` before testing; never point tests at a real-data database.

```powershell
cd frontend
npm run build
```

## Step-by-step Khmer guide

Read [BACKEND-GUIDE-KM.md](BACKEND-GUIDE-KM.md) for the database structure, environment configuration, API flow, implemented security controls, tests, and production preparation.
