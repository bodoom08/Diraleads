<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="zxx">

<head>
    <title><?php echo html_escape($title); ?> | <?php echo html_escape(CFG_TITLE); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="assets/favicon.png" />
    <?php $this->load->view('common/styles'); ?>
    <link rel="stylesheet" type="text/css" href="assets/css/styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            setVisibleNavBar();

            $(window).resize(function() {
                setVisibleNavBar();
            });
        });

        function setVisibleNavBar() {

            if ($(document).width() < 1200) {
                document.getElementById('about-diraleads-mobile').style = "display: block;";
                document.getElementById('about-diraleads-web').style = "display: none;";
                document.getElementById('user-menu-mobile').style = "display: block;";
                document.getElementById('user-menu-web').style = "display: none;";
            } else {
                document.getElementById('about-diraleads-web').style = "display: block;";
                document.getElementById('about-diraleads-mobile').style = "display: none;";
                document.getElementById('user-menu-mobile').style = "display: none;";
                document.getElementById('user-menu-web').style = "display: block;";
                document.getElementById('about-diraleads-owner').style = "display: none;";
                document.getElementById('about-diraleads-renter').style = "display: none;";
                document.getElementById('user-menu-dashboard').style = 'display: none;';
                document.getElementById('user-menu-rental').style = 'display: none;';
                document.getElementById('user-menu-profile').style = 'display: none;';
                document.getElementById('user-menu-logout').style = 'display: none;';
            }
        }

        function showAboutOptions() {
            if (document.getElementById('about-diraleads-owner').style.display == 'block') {
                document.getElementById('about-diraleads-owner').style = "display: none;";
                document.getElementById('about-diraleads-renter').style = "display: none;";
            } else {
                document.getElementById('about-diraleads-owner').style = "display: block;";
                document.getElementById('about-diraleads-renter').style = "display: block;";
            }
        }

        function showUserOptions() {
            console.log("Show User Options");
            if (document.getElementById('user-menu-dashboard').style.display == 'block') {
                document.getElementById('user-menu-dashboard').style = 'display: none';
                document.getElementById('user-menu-rental').style = 'display: none';
                document.getElementById('user-menu-profile').style = 'display: none';
                document.getElementById('user-menu-logout').style = 'display: none';
            } else {
                document.getElementById('user-menu-dashboard').style = 'display: block';
                document.getElementById('user-menu-rental').style = 'display: block';
                document.getElementById('user-menu-profile').style = 'display: block';
                document.getElementById('user-menu-logout').style = 'display: block';
            }
        }
    </script>
</head>

<body>
    <div class="my-header">

        <nav class="navbar navbar-expand-xl navbar-light container-fluid <?php if (current_url() == base_url('profile')) {
                                                                                echo 'costom_profile';
                                                                            } ?>">
            <a class="navbar-brand" href="<?php echo site_url(); ?>"><img src="<?php echo site_url() ?>uploads/<?php echo  CFG_LOGO ?>"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown" id="about-diraleads-web">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Why DiraLeads
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="/renters">The Renter's View</a>
                            <a class="dropdown-item" href="/owners">The Owner's Perch</a>
                        </div>
                    </li>
                    <!-- Mobile View -->
                    <li class="nav-item" id="about-diraleads-mobile" style="display: none;">
                        <a class="dropdown-toggle" href="javascript:showAboutOptions()">
                            Why DiraLeads
                        </a>
                    </li>
                    <li class="nav-item" id="about-diraleads-renter" style="display: none">
                        <a class="nav-link" href="/renters">The Renter's View</a>
                    </li>
                    <li class="nav-item" id="about-diraleads-owner" style="display:none">
                        <a class="nav-link" href="/owners">The Owner's View</a>
                    </li>
                    <!--  -->
                    <li class="nav-item">
                        <a class="nav-link" href="/properties">View Rentals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/rental">List Your Rental</a>
                    </li>
                </ul>

                <style>
                    .dropdown-menu a {
                        color: #000 !important;
                    }
                </style>
                <!-- Mobile Responsive -->
                <ul class="navbar-nav offcanvas-navbar position-relative" id="user-menu-web">
                    <div class="dropdown btns">
                        <a class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php echo site_url('assets/img/avatar/user.png'); ?>" width="20" alt="avatar">
                            Hi, <?php echo explode(' ', $_SESSION['name'])[0] ?>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?php echo site_url('dashboard'); ?>">Dashboard</a>
                            <a class="dropdown-item" href="<?php echo site_url('my_rentals'); ?>">My Rentals</a>
                            <a class="dropdown-item" href="<?php echo site_url('profile'); ?>">My Profile</a>
                            <a class="dropdown-item" href="<?php echo site_url('login/logout'); ?>">Logout</a>
                        </div>
                    </div>
                    <li class="nav-item  position-relative d-none">
                        <a class="nav-link" href="{{ site_url('login/logout') }}">
                            Logout
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav offcanvas-navbar position-relative" id="user-menu-mobile" style="display: none;">
                    <a class="dropdown-toggle" href="javascript:showUserOptions()" style="color: black;">
                        <img src="<?php echo site_url('assets/img/avatar/user.png'); ?>" width="20" alt="avatar">
                        Hi, <?php echo explode(' ', $_SESSION['name'])[0] ?>
                    </a>
                </ul>
                <li class="nav-item" id="user-menu-dashboard" style="display:none">
                    <a class="nav-link" href="<?php echo site_url('dashboard'); ?>" style="font-family: Raleway, sans-serif;color: black;">Dashboard</a>
                </li>
                <li class="nav-item" id="user-menu-rental" style="display:none">
                    <a class="nav-link" href="<?php echo site_url('my_rentals'); ?>" style="font-family: Raleway, sans-serif;color: black;">My Rentals</a>
                </li>
                <li class="nav-item" id="user-menu-profile" style="display:none">
                    <a class="nav-link" href="<?php echo site_url('profile'); ?>" style="font-family: Raleway, sans-serif;color: black;">My Profile</a>
                </li>
                <li class="nav-item" id="user-menu-logout" style="display:none">
                    <a class="nav-link" href="<?php echo site_url('login/logout'); ?>" style="font-family: Raleway, sans-serif;color: black;">Logout</a>
                </li>
                <!--  -->

            </div>
        </nav>
    </div>