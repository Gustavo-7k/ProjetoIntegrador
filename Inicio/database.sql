-- =============================================
-- ANTHEMS - ESTRUTURA DO BANCO DE DADOS
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;

-- =============================================
-- CRIAÇÃO DAS TABELAS
-- =============================================

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(30) UNIQUE NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email_token` VARCHAR(64),
    `email_verified_at` DATETIME NULL,
    `is_admin` BOOLEAN DEFAULT FALSE,
    `active` BOOLEAN DEFAULT TRUE,
    `newsletter` BOOLEAN DEFAULT FALSE,
    `profile_image` VARCHAR(255) NULL,
    `cover_image` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `terms_accepted_at` DATETIME NOT NULL,
    `last_login` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (`username`),
    INDEX idx_email (`email`),
    INDEX idx_active (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de álbuns
CREATE TABLE IF NOT EXISTS `albums` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `artist` VARCHAR(255) NOT NULL,
    `cover_image` VARCHAR(255),
    `release_year` YEAR,
    `genre` VARCHAR(100),
    `spotify_url` VARCHAR(500),
    `apple_music_url` VARCHAR(500),
    `total_comments` INT DEFAULT 0,
    `average_rating` DECIMAL(2,1) DEFAULT 0.0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_artist (`artist`),
    INDEX idx_genre (`genre`),
    INDEX idx_rating (`average_rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de comentários
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `album_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `comment` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
    `likes_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`album_id`) REFERENCES `albums`(`id`) ON DELETE CASCADE,
    INDEX idx_user_album (`user_id`, `album_id`),
    INDEX idx_status (`status`),
    INDEX idx_created (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de denúncias
CREATE TABLE IF NOT EXISTS `reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reporter_id` INT NOT NULL,
    `comment_id` INT NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `status` ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    `admin_notes` TEXT NULL,
    `reviewed_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX idx_status (`status`),
    INDEX idx_created (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de conexões/seguidores
CREATE TABLE IF NOT EXISTS `connections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `follower_id` INT NOT NULL,
    `following_id` INT NOT NULL,
    `status` ENUM('pending', 'accepted', 'blocked') DEFAULT 'accepted',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`follower_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`following_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_connection` (`follower_id`, `following_id`),
    INDEX idx_follower (`follower_id`),
    INDEX idx_following (`following_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de notificações
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('comment', 'follow', 'like', 'report', 'system') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `related_id` INT NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX idx_user_read (`user_id`, `is_read`),
    INDEX idx_created (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de likes em comentários
CREATE TABLE IF NOT EXISTS `comment_likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `comment_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_like` (`user_id`, `comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- INSERÇÃO DE DADOS DE EXEMPLO
-- =============================================

-- Usuários de exemplo
INSERT INTO `users` (`username`, `full_name`, `email`, `password_hash`, `is_admin`, `email_verified_at`, `bio`, `terms_accepted_at`) VALUES
('admin', 'Administrador Sistema', 'admin@anthems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE, NOW(), 'Administrador do sistema Anthems', NOW()),
('gustavo_schenkel', 'Gustavo Schenkel', 'gustavo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, NOW(), 'Gosto muito de ouvir música, shoegaze, grunge, alternativo, rap, trap, indie', NOW()),
('maria_silva', 'Maria Silva', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, NOW(), 'Apaixonada por música indie e rock alternativo', NOW()),
('carlos_souza', 'Carlos Souza', 'carlos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, NOW(), 'Fã de música eletrônica e hip-hop', NOW()),
('ana_beatriz', 'Ana Beatriz', 'ana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', FALSE, NOW(), 'MPB e jazz são minha paixão', NOW());

-- Álbuns de exemplo
INSERT INTO `albums` (`title`, `artist`, `cover_image`, `release_year`, `genre`) VALUES
('In Rainbows', 'Radiohead', 'InRainbows.jpeg', 2007, 'Alternative Rock'),
('Kid A', 'Radiohead', 'kida.jpeg', 2000, 'Electronic Rock'),
('OK Computer', 'Radiohead', 'okcomputer.jpeg', 1997, 'Alternative Rock'),
('Bury Me At Makeout Creek', 'Mitski', 'NTHMS.png', 2014, 'Indie Rock'),
('Blonde', 'Frank Ocean', 'NTHMS.png', 2016, 'R&B'),
('This Old Dog', 'Mac DeMarco', 'NTHMS.png', 2017, 'Indie Pop'),
('Lush', 'Snail Mail', 'Lush.jpeg', 2018, 'Indie Rock'),
('Ultraviolence', 'Lana Del Rey', 'Ultraviolence.jpeg', 2014, 'Dream Pop'),
('The End', 'My Chemical Romance', 'theEND.jpg', 2006, 'Alternative Rock'),
('Histórias de Kebrada Para Crianças Mal Criadas', 'Link do Zap', 'NTHMS.jpg', 2023, 'Hip Hop');

-- Comentários de exemplo
INSERT INTO `comments` (`user_id`, `album_id`, `rating`, `comment`) VALUES
(2, 1, 5, 'In Rainbows é simplesmente perfeito. Cada faixa é uma jornada emocional única. Radiohead conseguiu criar algo atemporal aqui.'),
(3, 1, 4, 'Um dos melhores álbuns dos anos 2000. A produção é impecável e as melodias são inesquecíveis.'),
(4, 2, 5, 'Kid A mudou completamente minha percepção sobre música eletrônica. Revolucionário e visionário.'),
(2, 3, 5, 'OK Computer ainda é relevante hoje. Uma crítica social brilhante embalada em um som inovador.'),
(5, 4, 4, 'Mitski tem uma forma única de expressar vulnerabilidade. Este álbum me emociona a cada escuta.'),
(3, 5, 5, 'Frank Ocean é um gênio. Blonde é uma obra-prima da música contemporânea.'),
(4, 6, 3, 'Gosto do estilo do Mac DeMarco, mas este álbum não me impactou tanto quanto esperava.'),
(2, 7, 4, 'Snail Mail representa muito bem o indie rock atual. Guitarras cristalinas e letras honestas.'),
(5, 8, 5, 'Lana del Rey em sua melhor forma. Atmosférico e cinematográfico.'),
(3, 10, 4, 'Link do Zap traz uma perspectiva interessante para o hip hop nacional. Letras inteligentes.');

-- Conexões de exemplo
INSERT INTO `connections` (`follower_id`, `following_id`) VALUES
(2, 3), (2, 4), (2, 5),
(3, 2), (3, 4),
(4, 2), (4, 3), (4, 5),
(5, 2), (5, 3);

-- Likes de exemplo
INSERT INTO `comment_likes` (`user_id`, `comment_id`) VALUES
(3, 1), (4, 1), (5, 1),
(2, 2), (4, 2),
(2, 3), (3, 3), (5, 3),
(3, 4), (4, 4), (5, 4);

-- Notificações de exemplo
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `related_id`) VALUES
(2, 'like', 'Novo like', 'Maria Silva curtiu seu comentário sobre In Rainbows', 1),
(2, 'follow', 'Novo seguidor', 'Carlos Souza começou a te seguir', 4),
(3, 'comment', 'Novo comentário', 'Gustavo comentou no álbum In Rainbows', 1),
(1, 'system', 'Bem-vindo!', 'Bem-vindo ao Anthems! Explore e compartilhe sua paixão pela música.', NULL);

-- =============================================
-- ATUALIZAR ESTATÍSTICAS DOS ÁLBUNS
-- =============================================

-- Atualizar contadores de comentários e média de avaliações
UPDATE `albums` a SET 
    `total_comments` = (
        SELECT COUNT(*) 
        FROM `comments` c 
        WHERE c.album_id = a.id AND c.status = 'approved'
    ),
    `average_rating` = (
        SELECT ROUND(AVG(c.rating), 1) 
        FROM `comments` c 
        WHERE c.album_id = a.id AND c.status = 'approved'
    );

-- Atualizar contadores de likes nos comentários
UPDATE `comments` c SET 
    `likes_count` = (
        SELECT COUNT(*) 
        FROM `comment_likes` cl 
        WHERE cl.comment_id = c.id
    );

-- =============================================
-- TRIGGERS PARA MANTER ESTATÍSTICAS
-- =============================================

-- Trigger para atualizar estatísticas do álbum quando um comentário é inserido
DELIMITER $$
CREATE TRIGGER update_album_stats_insert 
AFTER INSERT ON `comments`
FOR EACH ROW
BEGIN
    UPDATE `albums` SET 
        `total_comments` = `total_comments` + 1,
        `average_rating` = (
            SELECT ROUND(AVG(rating), 1) 
            FROM `comments` 
            WHERE album_id = NEW.album_id AND status = 'approved'
        )
    WHERE id = NEW.album_id;
END$$

-- Trigger para atualizar estatísticas do álbum quando um comentário é removido
CREATE TRIGGER update_album_stats_delete 
AFTER DELETE ON `comments`
FOR EACH ROW
BEGIN
    UPDATE `albums` SET 
        `total_comments` = GREATEST(`total_comments` - 1, 0),
        `average_rating` = COALESCE((
            SELECT ROUND(AVG(rating), 1) 
            FROM `comments` 
            WHERE album_id = OLD.album_id AND status = 'approved'
        ), 0)
    WHERE id = OLD.album_id;
END$$

-- Trigger para atualizar contador de likes
CREATE TRIGGER update_comment_likes_insert
AFTER INSERT ON `comment_likes`
FOR EACH ROW
BEGIN
    UPDATE `comments` SET 
        `likes_count` = `likes_count` + 1
    WHERE id = NEW.comment_id;
END$$

CREATE TRIGGER update_comment_likes_delete
AFTER DELETE ON `comment_likes`
FOR EACH ROW
BEGIN
    UPDATE `comments` SET 
        `likes_count` = GREATEST(`likes_count` - 1, 0)
    WHERE id = OLD.comment_id;
END$$

DELIMITER ;

-- =============================================
-- VIEWS ÚTEIS
-- =============================================

-- View para comentários com informações do usuário
CREATE VIEW `comments_with_user` AS
SELECT 
    c.*,
    u.username,
    u.full_name,
    u.profile_image,
    a.title as album_title,
    a.artist as album_artist,
    a.cover_image as album_cover
FROM `comments` c
JOIN `users` u ON c.user_id = u.id
JOIN `albums` a ON c.album_id = a.id
WHERE c.status = 'approved';

-- View para estatísticas de usuários
CREATE VIEW `user_stats` AS
SELECT 
    u.id,
    u.username,
    u.full_name,
    COUNT(DISTINCT c.id) as total_comments,
    COUNT(DISTINCT f1.following_id) as following_count,
    COUNT(DISTINCT f2.follower_id) as followers_count,
    AVG(c.rating) as average_rating_given
FROM `users` u
LEFT JOIN `comments` c ON u.id = c.user_id
LEFT JOIN `connections` f1 ON u.id = f1.follower_id
LEFT JOIN `connections` f2 ON u.id = f2.following_id
WHERE u.active = TRUE
GROUP BY u.id, u.username, u.full_name;

-- =============================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =============================================

-- Índices compostos para consultas frequentes
CREATE INDEX idx_comments_album_status ON `comments` (`album_id`, `status`);
CREATE INDEX idx_comments_user_created ON `comments` (`user_id`, `created_at`);
CREATE INDEX idx_notifications_user_read_created ON `notifications` (`user_id`, `is_read`, `created_at`);

COMMIT;

-- =============================================
-- INFORMAÇÕES DE LOGIN DE TESTE
-- =============================================

/*
USUÁRIOS DE TESTE:

Admin:
- Email: admin@anthems.com
- Senha: password
- Username: admin

Usuários regulares:
- Email: gustavo@example.com / Senha: password / Username: gustavo_schenkel
- Email: maria@example.com / Senha: password / Username: maria_silva
- Email: carlos@example.com / Senha: password / Username: carlos_souza
- Email: ana@example.com / Senha: password / Username: ana_beatriz

Nota: Todas as senhas são 'password' (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
*/
