<?php
include 'config.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = $_GET['id'];
$sql = "SELECT * FROM posts WHERE id=$post_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$post = $result->fetch_assoc();

$sql = "SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id=$post_id ORDER BY created_at DESC";
$comments_result = $conn->query($sql);

$comments = [];
if ($comments_result->num_rows > 0) {
    while ($row = $comments_result->fetch_assoc()) {
        $comments[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    $user_id = $_SESSION['user_id'];
    $comment = $_POST['comment'];
    $sql = "INSERT INTO comments (post_id, user_id, comment) VALUES ('$post_id', '$user_id', '$comment')";
    if ($conn->query($sql) === TRUE) {
        header("Location: post.php?id=$post_id");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];
    $sql = "DELETE FROM comments WHERE id=$comment_id AND user_id={$_SESSION['user_id']}";
    if ($conn->query($sql) === TRUE) {
        header("Location: post.php?id=$post_id");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post['title']; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1 class="my-4"><?php echo $post['title']; ?></h1>
        <?php if ($post['image_path']): ?>
            <img class="img-fluid mb-4" src="<?php echo $post['image_path']; ?>" alt="Post image">
        <?php endif; ?>
        <p><?php echo nl2br($post['content']); ?></p>
        <p><small>Posted on <?php echo $post['created_at']; ?></small></p>

        <h3 class="my-4">Comments</h3>
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="post" class="mb-4">
                <div class="form-group">
                    <textarea class="form-control" name="comment" rows="3" placeholder="Leave a comment" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Log in</a> to leave a comment.</p>
        <?php endif; ?>

        <?php foreach ($comments as $comment): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <p class="card-text"><?php echo nl2br($comment['comment']); ?></p>
                    <p class="card-text"><small class="text-muted">Posted by <?php echo $comment['username']; ?> on <?php echo $comment['created_at']; ?></small></p>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                        <form method="post" class="mt-2">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <button type="submit" name="delete_comment" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>