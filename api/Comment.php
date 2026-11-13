<?php


require_once __DIR__ . '/Database.php';

class Comment {

    /**
     * 
     * @param string $articleId Slug de l'article (ex: 'article-1')
     * @return array Liste des commentaires formatés pour l'affichage
     */
    public static function getByArticle(string $articleId): array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT `id`, `article_id`, `author`, `avatar`, `message`, `likes`, `created_at`
            FROM `comments`
            WHERE `article_id` = ?
            ORDER BY `created_at` DESC, `id` DESC
        ");
        $stmt->execute([$articleId]);
        $rows = $stmt->fetchAll();

        $comments = [];
        foreach ($rows as $row) {
            $comments[] = [
                'id'         => (string)$row['id'],
                'article_id' => $row['article_id'],
                'author'     => (string)$row['author'],
                'avatar'     => $row['avatar'],
                'message'    => (string)$row['message'],
                'likes'      => (int)$row['likes'],
                'date'       => self::formatRelativeDate($row['created_at']),
                'created_at' => $row['created_at'],
                'isUser'     => false
            ];
        }

        return $comments;
    }

    /**
     
     * 
     * @param string 
     * @param string 
     * @param string 
     * @param string
     * @return array 
     */
    public static function add(string $articleId, string $author, string $avatar, string $message): array {
        $pdo = Database::getConnection();

        
        $author = trim(strip_tags($author));
        $message = trim(strip_tags($message));
        $avatar = trim($avatar);

        if (empty($author) || empty($message) || empty($articleId)) {
            return [
                'success' => false,
                'error'   => "Veuillez renseigner un nom, un message et l'identifiant de l'article."
            ];
        }

       
        if (mb_strlen($author) > 100) $author = mb_substr($author, 0, 100);
        if (mb_strlen($message) > 2000) $message = mb_substr($message, 0, 2000);
        if (empty($avatar)) $avatar = '🌸';

        $stmt = $pdo->prepare("
            INSERT INTO `comments` (`article_id`, `author`, `avatar`, `message`, `likes`, `created_at`)
            VALUES (?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$articleId, $author, $avatar, $message]);

        $newId = (string)$pdo->lastInsertId();

        return [
            'success' => true,
            'message' => "Votre mot doux a été enregistré avec succès dans la base de données !",
            'comment' => [
                'id'         => $newId,
                'article_id' => $articleId,
                'author'     => (string)$author,
                'avatar'     => $avatar,
                'message'    => (string)$message,
                'likes'      => 0,
                'date'       => "À l'instant",
                'created_at' => date('Y-m-d H:i:s'),
                'isUser'     => true
            ]
        ];
    }

    /**
      
     * @param int 
     * @param bool 
     * @return array 
     */
    public static function toggleLike(int $commentId, bool $increment = true): array {
        $pdo = Database::getConnection();

        if ($commentId <= 0) {
            return ['success' => false, 'error' => "ID de commentaire invalide."];
        }

        $delta = $increment ? 1 : -1;
        $stmt = $pdo->prepare("UPDATE `comments` SET `likes` = GREATEST(0, `likes` + ?) WHERE `id` = ?");
        $stmt->execute([$delta, $commentId]);

        $check = $pdo->prepare("SELECT `likes` FROM `comments` WHERE `id` = ?");
        $check->execute([$commentId]);
        $newLikes = $check->fetchColumn();

        if ($newLikes === false) {
            return ['success' => false, 'error' => "Commentaire introuvable."];
        }

        return [
            'success' => true,
            'id'      => (string)$commentId,
            'likes'   => (int)$newLikes
        ];
    }

    /**
     
     * 
     * @param int 
     * @return array 
     */
    public static function delete(int $commentId): array {
        $pdo = Database::getConnection();

        if ($commentId <= 0) {
            return ['success' => false, 'error' => "ID de commentaire invalide."];
        }

        $stmt = $pdo->prepare("DELETE FROM `comments` WHERE `id` = ?");
        $stmt->execute([$commentId]);

        return [
            'success' => true,
            'message' => "Le mot doux a été supprimé de la base de données."
        ];
    }

    /**
      
     * @param string 
     * @return string 
     */
    public static function formatRelativeDate(string $datetimeStr): string {
        $timestamp = strtotime($datetimeStr);
        if (!$timestamp) return "Récemment";

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return "À l'instant";
        } elseif ($diff < 3600) {
            $mins = max(1, floor($diff / 60));
            return "Il y a {$mins} min";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Il y a {$hours} h";
        } elseif ($diff < 172800) {
            return "Hier à " . date('H:i', $timestamp);
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "Il y a {$days} jours";
        } else {
            return date('d/m/Y à H:i', $timestamp);
        }
    }
}
