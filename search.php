<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "koneksi.php";
require "session.php";
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['loginbtn']) || $_SESSION['loginbtn'] == false) {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = $_SESSION['user_id'];

$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$selected_categories = isset($_GET['category']) ? $_GET['category'] : [];
$select_kategori = mysqli_query($con, "SELECT * FROM kategori") or die("Query gagal: " . mysqli_error($con));

$sql_condition = "WHERE 1=1";

if ($search_query != '') {
    $sql_condition .= " AND (nama LIKE '%$search_query%' OR pengarang LIKE '%$search_query%')";
}

if (!empty($selected_categories)) {
    $category_ids_str = implode(",", array_map('intval', $selected_categories));
    $sql_condition .= " AND kategori_id IN ($category_ids_str)";
}

// Use the $sql_condition in the main query to filter by search and categories
$select_produk = mysqli_query($con, "SELECT * FROM produk $sql_condition ORDER BY id LIMIT 10") or die('Query failed');

$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'terbaru';

// Mapping parameter sort_by ke kolom database
$order_by = "p.id DESC"; // Default
switch ($sort_by) {
    case 'terlaris':
        // Mengurutkan berdasarkan jumlah rating terbanyak
        $order_by = "(SELECT COUNT(*) FROM rating r WHERE r.produk_id = p.id) DESC";
        break;
    case 'harga_terendah':
        // Pilih harga_diskon jika ada, jika tidak gunakan harga asli
        $order_by = "(CASE WHEN p.harga_diskon > 0 THEN p.harga_diskon ELSE p.harga END) ASC";
        break;
    case 'harga_tertinggi':
        // Pilih harga_diskon jika ada, jika tidak gunakan harga asli
        $order_by = "(CASE WHEN p.harga_diskon > 0 THEN p.harga_diskon ELSE p.harga END) DESC";
        break;
}

// Query untuk mengambil data produk dengan informasi tambahan
$sql = "
    SELECT 
        p.*, 
        IFNULL(o.total_penjualan, 0) AS total_penjualan, 
        IFNULL(o.total_quantity, 0) AS total_quantity 
    FROM produk p
    LEFT JOIN (
        SELECT 
            produk_id, 
            COUNT(*) AS total_penjualan, -- Banyaknya pesanan
            SUM(quantity) AS total_quantity -- Total quantity yang dibeli
        FROM order_items
        GROUP BY produk_id
    ) o ON p.id = o.produk_id
    $sql_condition
    ORDER BY $order_by
    LIMIT 10
";

$select_produk = mysqli_query($con, $sql) or die('Query failed: ' . mysqli_error($con));

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiksi - Kategori</title>
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="search.css">
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
                            <h4>Fiksi</a></h4>
                            <ul>
                                <li><a href="kategori.php?kategori=1">Novel</a></li>
                                <li><a href="kategori.php?kategori=2">Cerita Pendek</a></li>
                                <li><a href="kategori.php?kategori=3">Komik</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Non-Fiksi</a></h4>
                            <ul>
                                <li><a href="kategori.php?kategori=4">Biografi</a></li>
                                <li><a href="kategori.php?kategori=5">Sejarah</a></li>
                                <li><a href="kategori.php?kategori=6">Psikologi</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Sains & Teknologi</a></h4>
                            <ul>
                                <li><a href="kategori.php?kategori=7">Ilmu Komputer</a></li>
                                <li><a href="kategori.php?kategori=8">Fisika</a></li>
                                <li><a href="kategori.php?kategori=9">Biologi</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>Bisnis & Ekonomi</a></h4>
                            <ul>
                                <li><a href="kategori.php?kategori=10">Kewirausahaan</a></li>
                                <li><a href="kategori.php?kategori=11">Investasi</a></li>
                                <li><a href="kategori.php?kategori=12">Manajemen</a></li>
                            </ul>
                        </div>
                        <div class="dropdown-section">
                            <h4>
                                <a">Pengembangan Diri</a>
                            </h4>
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
                <form method="GET" action="search.php" style="width: 100%; display: flex; align-items: center;">
                    <input type="text" name="query" placeholder="Cari Buku" class="search-input"
                        value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-icon">
                        <img src="image/search-btn.svg" class="search-img" alt="Search Icon">
                    </button>
                </form>
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

    <main class="main-content">
        <div class="content-wrapper">
            <aside class="sidebar">
                <h3 class="filter-title">Filter</h3>
                <div class="filter-section">
                    <h4 class="filter-section-title">Kategori</h4>

                    <?php
                    ?>
                    <form action="search.php" method="get">
                        <input type="hidden" name="query" value="<?php echo htmlspecialchars($search_query); ?>">

                        <?php
                        $query_kategori = "SELECT * FROM subk ORDER BY id ASC";
                        $select_kategori = mysqli_query($con, $query_kategori) or die("Query gagal: " . mysqli_error($con));

                        if (mysqli_num_rows($select_kategori) > 0) {
                            echo '<ul class="filter-list">';

                            while ($fetch_kategori = mysqli_fetch_assoc($select_kategori)) {
                                echo '<li>';
                                echo '<div class="category-toggle" data-kategori-id="' . $fetch_kategori['id'] . '">' . $fetch_kategori['nama'] . '</div>';
                                echo '<ul class="subcategory-list" style="display: none;">';

                                $query_subkategori = "SELECT * FROM kategori WHERE sub_k = '{$fetch_kategori['id']}' ORDER BY id ASC";
                                $select_subkategori = mysqli_query($con, $query_subkategori) or die("Query gagal: " . mysqli_error($con));

                                if (mysqli_num_rows($select_subkategori) > 0) {
                                    while ($fetch_subkategori = mysqli_fetch_assoc($select_subkategori)) {
                                        $checked = in_array($fetch_subkategori['id'], $selected_categories) ? 'checked' : '';
                                        echo '<li>';
                                        echo '<label>';
                                        echo '<input type="checkbox" name="category[]" value="' . $fetch_subkategori['id'] . '" ' . $checked . ' /> ' . $fetch_subkategori['nama'];
                                        echo '</label>';
                                        echo '</li>';
                                    }
                                } else {
                                    echo '<li>Tidak ada subkategori ditemukan.</li>';
                                }

                                echo '</ul>';
                                echo '</li>';
                            }

                            echo '</ul>';
                            echo '<div class="filter-section">';
                            echo '<button type="submit" id="apply-price-filter" class="apply-price-btn">Apply</button>';
                            echo '</div>';
                        } else {
                            echo '<p>Tidak ada kategori ditemukan.</p>';
                        }
                        ?>
                    </form>
                </div>
            </aside>


            <section class="book-sections">
                <div class="section-header">
                    <h2 class="section-title">Hasil Dari Pencarian "<?php echo htmlspecialchars($search_query); ?>"</h2>
                    <div class="dropdown-filter">
                        <form method="GET" action="search.php" id="sortForm">
                            <input type="hidden" name="query" value="<?php echo htmlspecialchars($search_query); ?>">
                            <?php foreach ($selected_categories as $category) { ?>
                                <input type="hidden" name="category[]" value="<?php echo $category; ?>">
                            <?php } ?>

                            <select name="sort_by" class="dropdown-toggle2"
                                onchange="document.getElementById('sortForm').submit();">
                                <option value="terbaru" <?php echo $sort_by == 'terbaru' ? 'selected' : ''; ?>>Terbaru
                                </option>
                                <option value="terlaris" <?php echo $sort_by == 'terlaris' ? 'selected' : ''; ?>>Terlaris
                                </option>
                                <option value="harga_terendah" <?php echo $sort_by == 'harga_terendah' ? 'selected' : ''; ?>>Harga Terendah</option>
                                <option value="harga_tertinggi" <?php echo $sort_by == 'harga_tertinggi' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            </select>
                        </form>
                    </div>

                </div>

                <div class="container">
                    <?php
                    if (mysqli_num_rows($select_produk) > 0) {
                        while ($fetch_produk = mysqli_fetch_assoc($select_produk)) {
                            ?>
                            <form class="book-card" action="detail.php" method="get">
                                <div class="book-image">
                                    <a href="detail.php?produk_id=<?php echo $fetch_produk['id']; ?>">
                                        <div class="book-cover">
                                            <img class="book-cover" src="image/<?php echo $fetch_produk['foto']; ?>"
                                                alt="Gambar Produk" />
                                        </div>
                                    </a>
                                </div>
                                <div class="book-details">
                                    <h3 class="book-title"><?php echo $fetch_produk['nama']; ?></h3>
                                    <p class="book-author"><?php echo $fetch_produk['pengarang']; ?></p>
                                    <p class="stock-status">
                                        <?php echo ($fetch_produk['stok'] > 0) ? 'Stok Tersedia' : 'Stok Habis'; ?>
                                    </p>

                                    <?php if ($fetch_produk['harga_diskon'] != NULL) { ?>
                                        <p class="book-price normal-price">Rp.
                                            <?php echo number_format($fetch_produk['harga'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="book-price discount-price">Rp.
                                            <?php echo number_format($fetch_produk['harga_diskon'], 0, ',', '.'); ?>
                                        </p>
                                    <?php } else { ?>
                                        <p class="book-price discount-price">Rp.
                                            <?php echo number_format($fetch_produk['harga'], 0, ',', '.'); ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </form>
                            <?php
                        }
                    } else {
                        echo "<p>No Products Found.</p>";
                    }
                    ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const categoryToggles = document.querySelectorAll('.category-toggle');

            categoryToggles.forEach(toggle => {
                toggle.addEventListener('click', () => {
                    const subcategoryList = toggle.nextElementSibling;
                    if (subcategoryList.style.display === "none" || subcategoryList.style.display === "") {
                        subcategoryList.style.display = "block";
                    } else {
                        subcategoryList.style.display = "none";
                    }
                });
            });

            const dropdownToggle = document.querySelector('.dropdown-toggle2');
            const dropdownMenu = document.querySelector('.dropdown-menu2');
            const filterMenus = document.querySelectorAll('.filter-menu2');

            if (dropdownToggle && dropdownMenu) {
                dropdownToggle.addEventListener('click', () => {
                    dropdownMenu.style.display = dropdownMenu.style.display === "none" || dropdownMenu.style.display === "" ? "block" : "none";
                });

                filterMenus.forEach(menu => {
                    menu.addEventListener('click', () => {
                        document.querySelector('.filter-menu.active').classList.remove('active');
                        menu.classList.add('active');
                        dropdownMenu.style.display = "none";

                        
                        dropdownToggle.querySelector('.filter-menu').textContent = menu.textContent;
                    });
                });
            }
        });
    </script>
</body>

</html>