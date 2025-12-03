<php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0D1117] text-white font-sans flex justify-center items-center min-h-screen">

    <div class="flex flex-col items-center w-[340px]">
        <img src="assets/images/logo.png" alt="Logo" class="w-20 h-20 mb-6">

        <div class="bg-[#010409] border border-[#21262D] rounded-lg p-6 w-full">
            <h1 class="text-2xl font-light text-center mb-6">Sign in to your account</h1>
            <form>
                <div class="mb-4">
                    <label for="username" class="block mb-2 font-normal">Username or email address</label>
                    <input type="text" id="username" name="username" required class="w-full p-2.5 border border-[#21262D] rounded-md bg-[#0D1117] text-white focus:outline-none focus:border-[#3085D6] focus:ring-2 focus:ring-[#3085D6]/30">
                </div>
                <div class="mb-4">
                    <label for="password" class="block mb-2 font-normal">Password</label>
                    <input type="password" id="password" name="password" required class="w-full p-2.5 border border-[#21262D] rounded-md bg-[#0D1117] text-white focus:outline-none focus:border-[#3085D6] focus:ring-2 focus:ring-[#3085D6]/30">
                </div>
                <button type="submit" class="w-full py-3 border-none rounded-md bg-[#3085D6] text-white text-base font-medium cursor-pointer transition-colors hover:bg-[#4a9dec]">Sign in</button>
            </form>
        </div>

        <div class="text-center mt-6">
            <a href="#" class="text-[#8D96A0] no-underline text-sm hover:underline">Forgot password?</a>
        </div>

        <div class="mt-6 p-4 border border-[#21262D] rounded-lg text-center w-full">
            New to our service? <a href="#" class="text-[#3085D6] no-underline hover:underline">Create an account</a>.
        </div>
    </div>

</body>
</html>
