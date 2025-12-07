<?php
session_start();
require_once "includes/supabase.php";  // Ensure the path is correct

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $supabase = new Supabase();
    $user = $supabase->fetchUser($username);

    if ($user && count($user) > 0) {
        $row = $user[0];

        if (password_verify($password, $row['password_hash'])) {

            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_type'] = $row['user_type'];

            if ($row['user_type'] === 'student') {
                header("Location: student/studentdashboard.html");
            } elseif ($row['user_type'] === 'teacher') {
                header("Location: teacher/teacherdashboard.html");
            } elseif ($row['user_type'] === 'admin') {
                header("Location: admin/admindashboard.html");
            }
            exit();
        }
    }

    $login_error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="icon" href="assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#0D1117] text-white font-sans flex justify-center items-center min-h-screen relative overflow-hidden
             before:content-[''] before:fixed before:-top-[10px] before:-left-[10px] before:-right-[10px] before:-bottom-[10px] before:-z-10
             before:bg-[url('assets/images/background.avif')] before:bg-cover before:bg-center before:bg-no-repeat before:blur-lg
             after:content-[''] after:fixed after:-top-[10px] after:-left-[10px] after:-right-[10px] after:-bottom-[10px] after:-z-10
             after:bg-[rgba(13,17,23,0.5)]">


    <!-- INCREASED WIDTH TO 500px -->
    <div class="flex flex-col items-center w-[500px]">

        <!-- CARD enlarged -->
        <div class="bg-white border border-gray-300 rounded-2xl p-10 w-full text-black shadow-2xl h-[720px] flex flex-col justify-between">

            <div> <!-- Content wrapper -->
                <!-- Logo + Title BLOCK enlarged -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-20 h-20 bg-[#800000] flex justify-center items-center rounded-lg">
                        <img src="assets/images/logo.png" alt="Logo" class="w-14 h-14 object-contain">
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-[#800000] leading-tight">MNCHS</h1>
                        <p class="text-lg text-gray-600">Grade Portal</p>
                    </div>
                </div>

                <!-- Title enlarged -->
                <h2 class="text-3xl font-semibold text-[#0D1117] mb-4">Welcome back!</h2>

                <form class="space-y-6" method="POST" action="index.php">

                    <!-- Bigger Input -->
                    <input 
                        type="text" 
                        id="username" 
                        name="username"
                        placeholder="Username or Email"
                        class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                               focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                        required>

                    <!-- Bigger Password with icon -->
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="Password"
                            class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                   focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                            required>

                        <button type="button" onclick="togglePassword()"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600">
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>

                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 17L7 7"></path>
                                <path d="M1 12s4-7 11-7c1.8 0 3.4.5 4.8 1.3"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Smaller "Forgot password" link -->
                    <div class="text-right -mt-4">
                        <a href="#" class="text-sm text-[#800000] hover:underline">Forgot password?</a>
                    </div>

                    <!-- Bigger Button -->
                    <button 
                        type="submit" 
                        class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold 
                               hover:bg-[#990000] transition-colors">
                        Login
                    </button>

                </form>

                <!-- Create Account -->
                <div class="mt-16 text-center text-sm text-gray-700 space-y-2">
                    <p>
                        Student without an account?
                        <a href="student_register.html" class="text-[#800000] font-semibold hover:underline">Click here</a>.
                    </p>
                    <p>
                        Do you want to see your child's grade?
                        <a href="parent_register.html" class="text-[#800000] font-semibold hover:underline">Click here</a>.
                    </p>
                </div>
            </div>

            <!-- Manual Link -->
            <div class="text-center">
                <a href="#" class="text-[#ca8a04] hover:underline">How to access the MNCHS Grade Portal</a>
            </div>

        </div>
    </div>

    <script src="assets/js/login.js"></script>

</body>
</html>