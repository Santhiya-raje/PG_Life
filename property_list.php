<?php
session_start();
require "includes/database_connect.php";

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
$city_name = $_GET["city"];

$sql_1 = "SELECT * FROM cities WHERE name = '$city_name'";
$result_1 = mysqli_query($conn, $sql_1);
if (!$result_1) {
    echo "Something went wrong!";
    return;
}
$city = mysqli_fetch_assoc($result_1);
if (!$city) {
    echo "Sorry! We do not have any PG listed in this city.";
    return;
}
$city_id = $city['id'];

$order_by = "ORDER BY rent ASC"; // Default sorting: Lowest rent first
$gender_filter = ""; // Default: No gender filter

// Apply sorting if selected
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == "highest_rent") {
        $order_by = "ORDER BY rent DESC";
    } elseif ($_GET['sort'] == "lowest_rent") {
        $order_by = "ORDER BY rent ASC";
    }
}

// Apply gender filter if selected
if (isset($_GET['gender']) && $_GET['gender'] != "no_filter") {
    $gender = $_GET['gender'];
    $gender_filter = "AND gender = '$gender'";
}

// Final SQL query with sorting and filtering
$sql_2 = "SELECT * FROM properties WHERE city_id = $city_id $gender_filter $order_by";
$result_2 = mysqli_query($conn, $sql_2);
if (!$result_2) {
    echo "Something went wrong!";
    return;
}
$properties = mysqli_fetch_all($result_2, MYSQLI_ASSOC);

// Fetch interested users for each property
$sql_3 = "SELECT * 
            FROM interested_users_properties iup
            INNER JOIN properties p ON iup.property_id = p.id
            WHERE p.city_id = $city_id";
$result_3 = mysqli_query($conn, $sql_3);
if (!$result_3) {
    echo "Something went wrong!";
    return;
}
$interested_users_properties = mysqli_fetch_all($result_3, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Best PG's in <?php echo $city_name ?> | PG Life</title>

    <?php include "includes/head_links.php"; ?>
    <link href="css/property_list.css" rel="stylesheet" />
</head>

<body>
    <?php include "includes/header.php"; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb py-2">
            <li class="breadcrumb-item">
                <a href="index.php">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo $city_name; ?>
            </li>
        </ol>
    </nav>

    <div class="page-container">
        <div class="filter-bar row justify-content-around">
            <div class="col-auto filter-button" data-toggle="modal" data-target="#filter-modal">
                <img src="img/filter.png" alt="filter" />
                <span>Filter</span>
            </div>
            <div class="col-auto sort-button">
                <a href="property_list.php?city=<?= $city_name ?>&sort=highest_rent">
                    <img src="img/desc.png" alt="sort-desc" />
                    <span>Highest rent first</span>
                </a>
            </div>
            <div class="col-auto sort-button">
                <a href="property_list.php?city=<?= $city_name ?>&sort=lowest_rent">
                    <img src="img/asc.png" alt="sort-asc" />
                    <span>Lowest rent first</span>
                </a>
            </div>
        </div>

        <?php
        foreach ($properties as $property) {
            $property_images = glob("img/properties/" . $property['id'] . "/*");
        ?>
            <div class="property-card row">
                <div class="image-container col-md-4">
                    <img src="<?= $property_images[0] ?>" />
                </div>
                <div class="content-container col-md-8">
                    <div class="row no-gutters justify-content-between">
                        <?php
                        $total_rating = ($property['rating_clean'] + $property['rating_food'] + $property['rating_safety']) / 3;
                        $total_rating = round($total_rating, 1);
                        ?>
                        <div class="star-container" title="<?= $total_rating ?>">
                            <?php
                            $rating = $total_rating;
                            for ($i = 0; $i < 5; $i++) {
                                if ($rating >= $i + 0.8) {
                            ?>
                                    <i class="fas fa-star"></i>
                                <?php
                                } elseif ($rating >= $i + 0.3) {
                                ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php
                                } else {
                                ?>
                                    <i class="far fa-star"></i>
                            <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-container">
                        <div class="property-name"><?= $property['name'] ?></div>
                        <div class="property-address"><?= $property['address'] ?></div>
                        <div class="property-gender">
                            <?php if ($property['gender'] == "male") { ?>
                                <img src="img/male.png" />
                            <?php } elseif ($property['gender'] == "female") { ?>
                                <img src="img/female.png" />
                            <?php } else { ?>
                                <img src="img/unisex.png" />
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="rent-container col-6">
                            <div class="rent">₹ <?= number_format($property['rent']) ?>/-</div>
                            <div class="rent-unit">per month</div>
                        </div>
                        <div class="button-container col-6">
                            <a href="property_detail.php?property_id=<?= $property['id'] ?>" class="btn btn-custom">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php if (count($properties) == 0) { ?>
            <div class="no-property-container">
                <p>No PG to list</p>
            </div>
        <?php } ?>
    </div>

    <div class="modal fade" id="filter-modal" tabindex="-1" role="dialog" aria-labelledby="filter-heading" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Filters</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <a href="?city=<?= $city_name ?>&gender=no_filter" class="btn btn-filter">No Filter</a>
                    <a href="?city=<?= $city_name ?>&gender=unisex" class="btn btn-filter">Unisex</a>
                    <a href="?city=<?= $city_name ?>&gender=male" class="btn btn-filter">Male</a>
                    <a href="?city=<?= $city_name ?>&gender=female" class="btn btn-filter">Female</a>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/signup_modal.php"; ?>
    <?php include "includes/login_modal.php"; ?>
    <?php include "includes/footer.php"; ?>
</body>

</html>
