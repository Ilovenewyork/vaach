<?php
include "../conn.php";

header('Content-Type: application/json');

$response = array();

if (isset($_GET['lang_name'])) {
    $lang_name = mysqli_real_escape_string($conn, $_GET['lang_name']);
    $lang_name = strtolower($lang_name);

    // Query to check if the language exists (case-insensitive)
    $lang_query = "SELECT lang_id FROM languages WHERE LOWER(lang_name) = ?";
    $lang_stmt = $conn->prepare($lang_query);
    if ($lang_stmt === false) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to prepare language query: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    $lang_stmt->bind_param("s", $lang_name);
    $lang_stmt->execute();
    $lang_result = $lang_stmt->get_result();

    if ($lang_result->num_rows > 0) {
        $lang_row = $lang_result->fetch_assoc();
        $lang_id = $lang_row['lang_id'];

        // Language found, get its phrases
        $phrase_query = "SELECT * FROM phrases WHERE lang_id = ?";
        $phrase_stmt = $conn->prepare($phrase_query);
        if ($phrase_stmt === false) {
            $response['status'] = 'error';
            $response['message'] = 'Failed to prepare phrase query: ' . $conn->error;
            echo json_encode($response);
            exit;
        }
        $phrase_stmt->bind_param("i", $lang_id);
        $phrase_stmt->execute();
        $phrase_result = $phrase_stmt->get_result();

        $phrases = array();
        while ($row = $phrase_result->fetch_assoc()) {
            $phrases[] = $row;
        }

        // Close statement
        $phrase_stmt->close();

        $response['status'] = 'success';
        $response['data'] = $phrases;
    } else {
        // Language not found, return error
        $response['status'] = 'error';
        $response['message'] = 'Language not found';
        $lang_stmt->close();
        mysqli_close($conn);
        echo json_encode($response);
        exit;
    }

    // Close language statement
    $lang_stmt->close();
} else {
    // No language specified, get all phrases
    $phrase_query = "SELECT * FROM phrases";
    $phrase_result = mysqli_query($conn, $phrase_query);

    if ($phrase_result === false) {
        $response['status'] = 'error';
        $response['message'] = 'Failed to execute phrase query: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    $phrases = array();
    while ($row = mysqli_fetch_assoc($phrase_result)) {
        $phrases[] = $row;
    }

    $response['status'] = 'success';
    $response['data'] = $phrases;
}

// Close database connection
mysqli_close($conn);

// Output the JSON data
echo json_encode($response);
?>