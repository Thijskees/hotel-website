<?php

session_start();

if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
} else {
    header('Location: /admin');
    exit;
}

require '../../assets/php/fontawesome.php';
require '../../assets/php/db.php';


$result = mysqli_query($conn, "SELECT * FROM kamers");
$kamers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $kamers[] = $row;
}
;

$result = mysqli_query($conn, "SELECT * FROM afbeeldingen");
$afbeeldingen = [];
while ($row = mysqli_fetch_assoc($result)) {
    $afbeeldingen[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Logout system
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        header("Location: /admin");
    } else {
        // Change password system
        $oud_wachtwoord_input = $_POST['oud_wachtwoord'];
        $nieuw_wachtwoord_input = $_POST['nieuw_wachtwoord'];
        $herhaal_nieuw_wachtwoord_input = $_POST['herhaal_nieuw_wachtwoord'];

        if ($nieuw_wachtwoord_input !== $herhaal_nieuw_wachtwoord_input) {
            $error_message = ["red", "De nieuwe wachtwoorden komen niet overeen."];
        } else {
            $oud_wachtwoord_result = mysqli_query($conn, "SELECT wachtwoord FROM wachtwoord LIMIT 1");
            $oud_wachtwoord_row = mysqli_fetch_assoc($oud_wachtwoord_result);
            $oud_wachtwoord = $oud_wachtwoord_row ? $oud_wachtwoord_row['wachtwoord'] : '';

            if (password_verify($oud_wachtwoord_input, $oud_wachtwoord)) {
                $nieuw_wachtwoord_hash = password_hash($nieuw_wachtwoord_input, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE wachtwoord SET wachtwoord = '$nieuw_wachtwoord_hash' WHERE id = 1");
                $error_message = ["green", 'Wachtwoord succesvol gewijzigd.'];
            } else {
                $error_message = ["red", 'Oud wachtwoord is onjuist.'];
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - Hotel De Zonne Vallei</title>
    <link rel="icon" href="/assets/logos/favicon.ico">
    <link rel="stylesheet" href="/styling/global.css">
    <link rel="stylesheet" href="/styling/home.css">
    <link rel="stylesheet" href="/styling/panel.css">
    <script src="/assets/js/dropdown.js"></script>
</head>


<body>
    <aside>
        <a href="/"><i class="fas fa-home"></i> Home</a>
        <form class="logout" method="post">
            <button type="submit" name="logout"><i class="fas fa-arrow-left-from-bracket"></i> Uitloggen</button>
        </form>

        <main class="dropdown">
            <a href="#" onclick="toggleDropdown(event)">
                <i class="fas fa-key"></i> Wachtwoord wijzigen
            </a>

            <form id="dropdown-content" class="dropdown-content" method="post" enctype="multipart/form-data">
                <label for="oud_wachtwoord">Oud wachtwoord</label>
                <input type="password" name="oud_wachtwoord" required>

                <label for="nieuw_wachtwoord">Nieuw wachtwoord</label>
                <input type="password" name="nieuw_wachtwoord" required>

                <label for="herhaal_nieuw_wachtwoord">Herhaal nieuw wachtwoord</label>
                <input type="password" name="herhaal_nieuw_wachtwoord" required>

                <button type="submit">Wijzig</button>
            </form>
        </main>
        <p style="color: <?= $error_message[0] ?? '' ?>"><?= $error_message[1] ?? '' ?></p>
    </aside>
    <article>
        <section class="rooms-container">
            <a href="/admin/edit?kamer=<?= count($kamers) + 1 ?>" class="room-card">
                <article class="room-image" style="position:relative;">
                    <i class="fa-solid fa-plus"></i>
                </article>
                <article class="room-info">
                    <h2>Nieuw</h2>
                </article>
            </a>
            <?php
            foreach ($kamers as $kamer) {
                ?>
                <a href="/admin/edit?kamer=<?= $kamer['id'] ?>" class="room-card">
                    <?php
                    $kamerAfbeelding = null;
                    foreach ($afbeeldingen as $afbeelding) {
                        if ($afbeelding['kamer_id'] == $kamer['id']) {
                            $kamerAfbeelding = $afbeelding['link'];
                            break;
                        }
                    }
                    ?>
                    <?php if ($kamerAfbeelding): ?>
                        <article class="room-image" style="position:relative;">
                            <img src="<?= $kamerAfbeelding ?>" alt="<?= $kamer['naam'] ?>">
                            <p class="fas fa-pencil-alt" aria-hidden="true"></p>
                        </article>
                    <?php endif; ?>
                    <article class="room-info">
                        <h2><?= $kamer['naam'] ?></h2>
                        <span class="room-price">€<?= $kamer['prijs'] ?> / nacht</span>
                    </article>
                </a>
                <?php
            }
            ?>
        </section>
    </article>

</body>

</html>