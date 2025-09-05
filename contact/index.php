<?php
$env = false;

if (file_exists('../.env')) {
    $env = parse_ini_file('../.env');
}
elseif (file_exists('../../.env')) {
    $env = parse_ini_file('../../.env');
}

if ($env === false) {
    die("No .env file found");
}


$email_send_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $client_email = trim($_POST['email'] ?? '');
  $client_name = trim($_POST['naam'] ?? '');

  $question = $_POST['vraag'] ?? '';
  $question .= "<br><br><hr><br>";
  $question .= "<strong>Van:</strong> " . htmlspecialchars($client_name) . "<br>";
  $question .= "<strong>Email:</strong> " . htmlspecialchars($client_email);
  
  require_once($_SERVER['DOCUMENT_ROOT'] . "/assets/php/sender.php");
  $output = sender($env["admin_email"], $question, "Vraag van " . $client_name);

  if (strpos($output, 'Email verstuurd!') !== false) {
    $email_send_message = "Email verstuurd!";
  } else {
    $email_send_message = 'Email verzending mislukt!';
  }
}
?>


<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/styling/global.css">
  <link rel="stylesheet" href="/styling/contact.css">
  <link rel="icon" href="/assets/logos/favicon.ico">
  <title>contact - Hotel De Zonne Vallei</title>

  <?php include("../assets/php/fontawesome.php"); ?>
</head>


<body>
  <?php include('../assets/html/navbar.html'); ?>
  <section class="cont">
    <h1>Neem contact met ons.</h1>
    <main class="box">

      <form action='' method='post'>
        <input type="text" id="naam" name="naam" size="40" required placeholder="Naam" class="style">
        <input type="email" id="email" name="email" required placeholder="Email">
        <textarea id="vraag" name="vraag" rows="4" cols="40" minlength="30" maxlength="800" required placeholder="Stel je vraag"></textarea>
        <input type="submit" id="verzenden" name="verzenden" value="Verstuur" required>
        <?php if ($email_send_message): ?>
          <p class="error"><?php echo $email_send_message; ?></p>
        <?php endif; ?>
      </form>

      <section>
        <ul>
          <li><i class="fas fa-info my-info my-icon"></i> Contact info:</li>
          <li><a href="https://www.google.com/maps?q=ict+vanaf+morgen+alkmaar"><i class="fas fa-location-dot my-icon"></i> Straatnaam 85 1234 AB Alkmaar NL</a></li>
          <li><a href="tel:0724145343"><i class="fas fa-phone my-icon"></i> 072 41 45 343</a></li>
          <li><a href="mailto:info@hotelzon.nl"><i class="fas fa-envelope my-icon"></i> info@hotelzon.nl</a></li>
        </ul>
      </section>
    </main>
  </section>

  <?php include('../assets/html/footer.html'); ?>

</body>

</html>