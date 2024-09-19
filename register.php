<?php

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conn.php";
include "header.php";

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $tags = ",";

    // Check for existing username
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $error = "Username already exists.";
    } else {
        // Secure password hashing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepared statement for secure registration
        $stmt = $conn->prepare("INSERT INTO users (username, passcode, email, tags) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $tags);
        $stmt->execute();

        $u_id_stmt = $conn->prepare("SELECT user_id, tags FROM users WHERE username = ?");
        $u_id_stmt->bind_param("s", $username);
        $u_id_stmt->execute();
        $u_id_result = $u_id_stmt->get_result();
        $row_uid = $u_id_result->fetch_assoc();

        if ($row_uid) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row_uid['user_id'];
            $_SESSION['tags'] = explode(",", $row_uid['tags']);
        }

        header("Location: users.php?intro=true");
        exit;
    }
}

ob_end_flush();
?>

<?php include "navbar.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="min-h-screen flex flex-col">
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Register</h2>
                <?php if (isset($error)) : ?>
                    <div class="bg-red-100 text-red-800 border border-red-300 rounded-lg p-4 mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="register.php" class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <div class="text-red-500 text-xs mt-1">Please enter a username.</div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <div class="text-red-500 text-xs mt-1">Please enter a password.</div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <div class="text-red-500 text-xs mt-1">Please enter a valid email.</div>
                    </div>
                    <button type="submit" name="register" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 transition">Register</button>
                </form>
            </div>
        </div>
    </main>

    <?php include "footer.php"; ?>
</div>

</body>
</html>
