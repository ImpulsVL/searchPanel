CREATE DATABASE blog;

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    title VARCHAR(255),
    body TEXT
);

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    postId INT NOT NULL,
    name VARCHAR(255),
    email VARCHAR(255),
    body TEXT,
    FOREIGN KEY (postId) REFERENCES posts (id)
);