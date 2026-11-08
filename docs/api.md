# HTTP API

Base URL: `http://127.0.0.1:8000/api`

The Expo app calls this API. The website uses the session routes in `routes/web.php` and does not send these tokens.

## Authentication

Public:

| Method | Path | Body |
| --- | --- | --- |
| POST | `/login` | `username`, `password` |
| POST | `/signup` | `full_name`, `username`, `role`, `contact`, `password`, `password_confirmation` |

Login and signup return a token and the staff profile (`id`, `name`, `username`, `role`, `email`, `contact`, `station`, `assigned_shift`, `shift_type`, `avatar_initials`).

```json
{
  "token": "1|plain-text-token",
  "user": {
    "id": 1,
    "name": "Kane Sajol",
    "username": "kane_sajol",
    "role": "Medical Technologist",
    "email": "kane.sajol@crh-bloodbank.gov.ph"
  }
}
```

New signups get the email `username@staff.local` unless the username already contains `@`. Password must be at least 6 characters and confirmed.

Signup responds with HTTP 201.

Send the token on every other request:

```http
Authorization: Bearer 1|plain-text-token
Accept: application/json
```

Inactive accounts (`status` other than `Active`) cannot sign in. A bad password returns HTTP 422 with `username` set to `Invalid username or password.`

| Method | Path | Purpose |
| --- | --- | --- |
| POST | `/logout` | Closes the shift record and deletes the current token |
| GET | `/user` | Current staff profile |
| PATCH | `/profile` | Update name, email, or contact |
| PATCH | `/profile/password` | `current_password`, `password`, `password_confirmation` |

## Donors and donations

All routes below require the bearer token.

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/donors/search` | Search by name. Query: `q` |
| GET | `/donors/search-by-location` | Search by place. Query: `location`, optional `year` |
| GET | `/donors/returning` | Donors who already have a visit |
| GET | `/donors/meta` | Blood types and other form lists |
| POST | `/donors` | Register a donor, or attach a visit when the name already exists |
| GET | `/donors/{donor}` | Profile and donations |
| PATCH | `/donors/{donor}` | Update the profile |
| DELETE | `/donors/{donor}` | Delete one donor and that donor’s donations |
| DELETE | `/donors` | Delete every donor |
| POST | `/donors/{donor}/donations` | Add a visit. Body: `date`, `location` |
| DELETE | `/donations/{donation}` | Delete one visit |

Registration follows the same rules as the website:

- Age 16–70, gender `Male` or `Female`, blood type one of the eight ABO/Rh values.
- `mbd_date` must contain a real date (for example `WI 1/01/2025`).
- `status` is `passed` or `deferred`.
- Passed visits need a unique `serial_number` and are stored as 1 bag.
- Deferred visits need `reason_for_deferral` and are stored as 0 bags.

`POST /donors` returns `message` and `donor` (`id`, `name`). When the name already has a profile, `message` is `Donation added for existing donor.`

## Consumption

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/consumption/meta` | Components, provinces, and places |
| GET | `/consumption/returning-patients` | Query: `q`. Names that already have a record |
| GET | `/consumption` | List. Same filters as the website: `province`, `municipality`, `barangay`, `component`, `month`, `year`, `q` |
| POST | `/consumption` | Create a record |
| PATCH | `/consumption/{consumption}` | Update a record |
| DELETE | `/consumption/{consumption}` | Delete one record |
| DELETE | `/consumption` | Delete every record |

New patient body: `patient_mode=new`, name fields or `patient_name`, `date_submitted`, `age`, `sex` (`M` or `F`), `blood_component`, `units`, `municipality`, `province`, optional `barangay`.

Returning patient body: `patient_mode=returning`, `returning_patient_name`, `blood_component`, `units`.

`blood_component` must be one of: Cryoprecipitate, Fresh Frozen Plasma, Fresh Whole Blood, Packed RBC, Platelet Apheresis, Platelet Concentrate, Washed RBC.

## Reports

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/reports` | Report payload for the mobile reports screen |

Query parameters:

| Parameter | Values |
| --- | --- |
| `view` | `periodic` (default), `demographics`, `consumption` |
| `period` | `monthly` (default), `quarterly`, `yearly` |
| `month`, `year` | Donation period. Years outside 2024–2027 fall back to the current year in that list |
| `location` | Optional place filter |
| `consumption_month`, `consumption_year` | Consumption period. Use `all` for every month or year |
| `blood_type` | Optional consumption blood type |
| `print_all` | `1` returns every section and clears the consumption filters |

Donor totals are unique people in that period. Consumption totals are units issued. See `App\Services\ReportService`.

## Staff

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/staff/roles` | The five role names |
| GET | `/staff` | Account list |
| POST | `/staff` | Create an account. Body matches signup, without signing in as that person |
| DELETE | `/staff/{user}` | Delete an account. You cannot delete yourself |
| GET | `/staff/log` | Shift records. Optional query: `staff` (user id) |

## Web routes

The browser app is session-based and listed in `routes/web.php`. After login, pages live under `/dashboard`:

| Path | Name |
| --- | --- |
| `/` | `login` |
| `/signup` | `signup` |
| `/dashboard/blood-collection` | `blood-collection` |
| `/dashboard/blood-consumption` | `blood-consumption` |
| `/dashboard/reports` | `reports` |
| `/dashboard/staff-accounts` | `staff-accounts` |
| `/dashboard/staff-log` | `staff-log` |
| `/dashboard/profile` | `staff-profile` |

Guest users who open a dashboard URL are sent to `/`. Signed-in users who open `/` are sent to blood collection.

## Errors

Validation errors are HTTP 422 JSON:

```json
{
  "message": "The age field must be at least 16.",
  "errors": {
    "age": ["The age field must be at least 16."]
  }
}
```

A missing or expired token is HTTP 401. A missing record is HTTP 404.
