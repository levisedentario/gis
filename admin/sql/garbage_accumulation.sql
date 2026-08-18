CREATE TABLE garbage_accumulation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manhole VARCHAR(10) NOT NULL UNIQUE,
    nbase DECIMAL(5,3) NOT NULL,
    nmodified DECIMAL(5,3) NOT NULL,
    ngarbage DECIMAL(5,3) NOT NULL,
    flood_susceptibility ENUM('Low', 'Moderate', 'High') NOT NULL
);