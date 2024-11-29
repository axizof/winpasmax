<?php

header("Content-Type: application/json");


$db_host = "xxxxxx";
$db_name = "xxxxxx";
$db_user = "xxxxxx";
$db_password = "xxxxxx";


try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Échec de la connexion à la base de données"]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode de requête non valide"]);
    exit;
}


$data = json_decode(file_get_contents("php://input"), true);

// Vérification de l'action
if (!isset($data['action'])) {
    http_response_code(400);
    echo json_encode(["error" => "Paramètre d'action manquant"]);
    exit;
}

$action = $data['action'];


switch ($action) {
    case 'register':
        handleRegister($pdo, $data);
        break;
    case 'login':
        handleLogin($pdo, $data);
        break;
    case 'place_bet':
        handlePlaceBet($pdo, $data);
        break;
    case 'get_matches':
        handleGetMatches($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(["error" => "Action non valide"]);
        break;
}


/**
 * Récupère les matchs en cours, à venir, et les matchs terminés du jour
 */
function handleGetMatches($pdo) {
    try {
        $now = date('Y-m-d H:i:s'); // Heure actuelle
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');


        $stmt = $pdo->prepare("SELECT id, equipe1, equipe2, cote1, cote2, coteNul, statut, datfin 
                                FROM matches 
                                WHERE datfin > :now
                                ORDER BY datfin ASC");
        $stmt->execute(['now' => $now]);
        $upcomingMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les matchs terminés (uniquement ceux du jour)
        $stmt = $pdo->prepare("SELECT id, equipe1, equipe2, scoreEquipe1, scoreEquipe2, cote1, cote2, coteNul, statut, datfin 
                                FROM matches 
                                WHERE datfin <= :now AND datfin BETWEEN :todayStart AND :todayEnd
                                ORDER BY datfin ASC");
        $stmt->execute([
            'now' => $now,
            'todayStart' => $todayStart,
            'todayEnd' => $todayEnd
        ]);
        $finishedMatchesToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retourner les données sous forme de JSON
        http_response_code(200);
        echo json_encode([
            'upcoming_matches' => $upcomingMatches,
            'finished_matches_today' => $finishedMatchesToday
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => "Failed to fetch matches: " . $e->getMessage()]);
    }
}

/**
 * Gère la connexion d'un utilisateur
 */
function handleLogin($pdo, $data) {
    if (!isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "L'adresse électronique et le mot de passe sont nécessaires"]);
        return;
    }

    $email = trim($data['email']);
    $password = $data['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["error" => "Formule d'email non validet"]);
        return;
    }

    try {
        // Vérifier l'email
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(["error" => "email ou mot de passe invalide"]);
            return;
        }

        // Démarrer la session
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        http_response_code(200);
        echo json_encode(["message" => "Connexion réussie", "username" => $user['username'], "role" => $user['role']]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur de base de données : " . $e->getMessage()]);
    }
}

/**
 * Gère l'ajout de paris et la création de tickets
 */
function handlePlaceBet($pdo, $data) {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(["error" => "Utilisateur non connecté"]);
        return;
    }

    $userId = $_SESSION['user_id'];

    // Valider les données du pari
    if (!isset($data['stake'], $data['bets']) || !is_array($data['bets']) || count($data['bets']) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "Format de données invalide"]);
        return;
    }

    $stake = floatval($data['stake']);
    $bets = $data['bets'];

    // Vérifier que la mise est valide
    if ($stake <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Montant de la mise invalide"]);
        return;
    }

    try {
        // Récupérer le solde actuel de l'utilisateur
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['balance'] < $stake) {
            http_response_code(400);
            echo json_encode(["error" => "Solde insuffisant"]);
            return;
        }

        // Vérifier que l'utilisateur n'a pas déjà parié sur les mêmes matchs
        $matchIds = array_column($bets, 'id_match');
        $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
        $stmt = $pdo->prepare("SELECT id_match FROM parier 
                               INNER JOIN ticket ON ticket.id = parier.id_ticket 
                               WHERE ticket.id_user = ? AND id_match IN ($placeholders)");
        $stmt->execute(array_merge([$userId], $matchIds));
        $existingMatches = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($existingMatches)) {
            http_response_code(400);
            echo json_encode(["error" => "Vous avez déjà parié sur ces matches : " . implode(', ', $existingMatches)]);
            return;
        }

        // Créer un ticket
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO ticket (date, montantParis, id_user) VALUES (NOW(), :montantParis, :id_user)");
        $stmt->execute(['montantParis' => $stake, 'id_user' => $userId]);
        $ticketId = $pdo->lastInsertId();

        // Ajouter les paris au ticket
        $stmt = $pdo->prepare("INSERT INTO parier (coteActuel, equipe, id_match, id_ticket) VALUES (:coteActuel, :equipe, :id_match, :id_ticket)");
        foreach ($bets as $bet) {
            $stmt->execute([
                'coteActuel' => $bet['cote'],
                'equipe' => $bet['team'],
                'id_match' => $bet['id_match'],
                'id_ticket' => $ticketId
            ]);
        }

        // Débiter le solde utilisateur
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - :stake WHERE id = :id");
        $stmt->execute(['stake' => $stake, 'id' => $userId]);

        $pdo->commit();

        http_response_code(200);
        echo json_encode(["message" => "Pari placé avec succès", "ticket_id" => $ticketId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["error" => "Le pari n'a pas été placé : " . $e->getMessage()]);
    }
}



/**
 * Gère l'inscription d'un utilisateur
 */
function handleRegister($pdo, $data) {
    if (!isset($data['username'], $data['email'], $data['password'], $data['confirm_password'], $data['birthdate'], $data['accept_terms'])) {
        http_response_code(400);
        echo json_encode(["error" => "Tous les champs sont obligatoires"]);
        return;
    }

    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];
    $confirm_password = $data['confirm_password'];
    $birthdate = $data['birthdate'];
    $accept_terms = $data['accept_terms'];

    if (!$accept_terms) {
        http_response_code(400);
        echo json_encode(["error" => "Vous devez accepter les conditions générales"]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["error" => "Adresse électronique invalide"]);
        return;
    }

    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(["error" => "Les mots de passe ne correspondent pas"]);
        return;
    }


    $dob = new DateTime($birthdate);
    $now = new DateTime();
    $age = $dob->diff($now)->y;

    if ($age < 18) {
        http_response_code(400);
        echo json_encode(["error" => "Vous devez être âgé d'au moins 18 ans"]);
        return;
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(["error" => "Le nom d'utilisateur ou l'adresse électronique existe déjà"]);
            return;
        }


        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (:username, :email, :password_hash, :role)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $hashed_password,
            'role' => 'user'
        ]);

        http_response_code(201);
        echo json_encode(["message" => "L'utilisateur s'est enregistré avec succès"]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}