<?php defined('BASEPATH') or exit('No direct script access allowed');

$this->load->view('common/layout/top', [
    'title' => 'Pricing Plans'
]);
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@8/dist/sweetalert2.min.css" id="theme-styles">
<div class="sub-banner overview-bgi">
</div>
<style>
    .alert,
    .alert p {
        text-transform: none !important;
        line-height: 1.5 !important;
        font-size: 15px !important;
    }
</style>
<div class="pricing-table bg-grea">
    <div class="container">
        <div class="main-title text-center">
            <p>Find your perfect plan</p>
            <h1>Configurable Pricing Plans</h1>
        </div>
        
        <?php
        $args=  [
            'name' => 'manageSubscribed',
            'method' => 'POST'
        ];
        echo form_open('pricing/manage_subscribed_package', $args); ?>
            <input type="hidden" name="package_id">
            <input type="hidden" name="action">
        <?php echo form_close(); ?>
        <?php
        $area_arr = []; $month_arr = []; $package_arr = [];
        ?>
        <div class="row justify-content-md-center">
            <?php if(!empty('package_name')): ?>
                <div class="col-lg-5 col-lg-offset-3">
                    <div class="row">
                        <div class="col-lg-12 d-none">
                            <div class="form-group">
                                <label for="">Select Package Name <span class="text-danger">*</span></label>
                                <select class="form-control" id="package_select" onChange="changePackage(this.selectedIndex)">
                                    <?php foreach($package_name as  $package) { ?>
                                        <option value="<?php echo $package['id']; ?>"><?php echo $package['name']; ?></option>
                                        <?php
                                            $package_arr[] = [
                                                    $package['name'],                                              
                                                    $package['id']
                                            ];
                                        ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none">
                            <div class="form-group d-none">
                                <label for="">Price($)</label>
                                <input type="text" class="form-control" value="" name="package_price" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row subscribedAlert d-none" role="">
                        <div class="col-lg-12">
                            <div class="row alert alert-warning align-items-center" role="alert">
                                <div class="col-md-2">
                                    <i class="fa fa-info-circle" style="font-size:27px;"></i>
                                </div>
                                <div class="col-md-10 subscribedBody">
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row hideOnChange d-none">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="">Select No Of Days:<span class="text-danger">*</span></label>
                                <select class="form-control" id="days_select" onChange="changeDays(this.selectedIndex)">                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group d-none">
                                <label for="">Price($)</label>
                                <input type="text" class="form-control" value="" name="days_price" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row hideOnChange d-none">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label for="">Select No Of Areas:<span class="text-danger">*</span></label>
                                <select class="form-control" id="area_select" onChange="changeArea(this.selectedIndex)">                                    
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group d-none">
                                <label for="">Price($)</label>
                                <input type="text" class="form-control" value="" name="area_price" readonly>
                            </div>
                        </div>
                    </div>                    
                    
                </div>
                <div class="col-lg-4 hideOnChange d-none">
                    <h4 class="text-center">Total</h4>
                    <table class="table table-borderd">
                        <tr>
                            <td>
                                Package
                            </td>
                            <td>
                                <em><span id="lbl_pckname"></span></em>
                            </td>
                        </tr>                        
                        <tr>
                            <td>
                                No Of Days  &nbsp;&nbsp;<em></em>
                            </td>
                            <td>
                               <span id="lbl_daysprice"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                No Of Areas  &nbsp;&nbsp;<em></em>
                            </td>
                            <td>
                               <span id="lbl_areaname"></span>
                            </td>
                        </tr>
                       
                    </table>
                    <?php if(!isset($_SESSION['id'])) { ?>
                        <button class="btn btn-block btn-primary" id="subscribe" disabled onclick="location.href='/login?continue=<?php echo urlencode(site_url('/pricing/custom_pricing')); ?>'">Subscribe</button>
                    <?php } else { ?>
                        <?php
                            $arr = [
                                'class' => 'subscribeForm',
                                'name' => 'subscribeForm',
                                'id' => 'subscribeForm'
                            ];
                            echo form_open('pricing/pricing_pref', $arr); ?>
                            <input type="hidden" name="subscribe_info" />
                        <?php echo form_close(); ?>
                        <button class="btn btn-block btn-primary" id="subscribe" disabled>Subscribe</button>
                    <?php } ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>
        <div class="clearfix">
            
        </div>
    </div>
</div>
<?php $this->load->view('common/layout/bottom'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

<script>
    $area_arr = <?php echo json_encode($area_arr); ?>;
    $days_arr = <?php echo json_encode($month_arr); ?>;
    $package_arr = <?php echo json_encode($package_arr); ?>;
    var extend_subscribe = false;
    var price_arr = {
        'package_price' : 0,
        'no_of_area_price': 0,
        'no_of_months_price': 0,
        'tot_price': 0
    };
    var selected_item = {
        'no_of_area' : 0,
        'no_of_months' : 0
    };
    

    function changePackage(value) {
        total_price();
        $('#lbl_pckname').text($('#package_select option:selected').text());
        value = $package_arr[value-1][1];
        localStorage.setItem('input_save_package_id', value);

        $area_arr = [];
        $days_arr = [];
        $.ajax({
            url: '<?php echo site_url('pricing/custom_pricing_json_data') ?>',
            type: "POST",
            data: {
                'package_id' : value,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(data) {
                data = JSON.parse(data);
                if(data.subscribed_package != null) {
                    $('.hideOnChange').addClass('d-none');
                    $('.subscribedAlert .subscribedBody').html(data.textdiv);
                    $('.subscribedAlert').removeClass('d-none');
                //     Swal.fire({
                //     title: 'Are you sure?',
                //     text: "You are already subscribed for the package "+$('#lbl_pckname').text()+"! Current Package expiration date is "+data.subscribed_package.end_date,
                //     type: 'info',
                //     showCancelButton: true,
                //     confirmButtonColor: '#3085d6',
                //     cancelButtonColor: '#d33',
                //     confirmButtonText: 'Extend Subscription!'
                //     }).then((result) => {
                //         if(result.dismiss == "cancel") {
                //             $('#days_select').empty().trigger('change');
                //             $('#area_select').empty().trigger('change');
                //         }
                //         else if (result.value) {
                //             extend_subscribe = true;
                //             populate_data(data);
                //         }
                //     })
                }
                else {
                    $('.hideOnChange').removeClass('d-none');
                    $('.subscribedAlert').addClass('d-none');
                    $('.subscribedAlert .subscribedBody').html('');;

                    extend_subscribe = false;
                    populate_data(data);       

                    // The follwing text is only used for auto poulate the value
                    session_info = JSON.parse('<?php  echo json_encode($_SESSION['id']); ?>');
                    setTimeout(() => {
                        if(session_info != null) { // user is not logged in
                            input_save_package_id = localStorage.getItem('input_save_package_id');
                            input_save_package_days_id = parseInt(localStorage.getItem('input_save_package_days_id'));
                            input_save_package_area_id = parseInt(localStorage.getItem('input_save_package_area_id'));
                            if(input_save_package_area_id > 0) {
                                $('#area_select').val(input_save_package_area_id).trigger('change');
                            }
                            if(input_save_package_days_id > 0) {
                                $('#days_select').val(input_save_package_days_id).trigger('change');
                            }
                            
                        }
                        
                    }, 100);             
                }
                
                
                
            },
            error: function(error) {
                console.log(error);
            },
            complete: function(data) {
                console.log('complete');
            }
        });
    }
    function changeArea(index) {
        console.log('index', index);
        $('input[name="area_price"]').val($area_arr[index-1]);
        if(index > 0)
            localStorage.setItem('input_save_package_area_id', index);

        price_arr.no_of_area_price = $area_arr[index-1];
        selected_item.no_of_area = $('#area_select option:selected').text();
        total_price();
    }

    function changeDays(index) {
        $('input[name="days_price"]').val($days_arr[index-1]);
        if(index > 0)
            localStorage.setItem('input_save_package_days_id', index);

        price_arr.no_of_months_price = $days_arr[index-1];
        selected_item.no_of_month = $('#days_select option:selected').text();
        total_price();
    }

    function total_price() {
        tot_price = 0;
        tot_area_price = parseInt(price_arr.no_of_area_price);
        tot_days_price = parseInt(price_arr.no_of_months_price);
        if(tot_area_price > 0)
            tot_price += (tot_area_price - 0);

        if(tot_days_price > 0)
            tot_price += (tot_days_price - 0);
        
        $('#lbl_pckname').text($('#package_select option:selected').text());
        if(tot_area_price > 0 ) {
            $('#lbl_areaname').text(selected_item.no_of_area);
            $('#tot_areaprice').text(price_arr.no_of_area_price+"$");
        }
        else {
            $('#lbl_areaname, #tot_areaprice').text('');
        }

        if(tot_days_price > 0 && tot_price > 0) {
            $('#lbl_daysprice').text(selected_item.no_of_month);
            $('#tot_daysprice').text(price_arr.no_of_months_price+"$");
        }
        else
            $('#lbl_daysprice, #tot_daysprice').text('');

        if(tot_price > 0) {
            $('#tot_price').text(tot_price+'$');
            $('#subscribe').prop('disabled', false);
            price_arr.tot_price = tot_price;
        }
        else {
            $('#tot_price').text('');
            $('#subscribe').prop('disabled', true);
            price_arr.tot_price = 0;
        }

        if(extend_subscribe == true)
            $('#subscribe').text('Extend Subscription');
        else
            $('#subscribe').text('Subscribe')


    }
    // $(function() {
        
    // });
    $('#subscribe').click(function() {
        // console.log('ani');
        
        package_selected_id = $('#package_select option:selected').val();
        days_select_noof = $('#days_select option:selected').val();
        area_select_noof = $('#area_select option:selected').val();
        total = price_arr.tot_price;
        var obj = {
            package_selected_id,
            days_select_noof,
            area_select_noof,
            total,            
            action: 'new'
        };
        $('input[name="subscribe_info"]').val(JSON.stringify(obj));
        $.ajax({
            url:  "<?php echo site_url('pricing/check_subscribe_info'); ?>",
            type : "POST",
            data: {
                'subscribe_info' : $('input[name="subscribe_info"]').val(),
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(data) {
                data = JSON.parse(data);
                if(data.type == 'error') {
                    toastr[data.type](data.text);
                }
                if(data.type == 'success') {
                    toastr["success"]('Please wait until we redirect...');
                    $('form[name="subscribeForm"]').submit();
    
                }
            },
            error: function(error) {
                console.log(error);
            },
            complete: function() {
                console.log('complete');
            }
        });

    });

    function populate_data(data) {
        if(data.area_data){
            var option = '<option value="" disabled selected>Select no of areas...</option>';
            $(data.area_data).each(function(i, v) {
                option += `<option value="${v.noof}">${v.noof}</option>`;
                $area_arr.push(v.price);
            });
            $('#area_select').empty().append(option).trigger('change');
        }
        if(data.month_data){
            var option = '<option  disabled selected>Select no of days...</option>';
            $(data.month_data).each(function(i, v) {
                if(v != null) {
                    option += `<option value="${v.noof}">${v.noof}</option>`;
                    $days_arr.push(v.price);
                }
            });
            $('#days_select').empty().append(option).trigger('change');
        }        
    }

    function skip_subscription() {
        $.ajax({
            url: "<?php echo site_url('pricing/update_subscribe_flag') ?>",
            type: 'POST',
            dataType: 'json',
            data: {
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            success: function(arg) {
                window.location.href = "<?php echo site_url('dashboard') ?>"
            }
        });
    }

    function packRenew($id) {
        $('form[name="manageSubscribed"] input[name="package_id"]').val($id);
        $('form[name="manageSubscribed"] input[name="action"]').val('renew');
        $('form[name="manageSubscribed"]').submit();
    }

    function packModify($id) {
        $('form[name="manageSubscribed"] input[name="package_id"]').val($id);
        $('form[name="manageSubscribed"] input[name="action"]').val('modify');
        $('form[name="manageSubscribed"]').submit();
    }

</script>
<!-- All this section is for only to hold the value of the pricing package details,
if he or she is not looged in -->
<script>
    $(function() {
        
        session_info = JSON.parse('<?php echo json_encode($_SESSION['id']); ?>');
        if(session_info != null) { // user is not logged in
             localStorage.setItem('input_save_package_id', '14');
            input_save_package_id = localStorage.getItem('input_save_package_id');
            if(input_save_package_id > 0) {
                $('#package_select').val(input_save_package_id).trigger('change');

            }
            // if(input_package != null) {
            // }
        } else {
             input_save_package_id = localStorage.getItem('input_save_package_id');
             $('#package_select').val(input_save_package_id).trigger('change');
        }
        console.log(session_info);
    })
</script>