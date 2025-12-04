<?php
// Start the session to access and destroy it.
session_start();

// Unset all of the session variables.
$_SESSION = array();

// Destroy the session.
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Redirect to the login page after 2 seconds -->
    <meta http-equiv="refresh" content="2;url=login.php">
    <title>Logging Out... | MNCHS Grade Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #800000;
            --text: #2d3436;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            text-align: center;
        }
        .loading-container {
            background: white;
            padding: 3rem 4rem;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        h1 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 2rem;
        }
        p {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <h1>Logging Out</h1>
        <p>Please wait while we securely log you out...</p>
    </div>
</body>
</html>