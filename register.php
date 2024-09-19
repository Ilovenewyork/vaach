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
        $_SESSION['tags'] = explode("," , $row_uid['tags']);
      } 

      // $msg = "Congratulations! You have created an account on $name, the platform dedicated to small languages!\nEmail will be used to send notifications about post updates and language updates.";
      // $msg = wordwrap($msg,70);

      // $mailheaders = 'From: hakeg59439@laymro.com';

      // // send email
      // mail($email,"$name Account Created!",$msg, $mailheaders);

      // error_reporting(E_ALL);
      // ini_set('display_errors', 1);

      //addNotif($user_id, "Account successfully created!", "success");
      header("Location: users.php?intro=true");
      exit;
    }
  }

  ob_end_flush();
  ?>

  <?php include "navbar.php";?>

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h2 class="text-center mb-4">Register</h2>
        <?php if (isset($error)) : ?>
          <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action = "register.php" class="needs-validation">
          <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required>
            <div class="invalid-feedback">Please enter a username.</div>
          </div>
          <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="invalid-feedback">Please enter a password.</div>
          </div>
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="text" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">Please enter a valid email.</div>
          </div>
          <button type="submit" name="register" class="btn btn-primary btn-block brandblue">Register</button>
        </form>
      </div>
    </div>
  </div>

  <?php // include "footer.php"; ?>
