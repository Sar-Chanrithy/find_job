# DevHire Cambodia — Backend និងសុវត្ថិភាព ជាជំហានៗ

## 1. អ្វីដែលបានបង្កើត

- PostgreSQL database: `devhire_db`។ Database ចាស់ `devhire_cambodia` មិនបានលុប។
- Database សម្រាប់ tests: `devhire_test`។ Tests ប្រើ transaction ហើយ rollback ទិន្នន័យសាកល្បង។
- PostgreSQL login: `devhire_app` ជាមួយ random password ក្នុង `backend/.env`។ គណនីនេះមិនមានសិទ្ធិ superuser, បង្កើត database, បង្កើត role ឬ replication ទេ។
- តារាង `companies` និង `job_posts` ព្រមទាំងតារាងស្តង់ដារ Laravel។
- ក្រុមហ៊ុនគំរូ 5 និងការងារគំរូ 9។ ទិន្នន័យនៅ PostgreSQL ពិត ប៉ុន្តែការងារទាំងនេះមិនមែនការប្រកាសពិតរបស់ក្រុមហ៊ុនទេ។
- REST API សម្រាប់បញ្ជី និងព័ត៌មានលម្អិតការងារ។ React ប្រើ Axios ដើម្បីអាន API។

ដំណាក់កាលនេះជាការស្វែងរកការងារ។ Login/Register, employer CRUD, CV uploads, applications និង dashboards មិនទាន់បានអនុវត្តនៅ Backend ទេ។ ប៊ូតុងទាំងនោះនៅ UI បង្ហាញសារពន្យល់ ហើយមិនអះអាងថាបានបង្កើតគណនី ឬផ្ញើពាក្យទេ។

## 2. យល់ពី Database

```text
users (ម្ចាស់ក្រុមហ៊ុននាពេលក្រោយ)
  └── companies.owner_id (nullable សម្រាប់ sample companies)
        └── job_posts.company_id
```

`job_posts` មាន title, category, location, salary, description, responsibilities, requirements, status និង published/expiry dates។ ប្រើឈ្មោះនេះព្រោះតារាង `jobs` របស់ Laravel ទុកសម្រាប់ queue tasks។

Migration គឺជាឯកសារកំណត់រចនាសម្ព័ន្ធ Database។ Seeder គឺជាកូដបញ្ចូលទិន្នន័យគំរូ។

```powershell
cd backend
php artisan migrate
php artisan db:seed
```

Seeder របស់គម្រោងនេះអាចរត់ម្ដងទៀតដោយមិនបង្កើតគំរូស្ទួន និងមិនសរសេរជាន់ការងារដែលមានរួច។ វាមិនបង្កើត default user ដែលមាន password ដឹងជាមុនទេ។ កុំប្រើ `migrate:fresh` លើ database ដែលមានទិន្នន័យចង់រក្សា ព្រោះវាលុបតារាងទាំងអស់។

## 3. ការកំណត់ `.env`

`backend/.env` បានកំណត់រួច៖

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=devhire_db
DB_USERNAME=devhire_app
DB_PASSWORD=<password ដែលបានបង្កើតក្នុងឯកសាររបស់អ្នក>
APP_DEBUG=false
FRONTEND_URLS=http://127.0.0.1:5173,http://localhost:5173
```

កុំជំនួស password ពិតដោយអក្សរគំរូខាងលើ។ កុំដាក់ `.env` ឬ password នៅ GitHub, React, screenshot ឬ chat។ `.env.example` គ្រាន់តែបង្ហាញឈ្មោះ setting និងមិនមាន password ពិតទេ។

ក្រោយកែ `.env`៖

```powershell
php artisan config:clear
```

`devhire_app` អាចកែ schema ក្នុង database របស់វា ដើម្បីរត់ migrations។ សម្រាប់ production គួរបែងចែកគណនី migration និង runtime ដែលមានសិទ្ធិអាន/សរសេរតែតារាងចាំបាច់។ ការមិនប្រើ superuser មិនមានន័យថា database account នេះជា read-only ទេ។

## 4. ដំណើរការ Backend និង Frontend

បើក terminal 2៖

Terminal 1:

```powershell
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```powershell
cd frontend
npm run dev
```

បើក `http://127.0.0.1:5173/`។ Vite proxy បញ្ជូន `/api` ទៅ Laravel នៅ `127.0.0.1:8000`។ `VITE_API_BASE_URL` ជា public URL ប៉ុណ្ណោះ។ រាល់ `VITE_*` ត្រូវចាត់ទុកថាអ្នកប្រើអាចមើលឃើញបាន។

## 5. API ដែលមាន

| Request | តួនាទី |
| --- | --- |
| `GET /api/jobs` | បញ្ជីការងារ និង pagination |
| `GET /api/jobs/1` | ព័ត៌មានការងារមួយ |
| `GET /api/jobs?q=developer&remote=1` | ស្វែងរកការងារ Remote |
| `GET /api/jobs?category=Design&location=Battambang` | Filter category និងទីតាំង |
| `GET /api/jobs?type=Full%20time&sort=newest` | Filter ប្រភេទ និង sort |
| `GET /api/jobs?page=2&per_page=3` | ទំព័រទី 2 ជាមួយ 3 jobs |
| `GET /api/jobs?ids[]=1&ids[]=2` | ទាញយក saved jobs តាម IDs |

Response មាន `data` ជាទិន្នន័យ, `meta` ជាព័ត៌មាន pagination និង `links`។ Saved job IDs នៅក្នុង browser localStorage; មិនទាន់ sync ជាមួយគណនីទេ។

