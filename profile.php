<?php
require "session.php";
require "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$select_profile = mysqli_query($con, "SELECT username, email, alamat, foto FROM users WHERE id = '$user_id'") or die(mysqli_error($con));

if (mysqli_num_rows($select_profile) > 0) {
    $fetch_profile = mysqli_fetch_assoc($select_profile);
    $username = $fetch_profile['username'];
    $email = $fetch_profile['email'];
    $alamat = $fetch_profile['alamat'];
    $foto = $fetch_profile['foto'];
} else {
    $username = $email = $alamat = $foto = "";
}

if (isset($_POST['simpan'])) {
    $new_username = mysqli_real_escape_string($con, $_POST['username']);
    $new_alamat = mysqli_real_escape_string($con, $_POST['alamat']);


    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $tmp_name = $_FILES['image']['tmp_name'];

        $valid_extensions = ['jpg', 'jpeg', 'png', 'avif'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $valid_extensions)) {
            echo "<script>alert('Ekstensi gambar tidak valid.');</script>";
        } elseif ($file_size > 10000000) {
            echo "<script>alert('Ukuran file terlalu besar. Maksimal 10 MB.');</script>";
        } else {
            $new_image_name = uniqid() . '.' . $file_extension;
            $upload_path = 'image/' . $new_image_name;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                $queryupdate = "UPDATE users SET username='$new_username', email='$new_email', alamat='$new_alamat', foto='$new_image_name' WHERE id='$user_id'";
            } else {
                echo "<script>alert('Gagal mengunggah gambar.');</script>";
            }
        }
    } else {
        $queryupdate = "UPDATE users SET username='$new_username', alamat='$new_alamat' WHERE id='$user_id'";
    }

    mysqli_query($con, $queryupdate) or die(mysqli_error($con));
    header("Location: profile.php");
    exit();
}

if (isset($_POST['toggle_wishlist']) && isset($_POST['produk_id'])) {
    $produk_id = mysqli_real_escape_string($con, $_POST['produk_id']);

    $check_wishlist = mysqli_query($con, "SELECT * FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");

    if (mysqli_num_rows($check_wishlist) > 0) {
        $delete_wishlist = mysqli_query($con, "DELETE FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'");
        if (!$delete_wishlist) {
            http_response_code(500);
            echo "Error removing item from wishlist.";

            exit();
        }
    } else {

        $add_wishlist = mysqli_query($con, "INSERT INTO wishlist (produk_id, user_id) VALUES ('$produk_id', '$user_id')");
        if (!$add_wishlist) {
            http_response_code(500);
            echo "Error adding item to wishlist.";
            exit();
        }
    }
    http_response_code(200);
    echo "Wishlist updated successfully.";
}

$select_wishlist = mysqli_query($con, "SELECT c.*, p.nama, p.pengarang, p.foto, p.stok, p.harga,p.harga_diskon  FROM wishlist c JOIN produk p ON c.produk_id = p.id WHERE c.user_id = '$user_id'") or die('Query failed');


if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="image/favicon.png">
    <link rel="stylesheet" href="profil.css">
</head>

