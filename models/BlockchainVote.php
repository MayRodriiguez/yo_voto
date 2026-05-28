<?php
// models/BlockchainVote.php - Modelo para integrar blockchain con votos
require_once __DIR__ . '/../blockchain/Blockchain.php';

class BlockchainVote {
    private $conn;
    private $blockchain;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->blockchain = new Blockchain($conn, 2); // Dificultad 2 para minado rápido
    }
    
    // Registrar un voto en la blockchain (sobrescribe el método normal)
    public function registrarVotoBlockchain($id_usuario, $id_candidato, $carnet, $hashHuella = null) {
        // Primero registrar en la tabla normal de votos (por compatibilidad)
        $sqlVoto = "INSERT INTO votos (id_usuario, id_candidato) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sqlVoto);
        $stmt->bind_param("ii", $id_usuario, $id_candidato);
        
        if (!$stmt->execute()) {
            return ['success' => false, 'error' => 'Error al registrar voto en BD'];
        }
        
        // Actualizar contador de candidato
        $stmtCand = $this->conn->prepare("UPDATE candidatos SET votos_recibidos = votos_recibidos + 1 WHERE id_candidato = ?");
        $stmtCand->bind_param("i", $id_candidato);
        $stmtCand->execute();

        // Actualizar estado del usuario
        $stmtUser = $this->conn->prepare("UPDATE usuarios SET ya_voto = 1 WHERE id = ?");
        $stmtUser->bind_param("i", $id_usuario);
        $stmtUser->execute();
        
        // Registrar en la blockchain
        $bloque = $this->blockchain->registrarVoto($id_usuario, $id_candidato, $carnet, $hashHuella);
        
        if ($bloque) {
            return [
                'success' => true, 
                'bloque' => [
                    'indice' => $bloque->index,
                    'hash' => $bloque->hash,
                    'timestamp' => $bloque->timestamp
                ]
            ];
        }
        
        return ['success' => false, 'error' => 'Error al registrar en blockchain'];
    }
    
    // Verificar la integridad de la cadena
    public function verificarIntegridad() {
        return $this->blockchain->isChainValid();
    }
    
    // Obtener estadísticas de la blockchain
    public function getEstadisticas() {
        return $this->blockchain->getEstadisticas();
    }
    
    // Obtener todos los bloques
    public function getFullChain() {
        return $this->blockchain->getFullChain();
    }
    
    // Verificar un voto específico por su hash
    public function verificarVotoPorHash($hash) {
        $sql = "SELECT * FROM blockchain_votos WHERE hash_bloque = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $hash);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>