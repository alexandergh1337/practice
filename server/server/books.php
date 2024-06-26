<?php
include 'config.php';
include 'functions.php';

$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($bookId > 0) {
    $sql = "SELECT Books.*, Authors.name AS author FROM Books JOIN Authors ON Books.author_id = Authors.author_id WHERE Books.book_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        $bookPath = $book['path'];
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        try {
            $pages = findPages($bookPath);
            natsort($pages);
            $pages = array_values($pages);
            $totalPages = count($pages);

            if ($currentPage > $totalPages || $currentPage < 1) {
                $currentPage = 1;
            }

            $pageContent = file_get_contents($pages[$currentPage - 1]);
            $pageContent = strip_tags($pageContent, '<img><p><a><b><i><strong><em>');

            $pageContent = preg_replace('/src="([^"]+)"/', 'src="' . $bookPath . '/$1"', $pageContent);
            $pageContent = preg_replace('/href="([^"]+)"/', 'href="' . $bookPath . '/$1"', $pageContent);

        } catch (UnexpectedValueException $e) {
            echo "Ошибка: " . $e->getMessage();
            exit;
        }
    } else {
        echo "Книга не найдена.";
        exit;
    }
} else {
    echo "Идентификатор книги не указан.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($book['title']); ?></title>
    <style>
        .navigation {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }
        .navigation a {
            margin: 0 10px;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .navigation a:hover {
            background-color: #45a049;
        }
        .navigation a.disabled {
            pointer-events: none;
            background-color: #cccccc;
        }
    </style>
</head>
<body>
<h1><?php echo htmlspecialchars($book['title']); ?></h1>
<p><strong>Автор:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
<div><?php echo $pageContent; ?></div>
<div class="navigation">
    <?php if ($currentPage > 1): ?>
        <a href="books.php?id=<?php echo $book['book_id']; ?>&page=<?php echo $currentPage - 1; ?>">Назад</a>
    <?php else: ?>
        <a class="disabled">Назад</a>
    <?php endif; ?>
    <?php if ($currentPage < $totalPages): ?>
        <a href="books.php?id=<?php echo $book['book_id']; ?>&page=<?php echo $currentPage + 1; ?>">Вперёд</a>
    <?php else: ?>
        <a class="disabled">Вперёд</a>
    <?php endif; ?>
</div>
</body>
</html>
