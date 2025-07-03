<?php
require "session.php";
require "koneksi.php";

if (isset($_GET['produk_id'])) {
    $produk_id = intval($_GET['produk_id']); 

    $select_produk = mysqli_query($con, "SELECT * FROM `produk` WHERE `id` = $produk_id") or die('Query failed');

    if (mysqli_num_rows($select_produk) > 0) {
        $fetch_produk = mysqli_fetch_assoc($select_produk);
    } else {
        echo "";
    }
} else {
    echo "";
}

$query = "SELECT * FROM produk WHERE id = $produk_id";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $produk = mysqli_fetch_assoc($result); 
} else {
    die("");
}

if (isset($_POST['add'])) {
    if (isset($_POST['produk_id']) && !empty($_POST['produk_id'])) {
        $produk_id = $_POST['produk_id'];
        $user_id = $_SESSION['user_id']; 

        $stmt = $con->prepare("SELECT * FROM `produk` WHERE id = ?");
        $stmt->bind_param("i", $produk_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $message = "";
        } else {
            $stmt = $con->prepare("SELECT * FROM `cart` WHERE produk_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $produk_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "Produk sudah ada di keranjang.";
            } else {
                $stmt = $con->prepare("INSERT INTO `cart` (user_id, produk_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $produk_id);

                if ($stmt->execute()) {
                    $message = "Produk berhasil ditambahkan ke keranjang.";
                    header("location:cart.php");
                    exit();
                } else {
                    $message = "Gagal menambahkan produk ke keranjang.";
                }
            }
        }
    } else {
        $message = "Produk tidak valid.";
    }
}

$query_reviews = "
    SELECT r.*, u.username, u.foto
    FROM rating r
    JOIN users u ON r.user_id = u.id
    WHERE r.produk_id = ?
    ORDER BY r.created_at DESC
";

$stmt = $con->prepare($query_reviews);
$stmt->bind_param("i", $produk_id);
$stmt->execute();

$result_reviews = $stmt->get_result();

$query_avg_rating = "SELECT AVG(rating) AS avg_rating FROM rating WHERE produk_id = $produk_id";
$result_avg_rating = mysqli_query($con, $query_avg_rating);

$avg_rating = 0;
if ($result_avg_rating && mysqli_num_rows($result_avg_rating) > 0) {
    $row = mysqli_fetch_assoc($result_avg_rating);
    $avg_rating = $row['avg_rating'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="detail.css">
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
                        <img src="image/cart-btn.svg" class="icon-img white-icon" alt="Cart Icon">
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

    <div class="detail-header">Detail Buku</div>
    <div class="container">
        <div class="book-details">
            <div class="book-photo">
                <div class="image-container">
                    <img src="image/<?php echo $produk['foto']; ?>" alt="<?php echo $produk['nama']; ?>">
                </div>
                <div class="book-price">
                    <?php if ($produk['harga_diskon']): ?>
                        <div class="original-price">
                            <del>Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></del>
                        </div>
                        <div class="discounted-price">
                            Rp. <?php echo number_format($produk['harga_diskon'], 0, ',', '.'); ?>
                        </div>
                    <?php else: ?>
                        <div class="final-price">
                            Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="book-info">
                <div class="book-info-header">
                    <h2>Informasi Buku</h2>
                </div>
                <h3><strong><?php echo $produk['nama']; ?></strong></h3>
                <p><strong><?php echo $produk['pengarang']; ?></strong></p>
                <p><?php echo $produk['deskripsi']; ?></p>
                <div class="stock-section">
                    <span
                        class="stock-status"><?php echo ($fetch_produk['stok'] > 0) ? ' Tersedia' : 'Stok Habis'; ?></span>
                </div>
                <div class="container-footer">
                    <div class="ratings-section">
                        <div class="stars">
                            <?php
                            $full_stars = floor($avg_rating);
                            $half_star = ($avg_rating - $full_stars) >= 0.5 ? 1 : 0;
                            $empty_stars = 5 - $full_stars - $half_star; 
                            
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<span class="star filled">&#9733;</span>';
                            }

                            if ($half_star) {
                                echo '<span class="star half">&#9733;</span>';
                            }

                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '<span class="star empty">&#9734;</span>';
                            }
                            ?>
                            <span class="rating-value"><?php echo number_format($avg_rating, 1); ?></span>
                        </div>
                    </div>
                    <div class="add-to-cart">
                        <form method="post" action="">
                            <input type="hidden" name="produk_id" value="<?php echo $produk['id']; ?>">
                            <button type="submit" name="add" class="cart-btn" onclick="showNotification()">
                                <img src="image/cart-btn.png" alt="Cart Icon">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="reviews-container">
            <h2>Ulasan</h2>
            <?php
            if (mysqli_num_rows($result_reviews) > 0) {
                while ($review = mysqli_fetch_assoc($result_reviews)) {
                    $filled_stars = floor($review['rating']);
                    $empty_stars = 5 - $filled_stars;
                    ?>
                    <div class="review">
                        <div class="review-header">
                            <div class="user-info">
                                <img src="image/<?php echo !empty($review['foto']) ? $review['foto'] : 'avatar.png'; ?>"
                                    alt="User Avatar" class="avatar">
                                <span class="username"><?php echo htmlspecialchars($review['username']); ?></span>
                            </div>
                            <span class="review-date"><?php echo date("d F Y", strtotime($review['created_at'])); ?></span>
                        </div>
                        <p class="review-text">
                            <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                        </p>
                        <div class="review-footer">
                            <div class="stars">
                                <?php
                                for ($i = 0; $i < $filled_stars; $i++) {
                                    echo '<span class="star filled">&#9733;</span>';
                                }
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<span class="star empty">&#9734;</span>';
                                }
                                ?>
                                <span class="rating-value"><?php echo number_format($review['rating'], 1); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "Belum ada ulasan untuk produk ini.";
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>

    <div id="wishlist-notification" class="notification">
        Produk berhasil ditambahkan ke keranjang.
    </div>

    <script>
        function showNotification() {
            const notification = document.getElementById("wishlist-notification");
            notification.classList.add("show");

            setTimeout(() => {
                notification.classList.remove("show");
            }, 3000); 
        }
    </script>
</body>

</html>