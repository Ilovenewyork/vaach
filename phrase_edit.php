
<?php


include "conn.php";
include "header.php";
include "navbar.php";

if (!isset($_SESSION["user_id"]) or !isset($_SESSION["tags"]) or !isset($_GET["phrase_id"]) or !isset($_GET["lang_id"])){
    header("Location: index.php");
    // exit;
}

$phrase_id = (int)$_GET["phrase_id"];
$lang_id = (int)$_GET["lang_id"];

$is_admin = false;

if(in_array($lang_id."_a", $_SESSION["tags"]) or in_array($lang_id."_sa", $_SESSION["tags"])){
    $is_admin = true;
}
if(isset($_POST["phrase"]) and isset($_POST["translation"]) and isset($_POST["phonetic"])){
    $speech_part = $_POST["speech_part"];
    if($speech_part == "other"){
        $speech_part = $_POST["other_speech_part"];
    }
    $update_stmt = "INSERT INTO phrases (lang_id, phrase, romanization, speech_part, translation, phonetic, ipa, user_id, approved, replacing) VALUES ($lang_id, '".$_POST["phrase"]."', '".$_POST["romanization"]."', '".$speech_part."', '".$_POST["translation"]."','".$_POST["phonetic"]."', '".$_POST["ipa"]."', ".$_SESSION["user_id"].", ".(int)$is_admin.", ".$_GET["phrase_id"].")";
    //echo $update_stmt;
    mysqli_query($conn, $update_stmt);
    echo "INSERT INTO phrases (lang_id, phrase, translation, phonetic, user_id, approved, replacing) VALUES ($lang_id, '".$_POST["phrase"]."', '".$_POST["translation"]."',' ".$_POST["phonetic"]."', ".$_SESSION["user_id"].", ".(int)$is_admin.", ".$_GET["phrase_id"].")";
    if(true){
        if($is_admin){
            $expiration_query = $conn->prepare("DELETE FROM phrases WHERE phrase_id = ?");
            $expiration_query->bind_param("i", $phrase_id);
            $expiration_query->execute();
        }
         header("Location: language.php?id=".$lang_id);
    }
}
$stmt = $conn->prepare("SELECT * FROM phrases WHERE lang_id = ? AND phrase_id = ?");
$stmt->bind_param("ii", $lang_id, $phrase_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row and $row["approved"]==1) {
  $phrase = $row["phrase"];
  $romanization = $row["romanization"];
  $speech_part = $row["speech_part"];
  $translation = $row["translation"];
  $phonetic = $row["phonetic"];
  $ipa = $row["ipa"];
  $user_id = $row["user_id"];
}
else {
    header("Location: index.php");
    exit;
}

echo '<div class="container mt-5">';
echo '<a href = "language.php?id='.$lang_id.'">Back to Language Page</a>';
if($is_admin){
    echo "<h2> Edit a Phrase </h2><br>";
}
else{
    echo "<h2> Request an Edit to a Phrase </h2>";
}

?>
<h3>Phrase:</h3>
<div class="card mb-3"><div class="card-body">
        <h5 class="card-title"><?php echo $phrase; ?></h5>
        <h7 class="text-muted"><?php echo $romanization; ?></h7>
        <small class="text-muted"><?php echo $speech_part; ?></small>
        <p class="card-text"><?php echo $translation; ?></p>
        <small class="text-muted"><?php echo $phonetic; ?></small>
        <small class="text-muted"><?php echo $ipa; ?></small>
    </div>
    </div>

<br>

<h3>Suggested Edit:</h3>
    
    <form method="POST">
        <div class="mb-3">
            <label for="phrase" class="form-label">Phrase</label>
            <input type="text" class="form-control" id="phrase" name="phrase" required value = "<?php echo $phrase; ?>">
        </div>
        <div class="mb-3">
            <label for="romanization" class="form-label">Romanization</label>
            <input type="text" class="form-control" id="romanization" name="romanization" value = "<?php echo $romanization; ?>">
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
            <input type="text" class="form-control" id="translation" name="translation" required  value = "<?php echo $translation; ?>">
        </div>
        <div class="mb-3">
            <label for="phonetic" class="form-label">Phonetic Pronunciation (optional)</label>
            <input type="text" class="form-control" id="phonetic" name="phonetic"  value = "<?php echo $phonetic; ?>">
        </div>
        <div class="mb-3">
            <label for="ipa" class="form-label">IPA Pronunciation (optional)</label>
            <input type="text" class="form-control" id="ipa" name="ipa"  value = "<?php echo $ipa; ?>">
        </div>

        <?php
        if($is_admin){
            echo '<button type="submit" name="submit" class="btn btn-primary">Edit Phrase</button>';
        }
        else{
            echo '<button type="submit" name="submit" class="btn btn-primary">Suggest Edit</button>';
        }
        ?>

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