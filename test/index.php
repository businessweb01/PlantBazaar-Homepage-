<?php
include 'conn.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <script src="jquery.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/css/splide.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4/dist/js/splide.min.js"></script>
    <title>Plant-Bazaar</title>
</head>
<body>
    <div class="header">
        <nav class="navigation">
            <div class="logo">
                <span class="plant">PLANT</span>
                <p class="bazaar">-BAZAAR</p>
                <i class="fa-solid fa-spa"></i>
            </div>
            <div class="nav1">
                <a href="#">Home</a>
                <a href="#">About</a>
                <a href="#">Services</a>
                <a href="#">Contact</a>
            </div>
            <div class="login-signup">
                <a href="#">Login</a>
                <a href="#">Sign-Up</a>
            </div>
        </nav>

        <div class="hamburger">
            <i class="fas fa-bars"></i>
        </div>
    </div>

    <div class="dropdown-menu">
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
        <a href="#">Login</a>
        <a href="#">Sign-Up</a>
    </div>


    <div class="featured">
        <p class="featured-header">
            Top Seller
        </p>
        <div class="featured-contents" id="featured-contents"></div>
    </div>

    <div class="newly-listed">
        <p class="newly-header">
            Newly Listed Plants
        </p>
        <div class="locations" id="locations">
            <!-- Locations -->
        </div>
        <div class="newly-contents" id="newly-contents">
            <!-- Products -->
        </div>
    </div>
    <script>
    document.querySelector('.hamburger').addEventListener('click', function() {
        const dropdownMenu = document.querySelector('.dropdown-menu');
        dropdownMenu.classList.toggle('show'); // Toggle the dropdown menu
    });
     $(document).ready(function() {
            $.ajax({
                url: 'Ajax/fetch_top_seller.php',
                type: 'GET',
                success: function(response) {
                    $('#featured-contents').html(response);

                    // Get the number of sellers from the hidden input
                    var numSellers = $('#num_sellers').val();

                    // Configure Splide depending on the number of sellers
                    if (numSellers > 1) {
                        // If more than 1 seller, use loop mode
                        new Splide('#seller-slider', {
                            type   : 'loop:slide',
                            perPage: 5,
                            autoplay: true,
                            gap: '1rem',
                            breakpoints: {
                                600: {
                                    perPage: 1,
                                },
                                900: {
                                    perPage: 2,
                                },
                            }
                        }).mount();
                    } else if (numSellers == 1) {
                        // If only 1 seller, use rewind mode (no looping)
                        new Splide('#seller-slider', {
                            type   : 'slide',
                            rewind : true,  // No loop, just slide back to the beginning
                            perPage: 1,     // Display 1 item
                            autoplay: false, // Disable autoplay
                        }).mount();
                    }
                }
            });
            $.ajax({ 
                url: 'Ajax/fetch_newly_listed.php',
                type: 'GET',
                success: function(response) {
                    try {
                        let plants = response;

                        if (plants.error) {
                            alert(plants.error); // Show error message if any
                            return;
                        }

                        // Group plants by location
                        let plantsByLocation = {};
                        plants.forEach(function(product) {
                            if (!plantsByLocation[product.location]) {
                                plantsByLocation[product.location] = [];
                            }
                            plantsByLocation[product.location].push(product);
                        });

                        let contentHtml = '';
                        let locationsHtml = `
                            <div class="plant-location">
                                <button class="location-btn" data-location="all">Show All</button>
                            </div>`;

                        for (let location in plantsByLocation) {
                            // Add plant items to contentHtml
                            plantsByLocation[location].forEach(function(product) {
                                let imgPath = `Products/${product.seller_email}/${product.img1}`;
                                contentHtml += `
                                    <div class="plant-item" data-location="${product.location}">
                                        <div class="plant-image">
                                            <img src="${imgPath}" alt="${product.plantname}">
                                        </div>
                                        <p>${product.plantname}</p>
                                        <p>Price: ₱${product.price}</p>
                                        <div class="plant-item-buttons">
                                            <button class="view-details" data-id="${product.plantid}">View Details</button>
                                            <button class="chat-seller" data-email="${product.seller_email}">Chat Seller</button>
                                        </div>
                                    </div>`;
                            });

                            // Add location buttons to locationsHtml
                            locationsHtml += `
                                <div class="plant-location">
                                    <button class="location-btn" data-location="${location}">
                                        ${location}
                                    </button>
                                </div>`;
                        }

                        $('#newly-contents').html(contentHtml);
                        $('#locations').html(locationsHtml);

                        // Add event listeners to location buttons to filter plants
                        $('.location-btn').on('click', function() {
                            let location = $(this).data('location');
                            console.log(`Button clicked: Location=${location}`); // Log the location

                            if (location === 'all') {
                                $('.plant-item').show();
                            } else {
                                $('.plant-item').each(function() {
                                    if ($(this).data('location') === location) {
                                        $(this).show();
                                    } else {
                                        $(this).hide();
                                    }
                                });
                            }
                        });

                        // Add event listeners to view-details and chat-seller buttons
                        $(document).on('click', '.view-details', function() {
                            let plantId = $(this).data('id');
                            console.log(`View Details button clicked: Plant ID=${plantId}`); // Log the plant ID
                        });

                        $(document).on('click', '.chat-seller', function() {
                            let sellerEmail = $(this).data('email');
                            console.log(`Chat Seller button clicked: Seller Email=${sellerEmail}`); // Log the seller email
                        });

                    } catch (e) {
                        console.error("Error parsing JSON", e);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                }
            });

        });

    </script>
</body>
</html>