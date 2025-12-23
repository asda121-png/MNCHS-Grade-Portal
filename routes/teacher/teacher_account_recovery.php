<?php
session_start();
// If a user is already logged in, redirect them to the dashboard.
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'teacher') {
    header('Location: teacherdashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Account Recovery - MNCHS Grade Portal</title>
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#0D1117] text-white font-sans min-h-screen relative overflow-hidden">

    <!-- Background blurred layer -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-20 bg-[url('../../assets/images/background.avif')] bg-cover bg-center bg-no-repeat blur-sm"></div>
    
    <!-- Dark overlay -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-10 bg-[rgba(13,17,23,0.5)]"></div>

    <!-- Main Container -->
    <div class="flex h-screen justify-center items-center px-4">
        <div class="bg-white border border-gray-300 rounded-2xl p-10 w-full max-w-[500px] text-black shadow-2xl">

            <div>
                <!-- Logo + Title BLOCK -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-20 h-20 bg-[#800000] flex justify-center items-center rounded-lg shadow-sm">
                        <img src="../../assets/images/logo.png" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-[#800000] leading-tight">MNCHS</h2>
                        <p class="text-lg text-gray-600">Grade Portal</p>
                    </div>
                </div>

                <!-- Title -->
                <h3 class="text-2xl font-semibold text-[#0D1117] mb-2">Teacher Account Recovery</h3>
                <p class="text-gray-600 mb-6 text-sm">Enter your details to receive your login credentials via email.</p>

                <form id="recoveryForm" class="space-y-6">
                    <div class="relative">
                        <label for="employee_number" class="block text-sm font-semibold text-gray-700 mb-1">Employee Number</label>
                        <input type="text" id="employee_number" name="employee_number" placeholder="e.g. TCH001" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                    </div>

                    <div class="relative">
                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="full_name" name="full_name" placeholder="e.g. Juan Dela Cruz" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                    </div>

                    <div class="relative">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Gmail Address</label>
                        <input type="email" id="email" name="email" placeholder="your.email@gmail.com" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000] transition-colors shadow-md active:scale-[0.98]">
                        Send Credentials
                    </button>
                </form>

                <div class="text-center text-gray-700 mt-6">
                    <p>
                        Back to login?
                        <a href="../../index.php" class="text-[#800000] font-semibold hover:underline">Sign in</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('recoveryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Processing...';
            
            const formData = new FormData(this);

            fetch('../../server/api/teacher_recovery_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Success! Your credentials have been sent to your email.');
                    window.location.href = '../../index.php';
                } else {
                    alert('Error: ' + (data.error || 'Could not verify account details.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    </script>

</body>
</html>