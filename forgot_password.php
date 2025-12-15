<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow users to access forgot password page even if logged in
// They may need to reset their own password or help someone else

$message = '';
$message_type = '';
$step = isset($_POST['step']) ? trim($_POST['step']) : 'select_type';
$user_type = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';
$reset_token = isset($_GET['token']) ? trim($_GET['token']) : '';
$user_email = '';

require_once "includes/config.php";

// Check if token from URL is valid
if ($reset_token) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    if ($stmt) {
        $stmt->bind_param("s", $reset_token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $step = 'reset';
        } else {
            $message = "Invalid or expired reset link.";
            $message_type = 'error';
            $step = 'select_type';
        }
        $stmt->close();
    }
}

// Step 1: User type selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['step'] === 'select_type') {
    $user_type = trim($_POST['user_type'] ?? '');
    if (!empty($user_type) && in_array($user_type, ['student', 'teacher', 'parent', 'admin'])) {
        if ($user_type === 'student') {
            $step = 'student_lrn';
        } else {
            $step = 'email';
        }
    } else {
        $message = "Please select an account type.";
        $message_type = 'error';
    }
}

// Step 2a: Student LRN lookup
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['step'] === 'student_lrn') {
    $lrn = trim($_POST['lrn'] ?? '');
    $user_type = 'student';
    
    if (!empty($lrn)) {
        $stmt = $conn->prepare("
            SELECT u.id, u.email 
            FROM users u 
            INNER JOIN students s ON u.id = s.user_id 
            WHERE s.lrn = ? AND u.role = 'student'
        ");
        if ($stmt) {
            $stmt->bind_param("s", $lrn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $user_email = $user['email'];
                $user_id = $user['id'];
                
                // Move to student email verification step
                $step = 'student_email';
            } else {
                $message = "No student account found with this LRN.";
                $message_type = 'error';
                $step = 'student_lrn';
            }
            $stmt->close();
        }
    } else {
        $message = "Please enter your LRN.";
        $message_type = 'error';
        $step = 'student_lrn';
    }
}

// Step 2a-2: Student Email verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['step'] === 'student_email') {
    $email = trim($_POST['email'] ?? '');
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if (!empty($email) && $user_id > 0) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND email = ? AND role = 'student'");
        if ($stmt) {
            $stmt->bind_param("is", $user_id, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                // Email matches, generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("ssi", $reset_token, $token_expiry, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/forgot_password.php?token=" . $reset_token;
                    $message = "Reset link has been sent to: <strong>" . htmlspecialchars($email) . "</strong><br><br><a href='" . htmlspecialchars($reset_link) . "' style='color:#800000;text-decoration:underline;font-weight:bold'>Click here to reset your password</a>";
                    $message_type = 'success';
                    $step = 'email_sent';
                }
            } else {
                $message = "The email address does not match our records for this LRN.";
                $message_type = 'error';
                $step = 'student_email';
            }
            $stmt->close();
        }
    } else {
        $message = "Please enter a valid email address.";
        $message_type = 'error';
        $step = 'student_email';
    }
}

// Step 2b: Email lookup (for teachers, parents, admins)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['step'] === 'email') {
    $email = trim($_POST['email'] ?? '');
    $user_type = trim($_POST['user_type'] ?? '');
    
    if (!empty($email)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND role = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $email, $user_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
                
                // Generate reset token
                $reset_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("ssi", $reset_token, $token_expiry, $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/forgot_password.php?token=" . $reset_token;
                    $message = "Reset link has been sent to: <strong>" . htmlspecialchars($email) . "</strong><br><br><a href='" . htmlspecialchars($reset_link) . "' style='color:#800000;text-decoration:underline;font-weight:bold'>Click here to reset your password</a>";
                    $message_type = 'success';
                    $step = 'email_sent';
                }
            } else {
                $message = "No " . htmlspecialchars($user_type) . " account found with this email.";
                $message_type = 'error';
                $step = 'email';
            }
            $stmt->close();
        }
    } else {
        $message = "Please enter your email address.";
        $message_type = 'error';
        $step = 'email';
    }
}

