-- MarkGigs Phase One Database Schema
-- Import into XAMPP phpMyAdmin: markgig_db
-- Run: SOURCE schema.sql

SET FOREIGN_KEY_CHECKS = 0;

-- ── Core Authentication ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT           NOT NULL AUTO_INCREMENT,
    email         VARCHAR(255)  NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('individual','company','admin') NOT NULL DEFAULT 'individual',
    is_verified   TINYINT(1)    NOT NULL DEFAULT 0,
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Individual Profiles (students, alumni, professionals) ─────────────────────
CREATE TABLE IF NOT EXISTS individuals (
    id                   INT           NOT NULL AUTO_INCREMENT,
    user_id              INT           NOT NULL,
    full_name            VARCHAR(255)  NOT NULL,
    headline             VARCHAR(255)  DEFAULT NULL,
    bio                  TEXT          DEFAULT NULL,
    avatar               VARCHAR(500)  DEFAULT NULL,
    institution          VARCHAR(255)  DEFAULT NULL,
    course               VARCHAR(255)  DEFAULT NULL,
    graduation_year      YEAR          DEFAULT NULL,
    location             VARCHAR(255)  DEFAULT NULL,
    website              VARCHAR(500)  DEFAULT NULL,
    skills               TEXT          DEFAULT NULL,
    achievements_academic TEXT         DEFAULT NULL,
    achievements_field    TEXT         DEFAULT NULL,
    research_implementations TEXT      DEFAULT NULL,
    is_mentor            TINYINT(1)    NOT NULL DEFAULT 0,
    mentor_expertise     VARCHAR(500)  DEFAULT NULL,
    mentor_availability  ENUM('available','limited','unavailable') DEFAULT 'unavailable',
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_individuals_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Company Profiles ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS companies (
    id          INT           NOT NULL AUTO_INCREMENT,
    user_id     INT           NOT NULL,
    name        VARCHAR(255)  NOT NULL,
    industry    VARCHAR(255)  DEFAULT NULL,
    website     VARCHAR(500)  DEFAULT NULL,
    description TEXT          DEFAULT NULL,
    logo        VARCHAR(500)  DEFAULT NULL,
    location    VARCHAR(255)  DEFAULT NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_companies_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Follow System ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS follows (
    id          INT       NOT NULL AUTO_INCREMENT,
    follower_id INT       NOT NULL,
    following_id INT      NOT NULL,
    followed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_follow (follower_id, following_id),
    FOREIGN KEY (follower_id)  REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Connection Requests ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS connections (
    id           INT       NOT NULL AUTO_INCREMENT,
    requester_id INT       NOT NULL,
    receiver_id  INT       NOT NULL,
    status       ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_connection (requester_id, receiver_id),
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id)  REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Activity Feed Posts ───────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS posts (
    id          INT       NOT NULL AUTO_INCREMENT,
    author_id   INT       NOT NULL,
    content     TEXT      NOT NULL,
    post_type   ENUM('update','achievement','question') NOT NULL DEFAULT 'update',
    likes_count INT       NOT NULL DEFAULT 0,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_likes (
    id         INT       NOT NULL AUTO_INCREMENT,
    user_id    INT       NOT NULL,
    post_id    INT       NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_post_like (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Opportunities ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS opportunities (
    id           INT           NOT NULL AUTO_INCREMENT,
    company_id   INT           NOT NULL,
    title        VARCHAR(255)  NOT NULL,
    type         ENUM('internship','job','gig') NOT NULL,
    description  TEXT          NOT NULL,
    requirements TEXT          DEFAULT NULL,
    location     VARCHAR(255)  DEFAULT NULL,
    is_remote    TINYINT(1)    NOT NULL DEFAULT 0,
    status       ENUM('open','closed') NOT NULL DEFAULT 'open',
    deadline     DATE          DEFAULT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Applications ──────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS applications (
    id             INT       NOT NULL AUTO_INCREMENT,
    individual_id  INT       NOT NULL,
    opportunity_id INT       NOT NULL,
    cover_letter   TEXT      DEFAULT NULL,
    resume_path    VARCHAR(500) DEFAULT NULL,
    status         ENUM('pending','reviewed','accepted','rejected') NOT NULL DEFAULT 'pending',
    applied_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_application (individual_id, opportunity_id),
    FOREIGN KEY (individual_id)  REFERENCES individuals(id) ON DELETE CASCADE,
    FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Mentorships ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS mentorships (
    id            INT       NOT NULL AUTO_INCREMENT,
    student_id    INT       NOT NULL,
    mentor_id     INT       NOT NULL,
    topic         VARCHAR(255) DEFAULT NULL,
    message       TEXT      DEFAULT NULL,
    status        ENUM('pending','active','completed','declined') NOT NULL DEFAULT 'pending',
    requested_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES individuals(id) ON DELETE CASCADE,
    FOREIGN KEY (mentor_id)  REFERENCES individuals(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Direct Messages ───────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS messages (
    id          INT       NOT NULL AUTO_INCREMENT,
    sender_id   INT       NOT NULL,
    receiver_id INT       NOT NULL,
    content     TEXT      NOT NULL,
    sent_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read     TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Phase Two (idle — declared for schema completeness) ───────────────────────
CREATE TABLE IF NOT EXISTS items (
    id          INT            NOT NULL AUTO_INCREMENT,
    seller_id   INT            NOT NULL,
    title       VARCHAR(255)   NOT NULL,
    description TEXT           DEFAULT NULL,
    price       DECIMAL(10,2)  NOT NULL,
    category    ENUM('Electronics', 'Books', 'Clothing', 'Services', 'Other') DEFAULT 'Other',
    image_url   VARCHAR(500)   DEFAULT NULL,
    status      ENUM('active','sold') DEFAULT 'active',
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gigs (
    id          INT            NOT NULL AUTO_INCREMENT,
    seller_id   INT            DEFAULT NULL,
    title       VARCHAR(255)   DEFAULT NULL,
    description TEXT           DEFAULT NULL,
    price       DECIMAL(10,2)  DEFAULT NULL,
    category    VARCHAR(255)   DEFAULT NULL,
    status      ENUM('active','paused') DEFAULT 'active',
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
