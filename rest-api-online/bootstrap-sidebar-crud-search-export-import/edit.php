<?php
require_once 'config.php';

$id = $_GET['id'] ?? null;
if(!$id) {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $image = $_POST['image'];
    $release_at = $_POST['release_at'];
    $summary = $_POST['summary'];

    $stmt = $conn->prepare("UPDATE members SET title = ?, image = ?, release_at = ?, summary = ? WHERE id = ?");
    $stmt->execute([$title, $image, $release_at, $summary, $id]);

    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$id]);
$member = $stmt->fetch();

if(!$member) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Member</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($member['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Image URL</label>
            <input type="text" class="form-control" id="image" name="image" value="<?= htmlspecialchars($member['image']) ?>">
        </div>
        <div class="mb-3">
            <label for="release_at" class="form-label">Release Date</label>
            <input type="date" class="form-control" id="release_at" name="release_at" value="<?= htmlspecialchars($member['release_at']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="summary" class="form-label">Summary</label>
            <textarea class="form-control" id="summary" name="summary" rows="3" required><?= htmlspecialchars($member['summary']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
