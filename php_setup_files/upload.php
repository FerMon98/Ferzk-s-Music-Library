<?php
error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    // File upload directory
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    // Move file to the server directory
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        // Save file details to the database
        $stmt = $conn->prepare("INSERT INTO uploaded_files (file_name, file_path) VALUES (?, ?)");
        $stmt->bind_param("ss", $fileName, $filePath);
        
        if ($stmt->execute()) {
            echo "File uploaded and saved to database successfully.";
        } else {
            echo "Error saving file info to the database.";
        }

        $stmt->close();
    } else {
        echo "Failed to upload file.";
    }
}

$conn->close();
?>