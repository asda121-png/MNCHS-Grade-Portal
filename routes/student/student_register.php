<?php
session_start();
// If a user is already logged in, redirect them to the dashboard.
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
    header('Location: studentdashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - MNCHS Grade Portal</title>
    <link rel="icon" href="../../assets/images/logo.ico" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<!-- Added overflow-hidden to body to prevent scrollbars from the blur effect overflow -->
<body class="bg-[#0D1117] text-white font-sans min-h-screen relative overflow-hidden">

    <!-- Background blurred layer -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-20 bg-[url('../../assets/images/background.avif')] bg-cover bg-center bg-no-repeat blur-sm"></div>
    
    <!-- Dark overlay -->
    <div class="fixed -top-[10px] -left-[10px] -right-[10px] -bottom-[10px] -z-10 bg-[rgba(13,17,23,0.5)]"></div>

    <!-- Main Container: Two columns layout -->
    <div class="flex h-screen">
        
        <!-- LEFT SIDE: Branding and Message -->
        <div class="hidden lg:flex w-1/2 flex-col justify-center items-center px-16">
        </div>

        <!-- RIGHT SIDE: Registration Form -->
        <div class="w-full lg:w-1/2 flex justify-center items-center px-4 py-8">
            <div class="bg-white border border-gray-300 rounded-2xl p-10 w-full max-w-[500px] text-black shadow-2xl">

                <div> <!-- Content wrapper -->
                    <!-- Logo + Title BLOCK -->
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-20 h-20 bg-[#800000] flex justify-center items-center rounded-lg shadow-sm">
                            <!-- Ensure image path is correct or use a placeholder if missing -->
                            <img src="../../assets/images/logo.png" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-[#800000] leading-tight">MNCHS</h2>
                            <p class="text-lg text-gray-600">Grade Portal</p>
                        </div>
                    </div>

                    <!-- Title -->
                    <h3 class="text-3xl font-semibold text-[#0D1117] mb-4">Student Setup</h3>

                    <!-- Phase Tracker -->
                    <span id="phaseTracker" class="text-sm font-semibold text-gray-500 block mb-4">1/3</span>

                    <form id="registrationForm">
                        <!-- Phase 1: Personal Information -->
                        <div id="phase1" class="space-y-6">
                            <div class="relative">
                                <input
                                    type="text"
                                    id="lrn"
                                    name="lrn"
                                    placeholder="Learner Reference Number (LRN)"
                                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                                    maxlength="12"
                                    pattern="\d{12}"
                                    title="LRN must be exactly 12 digits"
                                    required>
                            </div>

                            <div class="relative">
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="First Name"
                                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                                    required>
                            </div>

                            <div class="relative">
                                <input
                                    type="text"
                                    id="middle_name"
                                    name="middle_name"
                                    placeholder="Middle Name (O)"
                                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30">
                            </div>

                            <div class="flex gap-4">
                                <div class="flex-grow relative">
                                    <input
                                        type="text"
                                        id="last_name"
                                        name="last_name"
                                        placeholder="Last Name"
                                        class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                               focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                                        required>
                                </div>
                                <div class="w-1/3 relative">
                                    <select
                                        id="suffix"
                                        name="suffix"
                                        class="w-full p-4 pr-12 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg h-full appearance-none
                                               focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30">
                                        <option value="">Suffix (O)</option>
                                        <option value="Jr.">Jr.</option>
                                        <option value="Sr.">Sr.</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phase 2: Academic Information -->
                        <div id="phase2" class="space-y-6 hidden">
                            <div class="relative">
                                <select id="grade_level" name="grade_level" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg h-full appearance-none focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                                    <option value="">Grade Level</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="text" id="section" name="section" placeholder="Section" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                            </div>
                            
                            <div id="strand_container" class="relative">
                                <select id="strand" name="strand" class="w-full p-4 pr-12 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg h-full appearance-none focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30 disabled:bg-gray-200 disabled:cursor-not-allowed" disabled>
                                    <option value="">Strand</option>
                                    <option value="STEM">STEM (Science, Technology, Engineering, and Mathematics)</option>
                                    <option value="ABM">ABM (Accountancy, Business, and Management)</option>
                                    <option value="HUMSS">HUMSS (Humanities and Social Sciences)</option>
                                    <option value="GAS">GAS (General Academic Strand)</option>
                                    <option value="TVL-ICT">TVL-ICT (Information and Communications Technology)</option>
                                    <option value="TVL-HE">TVL-HE (Home Economics)</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-700">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            
                            <div class="relative">
                                <input type="text" id="adviser_name" name="adviser_name" placeholder="Adviser's Name" class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30" required>
                            </div>
                        </div>

                        <!-- Phase 3: Account Details -->
                        <div id="phase3" class="space-y-6 hidden">
                            <div class="relative">
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    placeholder="Username"
                                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                                    required>
                            </div>

                            <div class="relative">
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Email address"
                                    class="w-full p-4 text-lg bg-gray-100 text-black border border-gray-300 rounded-lg
                                           focus:outline-none focus:border-[#F6D64A] focus:ring-2 focus:ring-[#F6D64A]/30"
                                    required>
                            </div>

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
                        </div>

                    <!-- Button Container -->
                    <div class="mt-8">
                        <div>
                            <button type="button" id="nextPhase1" class="w-full py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000]">Next</button>
                            
                            <div id="phase2_buttons" class="flex gap-4 hidden">
                                <button type="button" id="backPhase2" class="w-1/2 py-4 rounded-lg bg-gray-300 text-gray-800 text-lg font-bold hover:bg-gray-400">Back</button>
                                <button type="button" id="nextPhase2" class="w-1/2 py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000]">Next</button>
                            </div>

                            <div id="phase3_buttons" class="flex gap-4 hidden">
                                <button type="button" id="backPhase3" class="w-1/2 py-4 rounded-lg bg-gray-300 text-gray-800 text-lg font-bold hover:bg-gray-400">Back</button>
                                <button type="submit" id="createBtn" class="w-1/2 py-4 rounded-lg bg-[#800000] text-white text-lg font-bold hover:bg-[#990000]">Create</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Success Notification -->
            <div id="successNotification" class="absolute inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-20">
                <div class="bg-white p-8 rounded-lg shadow-xl text-center">
                    <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-2xl font-semibold text-gray-800">Account created successfully!</h3>
                </div>
            </div>

                    <div class="text-center text-gray-700 mt-6">
                        <p>
                            Already have an account?
                            <a href="../../index.php" id="signInLink" class="text-[#800000] font-semibold hover:underline">Sign in</a>.
                        </p>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script src="../../assets/js/student_register.js"></script>

</body>
</html>