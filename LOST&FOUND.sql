CREATE DATABASE IF NOT EXISTS lostfound_db;
USE lostfound_db;

-- Tabella UTENTI
CREATE TABLE UTENTI (
    idUtente INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    password VARCHAR(12) NOT NULL,
    ban TINYINT(1) NOT NULL DEFAULT 0
);

-- Tabella PORTAFOGLI
CREATE TABLE PORTAFOGLI (
    IBAN VARCHAR(34) PRIMARY KEY,
    saldo DECIMAL(10,2) NOT NULL,
    IdUtente INT UNIQUE, 
    FOREIGN KEY (idUtente) REFERENCES Utenti(idUtente)
);

-- Tabella AMMINISTRATORI
CREATE TABLE AMMINISTRATORI (
    idAmministratore INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL UNIQUE,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    password VARCHAR(12) NOT NULL
);

-- Tabella LUOGHI
CREATE TABLE LUOGHI (
    indirizzo VARCHAR(50) NOT NULL,
    cap VARCHAR(10) NOT NULL,
    citta VARCHAR(50) NOT NULL, #tipo tolto
    PRIMARY KEY (indirizzo, cap)
);

-- Tabella RICOMPENSE
CREATE TABLE RICOMPENSE (
    idRicompensa INT AUTO_INCREMENT PRIMARY KEY,
    importo DECIMAL(10,2) NOT NULL
);

-- Tabella CATEGORIE
CREATE TABLE CATEGORIE (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    tipoCategoria VARCHAR(50) NOT NULL
);

-- Tabella OGGETTI
CREATE TABLE OGGETTI (
    idOggetto INT AUTO_INCREMENT PRIMARY KEY,
    descrizioneOggetto TEXT NOT NULL,
    idCategoria INT NOT NULL,
    FOREIGN KEY (idCategoria) REFERENCES CATEGORIE(idCategoria)
);

-- Tabella SEGNALAZIONI
CREATE TABLE SEGNALAZIONI (
    idSegnalazione INT AUTO_INCREMENT PRIMARY KEY,
    idUtente INT NOT NULL,
    indirizzo VARCHAR(50) NOT NULL,
    cap VARCHAR(10) NOT NULL,
    data DATE NOT NULL,
    ora TIME NOT NULL,
    descrizioneSegnalazione TEXT NOT NULL,
    stato VARCHAR(50) NOT NULL,
    tipoSegnalazione TINYINT(1) NOT NULL, -- 0 = smarrimento, 1 = ritrovamento
    idRicompensa INT,
    idOggetto INT,
    FOREIGN KEY (idUtente) REFERENCES UTENTI(idUtente),
    FOREIGN KEY (indirizzo, cap) REFERENCES LUOGHI(indirizzo, cap),
    FOREIGN KEY (idRicompensa) REFERENCES RICOMPENSE(idRicompensa),
    FOREIGN KEY (idOggetto) REFERENCES OGGETTI(idOggetto)
);

-- Tabella FOTO
CREATE TABLE FOTO (
    idSegnalazione INT NOT NULL,
    idFoto INT AUTO_INCREMENT PRIMARY KEY,
    nomeFoto VARCHAR(50) NOT NULL,
    FOREIGN KEY (idSegnalazione) REFERENCES SEGNALAZIONI(idSegnalazione)
        ON DELETE CASCADE
);

-- Tabella RESTITUZIONI
CREATE TABLE RESTITUZIONI (
    idRestituzione INT AUTO_INCREMENT PRIMARY KEY,
    data DATE NOT NULL,
    ora TIME NOT NULL,
    indirizzo VARCHAR(50) NOT NULL,
    cap VARCHAR(10) NOT NULL,
    idAmministratore INT NOT NULL,
    idSegnalazione1 INT NOT NULL,
    idSegnalazione2 INT NOT NULL,
    FOREIGN KEY (idAmministratore) REFERENCES AMMINISTRATORI(idAmministratore),
    FOREIGN KEY (indirizzo, cap) REFERENCES LUOGHI(indirizzo, cap), #modifica 
    FOREIGN KEY (idSegnalazione1) REFERENCES SEGNALAZIONI(idSegnalazione),
    FOREIGN KEY (idSegnalazione2) REFERENCES SEGNALAZIONI(idSegnalazione)
);

