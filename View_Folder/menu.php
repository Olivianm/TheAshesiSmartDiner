<?php
require './../Setting_Folder/connection.php';

// Get the category ID from the URL (if provided)
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Prepare SQL query
$sql = $categoryId 
    ? "SELECT * FROM menu WHERE category_id = $categoryId"
    : "SELECT * FROM menu";

// Execute the query
$result = $connection->query($sql);

// Fetch all menu items
$menuItems = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Menu</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="./../lib/animate/animate.min.css" rel="stylesheet">
    <link href="./../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="./../lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="./../CSS_Folder/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="./../CSS_Folder/style.css" rel="stylesheet">
</head>

<body>

    <!-- Back to Dashboard Button -->
    <a href="./../View_Folder/studentHome.php" class="btn btn-outline-primary btn-lg position-absolute m-4">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="container-xxl bg-white p-0">
        <!-- Menu Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">The Menu</h5>
                    <h1 class="mb-5">Most Popular Items</h1>
                </div>
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                    <div class="d-flex justify-content-center mb-4">
                        <select id="categoryFilter" class="form-select w-50">
                            <option value="">All Categories</option>
                            <option value="1" <?php echo $categoryId == 1 ? 'selected' : ''; ?>>Breakfast</option>
                            <option value="2" <?php echo $categoryId == 2 ? 'selected' : ''; ?>>Lunch</option>
                            <option value="3" <?php echo $categoryId == 3 ? 'selected' : ''; ?>>Dinner</option>
                        </select>
                    </div>
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <div class="row g-4" id="menuContainer">
                                <?php if (!empty($menuItems)) : ?>
                                    <?php foreach ($menuItems as $item) : ?>
                                        <div class="col-lg-6">
                                            <div class="d-flex align-items-center">
                                              
                                                <div class="w-100 d-flex flex-column text-start ps-4">
                                                    <h5 class="d-flex justify-content-between border-bottom pb-2">
                                                        <span><?= $item['item_name']; ?></span>
                                                        <span class="text-primary">GHC <?= number_format($item['price'], 2); ?></span>
                                                    </h5>
                                                    <small class="fst-italic"><?= $item['description']; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p>No items found in this category.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Menu End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./../lib/wow/wow.min.js"></script>
    <script src="./../lib/easing/easing.min.js"></script>
    <script src="./../lib/waypoints/waypoints.min.js"></script>
    <script src="./../lib/counterup/counterup.min.js"></script>
    <script src="./../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="./../lib/tempusdominus/js/moment.min.js"></script>
    <script src="./../lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="./../lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="./../JS_Folder/main.js"></script>

    <script>
        // Event listener for category filter dropdown
        document.getElementById('categoryFilter').addEventListener('change', function() {
            const categoryId = this.value;
            window.location.href = `menu.php?category_id=${categoryId}`;
        });
    </script>
</body>

</html>
