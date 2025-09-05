<?php
session_start();

if (isset($_SESSION["admin_logged_in"]) && $_SESSION["admin_logged_in"] === true) {
} else {
    header('Location: /admin');
    exit;
}


require '../../assets/php/db.php';
require '../../assets/php/fontawesome.php';

function sanitize($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}


$current_room = $_GET['kamer'];
$kamers = [];
$is_new_room = false;

if (is_numeric($current_room)) {
    $result = mysqli_query($conn, "SELECT * FROM kamers WHERE id = $current_room");

    while ($row = mysqli_fetch_assoc($result)) {
        $kamers[] = $row;
    }

    // If no room found create a new one
    if (empty($kamers)) {
        $is_new_room = true;

        $kamers[] = [
            'id' => $current_room,
            'naam' => '',
            'prijs' => '',
            'beschrijving' => '',
            'beschikbaar' => ''
        ];
    }
}




// Update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Room info
    if (isset($_POST['naam']) && isset($_POST['prijs']) && isset($_POST['beschrijving']) && isset($_POST['beschikbaar'])) {
        $naam = sanitize($_POST['naam']);
        $prijs = sanitize($_POST['prijs']);
        $beschrijving = sanitize($_POST['beschrijving']);
        $beschikbaar = sanitize($_POST['beschikbaar']);

        // Database
        if ($is_new_room) {
            $command = "INSERT INTO kamers (id, naam, prijs, beschrijving, beschikbaar) VALUES ('$current_room', '$naam', '$prijs', '$beschrijving', '$beschikbaar')";
        } else {
            $command = "UPDATE kamers SET naam = '$naam', prijs = '$prijs', beschrijving = '$beschrijving', beschikbaar = '$beschikbaar' WHERE id = $current_room";
        }
        mysqli_query($conn, $command);
    }

    // Image deletion
    if (isset($_POST['delete_image'])) {
        $image_link = $_POST['image_link'];
        $image_id = $_POST['image_id'];

        // Delete from database
        $delete_query = "DELETE FROM afbeeldingen WHERE id = ? AND kamer_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "ii", $image_id, $current_room);
        mysqli_stmt_execute($stmt);

        // Delete file
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $image_link;
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Redirect to refresh 
        header("Location: " . $_SERVER['REQUEST_URI']);
    }



    // Image upload
    if (isset($_FILES["afbeelding"]) && $_FILES["afbeelding"]["error"] === UPLOAD_ERR_OK) {
        $target_dir = "../../assets/uploads/kamer_$current_room/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, recursive: true);
        }
        // Create file name with number in it
        $extension = pathinfo($_FILES["afbeelding"]["name"], PATHINFO_EXTENSION);
        $counter = 1;

        // Find next available number without file extension (.png, .jpeg, .jpg)
        while (glob($target_dir . "image_" . $counter . ".*")) {
            $counter++;
        }

        $target_file_name = "image_" . $counter . "." . $extension;
        $target_file = $target_dir . $target_file_name;
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["afbeelding"]["tmp_name"]);
        if ($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["afbeelding"]["size"] > 30000000) { // 30 MB
            echo "File is too big.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        ) {
            echo "Only JPG, JPEG, PNG files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "File was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES["afbeelding"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["afbeelding"]["name"])) . " has been uploaded.";
                $command = "INSERT INTO afbeeldingen (kamer_id, link) VALUES ($current_room, '/assets/uploads/kamer_$current_room/$target_file_name')";
                mysqli_query($conn, $command);
            } else {
                echo "Error uploading file.";
            }
        }
    }

    // Send back
    header("Location: " . $_SERVER['REQUEST_URI']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styling/global.css">
    <link rel="stylesheet" href="/styling/editKamer.css">
    <title><?= $is_new_room ? 'Nieuwe kamer ' : 'Bewerk kamer ' ?><?= htmlspecialchars($current_room) ?> - Hotel De
        Zonne Vallei</title>
    <script src="/assets/js/kamer_slideshow.js"></script>
    <link rel="icon" href="/assets/logos/favicon.ico">
</head>

<body>
    <?php if (!empty($kamers)): ?>
        <?php $kamer = $kamers[0]; ?>
        <section class="kamer-container">
            <h1 class="kamer-naam"><?= $is_new_room ? 'Nieuwe kamer toevoegen' : 'Kamer bewerken' ?></h1>
            <main class="kamer-content">
                <form method="post" enctype="multipart/form-data" class="kamer-info">
                    <label for="kamer-naam-input" class="kamer-label">Naam kamer</label>
                    <input type="text" id="kamer-naam-input" name="naam" class="kamer-input" value="<?= $kamer['naam'] ?>">

                    <label for="kamer-prijs-input" class="kamer-label">Prijs per nacht (€)</label>
                    <input type="number" step="0.01" id="kamer-prijs-input" name="prijs" class="kamer-input"
                        value="<?= $kamer['prijs'] ?>">

                    <label for="kamer-beschrijving-input" class="kamer-label">Beschrijving</label>
                    <textarea id="kamer-beschrijving-input" name="beschrijving" class="kamer-textarea"
                        rows="6"><?= $kamer['beschrijving'] ?></textarea>

                    <label for="kamer-beschikbaar-input" class="kamer-label">Kamers beschikbaar</label>
                    <input type="number" step="1" id="kamer-beschikbaar-input" name="beschikbaar" class="kamer-input"
                        value="<?= $kamer['beschikbaar'] ?>">

                    <label for="kamer-afbeelding-upload" class="kamer-label">Voeg nieuwe afbeelding toe</label>
                    <input type="file" id="kamer-afbeelding-upload" name="afbeelding" class="kamer-input" accept="image/*">
                    <article class="kamer-form-buttons">
                        <button type="submit" class="kamer-save-knop"><?= $is_new_room ? 'Toevoegen' : 'Opslaan' ?></button>
                        <a href="/admin" class="kamer-cancel-knop">Annuleren</a>
                    </article>
                </form>
                <?php if (!$is_new_room): ?>
                    <?php
                    $kamer_id = $kamer['id'];
                    $afbeeldingen = [];
                    $afbeeldingen_result = mysqli_query($conn, "SELECT link FROM afbeeldingen WHERE kamer_id = $kamer_id");
                    while ($afbeelding_row = mysqli_fetch_assoc($afbeeldingen_result)) {
                        $afbeeldingen[] = $afbeelding_row['link'];
                    }
                    ?>
                    <main class="rechts">
                        <article class="kamer-afbeeldingen-slideshow">
                            <?php if (!empty($afbeeldingen)): ?>
                                <?php if (count($afbeeldingen) > 1): ?>
                                    <button type="button" class="slideshow-arrow left" onclick="plusSlides(-1)">&#10094;</button>
                                <?php endif; ?>
                                <article class="slideshow-images">
                                    <?php foreach ($afbeeldingen as $index => $link): ?>
                                        <article class="slide-container" style="<?= $index === 0 ? '' : 'display:none;' ?>">
                                            <img class="kamer-afbeelding slideshow-slide" src="<?= $link ?>"
                                                alt="Afbeelding van <?= $kamer['naam'] ?>">
                                            <form method="post">
                                                <input type="hidden" name="delete_image" value="1">
                                                <input type="hidden" name="image_link" value="<?= $link ?>">
                                                <input type="hidden" name="image_id" value="<?php
                                                $img_result = mysqli_query($conn, "SELECT id FROM afbeeldingen WHERE link = '" . mysqli_real_escape_string($conn, $link) . "' AND kamer_id = $kamer_id LIMIT 1");
                                                echo mysqli_fetch_assoc($img_result)['id'];
                                                ?>">
                                                <button type="submit" class="kamer-afbeelding-verwijder fa-solid fa-trash"></button>
                                            </form>
                                        </article>
                                    <?php endforeach; ?>
                                </article>
                                <?php if (count($afbeeldingen) > 1): ?>
                                    <button type="button" class="slideshow-arrow right" onclick="plusSlides(1)">&#10095;</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="kamer-afbeelding">Geen afbeeldingen beschikbaar.</p>
                            <?php endif; ?>
                        </article>
                    </main>
                <?php else: ?>
                    <main class="rechts">
                        <p class="kamer-afbeelding">Afbeeldingen worden beschikbaar na het opslaan van de kamer.</p>
                    </main>
                <?php endif; ?>
            </main>
        </section>
    <?php endif; ?>
</body>

</html>