// Step 3: Password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['step'] === 'reset') {
    $token = trim($_POST['token'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    if (empty($token) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } elseif (strlen($new_password) < 8) {
        $message = "Password must be at least 8 characters long.";
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $stmt->close();
                
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $hashed, $user['id']);
                    if ($update_stmt->execute()) {
                        $message = "Password reset successfully! You can now log in.";
                        $message_type = 'success';
                        $step = 'complete';
                    } else {
                        $message = "Error updating password. Please try again.";
                        $message_type = 'error';
                    }
                    $update_stmt->close();
                }
            } else {
                $message = "Invalid or expired reset link.";
                $message_type = 'error';
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MNCHS Grade Portal</title>
    <link rel="icon" href="assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<!-- Added overflow-hidden to body to prevent scrollbars from the blur effect overflow -->
<body class="bg-[#0D1117] text-white font-sans min-h-screen relative overflow-hidden">

    <!-- Background blurred layer -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-20 bg-[url('assets/images/background.avif')] bg-cover bg-center bg-no-repeat blur-sm"></div>
    
    <!-- Dark overlay -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-10 bg-[rgba(13,17,23,0.5)]"></div>

    <!-- Main Container: Two columns layout -->

    <div class="flex h-screen">
        <!-- LEFT SIDE: (empty, for spacing/alignment) -->
        <div class="hidden lg:flex w-1/2"></div>
        <!-- RIGHT SIDE: Forgot Password Form -->
        <div class="w-full lg:w-1/2 flex justify-center items-center px-4 py-8">
            <div class="bg-white border border-gray-300 rounded-2xl p-10 w-full max-w-[500px] text-black shadow-2xl">
                <div> <!-- Content wrapper -->
                    <!-- Logo + Title BLOCK -->
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-20 h-20 bg-[#800000] flex justify-center items-center rounded-lg shadow-sm">
                            <img src="assets/images/logo.png" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-[#800000] leading-tight">MNCHS</h2>
                            <p class="text-lg text-gray-600">Grade Portal</p>
                        </div>
                    </div>
                    <!-- Title -->
                    <h3 class="text-3xl font-semibold text-[#0D1117] mb-4">Reset Password</h3>
                    <!-- Success Message -->
                    <?php if ($message_type === 'success'): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <!-- Error Message -->
                    <?php if ($message_type === 'error'): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p><?php echo htmlspecialchars($message); ?></p>
                        </div>
                    <?php endif; ?>
                    <!-- Step 1: Select Account Type -->
                    <?php if ($step === 'select_type'): ?>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="step" value="select_type">
                            <p class="text-gray-600 mb-4">What type of account do you have?</p>
                            <div class="space-y-3">
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition" onclick="document.getElementById('type_student').checked=true">
                                    <input type="radio" id="type_student" name="user_type" value="student" class="w-5 h-5" required>
                                    <span class="ml-3 font-semibold text-[#0D1117]">Student</span>
                                </label>
                                <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition" onclick="document.getElementById('type_parent').checked=true">
                                    <input type="radio" id="type_parent" name="user_type" value="parent" class="w-5 h-5">
                                    <span class="ml-3 font-semibold text-[#0D1117]">Parent</span>
                                </label>
                            </div>
                            <button type="submit" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors">
                                Continue
                            </button>
                            <div class="text-center mt-4">
                                <p class="text-gray-600">Back to login? <a href="index.php" class="text-[#800000] font-semibold hover:underline">Log in here</a></p>
                            </div>
                        </form>
                    <?php elseif ($step === 'student_lrn'): ?>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="step" value="student_lrn">
                            <input type="hidden" name="user_type" value="student">
                            <p class="text-gray-600 mb-4">Enter your Learner Reference Number (LRN)</p>
                            <div class="relative">
                                <input type="text" name="lrn" placeholder="Enter your LRN" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required autofocus>
                            </div>
                            <button type="submit" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors">
                                Find My Account
                            </button>
                            <div class="text-center mt-4 space-y-2">
                                <p class="text-gray-600"><a href="forgot_password.php" class="text-[#800000] font-semibold hover:underline">Use different account type</a></p>
                                <p class="text-gray-600">Back to login? <a href="index.php" class="text-[#800000] font-semibold hover:underline">Log in here</a></p>
                            </div>
                        </form>
                    <?php elseif ($step === 'student_email'): ?>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="step" value="student_email">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars((string)($user_id ?? '')); ?>">
                            <p class="text-gray-600 mb-4">Please enter the email address associated with your account to verify your identity</p>
                            <div class="relative">
                                <input type="email" name="email" placeholder="Enter your email address" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required autofocus>
                            </div>
                            <button type="submit" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors">
                                Send Reset Link
                            </button>
                            <div class="text-center mt-4 space-y-2">
                                <p class="text-gray-600"><a href="forgot_password.php" class="text-[#800000] font-semibold hover:underline">Use different account type</a></p>
                                <p class="text-gray-600">Back to login? <a href="index.php" class="text-[#800000] font-semibold hover:underline">Log in here</a></p>
                            </div>
                        </form>
                    <?php elseif ($step === 'email'): ?>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="step" value="email">
                            <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($user_type); ?>">
                            <p class="text-gray-600 mb-4">Enter the email address associated with your account</p>
                            <div class="relative">
                                <input type="email" name="email" placeholder="Enter your email address" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required autofocus>
                            </div>
                            <button type="submit" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors">
                                Send Reset Link
                            </button>
                            <div class="text-center mt-4 space-y-2">
                                <p class="text-gray-600"><a href="forgot_password.php" class="text-[#800000] font-semibold hover:underline">Use different account type</a></p>
                                <p class="text-gray-600">Back to login? <a href="index.php" class="text-[#800000] font-semibold hover:underline">Log in here</a></p>
                            </div>
                        </form>
                    <?php elseif ($step === 'email_sent'): ?>
                        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                            <p><strong>Check your email!</strong> We've sent a password reset link to your email address.</p>
                        </div>
                        <?php if ($message): ?>
                            <div class="bg-green-50 p-4 rounded-lg mb-6">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-center">
                            <p class="text-gray-600 mb-4">Didn't receive the email?</p>
                            <a href="forgot_password.php" class="block py-4 rounded-lg bg-gray-300 text-gray-800 text-lg font-bold hover:bg-gray-400 transition-colors text-center">Request New Link</a>
                        </div>
                    <?php elseif ($step === 'reset'): ?>
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="step" value="reset">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($reset_token); ?>">
                            <div class="relative">
                                <input type="password" name="new_password" placeholder="New Password (minimum 8 characters)" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required autofocus>
                            </div>
                            <div class="relative">
                                <input type="password" name="confirm_password" placeholder="Confirm Password" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                            </div>
                            <button type="submit" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors">
                                Reset Password
                            </button>
                            <div class="text-center mt-4">
                                <p class="text-gray-600"><a href="index.php" class="text-[#800000] font-semibold hover:underline">Back to Login</a></p>
                            </div>
                        </form>
                    <?php elseif ($step === 'complete'): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                            <p><?php echo htmlspecialchars($message); ?></p>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="block py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors text-center">Return to Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- RIGHT SIDE: (empty, removed) -->
    </div>

</body>
</html>
