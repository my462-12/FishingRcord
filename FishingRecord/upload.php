<?php
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="upload.css">
    <title>upload</title>
</head>
<body>
    <h1>UPLOAD</h1>
<div class="form">
            <form action="index.php" method="post" enctype="multipart/form-data">
                <ul>
                    <li><input type="file" name="image" required></li>
                    <li><input type="text" name="date" placeholder="Date(XXXX/XX/XX)"></li>
                    <li><input type="text" name="length" placeholder="Length(cm)"></li>
                    <li><input type="text" name="weight" placeholder="Weight(g)"></li>
                    <li><input type="text" name="lure" placeholder="Lure"></li>
                    <li><button class="formBtn" type="submit">保存</button></li>
                </ul>
            </form>
        </div>
</body>
</html>