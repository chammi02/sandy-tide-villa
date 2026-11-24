# Sandy Tide Villa, Hikkaduwa – Coastal Villa & Room Booking System

A dynamic web application for a small beachfront villa in **Hikkaduwa, Sri Lanka**. Tourists can explore the villa,
search room availability, register, and submit booking requests. The administrator manages rooms and confirms or
cancels bookings. The interface uses a warm **sand colour theme** with an ocean-teal accent.

Built with **HTML5, CSS3, JavaScript, PHP (PDO) and MySQL**. No frameworks or external libraries are required.

## Implemented features

**Guest side**
- Home page: villa introduction, Hikkaduwa location (interactive OpenStreetMap), facilities, featured rooms, gallery, "Book Now"
- Rooms page: room cards with image, description, facilities, max guests, price/night; filter by type, price and facility
- Room details page with guest reviews and ratings
- Availability search (check-in, check-out, guests) – only rooms with no overlapping **confirmed** booking are shown
- Booking form with live nights/total calculation and **AJAX availability check**
- Registration, login, logout (sessions, `password_hash`/`password_verify`)
- My Bookings: status (Pending / Confirmed / Cancelled) and cancellation of pending bookings
- Seasonal pricing (peak season multiplier applied night by night)
- Responsive layout (desktop / tablet / mobile) using CSS Grid, Flexbox and media queries

**Administrator side**
- Dashboard with statistics (bookings by status, confirmed revenue, active rooms, guests, most booked rooms)
- Room management: add, view, edit (details and pricing), activate/deactivate, delete
- Booking management: filter, confirm, cancel; confirmation is blocked if it would overlap another confirmed booking

**Security / quality**
- PDO prepared statements everywhere, output escaping (XSS), CSRF tokens on every POST form
- Passwords hashed with `password_hash()`; `session_regenerate_id()` on login
- Role-based access control; guests can only see and cancel their own pending bookings
- Client-side **and** server-side validation; total price is always recalculated on the server

## Software requirements
- XAMPP / WAMP / Laragon (or any Apache + PHP + MySQL/MariaDB stack)
- **PHP 8.1 or newer**, MySQL 5.7+ / MariaDB 10.4+
- A modern web browser

## Installation
1. Copy the `sandy-tide-villa` folder into your web root (`C:\xampp\htdocs\` for XAMPP).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) → *Import* → choose `db_structure.sql` → *Go*.
   (This creates the `sandy_tide_villa` database, tables and sample data.)
4. If your MySQL user/password differ from the XAMPP defaults (`root` / empty), edit `config/db.php`.
5. Visit **http://localhost/sandy-tide-villa/**

## Sample login credentials
| Role  | Email                 | Password   |
|-------|-----------------------|------------|
| Admin | admin@sandytide.lk    | Admin@123  |
| Guest | guest@example.com     | Guest@123  |

(New guests can register from the Register page. All data is fictional.)

## Project structure
```
index.php, rooms.php, room.php, search.php   public pages
book.php, my_bookings.php, cancel_booking.php  booking flow
register.php, login.php, logout.php          authentication
api/check_availability.php                   AJAX availability + price (JSON)
admin/                                       dashboard, rooms, room form, bookings
includes/                                    functions, header, footer, room card, admin nav
config/db.php                                database connection (PDO)
assets/css, assets/js, assets/images         styles, scripts, SVG images
db_structure.sql                             schema + sample records
```

## Database design (ER summary)
- `users (user_id PK, name, email UNIQUE, contact_number, password, role)`
- `rooms (room_id PK, room_name, room_type, description, facilities, price_per_night, max_guests, image, room_status)`
- `bookings (booking_id PK, user_id FK→users, room_id FK→rooms, guest_*, guests, check_in, check_out, total_amount, booking_status, booking_date)`
- `seasonal_rates (rate_id PK, season_name, start_date, end_date, multiplier)`
- `reviews (review_id PK, user_id FK→users, room_id FK→rooms, rating, comment)`

## Testing summary (manual)
- Register with invalid data → client and server messages shown
- Past dates / check-out before check-in → blocked in browser and on server
- Confirm a booking, then try to book or confirm an overlapping one → rejected
- Guest A cannot cancel Guest B's booking (checked by `user_id` in SQL)
- Guest opens `/admin/` URLs → HTTP 403

## Acknowledgements and AI declaration
- Map embed: © OpenStreetMap contributors. Room and gallery photos were supplied by the author for this project.
- **Generative AI declaration:** *(Edit this before submitting.)* This project's code was generated with the help of an
  AI assistant (Claude by Anthropic) and then reviewed by me. I must be able to explain every part of the code.
