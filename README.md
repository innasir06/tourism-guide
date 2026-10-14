# 🏔️ Pakistan Tourism Guide

A full-stack web application that promotes tourism in Pakistan. Users can explore destinations, browse tour packages, book trips, and manage their travel plans.

Built as a team project (4 members) for the Web Technology course at Fatima Jinnah Women University.

## ✨ Features
- **Authentication:** registration, login (bcrypt password hashing), "Remember me", password recovery, secure sessions
- **Destinations:** browse, search, and filter by province, category, and rating
- **Tour packages:** itineraries, pricing, difficulty, and a real-time price calculator
- **Booking system:** dates, guests, optional hotel, special requests
- **Shopping cart:** add multiple packages, update guests, price summary
- **User dashboard:** profile, booking history, and booking status
- **Reviews, favorites, travel tips, contact form, profile picture upload**
- **Admin panel:** manage bookings, packages, and destinations
- Responsive design for desktop, tablet, and mobile

## 🛠 Tech Stack
HTML • CSS • JavaScript • PHP • MySQL • Apache (XAMPP) • PHPMailer (Composer)

## 🗄 Database
MySQL database `tourism_guide_db1` with 15 tables, including users, destinations, tour_packages, hotels, bookings, reviews, favorites, provinces, travel_tips, and contact_messages. The SQL schema file is available on request.

## ▶️ Run Locally
1. Install [XAMPP](https://www.apachefriends.org/) and start Apache and MySQL
2. Copy this project into `C:\xampp\htdocs\tourism_guide`
3. Create a database named `tourism_guide_db1` in phpMyAdmin and import the schema
4. Run `composer install` in the project folder to install PHPMailer
5. Add your own SMTP email and app password in `includes/mailer.php` and `includes/booking-mailer.php` (the repo has placeholders)
6. Open `http://localhost/tourism_guide/`




## 📸 Screenshots
📄 [View project screenshots (PDF)](screenshot.pdf)

## 👥 Team
Inshal Nasir • Alishba Zainab • Lujain Zia • Fatima Kashif
