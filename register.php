<?php

require 'includes/supabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $confirm_password = $_POST["confirm_password"];

    if ($_POST["password"] !== $confirm_password) {
        die("Password does not match");
    }

    $db = new Supabase();

    $data = [
        "username" => $username,
        "email" => $email,
        "password" => $password,
        "user_type" => "student"
    ];

    $result = $db->insert("users", $data);

    if ($result) {
        header("Location: index.html?success=1");
        exit();
    } else {
        echo "Failed to create account.";
    }
}
?>

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MNCHS Grade Portal</title>
    <link rel="icon" href="assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0D1117] text-white font-sans flex justify-center items-center min-h-screen relative overflow-hidden py-10
             before:content-[''] before:fixed before:-top-[10px] before:-left-[10px] before:-right-[10px] before:-bottom-[10px] before:-z-10
             before:bg-[url('assets/images/background.avif')] before:bg-cover before:bg-center before:bg-no-repeat
             before:blur-lg before:bg-[linear-gradient(rgba(13,17,23,0.5),rgba(13,17,23,0.5)),url('assets/images/background.avif')]">

    <div class="flex flex-col items-center w-[500px]">

        <div class="bg-white border border-gray-300 rounded-2xl p-10 w-full text-black shadow-2xl">

            <div class="flex items-center gap-4 mb-8">
                <div class="w-20 h-20 bg-[#800000] flex justify-center items-center rounded-lg">
                    <img src="assets/images/logo.png" alt="Logo" class="w-14 h-14 object-contain">
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-[#800000] leading-tight">MNCHS</h1>
                    <p class="text-lg text-gray-600">Grade Portal</p>
                </div>
            </div>

            <h2 class="text-3xl font-semibold text-[#0D1117] mb-6">Create your account</h2>

           <form class="space-y-6" action="register-process.php" method="POST">


                <input 
                    type="text" 
                    id="username" 
                    name="username"
                    placeholder="Username"
                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                    required>

                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    placeholder="Email address"
                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                    required>

                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        placeholder="Password"
                        class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                               focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                        required>
                    <button type="button" onclick="togglePassword('password', 'eyeOpen1', 'eyeClosed1')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600">
                        <svg id="eyeOpen1" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg id="eyeClosed1" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    </button>
                </div>

                <div class="relative">
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password"
                        placeholder="Confirm Password"
                        class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                               focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                        required>
                    <button type="button" onclick="togglePassword('confirm_password', 'eyeOpen2', 'eyeClosed2')" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600">
                        <svg id="eyeOpen2" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <svg id="eyeClosed2" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    </button>
                </div>

                <button 
                    type="submit" 
                    class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold 
                           hover:bg-[#990000] transition-colors">
                    Create Account
                </button>

            </form>

            <div class="mt-8 text-center text-gray-700">
                <p>
                    Already have an account?
                    <a href="index.html" class="text-[#800000] font-semibold hover:underline">Sign in</a>.
                </p>
            </div>

        </div>
    </div>

    <script src="assets/js/register.js"></script>

</body>
</html>