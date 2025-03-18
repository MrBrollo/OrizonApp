<?php
header("Content-Type: application/json");
require_once "../config/database.php";

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    //caso GET per recuperare tutti i viaggi con relativi paesi
    case "GET":
        $id_paese = isset($_GET["id_paese"]) ? intval($_GET["id_paese"]) : null;
        $min_posti = isset($_GET["min_posti"]) ? intval($_GET["min_posti"]) : null;

        $query = "SELECT v.id, v.posti_disponibili, GROUP_CONCAT(p.nome) AS paesi
        FROM viaggi v
        LEFT JOIN viaggi_nei_paesi vp ON v.id = vp.id_viaggio
        LEFT JOIN paesi p ON vp.id_paese = p.id";

        $conditions = [];

        if($id_paese) {
            $conditions[] = "v.id IN (SELECT id_viaggio FROM viaggi_nei_paesi WHERE id_paese = :id_paese)";
        }

        if($min_posti !== null) {
            $conditions[] = "v.posti_disponibili >= :min_posti";
        }

        if(!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        $query .= " GROUP BY v.id";

        $stmt = $pdo->prepare($query);
        if($id_paese) {
            $stmt->bindValue(":id_paese", $id_paese, PDO::PARAM_INT);
        }
        if($min_posti !== null) {
            $stmt->bindValue(":min_posti", $min_posti, PDO::PARAM_INT);
        }

        $stmt->execute();
        $viaggi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($viaggi);
        break;

    //caso POST per inserire nuovi viaggi
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if(!isset($data["posti_disponibili"]) || !isset($data["id_viaggio"]) || !is_array($data["id_paese"])) {
            echo json_encode(["error" => "Numero di posti e paesi sono obbligatori"]);
            http_response_code(400);
            exit;
        }
        
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO viaggi (posti_disponibili) VALUES (:posti)");
        $stmt->execute(["posti" => $data["posti_disponibili"]]);
        $id_viaggio = $pdo->LastInsertId();
    
        $stmt = $pdo->prepare("INSERT INTO viaggi_nei_paesi (id_viaggio, id_paese) VALUES (:viaggio, :paese)");
        foreach($data["id_paesi"] as $id_paese) {
            $stmt->execute(["viaggio" => $id_viaggio, "paese" => $id_paese]);
        }

        $pdo->commit();
        echo json_encode(["message" => "Viaggio aggiunto con successo"]);
    }   catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["error" => "Errore nell'inserimento del viaggio: " . $e->getMessage()]);
            http_response_code(500);
        }
        break;

        //caso PUT per modificare i viaggi esistenti
        case "PUT":
            $data = json_decode(file_get_contents("php://input"), true);
            if(!isset($data["id"]) || !isset($data["posti_disponibili"]) || !isset($data["id_paese"]) || !is_array($data["id_paese"])) {
                echo json_encode(["error" => "ID, numero di posti e paesi sono obbligatori"]);
                http_response_code(400);
                exit;
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE viaggi SET posti_disponibili = :posti WEHERE id = :id");
                $stmt->execute(["posti" => $data["posti_disponibili"], "id" => $data["id"]]);

                //Elimina i vecchi paesi associati al viaggio e aggiunge quelli nuovi
                $stmt = $pdo->prepare("DELETE FROM viaggi_nei_paesi WHERE id_viaggio = :id");
                $stmt->execute(["id" => $data["id"]]);

                $stmt = $pdo->prepare("INSERT INTO viaggi_nei_paesi (id_viaggio, id_paese) VALUES (:viaggio, :paese)");
                foreach($data["id_paesi"] as $id_paese) {
                    $stmt->execute(["viaggio" => $data["id"], "paese" => $id_paese]);
                }
                $pdo->commit();
                echo json_encode(["message" => "Viaggio aggiornato con successo"]);
            } catch (PDOException $e) {
                $pdo->rollBack();
                echo json_encode(["error" => "Errore nell'aggiornamento del viaggio: " . $e->getMessage()]);
                http_response_code(500);
            }
            break;

        //caso DELETE per cancellare un viaggio esistente
        case "DELETE":
            $data = json_decode(file_get_contents("php://input"), true);
            if(!isset($data["id"])) {
                echo json_encode(["error" => "ID obbligatorio"]);
                http_response_code(400);
                exit;
            }
            $stmt = $pdo->prepare("DELETE FROM viaggi WHERE id = :id");
            $stmt->execute(["id" => $data["id"]]);
            echo json_encode(["message" => "Viaggio eliminato con successo"]);
            break;

            default:
                http_response_code(405);
                echo json_encode(["error" => "Metodo non consentito"]);
}
?>