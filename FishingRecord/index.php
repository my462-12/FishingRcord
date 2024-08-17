<!-- 要件定義
 ・画像ファイルを圧縮してデータベースに追加
 ・編集機能
 ・削除機能
 ・-->

<?php
// データベース接続
try {
    $pdo = new PDO('mysql:host=localhost;dbname=', '', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// フォームの値をデータベースに追加
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
    $length = isset($_POST['length']) ? $_POST['length'] : null;
    $weight = isset($_POST['weight']) ? $_POST['weight'] : null;
    $lure = isset($_POST['lure']) ? $_POST['lure'] : null;
    $date = isset($_POST['date']) ? $_POST['date'] : null;

    // 画像のパスを取得
    $imagePath = $_FILES["image"]["tmp_name"];

    // 画像のリソースを作成
    $imageResource = imagecreatefromjpeg($imagePath);

    // Exif情報から向きを取得して修正
    $exif = exif_read_data($imagePath);
    if (!empty($exif['Orientation'])) {
        switch ($exif['Orientation']) {
            case 3:
                $imageResource = imagerotate($imageResource, 180, 0);
                break;
            case 6:
                $imageResource = imagerotate($imageResource, -90, 0);
                break;
            case 8:
                $imageResource = imagerotate($imageResource, 90, 0);
                break;
        }
    }

    // 圧縮するための出力バッファを開始
    ob_start();

    // 画像を圧縮してJPEGとして出力（圧縮率は85）
    imagejpeg($imageResource, null, 50);

    // バッファから画像データを取得
    $imageData = ob_get_clean();

    // メモリの解放
    imagedestroy($imageResource);

    try {
        // 画像データをデータベースに挿入
        $stmt = $pdo->prepare("INSERT INTO dbname (image, length, weight, lure, date) VALUES (:image, :length, :weight, :lure, :date)");
        $stmt->bindValue(':image', $imageData, PDO::PARAM_LOB);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->bindValue(':weight', $weight, PDO::PARAM_INT);
        $stmt->bindValue(':lure', $lure, PDO::PARAM_STR);
        $stmt->bindValue(':date', $date, PDO::PARAM_STR);
        $stmt->execute();

        // 成功したらリダイレクト
        header("location:index.php");
        exit();
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
// 削除処理
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM dbname WHERE id = :id");
        $stmt->bindValue(':id', $delete_id, PDO::PARAM_INT);
        $stmt->execute();

    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

//データベースのデータを取得
try {
    $stmt = $pdo->query("SELECT * FROM dbname ORDER BY id DESC");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Error fetching records: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js" defer
        type="text/javascript"></script>
    <title>FishingRecord</title>
</head>

<body>
    <h1><span>F</span>ishing<span>R</span>ecord</h1>
    <!-- フォーム -->

    <main>
        
        <!-- データベースの要素を出力 -->
        <div class="content">
            <?php if (!empty($records)): ?>
                <?php foreach ($records as $row): ?>
                    <div class="contentlist">
                        <a href="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?> " data-lightbox="group"><img
                                src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>"></a>
                        <ul>
                            <li>Date : <?php echo ($row['date']); ?></li>
                            <li>Length : <?php echo ($row['length']); ?>cm</li>
                            <li>Weight : <?php echo ($row['weight']); ?>g</li>
                            <li>Lure : <?php echo ($row['lure']); ?></li>
                        </ul>
                        <a class="editBtn" href="edit.php?edit_id=<?php echo $row['id']; ?>">編集</a>
                        <form method="POST" action="index.php" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button class="deleteBtn" type="submit" onclick='return confirm("本当に削除しますか？")'>削除</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No records found.</p>
            <?php endif; ?>
        </div>
    </main>
    <script src="index.js"></script>

    <footer>
    <div class="add"><a href="upload.php">＋</a></div>
    </footer>
</body>

</html>