-- Tabella RISPOSTE_VERIFICA
CREATE TABLE RISPOSTE_VERIFICA (
    idRisposta INT AUTO_INCREMENT PRIMARY KEY,
    testo TEXT NOT NULL,
    idUtente INT NOT NULL,
    FOREIGN KEY (idUtente) REFERENCES UTENTI(idUtente)
);

-- Tabella DOMANDE_VERIFICA
CREATE TABLE DOMANDE_VERIFICA (
    idDomanda INT AUTO_INCREMENT PRIMARY KEY,
    testo TEXT NOT NULL,
    idRisposta INT,
    idSegnalazione INT NOT NULL,
    idAmministratore INT NOT NULL,
    FOREIGN KEY (idRisposta) REFERENCES RISPOSTE_VERIFICA(idRisposta),
    FOREIGN KEY (idSegnalazione) REFERENCES SEGNALAZIONI(idSegnalazione),
    FOREIGN KEY (idAmministratore) REFERENCES AMMINISTRATORI(idAmministratore)
);


-- Inserimento dati di test

-- Inserimento categorie (solo le 4 originali)
INSERT INTO amministratori (email, nome, cognome, password) VALUES
('admin1@email.com', 'Maicol', 'Marozzi', 'adminpass123'),
('admin2@email.com', 'Pippo', 'Baudo', 'adminpass1234');

INSERT INTO categorie (tipoCategoria) VALUES
('abbigliamento'),
('elettronica'),
('accessori'),
('altro');

-- Inserimento oggetti (adattati alle 4 categorie)
INSERT INTO oggetti (descrizioneOggetto, idCategoria) VALUES
('Giacca nera', 1),
('Portafoglio in pelle', 3),
('Smartphone Samsung', 2),
('Occhiali da sole', 3),
('Libro "Il nome della rosa"', 4),
('Orologio da polso', 3),
('Tablet iPad', 2),
('Cappello di lana', 1),
('Borsa a tracolla', 3),
('Macchina fotografica', 2),
('Ombrello nero', 4),
('Guanti in pelle', 1),
('Auricolari wireless', 2),
('Collana d\'argento', 3),
('Agenda personale', 4);

-- Inserimento luoghi
INSERT INTO luoghi (indirizzo, cap, citta) VALUES
('Via Roma 10', '00100', 'Roma'),
('Piazza Duomo 5', '20100', 'Milano'),
('Viale dei Mille 22', '80100', 'Napoli'),
('Corso Italia 15', '16100', 'Genova'),
('Via Garibaldi 33', '10100', 'Torino'),
('Largo Augusto 8', '50100', 'Firenze'),
('Piazza San Marco 1', '30100', 'Venezia'),
('Via Appia Nuova 145', '00100', 'Roma');

-- Inserimento utenti
INSERT INTO utenti (email, nome, cognome, password, ban) VALUES
('mario.rossi@email.com', 'Mario', 'Rossi', 'password123', 0),
('laura.bianchi@email.com', 'Laura', 'Bianchi', 'password1234', 0),
('giorgio.verdi@email.com', 'Giorgio', 'Verdi', 'password12345', 0),
('sara.neri@email.com', 'Sara', 'Neri', 'password123456', 0),
('luca.gialli@email.com', 'Luca', 'Gialli', 'password0123', 0);

-- Inserimento portafogli
INSERT INTO portafogli (IBAN, saldo, idUtente) VALUES
('IT60X0542811101000000123456', 50.00, 1),
('IT70X0542811101000000654321', 30.00, 2),
('IT80X0542811101000000789456', 75.00, 3),
('IT90X0542811101000000321456', 40.00, 4),
('IT10X0542811101000000963258', 60.00, 5);

