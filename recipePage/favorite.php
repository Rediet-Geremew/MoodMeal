<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private PDO $pdo;

    public function __construct() {
        $host = 'localhost';
        $db = 'mood_meal';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }

    public function insertFavorite(array $recipeData): bool {
        $sql = "INSERT INTO favorites (recipe_id, meal, description, image, recipe_link) 
                VALUES (:recipe_id, :meal, :description, :image, :recipe_link)
                ON DUPLICATE KEY UPDATE
                    meal = VALUES(meal),
                    description = VALUES(description),
                    image = VALUES(image),
                    recipe_link = VALUES(recipe_link)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':recipe_id' => $recipeData['spoonacular_id'] ?? null,
            ':meal' => $recipeData['meal'] ?? '',
            ':description' => $recipeData['description'] ?? '',
            ':image' => $recipeData['image'] ?? '',
            ':recipe_link' => $recipeData['recipe_link'] ?? '',
        ]);
    }
}

class FavoriteHandler {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleRequest() {
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        if (!$data || !isset($data['spoonacular_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or missing recipe data']);
            exit;
        }

        $success = $this->db->insertFavorite($data);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save favorite']);
        }
    }
}

$handler = new FavoriteHandler();
$handler->handleRequest();
