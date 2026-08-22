<?php
require_once 'config.php';

$errors = [];
$success = false;

// Giữ lại dữ liệu đã nhập khi submit lỗi (sticky form)
$old = [
    'ho_ten' => '',
    'email' => '',
    'chu_de' => '',
    'noi_dung' => '',
];

$chu_de_options = ['Hỗ trợ kỹ thuật', 'Góp ý', 'Khiếu nại', 'Khác'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old['ho_ten'] = trim($_POST['ho_ten'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['chu_de'] = trim($_POST['chu_de'] ?? '');
    $old['noi_dung'] = trim($_POST['noi_dung'] ?? '');

    // --- Validate họ tên ---
    if ($old['ho_ten'] === '') {
        $errors['ho_ten'] = 'Vui lòng nhập họ tên.';
    }

    // --- Validate email ---
    if ($old['email'] === '') {
        $errors['email'] = 'Vui lòng nhập email.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email không hợp lệ.';
    }

    // --- Validate chủ đề ---
    if ($old['chu_de'] === '' || !in_array($old['chu_de'], $chu_de_options, true)) {
        $errors['chu_de'] = 'Vui lòng chọn chủ đề.';
    }

    // --- Validate nội dung ---
    if ($old['noi_dung'] === '') {
        $errors['noi_dung'] = 'Vui lòng nhập nội dung.';
    } elseif (mb_strlen($old['noi_dung']) > 500) {
        $errors['noi_dung'] = 'Nội dung không được vượt quá 500 ký tự.';
    }

    // --- Validate ảnh đại diện (không bắt buộc) ---
    $ten_file_luu = null;
    if (!empty($_FILES['anh_dai_dien']['name'])) {
        $file = $_FILES['anh_dai_dien'];
        $dinh_dang_hop_le = ['jpg', 'jpeg', 'png', 'gif'];
        $duoi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dung_luong_toi_da = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['anh_dai_dien'] = 'Có lỗi khi tải ảnh lên.';
        } elseif (!in_array($duoi, $dinh_dang_hop_le, true)) {
            $errors['anh_dai_dien'] = 'Chỉ chấp nhận định dạng jpg, jpeg, png, gif.';
        } elseif ($file['size'] > $dung_luong_toi_da) {
            $errors['anh_dai_dien'] = 'Ảnh vượt quá dung lượng tối đa 2MB.';
        } else {
            $ten_file_luu = uniqid('lh_', true) . '.' . $duoi;
            $duong_dan_luu = __DIR__ . '/uploads/' . $ten_file_luu;
            if (!move_uploaded_file($file['tmp_name'], $duong_dan_luu)) {
                $errors['anh_dai_dien'] = 'Không thể lưu ảnh, vui lòng thử lại.';
                $ten_file_luu = null;
            }
        }
    }

    // --- Nếu không có lỗi thì lưu vào CSDL ---
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO lien_he (ho_ten, email, chu_de, noi_dung, anh_dai_dien)
             VALUES (:ho_ten, :email, :chu_de, :noi_dung, :anh_dai_dien)'
        );
        $stmt->execute([
            ':ho_ten' => $old['ho_ten'],
            ':email' => $old['email'],
            ':chu_de' => $old['chu_de'],
            ':noi_dung' => $old['noi_dung'],
            ':anh_dai_dien' => $ten_file_luu,
        ]);

        $success = true;
        $old = ['ho_ten' => '', 'email' => '', 'chu_de' => '', 'noi_dung' => '']; // reset form
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Liên hệ</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar"></div>

<div class="form-box">
    <h1>Liên hệ</h1>

    <?php if ($success): ?>
        <p class="subtitle success">Gửi liên hệ thành công!</p>
    <?php elseif (!empty($errors)): ?>
        <p class="subtitle error">thiếu thông tin</p>
    <?php endif; ?>

    <form action="index.php" method="post" enctype="multipart/form-data" novalidate>

        <label for="ho_ten">Họ tên</label>
        <input type="text" id="ho_ten" name="ho_ten" placeholder="Nhập họ và tên"
               value="<?= htmlspecialchars($old['ho_ten']) ?>">
        <?php if (isset($errors['ho_ten'])): ?>
            <div class="field-error"><?= $errors['ho_ten'] ?></div>
        <?php endif; ?>

        <label for="email">Email</label>
        <input type="text" id="email" name="email" placeholder="ten@example.com"
               value="<?= htmlspecialchars($old['email']) ?>">
        <?php if (isset($errors['email'])): ?>
            <div class="field-error"><?= $errors['email'] ?></div>
        <?php endif; ?>

        <label for="chu_de">Chủ đề</label>
        <select id="chu_de" name="chu_de">
            <option value="" <?= $old['chu_de'] === '' ? 'selected' : '' ?>>-- Chọn chủ đề --</option>
            <?php foreach ($chu_de_options as $option): ?>
                <option value="<?= htmlspecialchars($option) ?>"
                    <?= $old['chu_de'] === $option ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['chu_de'])): ?>
            <div class="field-error"><?= $errors['chu_de'] ?></div>
        <?php endif; ?>

        <label for="noi_dung">Nội dung</label>
        <textarea id="noi_dung" name="noi_dung" rows="5"
                  placeholder="Nhập nội dung liên hệ"><?= htmlspecialchars($old['noi_dung']) ?></textarea>
        <div class="hint">Nội dung giới hạn tối đa 500 ký tự</div>
        <?php if (isset($errors['noi_dung'])): ?>
            <div class="field-error"><?= $errors['noi_dung'] ?></div>
        <?php endif; ?>

        <label for="anh_dai_dien">Ảnh đại diện</label>
        <input type="file" id="anh_dai_dien" name="anh_dai_dien" accept=".jpg,.jpeg,.png,.gif">
        <div class="hint">Định dạng jpg, jpeg, png, gif — tối đa 2MB</div>
        <?php if (isset($errors['anh_dai_dien'])): ?>
            <div class="field-error"><?= $errors['anh_dai_dien'] ?></div>
        <?php endif; ?>

        <button type="submit">Gửi liên hệ</button>
    </form>
</div>

</body>
</html>
