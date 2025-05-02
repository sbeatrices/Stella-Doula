# Doula Scheduling and Invoicing App

This is a lightweight PHP and SQLite-based web application for managing doula-client interactions. D>

## Tech Stack

- PHP (with inline HTML and CSS)
- SQLite3
- Apache

## Features

- User authentication for doulas and clients
- Doula profile with bio and listed services
- Availability management (date and time ranges)
- Clients can view profiles and book services
- Bookings are restricted to available time slots
- Booking conflict checks to prevent double-booking
- Itemized invoice generation based on completed services

## Installation

1. Clone or download project from github:
        git clone https://github.com/sbeatrices/doula-app 
        cd /var/www/html/doula-app

2. Ensure PHP and SQLite3 are installed.

3. Create the SQLite database and required tables (see utils/db.php for structure).

4. Make sure the SQLite .db file is writable by the server.

## Directory Overview

/public/ – Frontend routes and views:

login.php, register.php, index.php, view-profile.php

/utils/ – Database connection:

db.php

/src/ - Doula and Client specific (and shared) views, dashboard, authorization logic:

/auth/, /client/, /dashboard/, /doula/, /setup/, /shared/, /utils/,

/database/ – SQLite database file

All pages use inline CSS to stay under 40 HTTP requests

## How To Use

Register as a doula or client

Doulas can edit bios, add services, and set availability

Clients can view doula profiles and submit booking requests

Bookings must match an availability window and cannot overlap existing bookings

Doulas can mark services as completed and generate invoices

