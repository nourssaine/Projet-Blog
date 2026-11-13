

-- 1. Création de la base de données
CREATE DATABASE IF NOT EXISTS `blog_journal` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `blog_journal`;

-- 2. Création de la table  articles
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Identifiant unique de l''article (ex: article-1)',
    `title` VARCHAR(255) NOT NULL COMMENT 'Titre complet de la page du journal',
    `category` VARCHAR(50) NOT NULL COMMENT 'Catégorie technique (matin, journaling, soir)',
    `category_label` VARCHAR(100) NOT NULL COMMENT 'Libellé affiché avec émoji',
    `excerpt` TEXT NOT NULL COMMENT 'Résumé court pour la liste d''accueil',
    `content` LONGTEXT NULL COMMENT 'Contenu complet de l''article (HTML / texte)',
    `image_url` VARCHAR(255) NOT NULL COMMENT 'Chemin relatif de l''image mise en avant',
    `image_alt` VARCHAR(255) NOT NULL COMMENT 'Texte alternatif d''accessibilité pour l''image',
    `image_caption` VARCHAR(255) NULL COMMENT 'Légende sous l''illustration',
    `author_name` VARCHAR(100) DEFAULT 'Emma Rose' COMMENT 'Nom de l''auteure',
    `read_time` VARCHAR(50) DEFAULT '5 min de lecture' COMMENT 'Temps estimé de lecture',
    `likes` INT DEFAULT 0 COMMENT 'Compteur global de j''aime',
    `published_at` DATE NOT NULL COMMENT 'Date officielle de publication',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Date d''enregistrement',
    INDEX `idx_slug` (`slug`),
    INDEX `idx_category` (`category`),
    INDEX `idx_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Création de la table des commentaires
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` VARCHAR(50) NOT NULL COMMENT 'Référence au slug de l''article',
    `author` VARCHAR(100) NOT NULL COMMENT 'Nom ou pseudonyme de l''auteur(e)',
    `avatar` VARCHAR(50) DEFAULT '🌸' COMMENT 'Emoji avatar choisi',
    `message` TEXT NOT NULL COMMENT 'Contenu de la confidence / commentaire',
    `likes` INT DEFAULT 0 COMMENT 'Nombre de likes / mentions j''aime sur le commentaire',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Date et heure de publication',
    INDEX `idx_article_id` (`article_id`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_comment_article` FOREIGN KEY (`article_id`) 
        REFERENCES `articles` (`slug`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Insertion des articles 
INSERT INTO `articles` (`slug`, `title`, `category`, `category_label`, `excerpt`, `image_url`, `image_alt`, `image_caption`, `author_name`, `read_time`, `likes`, `published_at`) VALUES
(
    'article-1',
    'Page 14 : Ralentir la course du temps et savourer les matins doux',
    'matin',
    '🌸 Matin Doux',
    'Avant que le tumulte de la journée ne commence, je m\'accorde une heure volée. Une tasse fumante entre les mains, le cœur au repos et quelques lignes tracées pour respirer.',
    'images/minimalisme.svg',
    'Illustration chaleureuse d\'un carnet intime rose et d\'une tasse de café chaud avec de la vapeur en forme de cœur',
    'Le rituel de l\'aurore : une tasse chaude, du silence et une page blanche prête à recevoir mes pensées.',
    'Emma Rose',
    '5 min de lecture',
    14,
    '2026-09-12'
),
(
    'article-2',
    'Page 28 : L\'art du journal intime – Écrire pour guérir et rêver',
    'journaling',
    '✨ Journaling',
    'Poser des mots sur papier, c\'est ranger son âme dans une boîte à trésors. Comment trois exercices de journaling quotidien peuvent transformer ton esprit.',
    'images/polices.svg',
    'Illustration poétique d\'un carnet ouvert avec photo polaroïd, rubans décoratifs et stylo doré',
    'Le pouvoir de l\'encre : transformer ses peines en leçons et ses désirs en projets éclatants.',
    'Emma Rose',
    '6 min de lecture',
    21,
    '2026-09-26'
),
(
    'article-3',
    'Page 42 : Mon cocon du soir – Bougies, douceur et déconnexion',
    'soir',
    '🕯️ Cocon du Soir',
    'Quand la nuit tombe, éteindre le téléphone devient un acte d\'amour propre. Voici ma routine réconfortante pour fermer la porte aux angoisses et glisser vers le sommeil.',
    'images/responsive.svg',
    'Illustration chaleureuse d\'une bougie rose allumée, pile de carnets pastel et lune bienveillante',
    'Le crépuscule des écrans : fermer les yeux du monde pour ouvrir ceux du cœur.',
    'Emma Rose',
    '7 min de lecture',
    18,
    '2026-10-08'
)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 5. Insertion des commentaires 
INSERT INTO `comments` (`article_id`, `author`, `avatar`, `message`, `likes`, `created_at`) VALUES
('article-1', 'Léa', '🌸', 'Quel régal de lire ces mots ce matin avec mon café au lait d\'avoine ! Ce rituel sans écran a complètement transformé mes journées.', 4, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
('article-1', 'Chloé', '✨', 'J\'adore la citation de Saint-Exupéry. Prendre le temps d\'écrire le matin m\'aide tellement à apaiser mes pensées avant d\'aller travailler.', 2, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('article-2', 'Camille', '🎀', 'Les carnets avec washi tapes et stickers, c\'est toute mon enfance et encore ma passion d\'adulte ! Merci pour ces doux exercices de journaling.', 5, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('article-2', 'Sarah', '🐱', 'Le "Brain Dump" de 10 minutes a sauvé mon sommeil plus d\'une fois. Un article si joliment écrit !', 3, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('article-3', 'Juliette', '🕯️', 'L\'odeur de la bougie à la rose et le mode avion à 21h30... je teste ça dès ce soir ! Merci pour cette bulle de bienveillance.', 6, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
('article-3', 'Manon', '☕', 'Un véritable doudou cet article. La palette rose et le texte justifié rendent la lecture tellement apaisante.', 3, DATE_SUB(NOW(), INTERVAL 1 DAY));
