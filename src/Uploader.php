<?php
/**
 * Clase para el manejo seguro de subida de archivos (Facturas/Tickets)
 */
class Uploader {
    private $uploadDir;
    private $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
    private $maxSize = 5242880; // 5MB

    public function __construct($dir = __DIR__ . '/../public/uploads/') {
        $this->uploadDir = $dir;
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Subir archivo y retornar la ruta relativa
     */
    public function upload($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception("El archivo es demasiado grande (máx 5MB)");
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExtensions)) {
            throw new Exception("Formato no permitido (Solo PDF, JPG, PNG)");
        }

        // Nombre único para evitar colisiones y hackeos por nombre de archivo
        $filename = uniqid('inv_', true) . '.' . $ext;
        $target = $this->uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return 'uploads/' . $filename;
        }

        return null;
    }
}
