<?php
session_start();

$hoTen    = '';
$email    = '';
$chuDe    = '';
$noiDung  = '';
$errors   = [];
$success  = false;
$avatarName = '';

$danhSachChuDe = [
    'Hỏi đáp kỹ thuật',
    'Góp ý dịch vụ',
    'Hợp tác kinh doanh',
    'Khác'
];

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hoTen   = trim($_POST['ho_ten'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $chuDe   = trim($_POST['chu_de'] ?? '');
    $noiDung = trim($_POST['noi_dung'] ?? '');

    if ($hoTen === '') {
        $errors['ho_ten'] = 'Họ tên không được để trống.';
    }
    if ($noiDung === '') {
        $errors['noi_dung'] = 'Nội dung không được để trống.';
    } elseif (mb_strlen($noiDung) > 500) {
        $errors['noi_dung'] = 'Nội dung không được vượt quá 500 ký tự.';
    }

    if ($email === '') {
        $errors['email'] = 'Email không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không đúng định dạng.';
    }

    if ($chuDe === '' || !in_array($chuDe, $danhSachChuDe, true)) {
        $errors['chu_de'] = 'Vui lòng chọn chủ đề.';
    }

    $allowedExt  = ['jpg', 'jpeg', 'png', 'gif'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['avatar'] = 'Vui lòng chọn ảnh đại diện.';
    } elseif ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errors['avatar'] = 'Có lỗi xảy ra khi tải ảnh lên (mã lỗi: ' . $_FILES['avatar']['error'] . ').';
    } else {
        $fileTmp  = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileSize = $_FILES['avatar']['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowedExt, true)) {
            $errors['avatar'] = 'Chỉ chấp nhận ảnh định dạng: ' . implode(', ', $allowedExt) . '.';
        }
        elseif ($fileSize > $maxFileSize) {
            $errors['avatar'] = 'Dung lượng ảnh không được vượt quá 2MB.';
        }
        // Kiểm tra thực sự là ảnh (chống giả mạo đuôi file)
        elseif (getimagesize($fileTmp) === false) {
            $errors['avatar'] = 'File tải lên không phải là ảnh hợp lệ.';
        }
    }

    if (empty($errors)) {
        // Đổi tên file để tránh trùng lặp
        $avatarName = uniqid('avatar_', true) . '.' . $fileExt;
        $destination = $uploadDir . $avatarName;

        if (move_uploaded_file($fileTmp, $destination)) {
            $success = true;

            $hoTen = $email = $chuDe = $noiDung = '';
        } else {
            $errors['avatar'] = 'Không thể lưu ảnh lên máy chủ. Vui lòng thử lại.';
        }
    }
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Liên hệ</title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f0f4f8;
        margin: 0;
        padding: 30px 15px;
    }
    .container {
        max-width: 480px;
        margin: 0 auto;
        background: #ddeeff;
        border-radius: 10px;
        padding: 25px 30px 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    h1 {
        text-align: center;
        color: #1a4f8b;
        margin-top: 0;
    }
    .subtitle {
        text-align: center;
        color: #555;
        font-size: 14px;
        margin-bottom: 20px;
    }
    label {
        display: block;
        font-weight: bold;
        color: #333;
        margin: 14px 0 6px;
        font-size: 14px;
    }
    input[type=text],
    input[type=email],
    select,
    textarea {
        width: 100%;
        padding: 9px 10px;
        border: 1px solid #b9c8d9;
        border-radius: 5px;
        font-size: 14px;
        background: #fff;
    }
    textarea { resize: vertical; min-height: 100px; }
    .char-hint { font-size: 12px; color: #777; margin-top: 4px; }
    .error { color: #c0392b; font-size: 13px; margin-top: 4px; }
    .field-error input,
    .field-error select,
    .field-error textarea {
        border-color: #c0392b;
    }
    button {
        width: 100%;
        margin-top: 22px;
        padding: 11px;
        background: #1a63c4;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
    }
    button:hover { background: #114a97; }
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        font-size: 14px;
    }
    .avatar-preview {
        margin-top: 10px;
        display: none;
    }
    .avatar-preview img {
        max-width: 120px;
        max-height: 120px;
        border-radius: 6px;
        border: 1px solid #b9c8d9;
        display: block;
        object-fit: cover;
    }
    .avatar-preview .file-info {
        font-size: 12px;
        color: #555;
        margin-top: 4px;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Liên hệ</h1>
    <p class="subtitle">thiếu thông tin</p>

    <?php if ($success): ?>
        <div class="alert-success">
            Gửi thành công
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" novalidate>

        <div class="<?= isset($errors['ho_ten']) ? 'field-error' : '' ?>">
            <label for="ho_ten">Họ tên</label>
            <input type="text" id="ho_ten" name="ho_ten" value="<?= h($hoTen) ?>" placeholder="Nhập họ và tên">
            <?php if (isset($errors['ho_ten'])): ?>
                <div class="error"><?= h($errors['ho_ten']) ?></div>
            <?php endif; ?>
        </div>

        <div class="<?= isset($errors['email']) ? 'field-error' : '' ?>">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" value="<?= h($email) ?>" placeholder="ten@example.com">
            <?php if (isset($errors['email'])): ?>
                <div class="error"><?= h($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="<?= isset($errors['chu_de']) ? 'field-error' : '' ?>">
            <label for="chu_de">Chủ đề</label>
            <select id="chu_de" name="chu_de">
                <option value="">-- Chọn chủ đề --</option>
                <?php foreach ($danhSachChuDe as $cd): ?>
                    <option value="<?= h($cd) ?>" <?= $chuDe === $cd ? 'selected' : '' ?>><?= h($cd) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['chu_de'])): ?>
                <div class="error"><?= h($errors['chu_de']) ?></div>
            <?php endif; ?>
        </div>

        <div class="<?= isset($errors['noi_dung']) ? 'field-error' : '' ?>">
            <label for="noi_dung">Nội dung</label>
            <textarea id="noi_dung" name="noi_dung" placeholder="Nhập nội dung liên hệ"><?= h($noiDung) ?></textarea>
            <div class="char-hint">Nội dung giới hạn tối đa 500 ký tự</div>
            <?php if (isset($errors['noi_dung'])): ?>
                <div class="error"><?= h($errors['noi_dung']) ?></div>
            <?php endif; ?>
        </div>

        <div class="<?= isset($errors['avatar']) ? 'field-error' : '' ?>">
            <label for="avatar">Ảnh đại diện</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            <div class="char-hint">Định dạng jpg, jpeg, png, gif — tối đa 2MB</div>
            <?php if (isset($errors['avatar'])): ?>
                <div class="error"><?= h($errors['avatar']) ?></div>
            <?php endif; ?>

            <!-- Khu vực xem trước ảnh đã chọn -->
            <div class="avatar-preview" id="avatarPreview">
                <img id="avatarPreviewImg" src="" alt="Ảnh xem trước">
                <div class="file-info" id="avatarFileInfo"></div>
            </div>
        </div>

        <button type="submit">Gửi liên hệ</button>
    </form>
</div>

<script>
    const avatarInput   = document.getElementById('avatar');
    const previewBox    = document.getElementById('avatarPreview');
    const previewImg    = document.getElementById('avatarPreviewImg');
    const fileInfoText  = document.getElementById('avatarFileInfo');

    let currentObjectUrl = null;

    function resetPreview() {
        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = null;
        }
        previewBox.style.display = 'none';
        previewImg.src = '';
        fileInfoText.textContent = '';
    }

    avatarInput.addEventListener('change', function () {
        const file = this.files && this.files[0];

        if (!file) {
            resetPreview();
            return;
        }

        const sizeKB = (file.size / 1024).toFixed(1);

        if (file.type && !file.type.startsWith('image/')) {
            resetPreview();
            fileInfoText.textContent = file.name + ' — ' + sizeKB + ' KB (không phải file ảnh)';
            previewBox.style.display = 'block';
            return;
        }

        if (currentObjectUrl) {
            URL.revokeObjectURL(currentObjectUrl);
        }
        currentObjectUrl = URL.createObjectURL(file);

        previewImg.onerror = function () {
            // Trường hợp file có đuôi/định dạng ảnh nhưng nội dung lỗi, không đọc được
            previewImg.style.display = 'none';
            fileInfoText.textContent = file.name + ' — ' + sizeKB + ' KB (không thể xem trước ảnh này)';
        };
        previewImg.onload = function () {
            previewImg.style.display = 'block';
            fileInfoText.textContent = file.name + ' — ' + sizeKB + ' KB';
        };

        previewImg.src = currentObjectUrl;
        previewBox.style.display = 'block';
    });
</script>
</body>
</html>