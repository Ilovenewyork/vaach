<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Language Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.0/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .phrasecard {
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1); /* Reduced shadow intensity */
            border: 1px solid #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-100">

    <?php
    // Import database connection and header files
    include "conn.php";
    include "header.php";
    include "navbar.php";
    ?>

    <div class="container mx-auto p-4">
        <?php
        // Get language ID from URL or redirect to index.php
        $lang_id = $_GET["id"] ?? null;
        if (!$lang_id) {
            header("Location: index.php");
            exit;
        }

        // Fetch language information
        $lang_info_query = "SELECT * FROM languages WHERE lang_id = $lang_id AND approved = 1";
        $lang_info_result = mysqli_query($conn, $lang_info_query);
        $lang_info = mysqli_fetch_assoc($lang_info_result);

        if (!$lang_info) {
            header("Location: index.php");
        }

        // Fetch phrases for the language
        $phrases_query = "SELECT * FROM phrases WHERE lang_id = $lang_id and approved = 1";
        $phrases_result = mysqli_query($conn, $phrases_query);

        // Display language information
        echo '<div class="bg-white shadow-md rounded-lg p-6 mb-6">'; /* Reduced shadow intensity */
        echo '<h2 class="text-2xl font-bold mb-2">' . htmlspecialchars($lang_info['lang_name']) . '</h2>';

        if (strlen($lang_info["lang_place"]) == 0) {
            echo '<p class="bg-gray-200 p-2 rounded inline-block text-gray-700">Language not tied to geographic location</p>';
        } else {
            echo '<p class="bg-gray-200 p-2 rounded inline-block text-gray-700">' . htmlspecialchars($lang_info['lang_place']) . '</p>';
        }

        echo '<p class="mt-2 text-sm">Language vitality: <span class="font-semibold">' . str_replace("_", " ", ucfirst(htmlspecialchars($lang_info['lang_vitality']))) . '</span></p>';

        if (isset($_SESSION["user_id"])) {
            echo '<a href="phrase_request.php?id=' . $lang_id . '" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Suggest a Phrase</a>';
        } else {
            echo '<div class="bg-gray-100 text-gray-700 p-4 rounded mt-4">Log in to suggest or edit phrases</div>';
        }

        if (isset($_SESSION["tags"])) {
            if (in_array($lang_id . '_a', $_SESSION["tags"]) || in_array($lang_id . '_sa', $_SESSION["tags"])) {
                echo '<br><a href="language_admin.php?id=' . $lang_id . '" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 mt-4 inline-block">Admin Panel</a>';
            }
        }

        echo '<div class="mt-4">';
        echo '<label for="phraseSearch" class="block text-lg font-semibold mb-2">Search for Phrases:</label>';
        echo '<input type="text" id="phraseSearch" class="border rounded-lg p-2 w-full" oninput="phraseSearch()">';
        echo '</div>';
        echo '</div>';

        // Check if language information and phrases found
        if (!$phrases_result) {
            echo '<div class="bg-red-100 text-red-700 p-4 rounded mt-4">No phrases found.</div>';
        } else {
            // Display phrases using Tailwind grid layout
            echo '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">';
            while ($row = mysqli_fetch_assoc($phrases_result)) {
                echo '<div class="bg-white shadow-md rounded-lg p-4 border border-gray-200 phrasecard flex flex-col h-full">'; /* Reduced shadow intensity and flex layout */
                echo '<h5 class="text-lg font-semibold mb-2">' . htmlspecialchars($row['phrase']) . '</h5>';
                echo '<small class="text-gray-500 mb-1">' . htmlspecialchars($row['romanization']) . '</small>';

                if ($row["speech_part"] != "") {
                    echo '<div class="mb-2">';
                    echo '<span class="text-gray-700 font-medium">Part of Speech:</span> ';
                    echo '<span class="text-gray-500">' . htmlspecialchars($row["speech_part"]) . '</span>';
                    echo '</div>';
                }

                echo '<p class="text-gray-700 mb-2"><span class="font-medium">Translation:</span> ' . htmlspecialchars($row['translation']) . '</p>';
                echo '<p class="text-gray-500 mb-2"><span class="font-medium">Phonetic:</span> ' . htmlspecialchars($row['phonetic']) . '</p>';
                if ($row["ipa"] != "") {
                    echo '<p class="text-gray-500 mb-2"><span class="font-medium">IPA:</span> ' . htmlspecialchars($row['ipa']) . '</p>';
                }

                if (isset($_SESSION["user_id"])) {
                    echo '<div class="mt-auto text-right text-sm">';
                    echo '<a href="phrase_edit.php?lang_id=' . $lang_id . '&phrase_id=' . $row["phrase_id"] . '" class="text-blue-600 hover:underline">&#9998; Edit</a>';
                    echo '</div>';
                }

                echo '</div>';
            }
            echo '</div></div>';
        }

        // Include footer, assuming it's in footer.php
        include "footer.php";

        // Close database connection
        mysqli_close($conn);
        ?>

        <script>
        function phraseSearch() {
            const searchbar = document.getElementById("phraseSearch");
            const lookstr = searchbar.value.toUpperCase();
            const phrasecards = document.querySelectorAll(".phrasecard");
            let phrasesDisplayed = false;

            phrasecards.forEach(phrasecard => {
                const phrasename = phrasecard.querySelector('h5').innerText.toUpperCase();
                if (!phrasename.includes(lookstr)) {
                    phrasecard.style.display = "none";
                } else {
                    phrasecard.style.display = "block";
                    phrasesDisplayed = true;
                }
            });

            const disclaimer = document.getElementById("disclaimer");
            disclaimer.innerHTML = phrasesDisplayed
                ? ""
                : `No phrases fit the keyword '${lookstr}'`;
        }
        </script>
    </div>
</body>
</html>
