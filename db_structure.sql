-- Sandy Tide Villa, Hikkaduwa - database structure and sample data
-- Import with phpMyAdmin or: mysql -u root < db_structure.sql
CREATE DATABASE IF NOT EXISTS sandy_tide_villa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sandy_tide_villa;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS seasonal_rates;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  contact_number VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,           -- bcrypt hash from password_hash()
  role ENUM('guest','admin') NOT NULL DEFAULT 'guest',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_name VARCHAR(100) NOT NULL,
  room_type VARCHAR(50) NOT NULL,
  description TEXT NOT NULL,
  facilities VARCHAR(255) NOT NULL,         -- comma separated list
  price_per_night DECIMAL(10,2) NOT NULL CHECK (price_per_night > 0),
  max_guests TINYINT UNSIGNED NOT NULL CHECK (max_guests > 0),
  image VARCHAR(100) NOT NULL DEFAULT 'room-garden-breeze.jpg',
  room_status ENUM('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  guest_name VARCHAR(100) NOT NULL,
  guest_email VARCHAR(150) NOT NULL,
  guest_phone VARCHAR(20) NOT NULL,
  guests TINYINT UNSIGNED NOT NULL DEFAULT 1,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  special_requests VARCHAR(300) NULL,
  booking_status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
  booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE RESTRICT,
  INDEX idx_room_dates (room_id, check_in, check_out, booking_status)
) ENGINE=InnoDB;

CREATE TABLE seasonal_rates (
  rate_id INT AUTO_INCREMENT PRIMARY KEY,
  season_name VARCHAR(60) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  multiplier DECIMAL(4,2) NOT NULL DEFAULT 1.00   -- 1.30 = 30% higher
) ENGINE=InnoDB;

CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment VARCHAR(500) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample users (Admin@123 / Guest@123)
INSERT INTO users (name,email,contact_number,password,role) VALUES
('Villa Administrator','admin@sandytide.lk','0771234567','$2y$10$SlZDH7ZlVL97qnMetnpPLeJMwvxKE3Aj30AoFbY4x9M5pj3ckdpvG','admin'),
('Nimali Perera','guest@example.com','0712345678','$2y$10$w9Pub3pePEVa.CJB0RhKJe5wwPwk/J7kHavoDKV3V27dCAfaaaNvO','guest');

INSERT INTO rooms (room_name,room_type,description,facilities,price_per_night,max_guests,image,room_status) VALUES
('Garden Breeze Room','Standard','A calm room with a mosquito-net bed, opening onto a veranda overlooking our tropical garden, a two-minute walk from Hikkaduwa beach.','Air conditioning,Free Wi-Fi,Hot water,Garden view,Veranda,Mosquito net',12000.00,2,'room-garden-breeze.jpg','active'),
('Deluxe Sand Room','Deluxe','A spacious deluxe room in warm sand tones with a king bed, comfortable lounge seating and a large smart TV.','Air conditioning,Free Wi-Fi,Hot water,Smart TV,Lounge seating,Breakfast included',16500.00,2,'room-deluxe-sand.jpg','active'),
('Ocean View Room','Deluxe','Wake up to sunrise over the Indian Ocean. Large windows and a sea-facing terrace.','Air conditioning,Free Wi-Fi,Hot water,Sea view,Terrace,Breakfast included',19500.00,3,'room-ocean-view.jpg','active'),
('Family Coral Room','Family','A roomy room with a double bed, a single bed and a sitting area, ideal for families exploring the Hikkaduwa coral reef.','Air conditioning,Free Wi-Fi,Hot water,Double and single beds,Sitting area,Pool access',24000.00,4,'room-family-coral.jpg','active'),
('Sunset Suite','Suite','Our top-floor suite with two comfortable beds, floor-to-ceiling windows and a sea-view balcony for the best sunsets in the villa.','Air conditioning,Free Wi-Fi,Hot water,Sea view,Private balcony,Two beds,Breakfast included',32000.00,3,'room-sunset-suite.jpg','active');

INSERT INTO seasonal_rates (season_name,start_date,end_date,multiplier) VALUES
('Peak Season 2026/27','2026-12-15','2027-01-05',1.30),
('Peak Season 2027/28','2027-12-15','2028-01-05',1.30),
('Easter / April Holidays 2027','2027-04-08','2027-04-20',1.15);

INSERT INTO bookings (user_id,room_id,guest_name,guest_email,guest_phone,guests,check_in,check_out,total_amount,special_requests,booking_status) VALUES
(2,2,'Nimali Perera','guest@example.com','0712345678',2,'2026-12-01','2026-12-04',49500.00,'Late check-in','Confirmed'),
(2,3,'Nimali Perera','guest@example.com','0712345678',2,'2027-01-10','2027-01-13',58500.00,NULL,'Pending');

INSERT INTO reviews (user_id,room_id,rating,comment) VALUES
(2,2,5,'Lovely room, friendly staff and the beach is right outside. Highly recommended!');
