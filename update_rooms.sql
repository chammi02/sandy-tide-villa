-- Run this in phpMyAdmin (database: sandy_tide_villa) if you imported an OLDER db_structure.sql.
-- It switches the rooms to the new photos and descriptions WITHOUT deleting users or bookings.
USE sandy_tide_villa;
UPDATE rooms SET image='room-garden-breeze.jpg',
  description='A calm room with a mosquito-net bed, opening onto a veranda overlooking our tropical garden, a two-minute walk from Hikkaduwa beach.',
  facilities='Air conditioning,Free Wi-Fi,Hot water,Garden view,Veranda,Mosquito net' WHERE room_name='Garden Breeze Room';
UPDATE rooms SET image='room-deluxe-sand.jpg',
  description='A spacious deluxe room in warm sand tones with a king bed, comfortable lounge seating and a large smart TV.',
  facilities='Air conditioning,Free Wi-Fi,Hot water,Smart TV,Lounge seating,Breakfast included' WHERE room_name='Deluxe Sand Room';
UPDATE rooms SET image='room-ocean-view.jpg' WHERE room_name='Ocean View Room';
UPDATE rooms SET image='room-family-coral.jpg',
  description='A roomy room with a double bed, a single bed and a sitting area, ideal for families exploring the Hikkaduwa coral reef.',
  facilities='Air conditioning,Free Wi-Fi,Hot water,Double and single beds,Sitting area,Pool access' WHERE room_name='Family Coral Room';
UPDATE rooms SET image='room-sunset-suite.jpg',
  description='Our top-floor suite with two comfortable beds, floor-to-ceiling windows and a sea-view balcony for the best sunsets in the villa.',
  facilities='Air conditioning,Free Wi-Fi,Hot water,Sea view,Private balcony,Two beds,Breakfast included' WHERE room_name='Sunset Suite';
ALTER TABLE rooms ALTER image SET DEFAULT 'room-garden-breeze.jpg';
