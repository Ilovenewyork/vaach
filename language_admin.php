<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<?php
include "header.php";
include "navbar.php";
include "conn.php";

// Get language ID from URL or redirect to index.php
$lang_id = $_GET["id"] ?? null;
if (!$lang_id) {
    header("Location: index.php?langid=no");
    exit;
}

// Fetch language information
$lang_info_query = "SELECT * FROM languages WHERE lang_id = $lang_id";
$lang_info_result = mysqli_query($conn, $lang_info_query);
$lang_info = mysqli_fetch_assoc($lang_info_result);

// Handle phrase approval or denial actions

// Handle bulk actions
if (isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $section = $_POST['section'];
    
    if ($section === 'changes') {
        $query = "SELECT * FROM phrases WHERE lang_id = $lang_id AND approved = 0 AND replacing != -1";
    } else {
        $query = "SELECT * FROM phrases WHERE lang_id = $lang_id AND approved = 0 AND replacing = -1";
    }
    
    $result = mysqli_query($conn, $query);
    
    while ($row = mysqli_fetch_assoc($result)) {
        // print_r($row);
        $phrase_id = $row['phrase_id'];
        $phrase_user_id = $row['user_id'];
        $phrase_text = $row['phrase'];
        
        if ($action === 'approve') {
            if ($section === 'changes') {
                $old_phrase_id = $row['replacing'];
                $update_query = $conn->prepare("UPDATE phrases SET approved = 1 WHERE phrase_id = ?");
                $update_query->bind_param("i", $phrase_id);
                $update_query->execute();
                addNotif($phrase_user_id, "Your edit to phrase '$phrase_text' was accepted.", "success");
                $expiration_query = "DELETE FROM phrases WHERE phrase_id = $old_phrase_id";
                mysqli_query($conn, $expiration_query);
            } else {
                $update_query = $conn->prepare("UPDATE phrases SET approved = 1 WHERE phrase_id = ?");
                $update_query->bind_param("i", $phrase_id);
                $update_query->execute();
                addNotif($phrase_user_id, "Your submission of phrase '$phrase_text' was accepted.", "success");

                // Check for admin promotion

                if ($phrase_user_id){
                // echo "Phrase User ID: " . htmlspecialchars($phrase_user_id) . "<br>";


                $is_admin_query = "SELECT tags FROM users WHERE user_id = $phrase_user_id";
                $is_admin_query_result = mysqli_query($conn, $is_admin_query);
                
                if (!$is_admin_query_result) {
                    die("Query failed: " . mysqli_error($conn));
                }
                
                $is_admin_tags = explode(",", mysqli_fetch_assoc($is_admin_query_result)["tags"]);
                

                $admin_tag = $lang_id."_a,";
                if (!in_array($admin_tag, $is_admin_tags) && !in_array($lang_id."_sa,", $is_admin_tags)) {
                    $approved_phrases_query = "SELECT COUNT(*) AS count FROM phrases WHERE user_id = $phrase_user_id AND lang_id = $lang_id AND approved = 1";
                    $approved_phrases_result = mysqli_query($conn, $approved_phrases_query);
                    $approved_phrases_row = mysqli_fetch_assoc($approved_phrases_result);
                    $approved_phrases_count = $approved_phrases_row['count'];

                    if ($approved_phrases_count >= 5) {
                        $add_admin_sql = "UPDATE users SET tags = CONCAT(tags, '$admin_tag') WHERE user_id = $phrase_user_id";
                        mysqli_query($conn, $add_admin_sql);
                        addNotif($phrase_user_id, "Congratulations! You are now an admin of language ".$lang_info["lang_name"], "success");
                    }
                }
                }
            }
        } else if ($action === 'deny') {
            $update_query = "DELETE FROM phrases WHERE phrase_id = $phrase_id";
            mysqli_query($conn, $update_query);
            addNotif($phrase_user_id, "Your " . ($section === 'changes' ? "edit to" : "submission of") . " phrase '$phrase_text' was denied.", "failure");
        }
    }
    
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">Bulk action completed successfully!</span>
          </div>';
}


