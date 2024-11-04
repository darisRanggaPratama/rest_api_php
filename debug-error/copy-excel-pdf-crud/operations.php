<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include database connection
    require_once 'connect-s.php';

    // Validasi request method
    $method = $_SERVER['REQUEST_METHOD'];
    $response = [];

    switch ($method) {
        case 'POST':
            // Create new record
            if (isset($_POST['action']) && $_POST['action'] === 'create') {
                $stmt = $connect->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['image'],
                    $_POST['release_at'],
                    $_POST['summary']
                ]);

                $response = [
                    'status' => 'success',
                    'message' => 'Data berhasil ditambahkan',
                    'id' => $connect->lastInsertId()
                ];
            }
            // Update existing record
            elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
                $stmt = $connect->prepare("UPDATE members SET title = ?, image = ?, release_at = ?, summary = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['title'],
                    $_POST['image'],
                    $_POST['release_at'],
                    $_POST['summary'],
                    $_POST['id']
                ]);

                $response = [
                    'status' => 'success',
                    'message' => 'Data berhasil diperbarui'
                ];
            }
            break;

        case 'DELETE':
            // Delete record
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $connect->prepare("DELETE FROM members WHERE id = ?");
                $stmt->execute([$id]);

                $response = [
                    'status' => 'success',
                    'message' => 'Data berhasil dihapus'
                ];
            }
            break;

        case 'GET':
            // Get single record for editing
            $id = $_GET['id'] ?? null;
            if ($id) {
                $stmt = $connect->prepare("SELECT * FROM members WHERE id = ?");
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                $response = [
                    'status' => 'success',
                    'data' => $data
                ];
            }
            break;
    }

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
