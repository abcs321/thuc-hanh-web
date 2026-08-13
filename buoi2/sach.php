<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Buổi 2 - Quản lý Sách</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f7f7f7; }
        h1 { color: #2c3e50; }
        form { border: 1px solid #ccc; background: #fff; padding: 20px; max-width: 400px; margin-bottom: 30px; border-radius: 6px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 15px; padding: 8px 16px; background: #2c7be5; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #1a5fc0; }
        table { border-collapse: collapse; width: 100%; max-width: 750px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #2c3e50; color: #fff; }
        .con-hang { color: #1a8c34; font-weight: bold; }
        .sap-het { color: #e0a300; font-weight: bold; }
        .het-hang { color: #c0392b; font-weight: bold; }
        .thongbao-thanhcong { color: #1a8c34; }
        .thongbao-loi { color: #c0392b; }
    </style>
</head>
<body>

<h1>Quản lý Sách - Bài tập Buổi 2</h1>

<form method="POST" action="">
    <label for="ten_sach">Tên sách:</label>
    <input type="text" id="ten_sach" name="ten_sach" required>

    <label for="tac_gia">Tác giả:</label>
    <input type="text" id="tac_gia" name="tac_gia" required>

    <label for="the_loai">Thể loại:</label>
    <select id="the_loai" name="the_loai">
        <option value="Van hoc">Văn học</option>
        <option value="Khoa hoc">Khoa học</option>
        <option value="Ky nang song">Kỹ năng sống</option>
        <option value="Thieu nhi">Thiếu nhi</option>
    </select>

    <label for="so_luong">Số lượng:</label>
    <input type="number" id="so_luong" name="so_luong" min="0" required>

    <button type="submit" name="them_sach">Thêm sách</button>
</form>

<?php
session_start();

// Tổ chức dữ liệu bằng mảng - danh sách sách được lưu trong session
// (buổi sau sẽ thay bằng lưu vào cơ sở dữ liệu MySQL)
if (!isset($_SESSION['danh_sach_sach'])) {
    $_SESSION['danh_sach_sach'] = [];
}

/**
 * Hàm tự định nghĩa: xác định tình trạng kho dựa trên số lượng sách
 * Đây là 1 nghiệp vụ có ý nghĩa: phân loại mức tồn kho để cảnh báo nhập thêm sách
 */
function xacDinhTinhTrang($soLuong) {
    if ($soLuong <= 0) {
        return "Hết hàng";
    } elseif ($soLuong <= 5) {
        return "Sắp hết";
    } else {
        return "Còn hàng";
    }
}

// Tiếp nhận và xử lý dữ liệu nhập từ form
if (isset($_POST['them_sach'])) {
    $tenSach = trim($_POST['ten_sach']);
    $tacGia  = trim($_POST['tac_gia']);
    $theLoai = $_POST['the_loai'];
    $soLuong = (int) $_POST['so_luong'];

    // Sử dụng điều kiện để kiểm tra dữ liệu hợp lệ trước khi lưu
    if ($tenSach !== "" && $tacGia !== "" && $soLuong >= 0) {
        $sach = [
            'ten_sach'    => $tenSach,
            'tac_gia'     => $tacGia,
            'the_loai'    => $theLoai,
            'so_luong'    => $soLuong,
            'tinh_trang'  => xacDinhTinhTrang($soLuong)
        ];
        $_SESSION['danh_sach_sach'][] = $sach;
        echo "<p class='thongbao-thanhcong'>Đã thêm sách thành công!</p>";
    } else {
        echo "<p class='thongbao-loi'>Vui lòng nhập đầy đủ và hợp lệ thông tin.</p>";
    }
}
?>

<h2>Danh sách sách</h2>

<?php if (count($_SESSION['danh_sach_sach']) === 0): ?>
    <p>Chưa có sách nào trong danh sách. Hãy thêm sách bằng form phía trên.</p>
<?php else: ?>
    <table>
        <tr>
            <th>#</th>
            <th>Tên sách</th>
            <th>Tác giả</th>
            <th>Thể loại</th>
            <th>Số lượng</th>
            <th>Tình trạng</th>
        </tr>
        <?php
        $stt = 1;
        // Sử dụng vòng lặp để duyệt và hiển thị dữ liệu
        foreach ($_SESSION['danh_sach_sach'] as $sach) {
            if ($sach['tinh_trang'] === "Hết hàng") {
                $lopCss = "het-hang";
            } elseif ($sach['tinh_trang'] === "Sắp hết") {
                $lopCss = "sap-het";
            } else {
                $lopCss = "con-hang";
            }

            echo "<tr>";
            echo "<td>" . $stt . "</td>";
            echo "<td>" . htmlspecialchars($sach['ten_sach']) . "</td>";
            echo "<td>" . htmlspecialchars($sach['tac_gia']) . "</td>";
            echo "<td>" . htmlspecialchars($sach['the_loai']) . "</td>";
            echo "<td>" . $sach['so_luong'] . "</td>";
            echo "<td class='" . $lopCss . "'>" . $sach['tinh_trang'] . "</td>";
            echo "</tr>";
            $stt++;
        }
        ?>
    </table>
<?php endif; ?>

</body>
</html>
