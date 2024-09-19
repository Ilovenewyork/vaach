<!DOCTYPE html>
<html lang="en">
<!-- <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head> -->

<?php
include "header.php";
include "conn.php";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Prepared statement for secure login
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && password_verify($password, $row['passcode'])) {
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['tags'] = explode(",", $row['tags']);
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<body class="bg-gray-100 font-sans">

<?php include "navbar.php"; ?>

<div class="min-h-screen flex flex-col">
    <main class="flex-grow">
        <div class="container mx-auto px-4 py-8">
            <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Log In</h2>
                <?php if (isset($error)) : ?>
                    <div class="bg-red-100 text-red-800 border border-red-300 rounded-lg p-4 mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <div class="text-red-500 text-xs mt-1">Please enter your username.</div>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                        <div class="text-red-500 text-xs mt-1">Please enter your password.</div>
                    </div>
                    <button type="submit" name="login" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 transition">Log In</button>
                </form>
                <p class="text-center mt-4 text-gray-600">Don't have an account? <a href="register.php" class="text-blue-600 hover:underline">Register</a></p>
            </div>
        </div>
    </main>

    <?php include "footer.php"; ?>
</div>

</body>
</html>
