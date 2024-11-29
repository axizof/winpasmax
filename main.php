<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$balance = $_SESSION['balance'] ?? 0.00;
$role = $_SESSION['role'];
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barre supérieure -->
    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <div>
            <p class="text-lg font-bold">Hello, <?php echo htmlspecialchars($username); ?></p>
            <p>Balance: <span id="user-balance">€<?php echo number_format($balance, 2); ?></span></p>
        </div>
        <div class="flex space-x-4">
            <button class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Deposit</button>
            <button id="cart-button" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600" onclick="openCart()">Panier</button>
            <a href="logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Logout</a>
        </div>
    </header>

    <!-- Liste des matchs -->
    <main class="p-6">
        <h1 class="text-2xl font-bold mb-6">Available Matches</h1>
        
        
        <div id="match-list">
        </div>
        <div class="space-y-6">
            
            
            
            
            
        </div>
    </main>

<!-- Popup du panier -->
<div id="cart-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
        <!-- Bouton pour fermer le popup -->
        <button onclick="closeCart()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        <!-- Contenu du popup -->
        <h2 class="text-xl font-bold mb-4">Fiche de pari</h2>
        <div id="cart-items" class="space-y-4">
            <!-- Les paris ajoutés apparaîtront ici -->
        </div>
        <div class="mt-4">
            <p class="font-bold">Cotes totales: <span id="total-odds">0.00</span></p>
            <div class="mt-4">
                <label for="stake" class="font-medium text-gray-700">Enjeu (€):</label>
                <input type="number" id="stake" class="w-full mt-2 px-4 py-2 border rounded-lg" oninput="calculatePotentialWinnings()">
            </div>
            <p class="mt-2 font-bold">Gains potentiels: €<span id="potential-winnings">0.00</span></p>
        </div>
        <div class="flex justify-end space-x-4 mt-6">
            <button onclick="clearCart()" class="text-red-500">Tout effacer</button>
            <button onclick="placeBet()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Place Bet</button>
        </div>
    </div>
</div>

    <script>
    
        async function fetchMatches() {
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_matches' })
            });

            if (!response.ok) {
                throw new Error('Failed to fetch matches');
            }

            const data = await response.json();
            displayMatches(data);
        } catch (error) {
            console.error('Error fetching matches:', error);
        }
    }
    
        function displayMatches(data) {
        const matchList = document.getElementById('match-list');
        matchList.innerHTML = ''; // Clear existing matches

        // Afficher les matchs à venir ou en cours
        if (data.upcoming_matches.length > 0) {
            data.upcoming_matches.forEach(match => {
                matchList.innerHTML += `
                    <div class="bg-white p-4 rounded-lg shadow-md border border-blue-200">
                    
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2">
                                <span class="text-yellow-500 text-xl">🏆</span>
                                <p class="font-medium text-gray-700">Match en cours / à venir</p>
                            </div>
                            <p class="text-sm text-gray-500">${new Date(match.datfin).toLocaleString()}</p>
                        </div>
                        <div class="mt-4 flex justify-between items-center">
                            <p class="font-semibold text-lg">${match.equipe1}</p>
                            <p class="text-gray-500 text-sm">vs</p>
                            <p class="font-semibold text-lg">${match.equipe2}</p>
                        </div>
                        <div class="flex justify-between mt-6">
                            <button class="w-1/3 py-2 text-center border border-gray-300 rounded-lg hover:bg-gray-100" onclick="addToCart('${match.equipe1}', ${match.cote1}, '${match.id}')">
                                <p class="font-bold">1</p>
                                <p class="text-blue-600 font-bold">${match.cote1.toFixed(2)}</p>
                            </button>
                            <button class="w-1/3 py-2 text-center border border-gray-300 rounded-lg hover:bg-gray-100" onclick="addToCart('Draw', ${match.coteNul}, '${match.id}')">
                                <p class="font-bold">X</p>
                                <p class="text-blue-600 font-bold">${match.coteNul.toFixed(2)}</p>
                            </button>
                            <button class="w-1/3 py-2 text-center border border-gray-300 rounded-lg hover:bg-gray-100" onclick="addToCart('${match.equipe2}', ${match.cote2}, '${match.id}')">
                                <p class="font-bold">2</p>
                                <p class="text-blue-600 font-bold">${match.cote2.toFixed(2)}</p>
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        // Afficher les matchs terminés d'aujourd'hui
        if (data.finished_matches_today.length > 0) {
            matchList.innerHTML += '<h2 class="text-xl font-bold mt-6">Matchs terminés aujourd\'hui</h2>';
            data.finished_matches_today.forEach(match => {
                matchList.innerHTML += `
                    <div class="bg-gray-100 p-4 rounded-lg shadow-md border border-gray-300">
                        <div class="flex justify-between items-center">
                            <p class="font-semibold text-lg">${match.equipe1} ${match.scoreEquipe1} - ${match.scoreEquipe2} ${match.equipe2}</p>
                            <p class="text-sm text-gray-500">${new Date(match.datfin).toLocaleString()}</p>
                        </div>
                        <div class="mt-2 text-gray-600">
                            <p>Cote 1: ${match.cote1.toFixed(2)} | Cote X: ${match.coteNul.toFixed(2)} | Cote 2: ${match.cote2.toFixed(2)}</p>
                        </div>
                    </div>
                `;
            });
        }
    }

    // Charger les matchs au chargement de la page
    document.addEventListener('DOMContentLoaded', fetchMatches);
    
    
        const cart = [];

        function addToCart(team, cote, match) {
            cart.push({ match, team, cote });
            updateCartDisplay();
            alert(`${team} ajouté à la Fiche de bet`);
        }

        function openCart() {
            updateCartDisplay();
            document.getElementById('cart-popup').classList.remove('hidden');
        }
        function closeCart(){
             document.getElementById('cart-popup').classList.add('hidden');
        }

        function updateCartDisplay() {
            const cartItems = document.getElementById('cart-items');
            cartItems.innerHTML = cart.map((item, index) => `
                <div class="flex justify-between items-center border-b py-2">
                    <p>${item.match} - ${item.team} @ ${item.cote}</p>
                    <button class="text-red-500" onclick="removeFromCart(${index})">x</button>
                </div>
            `).join('');
            calculateTotalOdds();
        }

        function calculateTotalOdds() {
            const totalOdds = cart.reduce((acc, item) => acc * item.cote, 1).toFixed(2);
            document.getElementById('total-odds').textContent = totalOdds || '0.00';
            calculatePotentialWinnings();
        }

        function calculatePotentialWinnings() {
            const stake = parseFloat(document.getElementById('stake').value) || 0;
            const totalOdds = parseFloat(document.getElementById('total-odds').textContent) || 0;
            const winnings = (stake * totalOdds).toFixed(2);
            document.getElementById('potential-winnings').textContent = winnings || '0.00';
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
        }

        function clearCart() {
            cart.length = 0;
            updateCartDisplay();
        }

async function placeBet() {
    if (cart.length === 0) {
        alert("Pas de paris dans le chariot.");
        return;
    }

    const stake = parseFloat(document.getElementById('stake').value);
    if (!stake || stake <= 0) {
        alert("Veuillez saisir un enjeu valide.");
        return;
    }

    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'place_bet',
            stake: stake,
            bets: cart
        })
    });

    const result = await response.json();

    if (response.ok) {
        alert(result.message);
        cart.length = 0;
        updateCartDisplay();
        document.getElementById('cart-popup').classList.add('hidden');
    } else {
        alert(result.error);
    }
}
    </script>
</body>
</html>