<body>



    <nav class="navbar">
        <div class="upper-nav">
            <div class="logo">
                <a href="dashboard.php"> <img src="image/logo white.png" alt="Logo"> </a>
            </div>

            <div class="nav-menu">
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
                        <img src="image/profile orange.svg" class="icon-img white-icon" alt="Profile Icon">
                        <img src="image/profile orange.svg" class="icon-img orange-icon" alt="Profile Icon">
                    </a>
                </div>
            </div>
        </div>
    </nav>


    <div class="main-container">
        <div class="sidebar">
            <ul class="menu">
                <li data-tab="profile" class="active">Profil</li>
                <li data-tab="wishlist">Wishlist</li>
                <li data-tab="orders">Pesanan</li>
            </ul>
        </div>

        <div class="content">
            <div id="profile" class="tab-content active">
                <div class="profile-container">

                    <form class="profile-form" method="POST" enctype="multipart/form-data">
                        <div class="photo-container">
                            <div class="header-content">
                                <span>Foto Pengguna</span>
                                <div class="header-line"></div>
                            </div>
                            <div class="photo">
                                <img class="avatar-placeholder"
                                    src="image/<?php echo $foto ? $foto : 'default-avatar.png'; ?>" alt="Foto Profil">
                            </div>
                            <button class="edit-button" type="button">Edit</button>
                            <input type="file" name="image" accept="image/*" class="file-input" hidden>
                        </div>

                        <div class="information-container">
                            <div class="header-content">
                                <span>Informasi Pengguna</span>
                                <div class="header-line"></div>
                            </div>

                            <div class="form-input">

                                <div class="id">
                                    <label>ID: </label>
                                    <span><?php echo htmlspecialchars($user_id); ?></span>
                                </div>
                                <label>Username</label>
                                <input type="text" placeholder="Nama Pengguna"
                                    value="<?php echo htmlspecialchars($username); ?>" name="username">
                                <label>Alamat</label>
                                <textarea placeholder="Alamat Pengguna" rows="3"
                                    name="alamat"><?php echo htmlspecialchars($alamat); ?></textarea>

                                <div class="information-button">
                                    <button name="simpan" type="submit" class="save-button">Simpan</button>
                                    <button name="logout" class="logout-button" type="submit">Log Out</button>
                                </div>


                            </div>

                        </div>
                    </form>


                </div>
            </div>

            <div id="wishlist" class="tab-content">
                <h2 style="color: #7b1fa2;">Wishlist</h2>
                <?php if (mysqli_num_rows($select_wishlist) > 0): ?>
                    <div class="card-list">
                        <?php while ($fetch_wishlist = mysqli_fetch_assoc($select_wishlist)):
                            $produk_id = $fetch_wishlist['produk_id'];
                            $fetch_orders = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM produk WHERE id = '$produk_id'"));
                            $in_wishlist = mysqli_num_rows(mysqli_query($con, "SELECT * FROM wishlist WHERE produk_id = '$produk_id' AND user_id = '$user_id'")) > 0;
                            ?>
                            <form class="book-card" action="detail.php" method="get">
                                <div class="book-image">
                                    <a href="detail.php?produk_id=<?php echo $fetch_orders['id']; ?>">
                                        <div class="book-cover">
                                            <img class="book-cover"
                                                src="image/<?php echo htmlspecialchars($fetch_orders['foto']); ?>"
                                                alt="Gambar Produk" />
                                        </div>
                                    </a>
                                    <button type="button" class="wishlist"
                                        onclick="toggleWishlist(this, <?php echo $fetch_orders['id']; ?>)"
                                        data-in-wishlist="<?php echo $in_wishlist ? 'true' : 'false'; ?>">
                                        <img src="image/heart_grey.svg" class="heart-icon heart-grey" alt="Heart Icon"
                                            style="display:<?php echo $in_wishlist ? 'none' : 'inline'; ?>;" />
                                        <img src="image/heart.svg" class="heart-icon heart-red" alt="Heart Icon"
                                            style="display:<?php echo $in_wishlist ? 'inline' : 'none'; ?>;" />
                                    </button>
                                </div>
                                <div class="book-details">
                                    <h3 class="book-title"><?php echo htmlspecialchars($fetch_orders['nama']); ?></h3>
                                    <p class="book-author"><?php echo htmlspecialchars($fetch_orders['pengarang']); ?></p>
                                    <p class="stock-status">
                                        <?php echo ($fetch_orders['stok'] > 0) ? 'Stok Tersedia' : 'Stok Habis'; ?>
                                    </p>

                                    <?php if ($fetch_orders['harga_diskon'] != NULL): ?>
                                        <p class="book-price normal-price">Rp.
                                            <?php echo number_format($fetch_orders['harga'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="book-price discount-price">Rp.
                                            <?php echo number_format($fetch_orders['harga_diskon'], 0, ',', '.'); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="book-price discount-price">Rp.
                                            <?php echo number_format($fetch_orders['harga'], 0, ',', '.'); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </form>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    <p>Wishlist Anda kosong.</p>
                <?php endif; ?>
            </div>


            <div id="orders" class="tab-content">
                <div class="header-content-or">
                    <div class="header-title-or">
                        <h2>Pesanan</h2>
                    </div>

                    <div class="dropdown-filter">
                        <button class="menu-item2 dropdown-toggle2">
                            <img src="image/dropdown_white.svg" class="dropdown-icon" alt="dropdown-icon" />
                            <span class="filter-menu active">All</span>
                        </button>

                        <div class="dropdown-menu2">
                            <ul>
                                <li class="filter-menu2 active" data-status="all">All</li>
                                <li class="filter-menu2 " data-status="dikemas">Dikemas</li>
                                <li class="filter-menu2" data-status="dikirim">Dikirim</li>
                                <li class="filter-menu2" data-status="ulasan">Menunggu ulasan</li>
                                <li class="filter-menu2" data-status="selesai">Selesai</li>
                            </ul>
                        </div>

                    </div>
                </div>



                <div class="order-container" data-status="dikemas">
                    <?php $select_orders = mysqli_query($con, "SELECT id, user_id, status_order, total_amount, created_at, updated_at FROM orders WHERE user_id = '$user_id' AND status_order= 1 ");
                    if (mysqli_num_rows($select_orders) > 0) {
                        while ($fetch_orders = mysqli_fetch_assoc($select_orders)) { ?>
                            <div class="order-card">
                                <div class="order-info">
                                    <div class="details">
                                        <p class="title">Tanggal Pesanan:
                                            <?php echo date("d F Y", strtotime($fetch_orders['created_at'])); ?>
                                        </p>
                                        <p class="order-date"><?php echo htmlspecialchars($alamat) ?></p>
                                        <p class="order-date">Diperbarui: <?php echo $fetch_orders['updated_at'] ?></p>
                                    </div>
                                </div>
                                <div class="pricing-section">
                                    <p class="status">Dikemas</p>
                                    <div class="pricing">
                                        <p class="total"><span>Total:
                                                <?php echo number_format($fetch_orders['total_amount'], 0, ',', '.'); ?></span>
                                        </p>
                                    </div>
                                    <div class="action-buttons">
                                        <form method="get" action="detailorder.php">
                                            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                                            <button type="submit" class="detail-button">Lihat Detail Pesanan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    }
                    ?>


                </div>


                <div class="order-container" data-status="dikirim">
                    <?php $select_orders = mysqli_query($con, "SELECT id, user_id, status_order, total_amount, created_at, updated_at FROM orders WHERE user_id = '$user_id' AND status_order= 2 ");
                    if (mysqli_num_rows($select_orders) > 0) {
                        while ($fetch_orders = mysqli_fetch_assoc($select_orders)) { ?>
                            <div class="order-card">
                                <div class="order-info">
                                    <div class="details">
                                        <p class="title">Tanggal Pesanan:
                                            <?php echo date("d F Y", strtotime($fetch_orders['created_at'])); ?>
                                        </p>
                                        <p class="order-date"><?php echo htmlspecialchars($alamat) ?></p>
                                        <p class="order-date">Diperbarui: <?php echo $fetch_orders['updated_at'] ?></p>
                                    </div>
                                </div>
                                <div class="pricing-section">
                                    <p class="status">Dikirim</p>
                                    <div class="pricing">
                                        <p class="total"><span>Total:
                                                <?php echo number_format($fetch_orders['total_amount'], 0, ',', '.'); ?></span>
                                        </p>
                                    </div>
                                    <div class="action-buttons">
                                        <form method="get" action="detailorder.php">
                                            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                                            <button type="submit" class="detail-button">Lihat Detail Pesanan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    }
                    ?>
                </div>


                <div class="order-container" data-status="ulasan">
                    <?php $select_orders = mysqli_query($con, "SELECT id, user_id, status_order, total_amount, created_at, updated_at FROM orders WHERE user_id = '$user_id' AND status_order= 3 ");
                    if (mysqli_num_rows($select_orders) > 0) {
                        while ($fetch_orders = mysqli_fetch_assoc($select_orders)) { ?>
                            <div class="order-card">
                                <div class="order-info">
                                    <div class="details">
                                        <p class="title">Tanggal Pesanan:
                                            <?php echo date("d F Y", strtotime($fetch_orders['created_at'])); ?>
                                        </p>
                                        <p class="order-date"><?php echo htmlspecialchars($alamat) ?></p>
                                        <p class="order-date">Diperbarui: <?php echo $fetch_orders['updated_at'] ?></p>
                                    </div>
                                </div>
                                <div class="pricing-section">
                                    <p class="status">Menunggu Ulasan</p>
                                    <div class="pricing">
                                        <p class="total"><span>Total:
                                                <?php echo number_format($fetch_orders['total_amount'], 0, ',', '.'); ?></span>
                                        </p>
                                    </div>
                                    <div class="action-buttons">
                                        <form method="get" action="reviewrate.php">
                                            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                                            <button type="submit" class="review-button">Berikan Ulasan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    }
                    ?>
                </div>

                <div class="order-container" data-status="selesai">
                    <?php $select_orders = mysqli_query($con, "SELECT id, user_id, status_order, total_amount, created_at, updated_at FROM orders WHERE user_id = '$user_id' AND status_order= 4 ");
                    if (mysqli_num_rows($select_orders) > 0) {
                        while ($fetch_orders = mysqli_fetch_assoc($select_orders)) { ?>
                            <div class="order-card ">
                                <div class="order-info">
                                    <div class="details">
                                        <p class="title">Tanggal Pesanan:
                                            <?php echo date("d F Y", strtotime($fetch_orders['created_at'])); ?>
                                        </p>
                                        <p class="order-date"><?php echo htmlspecialchars($alamat) ?></p>
                                        <p class="order-date">Diperbarui: <?php echo $fetch_orders['updated_at'] ?></p>
                                    </div>
                                </div>
                                <div class="pricing-section">
                                    <p class="status">Selesai</p>
                                    <div class="pricing">
                                        <p class="total"><span>Total:
                                                <?php echo number_format($fetch_orders['total_amount'], 0, ',', '.'); ?></span>
                                        </p>
                                    </div>
                                    <div class="action-buttons">
                                        <form method="get" action="detailorder.php">
                                            <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
                                            <button type="submit" class="detail-button">Lihat Detail Transaksi</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    }
                    ?>
                </div>






            </div>
        </div>
    </div>
    </div>
    </div>
    <footer>
        <p>&copy; Kelompok 1 | PPW | <a href="#" class="privacy-policy">Privacy Policy</a></p>
    </footer>

    <script>
        const tabs = document.querySelectorAll('.menu li');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
        const editButton = document.querySelector('.edit-button');
        const fileInput = document.querySelector('.file-input');
        const avatarPlaceholder = document.querySelector('.avatar-placeholder');

        editButton.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    avatarPlaceholder.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const heartIcon = document.getElementById('wishlist-btn');

            heartIcon.addEventListener('click', function () {
                if (heartIcon.src.includes('heart_red.svg')) {
                    heartIcon.src = 'image/heart_grey.svg';

                } else {
                    heartIcon.src = 'image/heart_red.svg';

                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.querySelector('.dropdown-toggle2');
    const dropdownMenu = document.querySelector('.dropdown-menu2');
    const filterItems = document.querySelectorAll('.filter-menu2');
    const orderContainers = document.querySelectorAll('.order-container');
    const activeFilterDisplay = document.querySelector('.filter-menu2.active');

    toggleButton.addEventListener('click', (event) => {
        event.stopPropagation();
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    });

    function applyFilter(selectedStatus) {
        filterItems.forEach((item) => {
            item.classList.remove('active');
            if (item.getAttribute('data-status') === selectedStatus) {
                item.classList.add('active');
            }
        });

        orderContainers.forEach((container) => {
            if (selectedStatus === 'all' || container.getAttribute('data-status') === selectedStatus) {
                container.style.display = 'block';  
            } else {
                container.style.display = 'none';  
            }
        });
    }

    filterItems.forEach((item) => {
        item.addEventListener('click', (event) => {
            event.stopPropagation();

            const selectedStatus = item.getAttribute('data-status');

            toggleButton.querySelector('.filter-menu').textContent = item.textContent;

            applyFilter(selectedStatus);

            dropdownMenu.style.display = 'none';
        });
    });

    applyFilter('all');

    document.addEventListener('click', (event) => {
        if (!dropdownMenu.contains(event.target) && !toggleButton.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});



        function toggleWishlist(button, produkId) {
            const greyIcon = button.querySelector('.heart-grey');
            const redIcon = button.querySelector('.heart-red');
            const inWishlist = button.getAttribute('data-in-wishlist') === 'true';

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "profile.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onload = function () {
                if (xhr.status === 200) {
                    button.setAttribute('data-in-wishlist', inWishlist ? 'false' : 'true');
                    greyIcon.style.display = inWishlist ? 'inline' : 'none';
                    redIcon.style.display = inWishlist ? 'none' : 'inline';
                } else {
                    alert("Gagal memperbarui wishlist.");
                }
            };

            xhr.onerror = function () {
                alert("Terjadi kesalahan jaringan.");
            };

            xhr.send(`toggle_wishlist=true&produk_id=${produkId}`);
        }




    </script>
</body>

</html>