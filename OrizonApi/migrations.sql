CREATE TABLE paesi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL UNIQUE
);
CREATE TABLE viaggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    posti_disponibili INT NOT NULL CHECK (posti_disponibili >= 0)
);

CREATE TABLE viaggi_nei_paesi (
    id_viaggio INT,
    id_paese INT,
    PRIMARY KEY (id_viaggio, id_paese),
    FOREIGN KEY (id_viaggio) REFERENCES viaggi(id) ON DELETE CASCADE,
    FOREIGN KEY (id_paese) REFERENCES paese(id) ON DELETE CASCADE
);
