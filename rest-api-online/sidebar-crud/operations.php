<?php
require_once 'config.php';

// Read all records
if($_POST['action'] == 'fetch') {
    $query = "SELECT * FROM members";
    $statement = $pdo->prepare($query);
    $statement->execute();
    $result = $statement->fetchAll();

    $output = array();
    foreach($result as $row) {
        $sub_array = array();
        $sub_array[] = $row['id'];
        $sub_array[] = $row['title'];
        $sub_array[] = '<img src="uploads/' . $row['image'] . '" width="50px">';
        $sub_array[] = $row['release_at'];
        $sub_array[] = $row['summary'];
        $sub_array[] = '<button type="button" class="btn btn-warning btn-sm edit" id="'.$row["id"].'">Edit</button>';
        $sub_array[] = '<button type="button" class="btn btn-danger btn-sm delete" id="'.$row["id"].'">Delete</button>';
        $output[] = $sub_array;
    }

    echo json_encode(array("data" => $output));
}

// Create record
if($_POST['action'] == 'create') {
    $image = '';
    if($_FILES['image']['name'] != '') {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    }

    $query = "INSERT INTO members (title, image, release_at, summary) VALUES (:title, :image, :release_at, :summary)";
    $statement = $pdo->prepare($query);
    $statement->execute(array(
        ':title' => $_POST['title'],
        ':image' => $image,
        ':release_at' => $_POST['release_at'],
        ':summary' => $_POST['summary']
    ));

    echo 'Data Created';
}

// Get single record
if($_POST['action'] == 'get_single') {
    $query = "SELECT * FROM members WHERE id = :id";
    $statement = $pdo->prepare($query);
    $statement->execute(array(':id' => $_POST['id']));
    $result = $statement->fetch();

    echo json_encode($result);
}

// Update record
if($_POST['action'] == 'update') {
    $image = $_POST['hidden_image'];
    if($_FILES['image']['name'] != '') {
        $image = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
    }

    $query = "UPDATE members SET title = :title, image = :image, release_at = :release_at, summary = :summary WHERE id = :id";
    $statement = $pdo->prepare($query);
    $statement->execute(array(
        ':title' => $_POST['title'],
        ':image' => $image,
        ':release_at' => $_POST['release_at'],
        ':summary' => $_POST['summary'],
        ':id' => $_POST['hidden_id']
    ));

    echo 'Data Updated';
}

// Delete record
if($_POST['action'] == 'delete') {
    $query = "DELETE FROM members WHERE id = :id";
    $statement = $pdo->prepare($query);
    $statement->execute(array(':id' => $_POST['id']));

    echo 'Data Deleted';
}
?>
