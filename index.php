<?php
session_start();

// Error reporting for debugging (Disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Use the new MySQL connection file
require_once "includes/config.php";

$login_error = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {

    $usernameOrEmail = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, password, role, first_name, last_name FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        // Handle a failure to prepare the statement, which is a server-side issue.
        $login_error = "An unexpected error occurred. Please try again later.";
    } else {
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the hashed password from the database
            if (password_verify($password, $user['password'])) {
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['role']; // Use 'role' from the database
                $_SESSION['user_name'] = trim($user['first_name'] . ' ' . $user['last_name']); // Store full name
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                $redirect_url = null;
                // Safe redirect logic
                switch ($user['role']) {
                    case 'student':
                        $redirect_url = "routes/student/studentdashboard.php";
                        break;
                    case 'teacher':
                        $redirect_url = "routes/teacher/teacherdashboard.php";
                        break;
                    case 'admin':
                        $redirect_url = "routes/admin/admindashboard.php";
                        break;
                    case 'parent':
                        $redirect_url = "routes/parent/Parent.php";
                        break;
                    default:
                        // Fallback if role is unknown, but stay on the login page
                        $login_error = "Unknown user role.";
                        break;
                }
                if ($redirect_url) {
                    header("Location: " . $redirect_url);
                    exit();
                }
            } else {
                // Password was incorrect
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "Invalid username or password.";
        }
        $stmt->close();
    }
}
if (isset($conn)) $conn->close();
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

<!-- Added overflow-hidden to body to prevent scrollbars from the blur effect overflow -->
<body class="bg-[#0D1117] text-white font-sans flex justify-center items-center min-h-screen relative overflow-hidden">

    <!-- Background blurred layer -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-20 bg-[url('assets/images/background.avif')] bg-cover bg-center bg-no-repeat blur-lg"></div>
    
    <!-- Dark overlay -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-10 bg-[rgba(13,17,23,0.5)]"></div>


    <!-- INCREASED WIDTH TO 500px -->
    <div class="flex flex-col items-center w-full max-w-[500px] px-4">

        <!-- CARD enlarged -->
        <div class="bg-white border border-gray-300 rounded-2xl p-8 md:p-10 w-full text-black shadow-2xl min-h-[720px] flex flex-col justify-between">

            <div> <!-- Content wrapper -->
                <!-- Logo + Title BLOCK enlarged -->
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

                <!-- Title enlarged -->
                <h2 class="text-3xl font-semibold text-[#0D1117] mb-4">Welcome back!</h2>

                <!-- PHP Success Message Display -->
                <?php if (isset($_GET['registration']) && $_GET['registration'] === 'success'): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>Registration successful! You can now log in.</p>
                    </div>
                <?php endif; ?>

                <!-- PHP Error Message Display -->
                <?php if (!empty($login_error)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?php echo htmlspecialchars($login_error); ?></p>
                    </div>
                <?php endif; ?>

                <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

                    <!-- Bigger Input -->
                    <input 
                        type="text" 
                        id="username" 
                        name="username"
                        placeholder="Username or Email"
                        class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30 transition-all"
                        required 
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >

                    <!-- Bigger Password with icon -->
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password"
                            placeholder="Password"
                            class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30 transition-all"
                            required>

                        <button type="button" onclick="togglePasswordVisibility(this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-[#800000]">
                            <!-- SVG Icons inline to ensure they show up even if JS fails to load immediately -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 eye-open" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden eye-closed" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 17L7 7"></path>
                                <path d="M1 12s4-7 11-7c1.8 0 3.4.5 4.8 1.3"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Smaller "Forgot password" link -->
                    <div class="text-right -mt-4">
                        <a href="#" class="text-sm text-[#800000] hover:underline font-medium">Forgot password?</a>
                    </div>

                    <!-- Bigger Button -->
                    <button 
                        type="submit" 
                        class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors shadow-md active:scale-[0.98]">
                        Login
                    </button>

                </form>

                <!-- Create Account -->
                <div class="mt-16 text-center text-sm text-gray-700 space-y-2">
                    <p>
                        Student without an account?
                        <a href="student_register.php" class="text-[#800000] font-semibold hover:underline">Click here</a>.
                    </p>
                    <p>
                        Do you want to see your child's grade?
                        <a href="parent_register.php" class="text-[#800000] font-semibold hover:underline">Click here</a>.
                    </p>
                </div>
            </div>

            <!-- Manual Link -->
            <div class="text-center pt-4">
                <a href="#" class="text-[#ca8a04] hover:underline font-medium">How to access the MNCHS Grade Portal</a>
            </div>

        </div>
    </div>

    <!-- Main application script -->
    <script src="assets/js/main.js"></script>

</body>
</html>