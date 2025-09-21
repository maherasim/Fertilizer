<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgriTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-agri">
  <div class="container">
    <a class="navbar-brand" href="index.php">AgriTrack</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="fertilizer.php">Fertilizers</a></li>
        <li class="nav-item"><a class="nav-link" href="pesticide.php">Pesticides</a></li>
        <li class="nav-item"><a class="nav-link" href="create_daily_report.php">New Sale</a></li>
        <li class="nav-item"><a class="nav-link" href="daily_report.php">Sales Reports</a></li>
      </ul>
    </div>
  </div>
  </nav>
  <div class="page-container">
