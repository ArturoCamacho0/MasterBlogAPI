CREATE DATABASE IF NOT EXISTS blogmaster;

USE blogmaster;

CREATE TABLE IF NOT EXISTS users(
    id_user INT(255) AUTO_INCREMENT,
    name_user VARCHAR(50) NOT NULL,
    surname_user VARCHAR(100),
    role_user VARCHAR(20),
    email_user VARCHAR(255) NOT NULL,
    password_user VARCHAR(255) NOT NULL,
    description_user TEXT,
    image_user VARCHAR(255),
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    remember_token VARCHAR(255),

    CONSTRAINT pk_user PRIMARY KEY (id_user)
)ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS categories(
    id_category INT(255) AUTO_INCREMENT,
    name_category VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,

    CONSTRAINT pk_category PRIMARY KEY (id_category)
)ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS posts(
    id_post INT(255) AUTO_INCREMENT,
    content_post TEXT NOT NULL,
    image_post VARCHAR(255),
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    user_id INT(255) NOT NULL,
    category_id INT(255) NOT NULL,

    CONSTRAINT pk_post PRIMARY KEY (id_post),
    CONSTRAINT fk_post_user FOREIGN KEY (user_id) REFERENCES users(id_user),
    CONSTRAINT fk_post_category FOREIGN KEY (category_id) REFERENCES categories(id_category)
)ENGINE=InnoDB;