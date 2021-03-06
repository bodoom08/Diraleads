<div class="dashboard-inner"  style="font-family: Poppins,sans-serif;">
    <h4>Main</h4>
    <ul>
        <li class=""><a href="<?php echo site_url('dashboard'); ?>"><i class="flaticon-dashboard"></i> Dashboard</a></li>
        <li class="<?php if(uri_string() == 'favourites'){echo "active";} ?>"><a href="<?php echo site_url('favourites'); ?>"><i class="flaticon-heart"></i> Favorites</a></li>
    </ul>
    <h4>Property</h4>
    <ul>
        <li class="<?php if(uri_string() == 'my_rentals') {echo "active";} ?>"><a href="<?php echo site_url('my_rentals'); ?>"><i class="flaticon-apartment-1"></i>My Rentals</a></li>
        <!-- <li class="<?php if(uri_string() == 'property') {echo "active";}  ?>"><a href="<?php echo site_url('property'); ?>"><i class="flaticon-plus"></i>Submit New Property</a></li> -->
    </ul>
    <h4>Subscription</h4>
    <ul>
        <!-- <li><a href="<?php echo site_url('subscription'); ?>"><i class="flaticon-financial"></i>My Subscribtions</a></li> -->
        <li class="<?php if(uri_string() == 'subscription/user') {echo "active";} ?>">
            <a href="<?php echo site_url('subscription/user'); ?>">
                <div class="d-flex justify-content-between">
                    <i class="flaticon-financial"></i>
                    <div class="text-left d-flex align-items-center" style="width: calc(100% - 65px);">My Email Preferences</div>
                </div>
            </a>
        </li>
        <!-- <li><a href="preferences"><i class="fa fa-gear"></i>My Preferences</a></li> -->
        <!-- <li><a href="preferences"><i class="flaticon-heart"></i>My Favorites</a></li> -->
        <!-- <li ><a href="<?php echo site_url('pricing/custom_pricing'); ?>"><i class="flaticon-financial"></i>Subscribe For A New Plan</a></li> -->
    </ul>
    <h4>Account</h4>
    <ul>
        <!-- <li class="<?php if(uri_string() == 'invoices') {echo "active";} ?>"><a href="invoices"><i class="flaticon-bill"></i>My Invoices</a></li> -->
        <li class="<?php if(uri_string() == 'profile') {echo "active";} ?>"><a href="<?php echo site_url('profile'); ?>"><i class="flaticon-people"></i>My Profile</a></li>
        <li><a href="<?php echo site_url('login/logout'); ?>"><i class="flaticon-logout"></i>Logout</a></li>
    </ul>
</div>
 