-- Inserimento ricompense (solo per alcune segnalazioni)
INSERT INTO ricompense (importo) VALUES
(10.00),
(5.00),
(15.00),
(20.00),
(40.00),
(15.00),
(35.00),
(8.00),
(12.00),
(25.00);

-- Inserimento 15 segnalazioni (8 smarriti con alcune ricompense, 7 ritrovati)
INSERT INTO segnalazioni (idUtente, indirizzo, cap, data, ora, descrizioneSegnalazione, stato, tipoSegnalazione, idRicompensa, idOggetto) VALUES
-- OGGETTI SMARITI (tipoSegnalazione = 0) - alcune con ricompensa
(1, 'Via Roma 10', '00100', '2023-06-01', '20:15:00', 'Giacca nera smarrita al bar', 'in attesa', 0, 1, 1),
(2, 'Piazza Duomo 5', '20100', '2023-06-02', '09:15:00', 'Portafoglio perso a scuola', 'in attesa', 0, 2, 2),
(3, 'Viale dei Mille 22', '80100', '2023-06-03', '16:45:00', 'Smartphone caduto al parco', 'in attesa', 0, 3, 3),
(4, 'Corso Italia 15', '16100', '2023-06-04', '11:20:00', 'Occhiali da sole smarriti in biblioteca', 'in attesa', 0, NULL, 4),
(5, 'Via Garibaldi 33', '10100', '2023-06-05', '19:30:00', 'Libro dimenticato al ristorante', 'in attesa', 0, NULL, 5),
(1, 'Largo Augusto 8', '50100', '2023-06-06', '15:10:00', 'Orologio perso al museo', 'in attesa', 0, 6, 6),
(2, 'Piazza San Marco 1', '30100', '2023-06-07', '10:45:00', 'Tablet iPad lasciato in piazza', 'in attesa', 0, 7, 7),
(3, 'Via Appia Nuova 145', '00100', '2023-06-08', '18:20:00', 'Cappello dimenticato al centro commerciale', 'in attesa', 0, NULL, 8),

-- OGGETTI RITROVATI (tipoSegnalazione = 1) - nessuna ricompensa
(4, 'Viale dei Mille 22', '80100', '2023-06-01', '14:30:00', 'Trovata borsa a tracolla al parco', 'in attesa', 1, NULL, 9),
(5, 'Piazza Duomo 5', '20100', '2023-06-02', '11:15:00', 'Trovata macchina fotografica a scuola', 'in attesa', 1, NULL, 10),
(1, 'Corso Italia 15', '16100', '2023-06-03', '16:00:00', 'Trovato ombrello in biblioteca', 'in attesa', 1, NULL, 11),
(2, 'Via Roma 10', '00100', '2023-06-04', '20:45:00', 'Trovati guanti in pelle al bar', 'in attesa', 1, NULL, 12),
(3, 'Largo Augusto 8', '50100', '2023-06-05', '14:10:00', 'Trovati auricolari al museo', 'in attesa', 1, NULL, 13),
(4, 'Piazza San Marco 1', '30100', '2023-06-06', '09:30:00', 'Trovata collana in piazza', 'in attesa', 1, NULL, 14),
(5, 'Via Appia Nuova 145', '00100', '2023-06-07', '17:50:00', 'Trovata agenda al centro commerciale', 'in attesa', 1, NULL, 15);

-- Inserimento foto
INSERT INTO foto (idSegnalazione, nomeFoto) VALUES
(1, 'giacca_nera.jpg'),
(2, 'portafoglio.jpg'),
(3, 'samsung_galaxy.jpg'),
(4, 'occhiali_sole.jpg'),
(5, 'libro_nome_rosa.jpg'),
(6, 'orologio.jpg'),
(7, 'ipad.jpg'),
(8, 'cappello_lana.jpg'),
(9, 'borsa_tracolla.jpg'),
(10, 'fotocamera.jpg'),
(11, 'ombrello_nero.jpg'),
(12, 'guanti_pelle.jpg'),
(13, 'auricolari.jpg'),
(14, 'collana_argento.jpg'),
(15, 'agenda.jpg');