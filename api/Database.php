<?php


class Database {
    private static string $host = 'localhost';
    private static string $dbName = 'blog_journal';
    private static string $username = 'root';
    private static string $password = '';
    private static ?PDO $pdo = null;

    
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ];

                self::$pdo = new PDO($dsn, self::$username, self::$password, $options);

                self::initDatabase();

            } catch (PDOException $e) {
                
                http_response_code(500);
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode([
                    'success' => false,
                    'error'   => "Impossible de se connecter à MySQL : " . $e->getMessage(),
                    'hint'    => "Vérifiez que le module MySQL est bien démarré (bouton 'Start') dans le Panneau de Contrôle XAMPP."
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        return self::$pdo;
    }

    
    private static function initDatabase(): void {
        if (self::$pdo === null) return;

        self::$pdo->exec("CREATE DATABASE IF NOT EXISTS `" . self::$dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        self::$pdo->exec("USE `" . self::$dbName . "`");

        self::$pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(50) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `category` VARCHAR(50) NOT NULL,
            `category_label` VARCHAR(100) NOT NULL,
            `excerpt` TEXT NOT NULL,
            `content` LONGTEXT NULL,
            `image_url` VARCHAR(255) NOT NULL,
            `image_alt` VARCHAR(255) NOT NULL,
            `image_caption` VARCHAR(255) NULL,
            `author_name` VARCHAR(100) DEFAULT 'Emma Rose',
            `read_time` VARCHAR(50) DEFAULT '5 min de lecture',
            `likes` INT DEFAULT 0,
            `published_at` DATE NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_slug` (`slug`),
            INDEX `idx_category` (`category`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::$pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `article_id` VARCHAR(50) NOT NULL,
            `author` VARCHAR(100) NOT NULL,
            `avatar` VARCHAR(50) DEFAULT '🌸',
            `message` TEXT NOT NULL,
            `likes` INT DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_article_id` (`article_id`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $checkArticles = self::$pdo->query("SELECT COUNT(*) FROM `articles`");
        if ($checkArticles->fetchColumn() == 0) {
            $insertArticle = self::$pdo->prepare("
                INSERT INTO `articles` 
                (`slug`, `title`, `category`, `category_label`, `excerpt`, `image_url`, `image_alt`, `image_caption`, `author_name`, `read_time`, `likes`, `published_at`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $seedArticles = [
                [
                    'article-1',
                    'Page 14 : Ralentir la course du temps et savourer les matins doux',
                    'matin',
                    '🌸 Matin Doux',
                    'Avant que le tumulte de la journée ne commence, je m\'accorde une heure volée. Une tasse fumante entre les mains, le cœur au repos et quelques lignes tracées pour respirer.',
                    'images/minimalisme.svg',
                    'Illustration d\'un carnet intime rose et d\'une tasse de café chaud',
                    'Le rituel de l\'aurore : une tasse chaude, du silence et une page blanche prête à recevoir mes pensées.',
                    'Emma Rose',
                    '5 min de lecture',
                    14,
                    '2026-09-12'
                ],
                [
                    'article-2',
                    'Page 28 : L\'art du journal intime – Écrire pour guérir et rêver',
                    'journaling',
                    '✨ Journaling',
                    'Poser des mots sur papier, c\'est ranger son âme dans une boîte à trésors. Comment trois exercices de journaling quotidien peuvent transformer ton esprit.',
                    'images/polices.svg',
                    'Illustration poétique d\'un carnet ouvert avec photo polaroïd et stylo doré',
                    'Le pouvoir de l\'encre : transformer ses peines en leçons et ses désirs en projets éclatants.',
                    'Emma Rose',
                    '6 min de lecture',
                    21,
                    '2026-09-26'
                ],
                [
                    'article-3',
                    'Page 42 : Mon cocon du soir – Bougies, douceur et déconnexion',
                    'soir',
                    '🕯️ Cocon du Soir',
                    'Quand la nuit tombe, éteindre le téléphone devient un acte d\'amour propre. Voici ma routine réconfortante pour fermer la porte aux angoisses et glisser vers le sommeil.',
                    'images/responsive.svg',
                    'Illustration chaleureuse d\'une bougie rose allumée et pile de carnets pastel',
                    'Le crépuscule des écrans : fermer les yeux du monde pour ouvrir ceux du cœur.',
                    'Emma Rose',
                    '7 min de lecture',
                    18,
                    '2026-10-08'
                ]
            ];

            foreach ($seedArticles as $art) {
                $insertArticle->execute($art);
            }
        }

        $checkComments = self::$pdo->query("SELECT COUNT(*) FROM `comments`");
        if ($checkComments->fetchColumn() == 0) {
            $insertComment = self::$pdo->prepare("
                INSERT INTO `comments` (`article_id`, `author`, `avatar`, `message`, `likes`, `created_at`) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $seedComments = [
                ['article-1', 'Léa', '🌸', "Quel régal de lire ces mots ce matin avec mon café au lait d'avoine ! Ce rituel sans écran a complètement transformé mes journées.", 4, date('Y-m-d H:i:s', strtotime('-3 hours'))],
                ['article-1', 'Chloé', '✨', "J'adore la citation de Saint-Exupéry. Prendre le temps d'écrire le matin m'aide tellement à apaiser mes pensées avant d'aller travailler.", 2, date('Y-m-d H:i:s', strtotime('-1 day'))],
                ['article-2', 'Camille', '🎀', "Les carnets avec washi tapes et stickers, c'est toute mon enfance et encore ma passion d'adulte ! Merci pour ces doux exercices de journaling.", 5, date('Y-m-d H:i:s', strtotime('-1 day'))],
                ['article-2', 'Sarah', '🐱', "Le \"Brain Dump\" de 10 minutes a sauvé mon sommeil plus d'une fois. Un article si joliment écrit !", 3, date('Y-m-d H:i:s', strtotime('-3 days'))],
                ['article-3', 'Juliette', '🕯️', "L'odeur de la bougie à la rose et le mode avion à 21h30... je teste ça dès ce soir ! Merci pour cette bulle de bienveillance.", 6, date('Y-m-d H:i:s', strtotime('-5 hours'))],
                ['article-3', 'Manon', '☕', "Un véritable doudou cet article. La palette rose et le texte justifié rendent la lecture tellement apaisante.", 3, date('Y-m-d H:i:s', strtotime('-1 day'))]
            ];

            foreach ($seedComments as $com) {
                $insertComment->execute($com);
            }
        }
    }
}
