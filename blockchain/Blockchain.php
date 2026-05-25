<?php
// blockchain/Blockchain.php - Cadena de bloques principal
require_once __DIR__ . '/Block.php';

class Blockchain {
    private $conn;
    private $difficulty;
    
    public function __construct($conn, $difficulty = 2) {
        $this->conn = $conn;
        $this->difficulty = $difficulty;
        
        // Verificar si existe el bloque génesis
        $this->crearBloqueGenesisSiNoExiste();
    }
    
    // Crear bloque génesis si no existe
    private function crearBloqueGenesisSiNoExiste() {
        $sql = "SELECT COUNT(*) as total FROM blockchain_votos WHERE indice = 0";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['total'] == 0) {
            $genesisBlock = new Block(0, time(), ["mensaje" => "Bloque Genesis - Sistema de Votacion Yo Voto"], "0");
            $genesisBlock->mineBlock($this->difficulty);
            
            $sql = "INSERT INTO blockchain_votos (indice, timestamp, datos_voto, hash_anterior, hash_bloque, nonce) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $datosJson = json_encode($genesisBlock->data);
            $stmt->bind_param("iisssi", 
                $genesisBlock->index, 
                $genesisBlock->timestamp, 
                $datosJson, 
                $genesisBlock->previousHash, 
                $genesisBlock->hash, 
                $genesisBlock->nonce
            );
            $stmt->execute();
        }
    }
    
    // Obtener el último bloque
    public function getLastBlock() {
        $sql = "SELECT * FROM blockchain_votos ORDER BY indice DESC LIMIT 1";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    
    // Registrar un voto en la blockchain
    public function registrarVoto($id_usuario, $id_candidato, $carnet, $hashHuella = null) {
        $lastBlock = $this->getLastBlock();
        $newIndex = $lastBlock['indice'] + 1;
        
        // Crear los datos del voto (ANONIMIZADOS para privacidad)
        $votoData = [
            "voto_id" => $newIndex,
            "id_usuario" => $this->hashUsuario($id_usuario),
            "id_candidato" => $id_candidato,
            "carnet_hash" => hash('sha256', $carnet . "SALT_SECRETO_VOTO"),
            "timestamp_voto" => time(),
            "huella_hash" => $hashHuella ? hash('sha256', $hashHuella) : null
        ];
        
        // Crear el bloque
        $newBlock = new Block($newIndex, time(), $votoData, $lastBlock['hash_bloque']);
        $newBlock->mineBlock($this->difficulty);
        
        // Guardar en la base de datos
        $sql = "INSERT INTO blockchain_votos (indice, timestamp, datos_voto, hash_anterior, hash_bloque, nonce) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $datosJson = json_encode($newBlock->data);
        $stmt->bind_param("iisssi", 
            $newBlock->index, 
            $newBlock->timestamp, 
            $datosJson, 
            $newBlock->previousHash, 
            $newBlock->hash, 
            $newBlock->nonce
        );
        
        if ($stmt->execute()) {
            return $newBlock;
        }
        return false;
    }
    
    // Verificar que la blockchain es válida
    public function isChainValid() {
        $sql = "SELECT * FROM blockchain_votos ORDER BY indice ASC";
        $result = $this->conn->query($sql);
        $blocks = $result->fetch_all(MYSQLI_ASSOC);
        
        for ($i = 1; $i < count($blocks); $i++) {
            $currentBlock = $blocks[$i];
            $previousBlock = $blocks[$i-1];
            
            // Verificar hash del bloque actual
            $calculatedHash = $this->calcularHashDesdeDatos($currentBlock);
            if ($currentBlock['hash_bloque'] !== $calculatedHash) {
                return false;
            }
            
            // Verificar que apunta al bloque anterior correcto
            if ($currentBlock['hash_anterior'] !== $previousBlock['hash_bloque']) {
                return false;
            }
        }
        return true;
    }
    
    // Calcular hash desde los datos guardados
    private function calcularHashDesdeDatos($block) {
        $datosVoto = json_decode($block['datos_voto'], true);
        return hash('sha256', 
            $block['indice'] . 
            $block['timestamp'] . 
            json_encode($datosVoto) . 
            $block['hash_anterior'] . 
            $block['nonce']
        );
    }
    
    // Obtener estadísticas de la blockchain
    public function getEstadisticas() {
        $sql = "SELECT COUNT(*) as total_bloques FROM blockchain_votos";
        $result = $this->conn->query($sql);
        $totalBloques = $result->fetch_assoc()['total_bloques'];
        
        $sql = "SELECT COUNT(*) as total_votos FROM blockchain_votos WHERE indice > 0";
        $result = $this->conn->query($sql);
        $totalVotos = $result->fetch_assoc()['total_votos'];
        
        $sql = "SELECT hash_bloque FROM blockchain_votos ORDER BY indice DESC LIMIT 1";
        $result = $this->conn->query($sql);
        $ultimoHash = $result->fetch_assoc()['hash_bloque'] ?? 'N/A';
        
        $esValida = $this->isChainValid();
        
        return [
            'total_bloques' => $totalBloques,
            'total_votos' => $totalVotos - 1, // Restar bloque génesis
            'ultimo_hash' => $ultimoHash,
            'cadena_valida' => $esValida,
            'dificultad' => $this->difficulty
        ];
    }
    
    // Obtener la cadena completa (para depuración)
    public function getFullChain() {
        $sql = "SELECT * FROM blockchain_votos ORDER BY indice ASC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Hashear ID de usuario para anonimizar
    private function hashUsuario($id) {
        $salt = "YO_VOTO_SECRET_2026";
        return hash('sha256', $salt . $id);
    }
}
?>