<?php
require "session.php";
require "koneksi.php";


if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];


if (isset($_POST['hapus'])) {
    $produk_id = $_POST['produk_id'];


    $delete_query = mysqli_query($con, "DELETE FROM `cart` WHERE produk_id = '$produk_id' AND user_id = '$user_id'");

}
if (isset($_POST['quantity']) && isset($_POST['produk_id'])) {
    $produk_id = $_POST['produk_id'];
    $quantity = $_POST['quantity'];

    if ($quantity > 0) {
        $update_query = mysqli_query($con, "UPDATE `cart` SET quantity = '$quantity' WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>

<body>

    <nav class="navbar">
        <div class="upper-nav">
            <div class="logo">
                <a href="dashboard.php"> <img src="image/logo white.png" alt="Logo"> </a>
            </div>

            <div class="menu">
                <div class="dropdown">

                    <a href="semua.php" class="menu-item dropdown-toggle">Semua Buku</a>
                </div>
                <div class="dropdown">

                    <a href="#" class="menu-item dropdown-toggle">Kategori</a>
                    <div class="dropdown-menu">
                        <div class="dropdown-section">
                            <h4>Fiksi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=1">Novel</a></li>
                                <li><a href="kategori.php?kategori=2">Cerita Pendek</a></li>
                                <li><a href="kategori.php?kategori=3">Komik</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Non-Fiksi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=4">Biografi</a></li>
                                <li><a href="kategori.php?kategori=5">Sejarah</a></li>
                                <li><a href="kategori.php?kategori=6">Psikologi</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Sains & Teknologi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=7">Ilmu Komputer</a></li>
                                <li><a href="kategori.php?kategori=8">Fisika</a></li>
                                <li><a href="kategori.php?kategori=9">Biologi</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Bisnis & Ekonomi</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=10">Kewirausahaan</a></li>
                                <li><a href="kategori.php?kategori=11">Investasi</a></li>
                                <li><a href="kategori.php?kategori=12">Manajemen</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Pengembangan Diri</h4>
                            <ul>
                                <li><a href="kategori.php?kategori=Motivasi">Motivasi</a></li>
                                <li><a href="kategori.php?kategori=Kesehatan">Kesehatan</a></li>
                                <li><a href="kategori.php?kategori=Spiritual">Spiritual</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-bar">
                <input type="text" placeholder="Cari Buku" class="search-input">
                <button type="submit" class="search-icon">
                    <img src="image/search-btn.svg" class="search-img" alt="Search Icon">
                </button>
            </div>

            <div class="nav-icons">
                <div class="nav-icon">
                    <a href="cart.php">
                        <img src="image/cart-btno.svg" class="icon-img white-icon" alt="Cart Icon">
                        <img src="image/cart-btno.svg" class="icon-img orange-icon" alt="Cart Icon">
                    </a>
                </div>

                <div class="nav-icon profile">
                    <a href="profile.php">
                        <img src="image/profile white.svg" class="icon-img white-icon" alt="Profile Icon">
                        <img src="image/profile orange.svg" class="icon-img orange-icon" alt="Profile Icon">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="header-title">Keranjang Buku</h1>

        <?php
        $select_cart = mysqli_query($con, "SELECT c.*, p.nama, p.pengarang, p.foto, p.harga,p.harga_diskon, p.stok FROM `cart` c JOIN `produk` p ON c.produk_id = p.id WHERE c.user_id = '$user_id'") or die('Query failed');

        if (mysqli_num_rows($select_cart) > 0) {
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                ?>

                <div class="book-item">
                    <div class="book-details">
                        <span class="author">Oleh <?php echo $fetch_cart['pengarang']; ?></span>
                        <div class="book-info">
                            <div class="book-cover">
                                <img class="book-cover" src="image/<?php echo $fetch_cart['foto']; ?>" alt="Gambar Produk" />
                            </div>
                            <div class="book-description">
                                <h2 class="book-title"><?php echo $fetch_cart['nama']; ?></h2>
                                <p class="stock"><?php echo ($fetch_cart['stok'] > 0) ? 'Stok Tersedia' : 'Stok Habis'; ?></p>
                                <div class="price-section">
                                    <p class="current-price">Rp. <?php echo number_format(
                                        (($fetch_cart['harga_diskon'] !== null ? min($fetch_cart['harga_diskon'], $fetch_cart['harga']) : $fetch_cart['harga']) * $fetch_cart['quantity']),
                                        0,
                                        ',',
                                        '.'
                                    ); ?>
                                    </p>
                                </div>
                            </div>
                            <form method="post" action="">
                                <label>Jumlah item</label>
                                <input type="number" name="quantity" class="quantity-input"
                                    value="<?php echo $fetch_cart['quantity']; ?>" min="1" onchange="this.form.submit()">
                                <input type="hidden" name="produk_id" value="<?php echo $fetch_cart['produk_id']; ?>">
                            </form>


                            <form method="post" action="">
                                <input type="hidden" name="produk_id" value="<?php echo $fetch_cart['produk_id']; ?>">
                                <button class="button delete" name="hapus">Hapus</button>
                            </form>

                        </div>
                    </div>
                </div>

                <?php
            } ?>
            <div class="action-buttons-2">
                <button class="buy-btn" onclick="window.location.href='payment.php'">Beli</button>
            </div><?php
        } else {
            echo "<p>Keranjang Anda kosong.</p>";
        }
        ?>


    </div>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>
</body>

</html>