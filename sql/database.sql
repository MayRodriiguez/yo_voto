-- =====================================================
-- YO VOTO - Schema de Base de Datos
-- =====================================================

CREATE DATABASE IF NOT EXISTS yo_voto010 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yo_voto010;

-- Tabla de usuarios (ciudadanos y administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_registro VARCHAR(20) UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    carnet VARCHAR(15) UNIQUE NOT NULL,
    fecha_nacimiento DATE,
    direccion VARCHAR(255),
    telefono VARCHAR(20),
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255) NOT NULL,
    foto_url VARCHAR(255) DEFAULT 'uploads/img/sin_foto.jpg',
    departamento VARCHAR(50),
    rol ENUM('usuario', 'admin') DEFAULT 'usuario',
    habilitado_voto TINYINT(1) DEFAULT 0,
    ya_voto TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    reset_code VARCHAR(10),
    reset_expira DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de candidatos
CREATE TABLE IF NOT EXISTS candidatos (
    id_candidato INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    partido VARCHAR(150),
    foto_url VARCHAR(255) DEFAULT 'uploads/img/sin_foto.jpg',
    descripcion TEXT,
    tipo ENUM('nacional', 'subnacional') DEFAULT 'nacional',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    votos_recibidos INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de votos
CREATE TABLE IF NOT EXISTS votos (
    id_voto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_candidato INT NOT NULL,
    fecha_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_candidato) REFERENCES candidatos(id_candidato) ON DELETE CASCADE,
    UNIQUE KEY unique_voto_usuario (id_usuario)
) ENGINE=InnoDB;

-- Tabla de equipos de candidatos
CREATE TABLE IF NOT EXISTS equipo (
    id_integrante INT AUTO_INCREMENT PRIMARY KEY,
    id_candidato INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    cargo VARCHAR(100),
    foto_url VARCHAR(255) DEFAULT 'uploads/img/sin_foto.jpg',
    nivel INT DEFAULT 1,
    FOREIGN KEY (id_candidato) REFERENCES candidatos(id_candidato) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de propuestas
CREATE TABLE IF NOT EXISTS propuestas (
    id_propuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_candidato INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_candidato) REFERENCES candidatos(id_candidato) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de configuración del sistema
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de blockchain de votos
CREATE TABLE IF NOT EXISTS blockchain_votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    indice INT NOT NULL,
    timestamp BIGINT NOT NULL,
    datos_voto JSON,
    hash_anterior VARCHAR(64) NOT NULL,
    hash_bloque VARCHAR(64) NOT NULL,
    nonce INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_indice (indice),
    UNIQUE KEY unique_hash (hash_bloque)
) ENGINE=InnoDB;

-- =====================================================
-- Datos iniciales de configuración
-- =====================================================
INSERT IGNORE INTO configuracion (clave, valor) VALUES
    ('votacion_activa', '0'),
    ('fecha_votacion', ''),
    ('hora_apertura', '08:00'),
    ('hora_cierre', '18:00'),
    ('tipo_candidatos_activo', 'nacional');

-- Usuario administrador por defecto (password: Admin2026!)
INSERT IGNORE INTO usuarios (numero_registro, nombres, apellidos, carnet, email, password, rol, habilitado_voto, activo)
VALUES (
    'REG-000001',
    'Administrador',
    'Sistema',
    '00000000',
    'admin@yovoto.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    'admin',
    0,
    1
);