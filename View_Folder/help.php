<?php
session_start();

// Assuming the user is logged in, and you have a database connection.
require './../Setting_Folder/connection.php';

// You can use this to fetch some help topics or FAQs from the database.
$query = "SELECT * FROM help_topics ORDER BY created_at DESC";
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$helpTopics = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $helpTopics[] = $row;
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Help Center</title>
  <link href="./../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }

    .container {
      max-width: 900px;
      margin: auto;
      padding: 30px;
    }

    h1 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 20px;
      color: #6c757d;
    }

    .help-topic {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 15px;
      cursor: pointer;
    }

    .help-topic:hover {
      background-color: #f1f1f1;
      transform: translateY(-5px);
      box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
    }

    .help-topic h5 {
      font-size: 1.25rem;
      color: #333;
    }

    .help-topic p {
      font-size: 0.95rem;
      color: #555;
    }

    .back-btn {
      background-color: #a7333f;
      color: white;
      padding: 10px 25px;
      text-align: center;
      display: inline-block;
      text-decoration: none;
      border-radius: 5px;
      margin-top: 30px;
    }

    .back-btn:hover {
      background-color: #8f3a45;
    }
  </style>
</head>
<body>

 <!-- Back to Dashboard Button -->
<a href="./../View_Folder/studentHome.php" class="btn btn-outline-primary btn-lg position-absolute m-4">
    <i class="bi bi-arrow-left"></i> Back
</a>

  <div class="container">
    <h1>Help Center</h1>

    <?php if (count($helpTopics) > 0): ?>
      <?php foreach ($helpTopics as $topic): ?>
        <div class="help-topic">
          <h5><?php echo $topic['title']; ?></h5>
          <p><?php echo substr($topic['content'], 0, 150); ?>...</p>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No help topics available at the moment.</p>
    <?php endif; ?>

    <a href="./studentHome.php" class="back-btn">Back to Home</a>
  </div>

  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
