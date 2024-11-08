<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avengers Members Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <h3>.: Menu :.</h3>
        </div>

        <ul class="list-unstyled components">
            <li class="active">
                <a href="index.php">
                    <i class="bi bi-house-door"></i> Home
                </a>
            </li>
            <li>
                <a href="members.php">
                    <i class="bi bi-people"></i> Members
                </a>
            </li>
            <li>
                <a href="reports.php">
                    <i class="bi bi-file-text"></i> Reports
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </nav>
