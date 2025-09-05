<?php
require '../../assets/php/db.php';

$room_id = isset($_REQUEST['num']) && is_numeric($_REQUEST['num']) ? (int) $_REQUEST['num'] : null;
$room = null;
if ($room_id) {
    $res = mysqli_query($conn, "SELECT id, naam, prijs, beschikbaar, beschrijving FROM kamers WHERE id = $room_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($res)) {
        $room = $row;
    }
}

$errors = [];
$success = false;
$email = '';
$email_send_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $klant_naam = $_POST['naam'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Voer een geldig e-mailadres in.';
    }
    if (!$room) {
        $errors[] = 'Ongeldige kamer geselecteerd.';
    }
    if (empty($errors)) {
        $message = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/assets/html/email_template.html");
        $message = str_replace('{{kamer_naam}}', $room['naam'], $message);
        $message = str_replace('{{kamer_link}}', "https://hotel.alexjonker.dev/kamer?num=" . $room['id'], $message);
        $message = str_replace('{{klant_naam}}', $klant_naam, $message);
        $message = str_replace("{{datum}}", $_POST['datum'] ?? '', $message);

        require_once($_SERVER['DOCUMENT_ROOT'] . "/assets/php/sender.php");
        $output = sender($email, $message, "Reservering bevestiging");

        if (strpos($output, 'Email verstuurd!') !== false) {
            $success = true;
        } else {
            $lines = explode("\n", trim(strip_tags($output)));
            $firstLine = $lines[0] ?? '';
            if ($firstLine === '' || stripos($firstLine, 'email verzending mislukt!') === false) {
                $firstLine = 'Onbekende fout.';
            }
            $email_send_error = 'Email verstuurd! ';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="/styling/global.css">
    <link rel="stylesheet" href="/styling/reserveer.css">
    <script src="/assets/js/kamer_slideshow.js"></script>
    <title>Reserveer kamer <?= htmlspecialchars($room_id) ?> - Hotel De Zonne Vallei</title>
</head>

<body>
    <?php include('../../assets/html/navbar.html'); ?>

    <?php if ($room): ?>
        <section class="kamer-container">
            <h1 class="kamer-naam">Reserveer: <?= htmlspecialchars($room['naam']) ?></h1>
            <main class="kamer-content">
                <article class="kamer-info">
                    <p class="kamer-prijs">Prijs per nacht: €<?= number_format($room['prijs'], 2, ',', '.') ?></p>
                    <p class="kamer-beschrijving"><?= nl2br(htmlspecialchars($room['beschrijving'])) ?></p>
                </article>
                <?php
                $kamer_id = $room['id'];
                $afbeeldingen = [];
                $afbeeldingen_result = mysqli_query($conn, "SELECT link FROM afbeeldingen WHERE kamer_id = $kamer_id");
                while ($afbeelding_row = mysqli_fetch_assoc($afbeeldingen_result)) {
                    $afbeeldingen[] = $afbeelding_row['link'];
                }
                ?>
                <main class="onder">
                    <div class="kamer-afbeeldingen-slideshow">
                        <?php if (!empty($afbeeldingen)): ?>
                            <?php if (count($afbeeldingen) > 1): ?>
                                <button class="slideshow-arrow left" onclick="plusSlides(-1)">&#10094;</button>
                            <?php endif; ?>
                            <div class="slideshow-images">
                                <?php foreach ($afbeeldingen as $index => $link): ?>
                                    <img class="kamer-afbeelding slide-container" src="<?= htmlspecialchars($link) ?>"
                                        alt="Afbeelding van <?= htmlspecialchars($room['naam']) ?>"
                                        style="<?= $index === 0 ? '' : 'display:none;' ?>">
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($afbeeldingen) > 1): ?>
                                <button class="slideshow-arrow right" onclick="plusSlides(1)">&#10095;</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="kamer-afbeelding">Geen afbeeldingen beschikbaar.</p>
                        <?php endif; ?>
                    </div>
                    <div class="rechts">
                        <p class="kamer-beschikbaarheid"">Nog <?= $room['beschikbaar'] ?> kamers beschikbaar</p>

                    <?php if ($success): ?>
                        <article class=" reserveer-success">
                            <p>Bedankt! Er is een reserveringsaanvraag verstuurd voor kamer
                                <?= htmlspecialchars($room['naam']) ?> met e-mailadres <?= htmlspecialchars($email) ?>.
                            </p>
                            <p><a class="terug-knop kamer-reserveer-knop" href="/kamer?num=<?= $room['id'] ?>">Terug naar
                                    kamer</a></p>
                            </article>
                        <?php elseif ($email_send_error): ?>
                            <article class="reserveer-errors">
                                <p><?= htmlspecialchars($email_send_error) ?></p>
                            </article>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <article class="reserveer-errors">
                                    <?php foreach ($errors as $err): ?>
                                        <p><?= htmlspecialchars($err) ?></p>
                                    <?php endforeach; ?>
                                </article>
                            <?php endif; ?>

                            <form class="reserveer-form" method="post" action="?num=<?= $room_id ?>">
                                <label for="naam">Naam</label>
                                <input id="naam" name="naam" type="text" required value="<?= htmlspecialchars($klant_naam) ?>">
                                <label for="email">E-mailadres</label>
                                <input id="email" name="email" type="email" required value="<?= htmlspecialchars($email) ?>">
                                <label for="datum">Datum</label>
                                <input id="datum" name="datum" type="date" required>
                                <button type="submit" class="kamer-reserveer-knop">Bevestig</button>
                            </form>

                            <p style="margin-top:1rem;"><a class="terug-knop kamer-reserveer-knop"
                                    href="/kamer?num=<?= $room['id'] ?>">Terug naar kamer</a></p>
                        <?php endif; ?>
                    </div>
                </main>
        </section>
    <?php else: ?>
        <article class="kamer-error">
            <p>Ongeldige kamer. Ga terug naar <a href="/kamers">overzicht kamers</a>.</p>
        </article>
    <?php endif; ?>

    <?php include('../../assets/html/footer.html'); ?>
</body>

</html>