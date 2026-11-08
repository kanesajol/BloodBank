# Staff guide

CRH Blood Bank is for hospital and mobile-collection staff. Donors and patients do not sign in.

Sign in at `/` with a username and password. Sign up at `/signup` if you do not have an account yet. After login you land on **Blood Collection**.

The left menu has:

- Blood Collection
- Blood Consumption
- Reports
- Staff Accounts
- Staff Log
- Profile (name in the sidebar)

## Blood collection

Open **Blood Collection** to find a donor or register a new one.

Search works in three ways:

- **Name** — type part of the donor’s name.
- **Location** — filter by place, then pick a donor.
- **Location and year** — same filter, limited to a donation year.

### Register a donor

Required fields:

- First name and last name (middle name and suffix are optional)
- Blood type
- Age, 16 to 70
- Gender (Male or Female)
- Contact number
- Barangay and municipality

Province is filled from the municipality when you leave it blank. Caraga provinces used by the app are Agusan del Norte, Agusan del Sur, Dinagat Islands, Surigao del Norte, and Surigao del Sur.

Screening fields:

| Field | Notes |
| --- | --- |
| MBD date | Venue plus a date, for example `WI 1/01/2025`. The date becomes the donation date. The words before the date become the venue. |
| Status | `passed` or `deferred` |
| Serial number | Required when the donor passed. Must be unique. |
| Reason for deferral | Required when deferred: Underweight, Low Hemoglobin, Lack of Sleep, Elevated BP, or a custom reason |
| Reserved for patient | Optional, and only when the donor passed |
| Remarks | Optional |

A **passed** donation counts as 1 bag. A **deferred** visit is stored with 0 bags, no serial number, and no reserved patient.

If that full name already exists, the app does not create a second profile. It adds this visit to the existing donor and keeps the original home address.

### Returning donor

Open the donor, then add another donation with a date and a location. The blood type stays the one on the profile.

You can delete a single donation, one donor, or every donor. Deleting a donor also deletes that donor’s visits.

## Blood consumption

Open **Blood Consumption** to record blood issued to a patient.

A new patient needs:

- Name (first and last; middle name and suffix optional)
- Date
- Age (0–120) and sex (`M` or `F`)
- Blood component
- Units (at least 1)
- Municipality and province
- Barangay, optional

Components:

- Cryoprecipitate
- Fresh Frozen Plasma
- Fresh Whole Blood
- Packed RBC
- Platelet Apheresis
- Platelet Concentrate
- Washed RBC

A **returning patient** is someone who already has a consumption record. Pick that name, then enter only the component and the number of units. Age, sex, and address are copied from the latest record.

You can edit a record, delete one record, or delete every consumption record. The patient page lists each visit for that name.

Filters on the list: province, municipality, barangay, component, month, year, and a name search.

## Reports

Open **Reports** and choose a period and, when you need it, a place.

| Report | What it shows |
| --- | --- |
| Monthly | Donors who donated that month, with visit history and a blood-type breakdown |
| Quarterly | Donation totals for each quarter of the year |
| Yearly | Donation totals for the year |
| Age | Donors grouped by age |
| Demographics | Donor counts by age and sex for the selected month |
| Gender | Male and female donation counts |
| Consumption | Units issued, with municipality detail |
| Consumption demographics | Patients grouped for the selected month |

Counts are **unique donors** for the period, not raw bag totals, except where a report is explicitly about units issued.

Drill-down pages:

- `/dashboard/reports/demographic-details` — donors inside one demographic group
- `/dashboard/reports/consumption-details` — consumption rows for one municipality
- `/dashboard/reports/consumption-demographic-details` — patients inside one consumption demographic group

## Staff accounts and log

**Staff Accounts** lists everyone and lets you add an account: full name, username, role, contact, and password (at least 6 characters, entered twice). A new username is stored with the email `username@staff.local`. You can remove another account. You cannot remove the account you are signed in with.

**Staff Log** is the duty history: shift, station, login time, logout time, and status (`On Duty` or `Completed`). Signing in opens a record. Signing out closes it. The app also records presence while you stay on a page.

**Profile** is your own name, email, contact, and password. Changing the password asks for the current one.

Roles:

- Medical Technologist
- Blood Collection Nurse
- Emergency Screening Officer
- Phlebotomist / Screener
- Blood Bank Administrator

These roles do not hide menus. Every signed-in staff member can open collection, consumption, reports, and staff pages.

## Mobile app

The phone app has the same jobs, split into tabs:

- **Collection** — search, donor detail, register, returning donor
- **Consumption** — list and add a record
- **Reports**
- **Staff** — accounts, duty log, and sign out

Sign in with the same username and password as the website. The session is a token stored on the device, not a browser cookie.
