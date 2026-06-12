<?php
// blockchain/Block.php - Clase para cada bloque
class Block {
    public $index;
    public $timestamp;
    public $data;
    public $previousHash;
    public $hash;
    public $nonce;
    
    public function __construct($index, $timestamp, $data, $previousHash = '') {
        $this->index = $index;
        $this->timestamp = $timestamp;
        $this->data = $data;
        $this->previousHash = $previousHash;
        $this->nonce = 0;
        $this->hash = $this->calculateHash();
    }
    
    // Calcular el hash del bloque (SHA-256)
    public function calculateHash() {
        return hash('sha256', 
            $this->index . 
            $this->timestamp . 
            json_encode($this->data) . 
            $this->previousHash . 
            $this->nonce
        );
    }
    
    // Prueba de trabajo (Proof of Work) - minar el bloque
    public function mineBlock($difficulty) {
        $target = str_repeat("0", $difficulty);
        while(substr($this->hash, 0, $difficulty) !== $target) {
            $this->nonce++;
            $this->hash = $this->calculateHash();
        }
        // No usar echo aquí: contamina las respuestas JSON de la API
    }
}
?>