<?php
require '../assets/php/db.php';

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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styling/global.css">
    <link rel="stylesheet" href="/styling/kamers.css">
    <title>Kamers - Hotel De Zonne Vallei</title>
</head>

<body>
    <?php include('../assets/html/navbar.html'); ?>
    <section class="hero-container">
        <main class="hero-content-wrapper">
            <h1>Ontdek Onze Kamers</h1>
             <!--tesk opmaak verbeteren-->
            <p>Welkom in het 3-duimen Hotel De Zonne Vallei, waar luxe en comfort hand in hand gaan. Gelegen in het hart van Alkmaar, bieden onze kamers een perfecte balans tussen modern design en gezelligheid. Of u nu voor een romantisch uitje, een familievakantie of een zakelijke bijeenkomst komt, ons hotel heeft precies wat u nodig heeft voor een onvergetelijk verblijf. Geniet van de rust en elegantie van onze kamers en ervaar de uitzonderlijke gastvrijheid die ons hotel kenmerkt.</p>
          
               <!--
               kamers types:
              1. Comfort Kamer
Onze Comfort Kamer biedt een serene ontsnapping met alle basisvoorzieningen die u nodig heeft. Geniet van een comfortabel bed, een moderne badkamer en een prachtig uitzicht op de stad. Perfect voor een kort verblijf of een zakenreis.

2. Deluxe Kamer
Voor degenen die net dat beetje extra comfort willen, is onze Deluxe Kamer de perfecte keuze. Deze kamers zijn ruimer en beschikken over luxere voorzieningen, zoals een zithoek en een Nespresso-apparaat. De ideale plek om te ontspannen na een dag vol ontdekkingen in Alkmaar.

3. Junior Suite
Onze Junior Suites bieden een ultieme combinatie van ruimte en luxe. Met een aparte woonkamer, een ruime badkamer en een balkon met een adembenemend uitzicht, is deze kamer perfect voor een romantisch uitje of een speciale gelegenheid.

4. Familie Suite
Speciaal ontworpen voor gezinnen, biedt onze Familie Suite voldoende ruimte en comfort voor iedereen. Deze suites beschikken over twee aparte slaapkamers, een ruime woonkamer en extra voorzieningen zoals een kitchenette en speelhoek voor de kinderen. De perfecte thuisbasis voor een onvergetelijke familievakantie.

5. Bruidssuite
Onze Bruidssuite is de ultieme romantische ontsnapping voor pasgetrouwde stellen. Deze luxueuze suite biedt een ruime slaapkamer met een kingsize bed, een stijlvolle woonkamer en een eigen balkon met een prachtig uitzicht op Alkmaar. Geniet van extra's zoals een bubbelbad, rozenblaadjes op het bed en een fles champagne om uw speciale gelegenheid te vieren in stijl.
-->
</main>
    </section>
    <main class="rooms-list">
        <section class="rooms-container">
            <?php
            foreach ($kamers as $kamer) {
                ?>
                <a href="/kamer?num=<?= $kamer['id'] ?>" class="room-card">
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
                        <img src="<?= $kamerAfbeelding ?>" alt="<?= $kamer['naam'] ?>">
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
    </main>
    <?php include('../assets/html/footer.html'); ?>
</body>

</html>
