<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
  <div class="w-full max-w-sm p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-center mb-6">Register</h2>
    <!-- Message container -->
    <div id="message" class="hidden p-4 mb-4 text-sm rounded-lg"></div>
    <form id="register-form" class="space-y-4">
      <input type="text" id="username" placeholder="Username" required
             class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
      <input type="email" id="email" placeholder="Email" required
             class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
      <input type="password" id="password" placeholder="Password" required
             class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
      <input type="password" id="confirm-password" placeholder="Confirm Password" required
             class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
      <input type="date" id="birthdate" placeholder="Date of Birth" required
             class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
      <div class="flex items-center">
        <input type="checkbox" id="accept-terms" required
               class="mr-2 focus:ring-2 focus:ring-blue-500">
        <label for="accept-terms" class="text-gray-700">I accept the <a href="#" class="text-blue-600 hover:underline">terms and conditions</a>.</label>
      </div>
      <button type="submit"
              class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        Register
      </button>
      <p class="text-center text-gray-500">
        Vous avez déjà un compte ? <a href="login.php" class="text-blue-600 hover:underline">Se connecter ici</a>
      </p>
    </form>
  </div>
  <script>
document.getElementById("register-form").addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm-password").value;
  const birthdate = document.getElementById("birthdate").value;
  const acceptTerms = document.getElementById("accept-terms").checked;

  const response = await fetch("api.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: "register",
      username,
      email,
      password,
      confirm_password: confirmPassword,
      birthdate,
      accept_terms: acceptTerms,
    }),
  });

  const result = await response.json();

  if (response.ok) {
    displayMessage(result.message, "success");
    setTimeout(() => {
      window.location.href = "login.php";
    }, 2000);
  } else {
    displayMessage(result.error, "error");
  }
});

function displayMessage(message, type) {
  const messageDiv = document.getElementById("message");
  messageDiv.textContent = message;
  messageDiv.className = `p-4 mb-4 text-sm rounded-lg ${
    type === "success" ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"
  }`;
  messageDiv.classList.remove("hidden");
}
  </script>
</body>
</html>