if (isset($_GET['action'])) {
    $phrase_id = $_GET['phrase_id'];
    $stmt = $conn->prepare("SELECT user_id, phrase FROM phrases WHERE phrase_id = ?");
    $stmt->bind_param("i", $phrase_id);
    $stmt->execute();
    $phrase_user_id_result = $stmt->get_result();
    $stmt->close();

    $phrase_user_id_row = mysqli_fetch_assoc($phrase_user_id_result);
    $phrase_user_id = $phrase_user_id_row['user_id'];
    $phrase_text = $phrase_user_id_row["phrase"];

    if ($_GET['action'] == 'approvechange') {
        $update_query = $conn->prepare("UPDATE phrases SET approved = 1 WHERE phrase_id = ?");
        $update_query->bind_param("i", $phrase_id);
        $update_query->execute();
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Change approved successfully!</span>
              </div>';
        addNotif($phrase_user_id, "Your edit to phrase '$phrase_text' was accepted.", "success");
        $expired_phrase_id = $_GET["oldphrase"];
        $expiration_query = "DELETE FROM phrases WHERE phrase_id = $expired_phrase_id";
        mysqli_query($conn, $expiration_query);
    }
    if ($_GET["action"] == 'denychange') {
        $update_query = "DELETE FROM phrases WHERE phrase_id = $phrase_id";
        mysqli_query($conn, $update_query);
        addNotif($phrase_user_id, "Your edit to phrase '$phrase_text' was denied.", "failure");
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">Change denied successfully!</span>
              </div>';
    }
    if ($_GET['action'] == 'approve') {
        $update_query = $conn->prepare("UPDATE phrases SET approved = 1 WHERE phrase_id = ?");
        $update_query->bind_param("i", $phrase_id);
        $update_query->execute();
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">Phrase approved successfully!</span>
              </div>';
        addNotif($phrase_user_id, "Your submission of phrase '$phrase_text' was accepted.", "success");

        // Check if user is an admin
        $is_admin_query = "SELECT tags FROM users WHERE user_id = $phrase_user_id";
        $is_admin_query_result = mysqli_query($conn, $is_admin_query);
        $is_admin_tags = explode(",", mysqli_fetch_assoc($is_admin_query_result)["tags"]);

        $admin_tag = $lang_id."_a,";
        if (!in_array($admin_tag, $is_admin_tags) && !in_array($lang_id."_sa,", $is_admin_tags)) {
            // Adding admin permission to users with more than 5 approved posts
            $approved_phrases_query = "SELECT COUNT(*) AS count FROM phrases WHERE user_id = $phrase_user_id AND lang_id = $lang_id AND approved = 1";
            $approved_phrases_result = mysqli_query($conn, $approved_phrases_query);
            $approved_phrases_row = mysqli_fetch_assoc($approved_phrases_result);
            $approved_phrases_count = $approved_phrases_row['count'];

            if ($approved_phrases_count >= 5) {
                $add_admin_sql = "UPDATE users SET tags = CONCAT(tags, '$admin_tag') WHERE user_id = $phrase_user_id";
                mysqli_query($conn, $add_admin_sql);
                addNotif($phrase_user_id, "Congratulations! You are now an admin of language ".$lang_info["lang_name"], "success");
            }
        }
    } elseif ($_GET['action'] == 'deny') {
        $update_query = "DELETE FROM phrases WHERE phrase_id = $phrase_id";
        mysqli_query($conn, $update_query);
        addNotif($phrase_user_id, "Your submission of phrase '$phrase_text' was denied.", "failure");
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">Phrase denied successfully!</span>
              </div>';
    }
}

// Add this after the existing approval handling code

