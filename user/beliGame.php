<?php
session_start();

// Cek login
if (!isset($_SESSION['username'])) {
    echo "<script>
        alert('Anda harus login terlebih dahulu.');
        window.location.href = '../login.php';
    </script>";
    exit;
}

require_once '../db_connection.php';
$conn = connect_db();

// Cek role dari session
$role = $_SESSION['role']; // Role akan diambil dari session

// Proses pembelian
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $conn->real_escape_string($_POST['id']);
    $id_pembeli = $_SESSION['id_user']; // Pastikan `id_user` tersimpan di session saat login
    $voucher_id = $role === 'member' ? ($_POST['voucher'] ?? null) : null; // Hanya gunakan voucher jika role adalah member

    // Ambil detail game berdasarkan id
    $query = "SELECT id, name, price FROM games WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $game = $result->fetch_assoc();
        $price = $game['price'];
        $totalPrice = $price; // Default total price
        $discountAmount = 0;  // Default discount

        // Cek dan terapkan diskon jika voucher dipilih
        if ($voucher_id) {
            $query_voucher = "SELECT discount_rate, max_discount FROM vouchers 
                              WHERE id = ? AND start_date <= CURDATE() 
                              AND end_date >= CURDATE() AND usage_limit > 0";
            $stmt_voucher = $conn->prepare($query_voucher);
            $stmt_voucher->bind_param("i", $voucher_id);
            $stmt_voucher->execute();
            $result_voucher = $stmt_voucher->get_result();

            if ($result_voucher->num_rows > 0) {
                $voucher = $result_voucher->fetch_assoc();

                // Hitung diskon
                $discountAmount = $price * ($voucher['discount_rate'] / 100);

                // Terapkan batas maksimal diskon jika ada
                if ($voucher['max_discount'] > 0) {
                    $discountAmount = min(
                        $discountAmount,
                        $voucher['max_discount']
                    );
                }

                // Hitung total harga setelah diskon
                $totalPrice = $price - $discountAmount;

                // Update usage_limit dan used_count pada voucher
                $query_update_voucher = "UPDATE vouchers 
                                         SET usage_limit = usage_limit - 1, used_count = used_count + 1 
                                         WHERE id = ?";
                $stmt_update_voucher = $conn->prepare($query_update_voucher);
                $stmt_update_voucher->bind_param("i", $voucher_id);
                $stmt_update_voucher->execute();
            }
        }

        // Masukkan data ke tabel riwayat pembelian
        $query_insert = "INSERT INTO purchase_history (user_id, game_id, voucher_id, discount_amount, total_price) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("iiidd", $id_pembeli, $game['id'], $voucher_id, $discountAmount, $totalPrice);

        if ($stmt_insert->execute()) {
            echo "<script>
                alert('Pesanan berhasil dibuat.');
                window.location.href = 'userHistory.php';
            </script>";
            exit;
        } else {
            echo "<script>
                alert('Terjadi kesalahan saat membuat pesanan.');
                window.location.href = 'gameList.php';
            </script>";
            exit;
        }
    } else {
        echo "<script>
            alert('Game tidak ditemukan.');
            window.location.href = 'gameList.php';
        </script>";
        exit;
    }
}


// Tampilkan halaman detail game
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>
        alert('ID Game tidak valid.');
        window.location.href = 'gameList.php';
    </script>";
    exit;
}

$idGame = $conn->real_escape_string($_GET['id']);
$query = "SELECT id, name, price, photo FROM games WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idGame);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>
        alert('Game tidak ditemukan.');
        window.location.href = 'gameList.php';
    </script>";
    exit;
}

$row = $result->fetch_assoc();

// Ambil daftar voucher jika role adalah member
$vouchers = [];
if ($role === 'member') {
    $query_vouchers = "SELECT id, code, discount_rate FROM vouchers 
                       WHERE start_date <= CURDATE() AND end_date >= CURDATE() AND usage_limit > 0";
    $result_vouchers = $conn->query($query_vouchers);
    $vouchers = $result_vouchers->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Beli Game</title>
    <link rel="stylesheet" href="../../css/user/beliGame.css" />
</head>

<body>
    <div class="navbar">
        <h1>GameStore</h1>
        <ul>
            <li><a href="dashboardUser.php">Dashboard</a></li>
            <li><a href="gameList.php">Game List</a></li>
            <li><a href="userHistory.php">Riwayat Pembelian</a></li>
            <li><a href="logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <div class="game-details">
            <div class="game-image">
                <img src="../images/<?php echo htmlspecialchars($row['photo']); ?>" alt="Game Image">
            </div>
            <div class="game-info">
                <h1><?php echo htmlspecialchars($row['name']); ?></h1>
                <div class="price-container">
                    <p class="original-price">Rp. <?php echo number_format($row['price'], 0, ',', '.'); ?></p>
                    <p class="discounted-price" id="discountedPrice" style="display: none;"></p>
                    <p class="discount-info" id="discountInfo" style="display: none;"></p>
                </div>

                <form method="POST" id="purchaseForm">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                    <!-- Select Voucher hanya untuk role member -->
                    <?php if ($role === 'member') : ?>
                        <label for="voucher">Pilih Voucher:</label>
                        <select name="voucher" id="voucher">
                            <option value="">Tidak Menggunakan Voucher</option>
                            <?php foreach ($vouchers as $voucher) : ?>
                                <option value="<?php echo $voucher['id']; ?>"
                                    data-rate="<?php echo $voucher['discount_rate']; ?>"
                                    data-max-discount="<?php echo $voucher['max_discount'] ?? 0; ?>">
                                    <?php echo $voucher['code']; ?> - Diskon <?php echo $voucher['discount_rate']; ?>%
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <button type="submit" class="buy-btn">Konfirmasi Beli</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const originalPrice = <?php echo $row['price']; ?>;
        const voucherSelect = document.getElementById('voucher');
        const discountedPriceEl = document.getElementById('discountedPrice');
        const discountInfoEl = document.getElementById('discountInfo');
        const originalPriceEl = document.querySelector('.original-price');

        function formatRupiah(number) {
            return 'Rp. ' + number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        voucherSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                const discountRate = parseFloat(selectedOption.getAttribute('data-rate'));
                const maxDiscount = parseFloat(selectedOption.getAttribute('data-max-discount'));

                // Hitung diskon
                let discountAmount = originalPrice * (discountRate / 100);

                // Terapkan batas maksimal diskon jika ada
                if (maxDiscount > 0) {
                    discountAmount = Math.min(discountAmount, maxDiscount);
                }

                const discountedPrice = originalPrice - discountAmount;

                // Tampilkan informasi diskon
                originalPriceEl.style.textDecoration = 'line-through';
                discountedPriceEl.style.display = 'block';
                discountedPriceEl.textContent = formatRupiah(discountedPrice);
                discountInfoEl.style.display = 'block';
                discountInfoEl.textContent = `Diskon: ${formatRupiah(discountAmount)} (${discountRate}%)`;
            } else {
                // Kembalikan ke harga asli jika tidak ada voucher
                originalPriceEl.style.textDecoration = 'none';
                discountedPriceEl.style.display = 'none';
                discountInfoEl.style.display = 'none';
            }
        });
    });
</script>

<?php
$conn->close();
?>