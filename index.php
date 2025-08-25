<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgriTrack - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://tse1.mm.bing.net/th/id/OIP.uOec0AN_7fhQlrNB0203lgHaEc?pid=Api') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        .hero {
            background: rgba(0, 0, 0, 0.6);
            padding: 60px 20px;
            border-radius: 15px;
            margin-top: 80px;
        }

        .nav-link {
            color: #fff !important;
        }

        .nav-link:hover {
            text-decoration: underline;
        }

        .nav-card a {
            text-decoration: none;
            display: block;
            padding: 12px;
            background-color: #28a745;
            color: #fff;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }

        .nav-card a:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">AgriTrack</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="farmer.php">Farmers</a></li>
                <li class="nav-item"><a class="nav-link" href="land.php">Land</a></li>
                <li class="nav-item"><a class="nav-link" href="crop.php">Crops</a></li>
                <li class="nav-item"><a class="nav-link" href="fertilizer.php">Fertilizers</a></li>
                <li class="nav-item"><a class="nav-link" href="pesticide.php">Pesticides</a></li>
                <li class="nav-item"><a class="nav-link" href="planting.php">Planting</a></li>
                <li class="nav-item"><a class="nav-link" href="market.php">Market Rates</a></li>
                <li class="nav-item"><a class="nav-link" href="daily_report.php">Daily Reports</a></li>
                <li class="nav-item"><a class="nav-link" href="create_daily_report.php">New Report</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<!-- Hero Section -->
<div class="container hero text-center">
    <h1 class="mb-4">Welcome to AgriTrack</h1>
    <p class="lead mb-5">Efficiently manage all aspects of your farming system.</p>

    <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
        <!-- Card 1 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">1. Manage Farmers</h5>
                    <p class="card-text">Add and update farmer details and records.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="farmer.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">2. Manage Land</h5>
                    <p class="card-text">Track land usage, size, and ownership.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="land.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">3. Manage Crops</h5>
                    <p class="card-text">Organize seasonal and crop details.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="crop.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">4. Manage Fertilizers</h5>
                    <p class="card-text">Track usage of fertilizers and quantities.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="fertilizer.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 5 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">5. Manage Pesticides</h5>
                    <p class="card-text">Log pesticide application records.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="pesticide.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 6 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">6. Manage Planting</h5>
                    <p class="card-text">Track planting schedules and progress.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="planting.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 7 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">7. Manage Market Rates</h5>
                    <p class="card-text">Keep updated with current crop prices.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="market.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 8 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">8. Manage Daily Reports</h5>
                    <p class="card-text">View and analyze daily activity reports.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="daily_report.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>

        <!-- Card 9 -->
        <div class="col">
            <div class="card h-100 bg-success text-white shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">9. Create Daily Reports</h5>
                    <p class="card-text">Manually input new daily reports.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="create_daily_report.php" class="btn btn-light w-100">Go</a>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