// Handle CSV upload
if (isset($_FILES['phrase_csv']) && isset($_POST['upload_csv'])) {
    $file = $_FILES['phrase_csv'];
    if ($file['error'] == 0 && ($file['type'] == 'text/csv' || $file['type'] == 'application/vnd.ms-excel')) {
        $handle = fopen($file['tmp_name'], "r");
        
        // Get headers and normalize them
        $headers = fgetcsv($handle);
        $normalized_headers = array();
        $valid_fields = array('phrase', 'romanization', 'translation', 'speech_part', 'phonetic', 'ipa');
        
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            foreach ($valid_fields as $field) {
                if (strpos($header, $field) !== false) {
                    $normalized_headers[$index] = $field;
                    break;
                }
            }
        }
        
        // Process each row
        $success_count = 0;
        $error_count = 0;
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $phrase_data = array_fill_keys($valid_fields, ''); // Initialize with empty values
            
            // Map CSV data to fields
            foreach ($normalized_headers as $index => $field) {
                if (isset($data[$index])) {
                    $phrase_data[$field] = $data[$index];
                }
            }
            
            // Skip if required fields are empty
            if (empty($phrase_data['phrase']) || empty($phrase_data['translation'])) {
                $error_count++;
                continue;
            }
            
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO phrases (lang_id, phrase, romanization, translation, speech_part, phonetic, ipa, approved, replacing) VALUES (?, ?, ?, ?, ?, ?, ?, 0, -1)");
            $stmt->bind_param("issssss", 
                $lang_id,
                $phrase_data['phrase'],
                $phrase_data['romanization'],
                $phrase_data['translation'],
                $phrase_data['speech_part'],
                $phrase_data['phonetic'],
                $phrase_data['ipa']
            );
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
            $stmt->close();
        }
        fclose($handle);
        
        echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                <strong class='font-bold'>Upload Complete!</strong>
                <span class='block sm:inline'>Successfully added $success_count phrases. Failed to add $error_count phrases.</span>
              </div>";
    } else {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                <strong class='font-bold'>Error!</strong>
                <span class='block sm:inline'>Please upload a valid CSV file.</span>
              </div>";
    }
}

$username = $_SESSION['username'];
$tags = $_SESSION['tags'];
if (!in_array($lang_id . '_a', $tags) && !in_array($lang_id . '_sa', $tags)) {
    header("Location: language.php?id=$lang_id");  // Redirect non-admins to language page
    exit;
}

// Fetch phrases for approval
$pending_phrases_query = "SELECT * FROM phrases WHERE lang_id = $lang_id AND approved = 0 AND replacing = -1";
$pending_phrases_result = mysqli_query($conn, $pending_phrases_query);

$changing_phrases_query = "SELECT * FROM phrases WHERE lang_id = $lang_id AND approved = 0 AND replacing != -1";
$changing_phrases_result = mysqli_query($conn, $changing_phrases_query);
?>

<div class="min-h-screen flex flex-col">
    <main class="flex-grow p-6">
        <div class="container mx-auto bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Admin Panel: <?php echo htmlspecialchars($lang_info['lang_name']); ?></h2>
            
            <p class="text-gray-700 mb-4">
                <?php echo strlen($lang_info["lang_place"]) == 0 ? "Language not tied to geographic location" : htmlspecialchars($lang_info['lang_place']); ?>
            </p>
            <p class="text-gray-700 mb-4">
                Language vitality: <?php echo str_replace("_", " ", ucfirst($lang_info['lang_vitality'])); ?>
            </p>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="phrase_request.php?id=<?php echo htmlspecialchars($lang_id); ?>" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Suggest a Phrase</a>
            <?php else: ?>
                <div class="bg-gray-100 border border-gray-300 text-gray-700 px-4 py-3 rounded mb-4" role="alert">
                    <strong class="font-bold">Notice</strong>
                    <span class="block sm:inline">Log in to suggest or edit phrases</span>
                </div>
            <?php endif; ?>
            <a href="language.php?id=<?php echo htmlspecialchars($lang_id); ?>" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Back To Language Page</a>
        

                    <!-- Add this HTML after the "Suggest a Phrase" button -->
