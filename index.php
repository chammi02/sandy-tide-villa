<?php
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
$featured = db()->query("SELECT * FROM rooms WHERE room_status='active' ORDER BY price_per_night DESC LIMIT 3")->fetchAll();
?>
<section class="hero" style="background-image:url('<?= e(url('assets/images/hero.jpg')) ?>')">
  <div class="hero-overlay">
    <div class="container">
      <h1>Sun, sand &amp; sea in Hikkaduwa</h1>
      <p>Wake up to the sound of the Indian Ocean. Snorkel with turtles, surf at sunrise and relax on golden sand — just steps from our door.</p>
      <a class="btn btn-primary" href="<?= e(url('book.php')) ?>">Book Now</a>
      <a class="btn btn-light" href="<?= e(url('rooms.php')) ?>">Explore Rooms</a>
      <form class="quick-search" action="<?= e(url('search.php')) ?>" method="get" data-date-form>
        <label>Check-in<input type="date" name="check_in" required></label>
        <label>Check-out<input type="date" name="check_out" required></label>
        <label>Guests<input type="number" name="guests" min="1" max="6" value="2" required></label>
        <button class="btn btn-primary" type="submit">Check Availability</button>
      </form>
    </div>
  </div>
</section>

<section class="section container">
  <h2 class="section-title">Welcome to <?= e(VILLA_NAME) ?></h2>
  <p class="lead">A small family-run villa on the Hikkaduwa coast, famous for its coral reef, sea turtles, surf breaks and golden sunsets. We offer five comfortable rooms, a garden pool and warm Sri Lankan hospitality, a short walk from the beach, restaurants and the Hikkaduwa National Park.</p>
</section>

<section class="section alt">
  <div class="container">
    <h2 class="section-title">Villa facilities</h2>
    <div class="grid grid-4">
      <div class="card feature"><span>🏖️</span><h3>2 min to beach</h3><p>Direct sandy beach access and sun loungers.</p></div>
      <div class="card feature"><span>🏊</span><h3>Garden pool</h3><p>Freshwater pool surrounded by coconut palms.</p></div>
      <div class="card feature"><span>🍳</span><h3>Local breakfast</h3><p>Hoppers, string hoppers, fruit and Ceylon tea.</p></div>
      <div class="card feature"><span>📶</span><h3>Wi-Fi &amp; A/C</h3><p>Fast Wi-Fi and air conditioning in every room.</p></div>
    </div>
  </div>
</section>

<section class="section container">
  <h2 class="section-title">Featured rooms</h2>
  <div class="grid grid-3">
  <?php foreach ($featured as $r): include __DIR__ . '/includes/room_card.php'; endforeach; ?>
  </div>
  <p class="center"><a class="btn btn-outline" href="<?= e(url('rooms.php')) ?>">View all rooms</a></p>
</section>

<section class="section alt">
  <div class="container">
    <h2 class="section-title">Gallery</h2>
    <div class="grid grid-3 gallery">
      <figure><img src="<?= e(url('assets/images/gallery-beach.jpg')) ?>" alt="Hikkaduwa beach"><figcaption>Hikkaduwa beach</figcaption></figure>
      <figure><img src="<?= e(url('assets/images/gallery-sunset.jpg')) ?>" alt="Sunset over the ocean"><figcaption>Golden sunsets</figcaption></figure>
      <figure><img src="<?= e(url('assets/images/gallery-pool.jpg')) ?>" alt="Villa garden pool"><figcaption>Garden pool</figcaption></figure>
    </div>
  </div>
</section>

<section class="section container">
  <h2 class="section-title">Find us in Hikkaduwa</h2>
  <div class="map-wrap">
    <iframe title="Hikkaduwa map" loading="lazy"
      src="https://www.openstreetmap.org/export/embed.html?bbox=80.07%2C6.12%2C80.13%2C6.16&amp;layer=mapnik&amp;marker=6.1395%2C80.1005"></iframe>
  </div>
  <p class="center"><a href="https://www.openstreetmap.org/?mlat=6.1395&amp;mlon=80.1005#map=14/6.1395/80.1005" target="_blank" rel="noopener">Open larger map</a></p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