សាកល្បងពី PowerShell៖

```powershell
Invoke-RestMethod 'http://127.0.0.1:8000/api/jobs?q=developer'
```

កូដ Axios៖

```js
import { api } from './services/api';
const response = await api.get('/jobs', {
  params: { q: 'developer', page: 1, per_page: 9 },
});
console.log(response.data.data);
```

លំហូរ៖ React → Axios → `/api/jobs` → validation → Eloquent query → PostgreSQL → JSON Resource → React។

## 6. សុវត្ថិភាពដែលបានអនុវត្ត

1. **Validate input នៅ Backend**៖ search មានប្រវែងកំណត់, sort/type មានបញ្ជីអនុញ្ញាត, page ជាចំនួនវិជ្ជមាន និង `per_page` មិនលើស 50។ Invalid inputs ទទួល `422`។ ការពិនិត្យត្រឹម React មិនគ្រប់គ្រាន់ទេ។
2. **ការពារ SQL injection**៖ Eloquent បញ្ជូនតម្លៃជា bound parameters។ ឈ្មោះ column និង sort មិនត្រូវយកដោយផ្ទាល់ពី user input។ LIKE wildcards ត្រូវបាន escape ដើម្បីស្វែងរកជាអក្សរពិត។
3. **បង្ហាញតែទិន្នន័យសាធារណៈ**៖ API Resource កំណត់ fields ជាក់លាក់ មិនបញ្ជូន password, owner account ឬ internal status។ Draft, closed, expired និង scheduled jobs មិនអាចមើលបានទាំងបញ្ជី និង detail។
4. **Rate limit**៖ 120 requests ក្នុងមួយនាទីតាម IP; លើសកំណត់ទទួល `429`។ នេះជាការកំណត់មូលដ្ឋាន មិនមែនជាការការពារ DDoS ពេញលេញទេ។
5. **CORS ជាក់លាក់**៖ អនុញ្ញាត browser origins ដែលកំណត់ក្នុង `FRONTEND_URLS`។ CORS មិនមែនជា authentication ទេ; public jobs អាចអានតាម curl ឬកម្មវិធីផ្សេងបាន។
6. **មិនបើក write API មុនមាន authorization**៖ POST/PATCH/DELETE jobs មិនមាន route។ នៅដំណាក់កាល employer ត្រូវមាន login និង Policy ពិនិត្យ ownership លើ server; លាក់ប៊ូតុងក្នុង React មិនគ្រប់គ្រាន់ទេ។
7. **លាក់ debugging details**៖ `APP_DEBUG=false` មិនបង្ហាញ stack trace និងព័ត៌មានខាងក្នុងទៅអ្នកប្រើ។ ពេល debug ពិនិត្យ `backend/storage/logs/laravel.log` នៅក្នុងម៉ាស៊ីន។ កុំផ្សព្វផ្សាយ log ពេញដែលអាចមានព័ត៌មានសម្ងាត់។
8. **Render text ជាមួយ React**៖ description និង requirements បង្ហាញជា text មិនប្រើ `dangerouslySetInnerHTML`។ Axios មាន timeout និង cancel requests ចាស់ ដើម្បីកុំឱ្យលទ្ធផលយឺតមកជំនួស search ថ្មី។

## 7. Tests

Database `devhire_test` បានបង្កើត និង migrate រួច។ រត់៖

```powershell
cd backend
php artisan test
```

`phpunit.xml` បង្ខំប្រើ `devhire_test`; tests មិនប្រើ `migrate:fresh` ទេ។ Tests ពិនិត្យ filters, pagination, hidden jobs, validation, SQL-like input, CORS, rate limit និងមិនមាន public writes។

Frontend៖

```powershell
cd frontend
npm run build
```

## 8. មុនដាក់ Production

- ប្រើ HTTPS និង `APP_ENV=production`, `APP_DEBUG=false`។
- Web server document root ត្រូវទៅ `backend/public` មិនមែន root របស់ backend ទេ។
- កំណត់ CORS ជា domain ពិត និង reverse proxy `/api` ទៅ Laravel ឬប្រើ public HTTPS API URL ក្នុង frontend build។ Vite development proxy មិនដំណើរការនៅ production build ទេ។
- កុំដាក់ PostgreSQL port 5432 ជាសាធារណៈ; ប្រើ network access rules និង TLS តាម deployment។
- រក្សា secrets ក្នុង server environment/secret manager, backup database និងសាកល្បង restore។
- ប្រើ runtime DB account ដែលមានសិទ្ធិតិចជាង account សម្រាប់ migrations។
- Laravel `artisan serve` និង Vite `npm run dev` គឺសម្រាប់ local development។
- ពេលបន្ថែម login/register ប្រើ Laravel Sanctum session cookies + CSRF សម្រាប់ first-party SPA, password hashing, login throttling, server-side role/ownership policies និង email verification។ កុំទុក auth token ក្នុង localStorage ជាការជ្រើសរើសដំបូង។
- ពេលបន្ថែម CV uploads ត្រូវ validate file size/type, private storage និង download authorization។

នេះជាមូលដ្ឋានសុវត្ថិភាពសម្រាប់ដំណាក់កាល browsing API មិនមែនជាការបញ្ជាក់ថា platform ពេញលេញបានធ្វើ security audit រួចទេ។

ឯកសារផ្លូវការ៖ [Laravel validation](https://laravel.com/docs/12.x/validation), [routing និង rate limiting](https://laravel.com/docs/12.x/routing), [deployment](https://laravel.com/docs/12.x/deployment)។
