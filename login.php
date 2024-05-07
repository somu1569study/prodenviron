<?php
session_start();

$nameErr = $passErr = $phoneErr = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['name'])) {
        $nameErr = "This field is required";
    } else {
        $pattern = "/^[a-zA-Z]+$/";
        $check = preg_match_all($pattern, $_POST['name']);
        if ($check) {
            $name = $_POST['name'];
        } else {
            $nameErr = "Enter correct pattern of your name";
        }
    }

    if (empty($_POST['password'])) {
        $passErr = "This field is required";
    } else {
        $password = $_POST['password'];
        if (strlen($password) < 4) {
            $passErr = "Password must be at least 4 characters long";
        } else {
            $pass = $password; // Assigning password to $pass for further use
        }
    }

    // Connect to database
    $conn = new mysqli("localhost", "root", "", "users");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and bind statement
    $stmt = $conn->prepare("SELECT id FROM users1 WHERE username=? AND password=?");
    $stmt->bind_param("ss", $_POST['username'], $_POST['password']);

    // Execute statement
    $stmt->execute();
    $stmt->store_result();

    // Bind the result variable
    $stmt->bind_result($id);

    // If a matching record is found, log in the user
    if ($stmt->num_rows == 1) {
        // Fetch the result
        $stmt->fetch();
        // Store username in session

        $_SESSION['username'] = $_POST['username'];
        $_SESSION['password'] = $_POST['password'];
        
        $_SESSION['id'] = $id;
        // Redirect to dashboard or any other authenticated page
        header("Location: use.php");//store.php
        exit();
    } else {
        // If no matching record found, display error message
        $loginError = "Invalid username or password";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Your CSS styles */
        body {
    font-family: Arial, sans-serif;
    background-color: #f0f0f0;
}

.container {
    max-width: 400px;
    margin: 50px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
}

form {
    margin-bottom: 20px;
}

input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #0056b3;
}
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form id="loginForm" method="post" action="">
            <h2>Login</h2>
            <input type="text" name="username" id="loginUsername" placeholder="Username" required><br>
            <span class='red-message'>* <?php echo $nameErr; ?></span><br>
            <input type="password" name="password" id="loginPassword" placeholder="Password" required><br>
            <span class='red-message'>* <?php echo $passErr; ?></span><br>
            <button type="submit">Login</button><br>
        </form>
    </div>
</body>
</html>
