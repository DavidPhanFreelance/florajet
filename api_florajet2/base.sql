CREATE TABLE source (
    id int NOT NULL auto_increment,
    name varchar(255),
    PRIMARY KEY(id)
);
CREATE TABLE article (
    id int NOT NULL auto_increment,
    source_id int NOT NULL,
    name varchar(255),
    content BLOB,
    PRIMARY KEY(id)
);

INSERT INTO source VALUES (1, 'src-1');
INSERT INTO source VALUES (2, 'src-2');

INSERT INTO article VALUES (1, 1, 'Politique', 'Lorem ipsum dolor sit amet 1', '2024-03-11 00:00:00', 'author1');
INSERT INTO article VALUES (2, 2, 'Economie', 'Lorem ipsum dolor sit amet 2', '2024-03-12 00:00:00', 'author2');
INSERT INTO article VALUES (3, 2, 'Ecologie', 'Lorem ipsum dolor sit amet 3', '2024-03-17 00:00:00', 'author3');
INSERT INTO article VALUES (4, 1, 'Jeu Video', 'Lorem ipsum dolor sit amet 4', '2024-03-19 00:00:00', 'author4');
