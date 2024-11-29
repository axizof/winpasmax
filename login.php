<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
<div class="w-full max-w-sm p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-center mb-6">Login</h2>
    <form id="login-form" class="space-y-4">
        <input type="email" id="email" placeholder="Email" required
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <input type="password" id="password" placeholder="Password" required
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
        <button type="submit"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Login
        </button>
        <p class="text-center text-gray-500">
            Vous n'avez pas de compte ? <a href="register.php" class="text-blue-600 hover:underline">S'inscrire ici</a>
        </p>
    </form>
</div>

<script>
document.getElementById("login-form").addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    const response = await fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ action: "login", email, password })
    });

    const result = await response.json();

    if (response.ok) {
        alert("Login successful! Redirecting...");
        window.location.href = "main.php";
    } else {
        alert(result.error || "An error occurred");
    }
});
</script>
</body>
</html>