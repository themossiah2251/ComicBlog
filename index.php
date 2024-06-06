<?php

include 'config.php';
session_start();

// Define number of results per page
$results_per_page = 5;

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Handle search query
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch all posts with pagination and search
$sql = "SELECT * FROM posts WHERE title LIKE '%$search_query%' ORDER BY created_at DESC LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

$posts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Fetch total number of posts for pagination
$sql = "SELECT COUNT(id) AS total FROM posts WHERE title LIKE '%$search_query%'";
$total_posts_result = $conn->query($sql);
$total_posts_row = $total_posts_result->fetch_assoc();
$total_posts = $total_posts_row['total'];
$total_pages = ceil($total_posts / $results_per_page);

$conn->close();
?>

<!DOCTYPE html>
<html>
<?php include 'header.php'; ?>
<head>
    <title>Comic Blog</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/s.css"> 
</head>
<body>
    <div class="container">
        <h1 class="my-4">Comic Blog</h1>
        
        <!-- Search -->
        <form method="GET" action="index.php" class="form-inline mb-4">
            <input type="text" name="search" class="form-control mr-2" placeholder="Search by title" value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        
        <?php foreach ($posts as $post): ?>
            <div class="card mb-4">
                <img class="card-img-top custom-img" src="<?php echo $post['image_path']; ?>" alt="Card image cap">
                <div class="card-body">
                    <h2 class="card-title"><?php echo $post['title']; ?></h2>
                    <p class="card-text"><?php echo substr($post['content'], 0, 200); ?>...</p>
                    <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">Read More &rarr;</a>
                </div>
                <div class="card-footer text-muted">
                    Posted on <?php echo $post['created_at']; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pageination -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page)
                        echo 'active'; ?>">
                        <a class="page-link" href="index.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search_query); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</body>
<?php include 'footer.php'; ?>
</html>