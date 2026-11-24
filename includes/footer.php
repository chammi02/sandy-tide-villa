</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div><h3>🌴 <?= e(VILLA_NAME) ?></h3><p>Beachfront comfort in Hikkaduwa, on Sri Lanka's southwest coast.</p></div>
    <div><h4>Contact</h4><p>Galle Road, Hikkaduwa, Sri Lanka<br>+94 77 123 4567<br>stay@sandytide.lk</p></div>
    <div><h4>Quick links</h4><p><a href="<?= e(url('rooms.php')) ?>">Rooms</a> · <a href="<?= e(url('search.php')) ?>">Availability</a> · <a href="<?= e(url('login.php')) ?>">Login</a></p></div>
  </div>
  <div class="copyright">&copy; <?= date('Y') ?> <?= e(VILLA_NAME) ?>. Student project with fictional data.</div>
</footer>
<script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