<div class="mt-4">
    <div class="flex items-center space-x-2">
        <form action="" method="post" enctype="multipart/form-data" class="inline-flex items-center space-x-2">
            <button type="submit" name="upload_csv" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                Upload CSV
            </button>
            <input type="file" name="phrase_csv" accept=".csv" class="block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-full file:border-0
                file:text-sm file:font-semibold
                file:bg-blue-50 file:text-blue-700
                hover:file:bg-blue-100
            ">
            <div class="relative inline-block">
                <span class="text-gray-500 cursor-help text-xl" 
                      onmouseover="document.getElementById('csv-help').classList.remove('hidden')"
                      onmouseout="document.getElementById('csv-help').classList.add('hidden')">
                    ?
                </span>
                <div id="csv-help" class="hidden absolute z-10 w-64 px-4 py-2 bg-gray-900 text-white text-sm rounded-lg shadow-lg" style="bottom: 100%; left: 50%; transform: translateX(-50%);">
                    CSV Format Guidelines:
                    <ul class="list-disc pl-4 mt-1">
                        <li>Accepts columns containing these keywords: phrase, romanization, translation, part of speech, phonetic, ipa</li>
                        <li>Column names are case-insensitive</li>
                        <li>Matches partial names (e.g., "Phrase (Angika)" matches "phrase")</li>
                        <li>Only phrase and translation are required</li>
                        <li>Extra columns are ignored</li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>
        
        </div>



        <div class="container mx-auto bg-white p-6 rounded-lg shadow-md mt-6"> 
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Phrase Changes:</h3>

            <div class="mb-4 flex space-x-2">
                <form method="post" class="inline">
                    <input type="hidden" name="section" value="changes">
                    <button type="submit" name="bulk_action" value="approve" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                        Approve All Changes
                    </button>
                </form>
                <form method="post" class="inline">
                    <input type="hidden" name="section" value="changes">
                    <button type="submit" name="bulk_action" value="deny" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        Deny All Changes
                    </button>
                </form>
            </div>

            <?php if (mysqli_num_rows($changing_phrases_result) > 0): ?>
                <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Phrase</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Romanization</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Part of Speech</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Translation</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Phonetic</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">IPA</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($changing_phrases_result)): ?>
                            <?php
                            $old_phrase_id = $row["replacing"];
                            $old_phrase_query = "SELECT * FROM phrases WHERE phrase_id = $old_phrase_id";
                            $old_phrase_result = mysqli_query($conn, $old_phrase_query);
                            ?>
                            <?php while ($oldrow = mysqli_fetch_assoc($old_phrase_result)): ?>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($oldrow['phrase']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($oldrow['romanization']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($oldrow['speech_part']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($oldrow['translation']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($oldrow['phonetic']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($oldrow['ipa']); ?></td>
                                    <td class="py-2 px-4 border-b"></td>
                                </tr>
                                <tr>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['phrase']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['romanization']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['speech_part']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['translation']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['phonetic']); ?></td>
                                    <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['ipa']); ?></td>
                                    <td class="py-2 px-4 border-b">
                                        <a href="language_admin.php?id=<?php echo htmlspecialchars($lang_id); ?>&action=approvechange&phrase_id=<?php echo htmlspecialchars($row['phrase_id']); ?>&oldphrase=<?php echo htmlspecialchars($old_phrase_id); ?>" class="bg-green-500 text-white py-1 px-2 rounded hover:bg-green-600">Approve</a>
                                        <br><br>
                                        <a href="language_admin.php?id=<?php echo htmlspecialchars($lang_id); ?>&action=denychange&phrase_id=<?php echo htmlspecialchars($row['phrase_id']); ?>" class="bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600">Deny</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-700">No changes to review.</p>
            <?php endif; ?>

            <h3 class="text-2xl font-semibold text-gray-800 mt-6 mb-4">Phrase Additions:</h3>

            <div class="mb-4 flex space-x-2">
                <form method="post" class="inline">
                    <input type="hidden" name="section" value="additions">
                    <button type="submit" name="bulk_action" value="approve" class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                        Approve All Additions
                    </button>
                </form>
                <form method="post" class="inline">
                    <input type="hidden" name="section" value="additions">
                    <button type="submit" name="bulk_action" value="deny" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">
                        Deny All Additions
                    </button>
                </form>
            </div>

            <?php if (mysqli_num_rows($pending_phrases_result) > 0): ?>
                <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Phrase</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Romanization</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Part of Speech</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Translation</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Phonetic</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">IPA</th>
                            <th class="py-2 px-4 border-b text-left text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($pending_phrases_result)): ?>
                            <tr>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['phrase']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['romanization']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['speech_part']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['translation']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['phonetic']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($row['ipa']); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <a href="language_admin.php?id=<?php echo htmlspecialchars($lang_id); ?>&action=approve&phrase_id=<?php echo htmlspecialchars($row['phrase_id']); ?>" class="bg-green-500 text-white py-1 px-2 rounded hover:bg-green-600">Approve</a>
                                    <a href="language_admin.php?id=<?php echo htmlspecialchars($lang_id); ?>&action=deny&phrase_id=<?php echo htmlspecialchars($row['phrase_id']); ?>" class="bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600">Deny</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-gray-700">No new phrases to review.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include "footer.php"; ?>
<?php mysqli_close($conn); ?>

</body>
</html>