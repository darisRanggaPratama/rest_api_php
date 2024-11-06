<?php
function getAllMembers($conn, $search = '') {
    $query = "SELECT * FROM members WHERE 1=1";
    if($search) {
        $query .= " AND (title LIKE :search OR summary LIKE :search OR release_at LIKE :search)";
    }
    $query .= " ORDER BY id DESC";

    $stmt = $conn->prepare($query);
    if($search) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function getMemberById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createMember($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO members (title, image, release_at, summary) VALUES (?, ?, ?, ?)");
    return $stmt->execute([
        $data['title'],
        $data['image'],
        $data['release_at'],
        $data['summary']
    ]);
}

function updateMember($conn, $id, $data) {
    $stmt = $conn->prepare("UPDATE members SET title = ?, image = ?, release_at = ?, summary = ? WHERE id = ?");
    return $stmt->execute([
        $data['title'],
        $data['image'],
        $data['release_at'],
        $data['summary'],
        $id
    ]);
}

function deleteMember($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    return $stmt->execute([$id]);
}
?>