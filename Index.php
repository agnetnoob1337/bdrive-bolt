<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false;
}elseif ($_SESSION['logged_in'] == true) {
    header("Location: BDrive.php");
    exit();
}

$username = 'admin';
$password = 'password';

if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === $username && $_POST['password'] === $password) {
        $_SESSION['logged_in'] = true;
        header("Location: BDrive.php");
        exit();

    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDrive</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            color: whitesmoke;
            background-color: rgb(52, 52, 52);
        }
        html{
            background-color: rgb(42, 42, 42);
        }
        body{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 90vh;
            font-family: Arial, sans-serif;
            background-color: rgb(42, 42, 42);
        }
        h1{
            margin-bottom: 20px;
            background-color: rgb(42, 42, 42);
        }
        form{
            display: flex;
            flex-direction: column;
            width: 300px;
            border-radius: 5px;
            margin-bottom: 10vh;
        }
        input{
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
        }
        button{
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>BDrive</h1>
    <form action="Index.php" method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
</body>
</html>