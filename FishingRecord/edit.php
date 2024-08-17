<?php
// データベース接続
try {
    $pdo = new PDO('mysql:host=localhost;dbname=fishing_record_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// 編集対象のデータを取得
$editMode = false;
$editData = null;

if (isset($_GET['edit_id'])) {
    $editMode = true;
    $editId = $_GET['edit_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM record WHERE id = :id");
        $stmt->bindValue(':id', $editId, PDO::PARAM_INT);
        $stmt->execute();
        $editData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Error fetching record: ' . $e->getMessage();
    }
}

// 更新処理
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $length = isset($_POST['length']) ? $_POST['length'] : null;
    $weight = isset($_POST['weight']) ? $_POST['weight'] : null;
    $lure = isset($_POST['lure']) ? $_POST['lure'] : null;
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $imageData = isset($_FILES["image"]["tmp_name"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK ? file_get_contents($_FILES["image"]["tmp_name"]) : null;

    try {
        if ($id) {
            // 編集処理
            if ($imageData) {
                $stmt = $pdo->prepare("UPDATE record SET image = :image, length = :length, weight = :weight, lure = :lure, date = :date WHERE id = :id");
                $stmt->bindValue(':image', $imageData, PDO::PARAM_LOB);
            } else {
                $stmt = $pdo->prepare("UPDATE record SET length = :length, weight = :weight, lure = :lure, date = :date WHERE id = :id");
            }
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':length', $length, PDO::PARAM_INT);
            $stmt->bindValue(':weight', $weight, PDO::PARAM_INT);
            $stmt->bindValue(':lure', $lure, PDO::PARAM_STR);
            $stmt->bindValue(':date', $date, PDO::PARAM_STR);
            $stmt->execute();

            header("location:index.php");
            exit();
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="edit.css">
    <title>EDIT</title>
</head>
<body>
    <h1>データ編集</h1>
    <form action="edit.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $editMode ? $editData['id'] : ''; ?>">
        <ul>
            <li>
                <input type="file" name="image">
            </li>
            <li>
                <input type="text" name="date" placeholder="Date(XXXX/XX/XX)" value="<?php echo $editMode ? htmlspecialchars($editData['date']) : ''; ?>">
            </li>
            <li>
                <input type="text" name="length" placeholder="Length(cm)" value="<?php echo $editMode ? htmlspecialchars($editData['length']) : ''; ?>">
            </li>
            <li>
                <input type="text" name="weight" placeholder="Weight(g)" value="<?php echo $editMode ? htmlspecialchars($editData['weight']) : ''; ?>">
            </li>
            <li>
                <input type="text" name="lure" placeholder="Lure" value="<?php echo $editMode ? htmlspecialchars($editData['lure']) : ''; ?>">
            </li>
            <li>
                <button class="formBtn" type="submit">更新</button>
            </li>
        </ul>
    </form>
</body>
</html>
