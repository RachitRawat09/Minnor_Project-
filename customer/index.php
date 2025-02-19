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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .menu-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
            overflow: hidden;
            background: white;
        }
        .menu-card:hover {
            transform: scale(1.02);
        }
        .menu-card img {
            height: 180px;
            object-fit: cover;
        }
        .category-filter {
            max-width: 300px;
            margin: 0 auto 20px;
        }
        .btn-cart {
            width: 100%;
            font-weight: bold;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .menu_card{
            margin-top: 150px;
            padding: 15px;
            
        }
        input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0; 
}

/* pop css  */
 /* ✅ Background color for the popup */
 .custom-swal-popup {
        background-color: #fdd835; /* Yellow background */
        border-radius: 12px;
    }
    
    /* ✅ Styling for the Enter button */
    .custom-confirm-button {
        background-color: #ff5722; /* Orange button */
        color: white;
        font-weight: bold;
        padding: 10px 24px;
        font-size: 16px;
        border-radius: 8px;
    }

    /* ✅ Title style */
    .custom-title {
        color: #333;
        font-weight: bold;
        font-size: 22px;
    }
     /* ✅ Keep input field background default (white) */
     .custom-input-field {
        background-color: white !important;
        color: black !important;
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
            imageUrl: "../assets/images/CODE TO CUISINE.png", // ✅ Replace with actual logo path
            imageWidth: 200,
            imageHeight: 200,
            text: "Delicious food at your fingertips!",
            showConfirmButton: false,
            timer: 2000
        });
    <?php endif; ?>
});
</script>



<!-- ✅ Modern Responsive Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3">
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
                $categories->data_seek(0); // Reset result set pointer
                while ($cat = $categories->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($cat['category']); ?>">
                        <?= htmlspecialchars($cat['category']); ?>
                    </option>
                <?php } ?>
            </select>

            <ul class="navbar-nav d-flex align-items-center gap-4">
                <li class="nav-item d-flex align-items-center">
                    <a href="cart.php" class="nav-link fw-bold text-primary px-3 position-relative">
                        <i class="fas fa-shopping-cart fs-4"></i>
                        <span class="ms-2">Order</span>
                        
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
            <a href="cart.php" class="text-primary fs-5 text-center flex-grow-1 p-2 border border-primary position-relative">
                <i class="fas fa-shopping-cart"></i>
                
                
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
                $categories->data_seek(0); // Reset result set pointer again for mobile view
                while ($cat = $categories->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($cat['category']); ?>">
                        <?= htmlspecialchars($cat['category']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

    </div>
</nav>

<!-- ✅ Fixing Menu Items Hidden Behind Navbar -->
<div class=" menu_card container text-center ">
    


    <!-- Menu Items -->
    <div class="row" id="menuItems">
        <?php while ($item = $menu_items->fetch_assoc()) { ?>
            <div class="col-md-4 col-sm-6 mb-4 menu-item" data-category="<?= htmlspecialchars($item['category']); ?>">
                <div class="card menu-card">
                    <img src="../uploads/<?= htmlspecialchars($item['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($item['name']); ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($item['category']); ?></p>

                         <!-- ✅ Fix Pizza Prices -->
                         <p class="fw-bold">
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


                        <!-- ✅ Add to Cart Button -->
                        <button type="button" class="btn btn-success add-to-cart btn-cart"
                            data-id="<?= $item['id']; ?>"
                            data-name="<?= htmlspecialchars($item['name']); ?>"
                            data-image="../uploads/<?= htmlspecialchars($item['image']); ?>"
                            data-size="<?= $item['size_type']; ?>"
                            data-price-half="<?= $item['price_half'] ?? 0; ?>"
                            data-price-full="<?= $item['price_full'] ?? 0; ?>"
                            data-price-small="<?= $item['price_small'] ?? 0; ?>"
                            data-price-medium="<?= $item['price_medium'] ?? 0; ?>"
                            data-price-large="<?= $item['price_large'] ?? 0; ?>"
                            data-price-xl="<?= $item['price_extra_large'] ?? 0; ?>">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div> 

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    

document.addEventListener("DOMContentLoaded", function () {
    // ✅ Category Filter
    document.getElementById("categoryFilter").addEventListener("change", function () {
        let selectedCategory = this.value.toLowerCase();
        let menuItems = document.querySelectorAll(".menu-item");

        menuItems.forEach(item => {
            let itemCategory = item.getAttribute("data-category").toLowerCase();
            item.style.display = (selectedCategory === "all" || itemCategory === selectedCategory) ? "block" : "none";
        });
    });

    // ✅ Add to Cart Popup & Store in PHP Session
document.querySelectorAll(".add-to-cart").forEach(button => {
    button.addEventListener("click", function () {
        let itemId = this.dataset.id;
        let itemName = this.dataset.name;
        let itemImage = this.dataset.image;
        let sizeType = this.dataset.size;

        // ✅ Get all pizza prices
        let priceSmall = parseFloat(this.dataset.priceSmall);
        let priceMedium = parseFloat(this.dataset.priceMedium);
        let priceLarge = parseFloat(this.dataset.priceLarge);
        let priceXL = parseFloat(this.dataset.priceXL);
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
                        <input type="number" id="quantity" class="swal2-input" style="width: 60%;" value="1" min="1">
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
                let sizeElement = document.getElementById("size"); // ✅ Check if size dropdown exists
                
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
                        Swal.fire("Added!", `Added ${result.value.quantity} × ${itemName}`, "success");
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





});
</script>

</body>
</html>
