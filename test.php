<?php
session_start();

// Error reporting for debugging (Disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Ensure this path is correct relative to where this file is saved
// If index.php is in root, and supabase.php is in includes/, this is correct.
require_once "includes/supabase.php"; 

$login_error = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $supabase = new Supabase();
        $user = $supabase->fetchUser($username);

        // Check if we got data back
        if ($user) {
            $row = null;

            // Handle different return formats:
            // Case 1: Array of rows (e.g., [['id'=>1...]]) -> Use index 0
            if (isset($user[0])) {
                $row = $user[0];
            } 
            // Case 2: Single row (associative array e.g., ['id'=>1...]) -> Use directly
            elseif (isset($user['password_hash'])) {
                $row = $user;
            }

            // Verify password if we have a valid row with a hash
            if ($row && isset($row['password_hash']) && password_verify($password, $row['password_hash'])) {
                
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_type'] = $row['user_type'];

                // Safe redirect logic
                switch ($row['user_type']) {
                    case 'student':
                        header("Location: student/studentdashboard.html");
                        break;
                    case 'teacher':
                        header("Location: teacher/teacherdashboard.html");
                        break;
                    case 'admin':
                        header("Location: admin/admindashboard.html");
                        break;
                    default:
                        // Fallback if user_type is unknown
                        $login_error = "Unknown user role.";
                        break;
                }
                
                // Only exit if we actually set a header
                if (empty($login_error)) {
                    exit();
                }
            } else {
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }
    } catch (Exception $e) {
        // Handle database errors gracefully
        $login_error = "System error. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MNCHS Login Portal</title>
    <!-- Ensure your favicon path is correct -->
    <link rel="icon" href="assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<!-- Added overflow-hidden to body to prevent scrollbars from the blur effect overflow -->
<body class="bg-[#0D1117] text-white font-sans flex justify-center items-center min-h-screen relative overflow-hidden">

    <!-- Background blurred layer -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-20 bg-[url('assets/images/background.avif')] bg-cover bg-center bg-no-repeat blur-lg"></div>
    
    <!-- Dark overlay -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-10 bg-[rgba(13,17,23,0.5)]"></div>

    <div class="flex flex-col items-center w-full max-w-[500px] px-4">

        <div class="bg-white border border-gray-300 rounded-2xl p-8 md:p-10 w-full text-black shadow-2xl min-h-[720px] flex flex-col justify-between">

            <div> 
                <!-- Header -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-20 h-20 bg-[#800000] flex justify-center items-center rounded-lg shadow-sm">
                        <!-- Ensure image path is correct or use a placeholder if missing -->
                        <img src="assets/images/logo.png" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-[#800000] leading-tight">MNCHS</h1>
                        <p class="text-lg text-gray-600">Grade Portal</p>
                    </div>
                </div>

                <h2 class="text-3xl font-semibold text-[#0D1117] mb-4">Welcome back!</h2>

                <!-- PHP Error Message Display -->
                <?php if (!empty($login_error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?php echo htmlspecialchars($login_error); ?></p>
                    </div>
                <?php endif; ?>

                <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

                    <!-- Username -->
                    <input 
                        type="text" 
                        id="username" 
                        name="username"
                        placeholder="Username or Email"
                        class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30 transition-all"
                        required 
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >

                    <!-- Password -->
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="Password"
                            class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30 transition-all"
                            required
                        >

                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-[#800000]">
                            <!-- SVG Icons inline to ensure they show up even if JS fails to load immediately -->
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

                    <div class="text-right -mt-4">
                        <a href="#" class="text-sm text-[#800000] hover:underline font-medium">Forgot password?</a>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors shadow-md active:scale-[0.98]">
                        Login
                    </button>

                </form>

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

            <div class="text-center pt-4">
                <a href="#" class="text-[#ca8a04] hover:underline font-medium">How to access the MNCHS Grade Portal</a>
            </div>

        </div>
    </div>

    <!-- Moved JS inline for reliability if file is missing -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
    </script>

</body>
</html>