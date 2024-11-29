<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $username = $_SESSION['username'];
    // $role = $_SESSION['role'];
    // if($role != "bookmaker"){
    //     header("Location: main.php");
    //     exit;
    // }
    
$db_host = "xxxx";
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

if(isset($_POST["addMatch"])){
    $stmt = $pdo->prepare("UPDATE `matches` SET `cote1`= :cote1 ,`cote2`= :cote2 ,`coteNul`= :coteNul, statut = 'pariable' WHERE id = :id");
    $stmt->bindParam(':cote1', $_POST["cote1"], PDO::PARAM_STR);
    $stmt->bindParam(':cote2', $_POST["cote2"], PDO::PARAM_STR);
    $stmt->bindParam(':coteNul', $_POST["coteNul"], PDO::PARAM_STR);
    $stmt->bindParam(':id', $_POST["addMatch"], PDO::PARAM_STR);
    $stmt->execute();

}

if(isset($_POST["modifyMatch"])){
    $stmt = $pdo->prepare("UPDATE `matches` SET `cote1`= :cote1 ,`cote2`= :cote2 ,`coteNul`= :coteNul WHERE id = :id");
    $stmt->bindParam(':cote1', $_POST["cote1"], PDO::PARAM_STR);
    $stmt->bindParam(':cote2', $_POST["cote2"], PDO::PARAM_STR);
    $stmt->bindParam(':coteNul', $_POST["coteNul"], PDO::PARAM_STR);
    $stmt->bindParam(':id', $_POST["modifyMatch"], PDO::PARAM_STR);
    $stmt->execute();

}


?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookmaker Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <h1 class="text-lg font-bold"><a href="/main.php" >Bookmaker Panel</a></h1>
        <button id="logout" class="px-4 py-2 bg-blue-800 text-white rounded-lg hover:bg-blue-700">
            Logout
        </button>
    </header>
    <main class="p-6">
        <section class="bg-white p-4 rounded-lg shadow-md border border-blue-200">
            <h2 class="text-xl font-semibold mb-4">Matchs dispo</h2>
            <div id="match-list" class="space-y-4">
                <!-- les match sont charger dynamiqueemnt ici -->

                <?php
                $stmt = $pdo->prepare("SELECT * FROM matches WHERE statut = 'initial' ");
                $stmt->execute(); 
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if($res){
                    ?>
                    
                        <?php
                        foreach ($res as $row) {
                            ?> 
                            <form method="POST" style="max-width: 100%;">
                                <span><?php echo $row["equipe1"]; ?> ( <input type="number" name="cote1" value="<?php echo $row["cote1"]; ?>" placeholder="Cote équipe 1" required
                                class=" px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">)
                            VS <?php echo $row["equipe2"]; ?> ( <input type="number" name="cote2" value="<?php echo $row["cote2"]; ?>" placeholder="Cote équipe 2" required
                                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">)
                                ou match null (<input type="number" name="matchNull" value="<?php echo $row["coteNul"]; ?>" placeholder="Cote match null" required
                                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">)
                            <button type="submit" name="addMatch"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" value="<?php echo $row["id"]; ?>">
                                Ajouter
                            </button>
                            </span>
                            </form>
                            <?php
                        }

                        ?>
                   
                    <?php
                }else{
                    echo "Aucun match n'est disponible :(";
                }
                ?>
                
                
            </div>
        </section>
        <hr>

        <section class="bg-white p-4 rounded-lg shadow-md border border-blue-200 mt-10">
            <h2 class="text-xl font-semibold mb-4">Matchs en cours</h2>
            <div id="match-list" class="space-y-4">
                <!-- les match sont charger dynamiqueemnt ici -->
                <?php
                $stmt2 = $pdo->prepare("SELECT * FROM matches WHERE statut = 'pariable' ");
                $stmt2->execute(); 
                $res2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                if($res2){
                    ?>
                    
                        <?php
                        foreach ($res2 as $row) {
                            ?> 
                            <form method="POST" style="max-width: 100%;">
                                <span><?php echo $row["equipe1"]; ?> ( <input type="number" name="cote1" value="<?php echo $row["cote1"]; ?>" placeholder="Cote équipe 1" required
                                class=" px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">)
                            VS <?php echo $row["equipe2"]; ?> ( <input type="number" name="cote2" value="<?php echo $row["cote2"]; ?>" placeholder="Cote équipe 2" required
                                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">)
                                ou match null (<input type="number" name="matchNull" value="<?php echo $row["coteNul"]; ?>" placeholder="Cote match null" required
                                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">)
                            <button type="submit" name="modifyMatch"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" value="<?php echo $row["id"]; ?>">
                                Save
                            </button>
                            </span>
                            </form>
                            <?php
                        }

                        ?>
                    
                    <?php
                }else{
                    echo "Aucun match n'est disponible :(";
                }
                ?>
            </div>
        </section>
    </main>
</body>

</html>
