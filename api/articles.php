<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/Article.php';


$rawBody = file_get_contents('php://input');
$inputData = [];
if (!empty($rawBody)) {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $inputData = $decoded;
    }
}
$requestData = array_merge($_GET, $_POST, $inputData);
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($requestData['action']) ? trim($requestData['action']) : '';

if ($method === 'GET') {
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

    
    if (!empty($slug)) {
        $article = Article::getBySlug($slug);
        if ($article) {
            echo json_encode([
                'success' => true,
                'article' => $article
            ], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error'   => "Article introuvable pour '{$slug}'."
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    
    $articles = Article::getAll();
    echo json_encode([
        'success'  => true,
        'count'    => count($articles),
        'articles' => $articles
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {
    if ($action === 'like') {
        $slug = isset($requestData['slug']) ? trim($requestData['slug']) : '';
        $liked = isset($requestData['liked']) ? filter_var($requestData['liked'], FILTER_VALIDATE_BOOLEAN) : true;

        if (empty($slug)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Le paramètre 'slug' est requis."], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = Article::toggleLike($slug, $liked);
        if (!$result['success']) {
            http_response_code(404);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }
}


http_response_code(405);
echo json_encode(['success' => false, 'error' => "Méthode ou action non autorisée."], JSON_UNESCAPED_UNICODE);
