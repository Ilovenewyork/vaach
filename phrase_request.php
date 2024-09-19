<?php

include "conn.php";
include "header.php";
include "navbar.php";

// Get language ID from URL or redirect to index.php
$lang_id = $_GET["id"] ?? null;
if (!$lang_id or !isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

// Fetch language information
$lang_info_query = "SELECT * FROM languages WHERE lang_id = $lang_id";
$lang_info_result = mysqli_query($conn, $lang_info_query);
$lang_info = mysqli_fetch_assoc($lang_info_result);

if (!$lang_info) {
    echo '<div class="alert alert-danger">Invalid language ID.</div>';
    exit;
}

// $adtag = $lang_id."_a";
// $sadtag = $lang_id."_sa";

// $query = "SELECT COUNT(*) AS count FROM users WHERE tags LIKE '%,$adtag,%' OR tags LIKE '%,$sadtag,%'";
// $result = mysqli_query($conn, $query);

// $row = mysqli_fetch_assoc($result);
// $count = $row['count'];


// Handle form submission
if (isset($_POST['submit']) && isset($_SESSION["user_id"])) {
    $phrase = $_POST['phrase'];
    $romanization = $_POST["romanization"];
    $speech_part = $_POST["speech_part"];

    if($speech_part == "other"){
        $speech_part = strtolower($_POST["other_speech_part"]);
    }
    
    $translation = $_POST['translation'];
    $phonetic = $_POST['phonetic'];
    $ipa = $_POST["ipa"];
    $user_id = $_SESSION["user_id"];

    // Validate and sanitize input data here
    $insert_query = "INSERT INTO phrases (lang_id, phrase, romanization, speech_part, translation, phonetic, ipa, user_id) VALUES ($lang_id, '$phrase', '$romanization', '$speech_part', '$translation', '$phonetic', '$ipa', $user_id)";
    mysqli_query($conn, $insert_query);

    echo '<div class="alert alert-success">Phrase suggested successfully!</div>';
}

?>

<div class="container mt-5">
    <h2>Suggest a Phrase for <?php echo $lang_info['lang_name']; ?></h2>
    <a href = "language.php?id=<?php echo $lang_id; ?>">Back to Language Page</a>
    <form method="POST">

        <div class="mb-3">
            <label for="phrase" class="form-label">Phrase in <?php echo $lang_info['lang_name']; ?></label>
            <input type="text" class="form-control" id="phrase" name="phrase" required>
        </div>

        <div class="mb-3">
            <label for="romanization" class="form-label">Romanization (Optional)</label>
            <input type="text" class="form-control" id="romanization" name="romanization">
        </div>

        <div class = "form-check">
            <p class = "form-label">Part of Speech</p> 
            <input type = "radio" id  = "noun" name = "speech_part" value = "noun" onclick = "toggle_field()">
            <label class = "form-check-label" for = "noun">Noun</label>
        </div>
        <div class = "form-check">
            <input type = "radio" id  = "adjective" name = "speech_part" value = "adjective" onclick = "toggle_field()">
            <label class = "form-check-label" for = "adjective">Adjective</label>
        </div>
        <div class = "form-check">
            <input type = "radio" id  = "verb" name = "speech_part" value = "verb" onclick = "toggle_field()">
            <label class = "form-check-label" for = "verb">Verb</label>
        </div>
        <div class = "form-check">
            <input type = "radio" id  = "other" name = "speech_part" value = "other" onclick = "toggle_field()">
            <label class = "form-check-label" for = "other">Other</label>

        </div>

        <div class = "mb-3" id = "othercont">
            <label for = "other" class = "form-label">Other Part of Speech: </label>
            <input type = "text" class = "form-control" id = "other_speech_part" name = "other_speech_part">
        </div>

        <div class="mb-3">
            <label for="translation" class="form-label">Translation in English</label>
            <input type="text" class="form-control" id="translation" name="translation" required>
        </div>

        <div class="mb-3">
            <label for="phonetic" class="form-label">Phonetic Pronunciation (optional)</label>
            <input type="text" class="form-control" id="phonetic" name="phonetic">
        </div>

        <div class="mb-3">
            <label for="ipa" class="form-label">IPA Pronunciation (Optional)</label>
            <input type="text" class="form-control" id="ipa" name="ipa">
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Suggest Phrase</button>
    </form>
</div>

<script>
    var otherfield = document.getElementById("othercont")
    function toggle_field(){
        if(document.getElementById("other").checked){
            otherfield.style.display = "block"
        }
        else{
            otherfield.style.display = "none"
        }
    }
    toggle_field()
</script>
