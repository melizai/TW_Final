CREATE TABLE users
(
    id       int NOT NULL AUTO_INCREMENT,
    type     varchar(45)  NOT NULL,
    username varchar(45)  NOT NULL unique,
    password varchar(45)  NOT NULL,
    email    varchar(45)  NOT NULL unique,
    country  varchar(45)  NOT NULL,
    age      int          NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE shows
(
    id           INT NOT NULL AUTO_INCREMENT,
    type         VARCHAR(45) NULL,
    title        VARCHAR(200) NOT NULL,
    director     VARCHAR(500) NULL,
    cast         LONGTEXT NULL,
    country      VARCHAR(200) NULL,
    release_year INT NULL,
    rating       VARCHAR(45) NULL,
    duration     VARCHAR(45) NULL,
    description  LONGTEXT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE reviews
(
    id      INT NOT NULL AUTO_INCREMENT,
    show_id INT NOT NULL,
    user_id INT NOT NULL,
    text    TEXT(1000) NULL,
    stars   INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (show_id) REFERENCES `shows`(`id`),
    FOREIGN KEY (user_id) REFERENCES `users`(`id`)
);

CREATE TABLE statistics
(
    id      INT NOT NULL AUTO_INCREMENT,
    show_id INT NOT NULL,
    user_id INT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (show_id) REFERENCES `shows`(`id`),
    FOREIGN KEY (user_id) REFERENCES `users`(`id`)
);

CREATE TABLE newsletter
(
    id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(45) NOT NULL unique,
    name  VARCHAR(45) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE genres
(
    id   INT         NOT NULL AUTO_INCREMENT,
    name VARCHAR(45) NOT NULL unique,
    PRIMARY KEY (id)
);

CREATE TABLE show_genres
(
    id       INT NOT NULL AUTO_INCREMENT,
    show_id  INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (show_id) REFERENCES `shows`(`id`),
    FOREIGN KEY (genre_id) REFERENCES `genres`(`id`)
);
