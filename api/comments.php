<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/Comment.php';

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

if ($method === 'GET' && empty($action)) {
    $articleId = isset($_GET['article_id']) ? trim($_GET['article_id']) : '';

    if (empty($articleId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error'   => "Paramètre 'article_id' manquant."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $comments = Comment::getByArticle($articleId);

    echo json_encode([
        'success'  => true,
        'count'    => count($comments),
        'comments' => $comments
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($method === 'POST') {

    
    if ($action === 'like') {
        $id = isset($requestData['id']) ? intval($requestData['id']) : 0;
        $liked = isset($requestData['liked']) ? filter_var($requestData['liked'], FILTER_VALIDATE_BOOLEAN) : true;

        $result = Comment::toggleLike($id, $liked);
        if (!$result['success']) {
            http_response_code(400);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'delete') {
        $id = isset($requestData['id']) ? intval($requestData['id']) : 0;

        $result = Comment::delete($id);
        if (!$result['success']) {
            http_response_code(400);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $articleId = isset($requestData['article_id']) ? trim($requestData['article_id']) : '';
    $author = isset($requestData['author']) ? $requestData['author'] : '';
    $avatar = isset($requestData['avatar']) ? $requestData['avatar'] : '🌸';
    $message = isset($requestData['message']) ? $requestData['message'] : '';

    $result = Comment::add($articleId, $author, $avatar, $message);
    if (!$result['success']) {
        http_response_code(400);
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => "Méthode non autorisée."], JSON_UNESCAPED_UNICODE);
