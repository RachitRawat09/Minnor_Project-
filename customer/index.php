<?php
session_start();
include '../includes/db_connect.php';

// ✅ Fetch categories dynamically
$category_query = "SELECT DISTINCT category FROM menu_items ORDER BY category ASC";
$categories = $conn->query($category_query);

// ✅ Fetch all menu items
$menu_query = "SELECT * FROM menu_items ORDER BY category ASC";
$menu_items = $conn->query($menu_query);

// ✅ Splash Screen Logic (Show Only Once Per Session)
if (!isset($_SESSION["seen_splash"])) {
    $_SESSION["seen_splash"] = true; 
    $showSplash = true;
} else {
    $showSplash = false;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - CodeToCuisine</title>

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <style>
        :root {
            --primary-color: #FFC72C;  /* Changed to McDonald's yellow */
            --secondary-color: #4ecdc4;
            --accent-color: #ffd166;
            --text-color: #2d3436;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            padding-top: 80px; /* Add padding to body to account for fixed navbar */
        }

        /* Hero Slider Styles */
        .hero-slider {
            height: 400px;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .hero-slide {
            height: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to right, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
            display: flex;
            align-items: center;
            padding: 2rem;
        }

        .hero-content {
            color: white;
            max-width: 600px;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        /* Category Carousel */
        .category-carousel {
            margin: 2rem 0;
            padding: 1rem 0;
        }

        .category-item {
            text-align: center;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 15px;
        }

        .category-item:hover {
            transform: translateY(-5px);
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .category-item.active {
            background: var(--primary-color);
            color: white;
        }

        .category-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        /* Menu Card Styles */
        .menu-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
            background: white;
            margin-bottom: 2rem;
            height: 100%;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .menu-card img {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .menu-card:hover img {
            transform: scale(1.1);
        }

        .menu-card .card-body {
            padding: 1.5rem;
        }

        .menu-card .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .menu-card .category-badge {
            background: var(--light-bg);
            color: var(--text-color);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .menu-card .price {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
        }

        .btn-cart {
            background: #FFC72C;  /* Changed to McDonald's yellow */
            color: var(--text-color);  /* Changed to dark text for better contrast */
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-cart:hover {
            background: #e6b328;  /* Slightly darker yellow for hover state */
            transform: translateY(-2px);
        }

        /* Navbar Styles */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: var(--light-bg);
        }

        /* Mobile Navigation Badge Styles */
        .mobile-nav-link {
            position: relative;
            display: inline-block;
        }

        .mobile-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: #FFC72C;  /* Changed to McDonald's yellow */
            border-radius: 4px;
        }
        .menu-card{
            margin-top: 1.5rem;
        }
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            body {
                padding-top: 140px; /* Increased padding for mobile to account for additional navbar elements */
            }

            .hero-slider {
                height: 300px;
            }

            .menu-card {
                margin-bottom: 1.5rem;
            }
        }

        /* Menu Container Styles */
        .menu-container {
            padding-top: 2rem;
        }
    </style>
</head>
<body>

<!-- ✅ Show Splash Screen -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php if ($showSplash): ?>
        Swal.fire({
            title: "Welcome to CodeToCuisine! 🍽",
            imageUrl: "../assets/images/CODE TO CUISINE.png", // Use the actual logo here
            imageWidth: 200,
            imageHeight: 200,
            text: "Delicious food at your fingertips!",
            showConfirmButton: false,
            timer: 2000
        });
    <?php endif; ?>
});
</script>

<!-- Hero Slider -->
<div class="container mt-3">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide hero-slide" style="background-image: url('https://images.unsplash.com/photo-1513104890138-7c749659a591?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Delicious Pizzas</h1>
                        <p>Hand-tossed dough, premium toppings, and authentic flavors</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide hero-slide" style="background-image: url('https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1998&q=80');">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Juicy Burgers</h1>
                        <p>Freshly grilled patties with the perfect blend of spices</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide hero-slide" style="background-image: url('https://images.unsplash.com/photo-1551183053-bf91a1d81141?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2032&q=80');">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Fresh Pasta</h1>
                        <p>Handmade pasta with rich, authentic Italian sauces</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide hero-slide" style="background-image: url('https://awadh360.com/assets/img/articles/202405070811Summer%20drink.png');">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Refreshing Drinks</h1>
                        <p>Cool and refreshing beverages to complement your meal</p>
                    </div>
                </div>
            </div>
            <div class="swiper-slide hero-slide" style="background-image: url('https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2027&q=80');">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Sweet Desserts</h1>
                        <p>Indulge in our delicious selection of desserts</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
        <!-- <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div> -->
    </div>
</div>


<!-- ✅ Modern Responsive Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
        
        <!-- ✅ CodeToCuisine (Always Centered for Small Screens) -->
        <a class="navbar-brand fw-bold text-primary mx-auto d-lg-none">
            <i class="fas fa-utensils"></i> CodeToCuisine
        </a>

        <!-- ✅ For Large Screens (Show Full Navbar) -->
        <div class="d-none d-lg-flex w-100 justify-content-between align-items-center">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="fas fa-utensils"></i> CodeToCuisine
            </a>

            <!-- ✅ Category Filter in Navbar (Large Screen) -->
            <select id="categoryFilter" class="form-select w-25">
                <option value="all">All Categories</option>
                <?php 
                $categories->data_seek(0); // Reset the pointer for the next loop
                while ($cat = $categories->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($cat['category']); ?>">
                        <?= htmlspecialchars($cat['category']); ?>
                    </option>
                <?php } ?>
            </select>

            <ul class="navbar-nav d-flex align-items-center gap-4">
            <li class="nav-item d-flex align-items-center">
    <a id="order-btn" href="cart.php" class="nav-link fw-bold text-primary px-3 position-relative">
        <i class="fas fa-shopping-cart fs-4"></i>
        <span class="ms-2">Order</span>
        <!-- ✅ This is the red notification badge -->
        <span id="order-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
            •
        </span>
    </a>
</li>

                <li class="nav-item d-flex align-items-center">
                    <a href="bill.php" class="nav-link fw-bold text-success px-3">
                        <i class="fas fa-receipt fs-4"></i>
                        <span class="ms-2">Bill</span>
                    </a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <a href="track_order.php" class="nav-link fw-bold text-secondary px-3">
                        <i class="fas fa-map-marker-alt fs-4"></i>
                        <span class="ms-2">Track Order</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- ✅ For Small Screens (Icons & Names Below) -->
        <div class="d-flex d-lg-none w-100 mt-2">
            <a href="#" class="text-primary fs-5 text-center flex-grow-1 p-2 border border-primary">
                <i class="fas fa-utensils"></i>
            </a>
            <a href="cart.php" class="text-primary fs-5 text-center flex-grow-1 p-2 border border-primary mobile-nav-link">
                <i class="fas fa-shopping-cart"></i>
                <span class="mobile-badge d-none">•</span>
            </a>
            <a href="bill.php" class="text-success fs-5 text-center flex-grow-1 p-2 border border-success">
                <i class="fas fa-receipt"></i>
            </a>
            <a href="track_order.php" class="text-secondary fs-5 text-center flex-grow-1 p-2 border border-secondary">
                <i class="fas fa-map-marker-alt"></i>
            </a>
        </div>

        <!-- ✅ Category Filter in Small Screens (Below Navbar) -->
        <div class="d-lg-none w-100 mt-2 px-3">
            <select id="categoryFilterMobile" class="form-select">
                <option value="all">All Categories</option>
                <?php 
                $categories->data_seek(0); // Reset again for mobile view
                while ($cat = $categories->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($cat['category']); ?>">
                        <?= htmlspecialchars($cat['category']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

    </div>
</nav>

<!-- Menu Container -->
<div class="container menu-container">
    <!-- Menu Items -->
    <div class="row" id="menuItems">
        <?php while ($item = $menu_items->fetch_assoc()) { ?>
            <div class="col-md-4 col-sm-6 mb-4 menu-item" data-category="<?= htmlspecialchars($item['category']); ?>">
                <div class="card menu-card">
                    <img src="../uploads/<?= htmlspecialchars($item['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="card-body">
                        <span class="category-badge"><?= htmlspecialchars($item['category']); ?></span>
                        <h5 class="card-title"><?= htmlspecialchars($item['name']); ?></h5>
                        
                        <p class="price">
                            <?php 
                            if ($item['size_type'] == 'half_full') { ?>
                                Half: ₹<?= htmlspecialchars($item['price_half'] ?? 'N/A'); ?> / 
                                Full: ₹<?= htmlspecialchars($item['price_full'] ?? 'N/A'); ?>
                            <?php } elseif ($item['size_type'] == 'sml_lrg') { ?>
                                Small: ₹<?= htmlspecialchars($item['price_small'] ?? 'N/A'); ?>, 
                                Medium: ₹<?= htmlspecialchars($item['price_medium'] ?? 'N/A'); ?>, 
                                Large: ₹<?= htmlspecialchars($item['price_large'] ?? 'N/A'); ?>, 
                                XL: ₹<?= htmlspecialchars($item['price_extra_large'] ?? 'N/A'); ?>
                            <?php } else { ?>
                                ₹<?= htmlspecialchars($item['price_full'] ?? 'N/A'); ?>
                            <?php } ?>
                        </p>

                        <button class="btn btn-cart w-100 add-to-cart" 
                                data-id="<?= $item['id']; ?>"
                                data-name="<?= htmlspecialchars($item['name']); ?>"
                                data-image="../uploads/<?= htmlspecialchars($item['image']); ?>"
                                data-size="<?= htmlspecialchars($item['size_type']); ?>"
                                data-price-small="<?= htmlspecialchars($item['price_small'] ?? 'N/A'); ?>"
                                data-price-medium="<?= htmlspecialchars($item['price_medium'] ?? 'N/A'); ?>"
                                data-price-large="<?= htmlspecialchars($item['price_large'] ?? 'N/A'); ?>"
                                data-price-xl="<?= htmlspecialchars($item['price_extra_large'] ?? 'N/A'); ?>"
                                data-price-half="<?= htmlspecialchars($item['price_half'] ?? 'N/A'); ?>"
                                data-price-full="<?= htmlspecialchars($item['price_full'] ?? 'N/A'); ?>">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div> 



<!-- ✅ Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    

document.addEventListener("DOMContentLoaded", function () {
    // ✅ Category Filter for both desktop and mobile
    const categoryFilter = document.getElementById("categoryFilter");
    const categoryFilterMobile = document.getElementById("categoryFilterMobile");

    function filterMenuItems(selectedCategory) {
        let menuItems = document.querySelectorAll(".menu-item");
        menuItems.forEach(item => {
            let itemCategory = item.getAttribute("data-category").toLowerCase();
            item.style.display = (selectedCategory === "all" || itemCategory === selectedCategory) ? "block" : "none";
        });
    }

    // Desktop filter
    categoryFilter.addEventListener("change", function () {
        filterMenuItems(this.value.toLowerCase());
    });

    // Mobile filter
    categoryFilterMobile.addEventListener("change", function () {
        filterMenuItems(this.value.toLowerCase());
    });

    // ✅ Add to Cart Popup & Store in PHP Session
    document.querySelectorAll(".add-to-cart").forEach(button => {
        button.addEventListener("click", function() {
            let itemId = this.dataset.id;
            let itemName = this.dataset.name;
            let itemImage = this.dataset.image;
            let sizeType = this.dataset.size;

            // ✅ Get all pizza prices
            let priceSmall = parseFloat(this.dataset.priceSmall);
            let priceMedium = parseFloat(this.dataset.priceMedium);
            let priceLarge = parseFloat(this.dataset.priceLarge);
            let priceXL = parseFloat(this.dataset.priceXl);
            let priceFull = parseFloat(this.dataset.priceFull);
            let priceHalf = parseFloat(this.dataset.priceHalf);

            Swal.fire({
                title: itemName,
                imageUrl: itemImage,
                imageWidth: 180,
                showCloseButton: true,
                
                customClass: { popup: '' },

                html: `
                    <div style="display: flex; flex-direction: column; gap: 5px; font-size: 13px; text-align: left;">
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 5px;">
                            <label for="quantity" style="width: 35%;">Quantity:</label>
                            <select id="quantity" class="swal2-select" style="width: 60%;">
                        ${Array.from({ length: 10 }, (_, i) => `<option value="${i + 1}">${i + 1}</option>`).join('')}
                    </select>
                        </div>

                        ${sizeType !== "none" ? `
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 5px;">
                            <label for="size" style="width: 35%;">Size:</label>
                            <select id="size" class="swal2-select" style="width: 60%;">
                                ${sizeType === "half_full" ? `<option value="half">Half - ₹${priceHalf}</option>` : ""}
                                ${sizeType === "sml_lrg" ? `
                                    <option value="small">Small - ₹${priceSmall}</option>
                                    <option value="medium">Medium - ₹${priceMedium}</option>
                                    <option value="large">Large - ₹${priceLarge}</option>
                                    <option value="xl">XL - ₹${priceXL}</option>
                                ` : ""}
                                <option value="full">Full - ₹${priceFull}</option>
                            </select>
                        </div>` : ""}
                    </div>
                `,
                preConfirm: () => {
                    let quantity = document.getElementById("quantity").value;
                    let sizeElement = document.getElementById("size"); // Only check if the size dropdown is there
                    
                    let selectedSize = sizeElement ? sizeElement.value : "full";
                    let selectedPrice = sizeElement ? (
                        selectedSize === "half" ? priceHalf :
                        selectedSize === "small" ? priceSmall :
                        selectedSize === "medium" ? priceMedium :
                        selectedSize === "large" ? priceLarge :
                        selectedSize === "xl" ? priceXL :
                        priceFull
                    ) : priceFull;

                    // ✅ Ensure quantity is greater than 0
                    if (quantity < 1) {
                        Swal.showValidationMessage("Quantity must be at least 1");
                        return false;
                    }

                    return {
                        itemId,
                        itemName,
                        itemImage,
                        quantity,
                        selectedSize,
                        selectedPrice
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("add_to_cart.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(result.value)
                    }).then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: "Added!",
                                text: `Added ${result.value.quantity} × ${itemName}`,
                                icon: "success",
                                timer: 2000, // Auto-close after 2 seconds
                                timerProgressBar: true
                            });
                            
                            // ✅ Show the Red Badge for both desktop and mobile
                            document.getElementById("order-badge").classList.remove("d-none");
                            // Also show badge in mobile view
                            const mobileBadge = document.querySelector('.mobile-badge');
                            if (mobileBadge) {
                                mobileBadge.classList.remove("d-none");
                            }
                        } else {
                            Swal.fire("Error!", "Failed to add item to cart.", "error");
                        }
                    }).catch(error => {
                        console.error("Error:", error);
                        Swal.fire("Error!", "Something went wrong.", "error");
                    });
                }
            });
        });
    });

    // Initialize Swiper for Hero Slider with enhanced options
    const heroSwiper = new Swiper('.hero-slider', {
        loop: true,
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

});



</script>

</body>
</html>
