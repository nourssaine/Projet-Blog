<?php
require_once __DIR__ . '/Database.php';

class Article {

    /**
     * Récupère la liste de tous les articles
     * 
     * @return array 
     */
    public static function getAll(): array {
        $pdo = Database::getConnection();

        $sql = "SELECT `id`, `slug`, `title`, `category`, `category_label`, `excerpt`, 
                       `image_url`, `image_alt`, `image_caption`, `author_name`, 
                       `read_time`, `likes`, `published_at`, `created_at`
                FROM `articles`
                ORDER BY `published_at` DESC, `id` DESC";

        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        //  formatage des données
        $articles = [];
        foreach ($rows as $row) {
            $articles[] = [
                'id'             => (int)$row['id'],
                'slug'           => $row['slug'],
                'title'          => (string)$row['title'],
                'category'       => $row['category'],
                'category_label' => $row['category_label'],
                'excerpt'        => (string)$row['excerpt'],
                'image_url'      => $row['image_url'],
                'image_alt'      => (string)$row['image_alt'],
                'image_caption'  => (string)($row['image_caption'] ?? ''),
                'author_name'    => (string)$row['author_name'],
                'read_time'      => $row['read_time'],
                'likes'          => (int)$row['likes'],
                'published_at'   => $row['published_at'],
                'formatted_date' => self::formatPublishedDate($row['published_at']),
                'url'            => 'pages/' . $row['slug'] . '.html'
            ];
        }

        return $articles;
    }

    /**
     * 
     * 
     * @param string 
     * @return array|null 
     */
    public static function getBySlug(string $slug): ?array {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM `articles` WHERE `slug` = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id'             => (int)$row['id'],
            'slug'           => $row['slug'],
            'title'          => (string)$row['title'],
            'category'       => $row['category'],
            'category_label' => $row['category_label'],
            'excerpt'        => (string)$row['excerpt'],
            'content'        => $row['content'],
            'image_url'      => $row['image_url'],
            'image_alt'      => (string)$row['image_alt'],
            'image_caption'  => (string)($row['image_caption'] ?? ''),
            'author_name'    => (string)$row['author_name'],
            'read_time'      => $row['read_time'],
            'likes'          => (int)$row['likes'],
            'published_at'   => $row['published_at'],
            'formatted_date' => self::formatPublishedDate($row['published_at'])
        ];
    }

    /**
   
     * 
     * @param string 
     * @param bool
     * @return array 
     */
    public static function toggleLike(string $slug, bool $increment = true): array {
        $pdo = Database::getConnection();

        
        $check = $pdo->prepare("SELECT `id`, `likes` FROM `articles` WHERE `slug` = ?");
        $check->execute([$slug]);
        $article = $check->fetch();

        if (!$article) {
            return [
                'success' => false,
                'error'   => "Article introuvable pour le slug '{$slug}'."
            ];
        }

        
        $delta = $increment ? 1 : -1;
        $update = $pdo->prepare("UPDATE `articles` SET `likes` = GREATEST(0, `likes` + ?) WHERE `slug` = ?");
        $update->execute([$delta, $slug]);

        
        $getNew = $pdo->prepare("SELECT `likes` FROM `articles` WHERE `slug` = ?");
        $getNew->execute([$slug]);
        $newLikes = (int)$getNew->fetchColumn();

        return [
            'success' => true,
            'slug'    => $slug,
            'liked'   => $increment,
            'likes'   => $newLikes,
            'message' => $increment ? "Merci pour votre mention J'aime !" : "Mention J'aime retirée."
        ];
    }

    /**
    
     * 
     * @param string 
     * @return int
     */
    public static function getLikesCount(string $slug): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT `likes` FROM `articles` WHERE `slug` = ?");
        $stmt->execute([$slug]);
        $count = $stmt->fetchColumn();
        return $count !== false ? (int)$count : 0;
    }

    
    private static function formatPublishedDate(string $dateStr): string {
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;

        $moisFrancais = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];

        $jour = date('j', $timestamp);
        $mois = $moisFrancais[(int)date('n', $timestamp)] ?? date('m', $timestamp);
        $annee = date('Y', $timestamp);

        return "{$jour} {$mois} {$annee}";
    }
}
