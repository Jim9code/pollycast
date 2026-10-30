# INEC Delta State Election Results Portal (Bincom PHP/Laravel Test)

A web application built with **Laravel (PHP MVC)** using the provided `bincom_test.sql` database for the 2011 Delta State (State ID: 25) General Elections.

---

## Features & Solutions

### 1. Question 1: Individual Polling Unit Results (`/`)
* Displays the announced party scores (`announced_pu_results`) for any individual Polling Unit (`polling_unit.uniqueid = announced_pu_results.polling_unit_uniqueid`).
* Includes a **3-level Chained Combo Box** (`LGA` &rarr; `Ward` &rarr; `Polling Unit`) powered by AJAX endpoints (`/api/lgas/{lgaId}/wards` and `/api/wards/{uniqueWardId}/polling-units`), plus a quick-jump selector for rapid assessment.

### 2. Question 2: Summed LGA Results (`/lga-results`)
* Allows selecting any Local Government Area in Delta State (`state_id = 25`) via a `select` box.
* Calculates the **summed total votes for each party** across all Polling Units in that LGA directly from `announced_pu_results` joined with `polling_unit` (`SUM(party_score) GROUP BY party_abbreviation`).
* Does **not** rely on `announced_lga_results` for the required total, while displaying `announced_lga_results` in a side-by-side comparison column for cross-checking.

### 3. Question 3: Store Results for All Parties for a New Polling Unit (`/polling-unit/create`)
* Provides a user-friendly form with a **chained combo box** (`LGA` &rarr; `Ward`) to create a new Polling Unit and enter scores for **all 9 registered political parties** (`PDP, DPP, ACN, PPA, CDC, JP, ANPP, LABOUR, CPP`).
* Saves the new Polling Unit in `polling_unit` and inserts individual party score rows into `announced_pu_results` inside an atomic database transaction.

---

## Local Setup Instructions

```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Create tables and seed data from bincom_test.sql
touch database/database.sqlite
php artisan migrate:fresh --seed

# 4. Start the development server
php artisan serve
```

Then visit `http://127.0.0.1:8000` in your browser.
