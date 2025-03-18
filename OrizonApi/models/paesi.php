<?php
header("Content-Type: application/json");
require_once "../config/database.php";

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {

    //metodo GET per recuperare tutti i paesi
    case "GET":
        $stmt = $pdo->query("SELECT * FROM paesi");
        $paesi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($paesi);
        break;

    //metodo POST per inserire nuovi paesi
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["nome"]) || empty($data["nome"])) {
            echo json_encode(["error" => "Il nome è obbligatorio"]);
            http_response_code(400);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO paesi (nome) VALUES (:nome)");
        $stmt->execute(["nome" => $data["nome"]]);
        echo json_encode(["message" => "Paese aggiunto con successo"]);
        break;


    //metodo PUT per modificare i paesi esistenti
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["id"]) || !isset($data["nome"])) {
            echo json_encode(["error" => "ID e nome sono obbligatori"]);
            http_response_code(400);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE paesi SET nome = :nome WHERE id = :id");
        $stmt->execute(["id" => $data["id"], "nome" => $data["nome"]]);
        echo json_encode(["message" => "Paese aggiornato con successo"]);
        break;

    //metodo DELETE per cancellare un paese esistente
    case "DELETE":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["id"])) {
            echo json_encode(["error" => "ID obbligatorio"]);
            http_response_code(400);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM paesi WHERE id = :id");
        $stmt->execute(["id" => $data["id"]]);
        echo json_encode(["message" => "Paese eliminato con successo"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Metodo non consentito"]);
}
?>