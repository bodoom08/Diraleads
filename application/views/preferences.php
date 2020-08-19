<?php defined('BASEPATH') or exit('No direct script access allowed');

$this->load->view('common/front_end_layout/top', [
    'title' => 'My Preferences'
]);
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" />
<div class="dashboard">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2 col-md-12 col-sm-12 col-pad">
                <div class="dashboard-nav d-none d-xl-block d-lg-block">
                    <?php $this->load->view('common/front_end_layout/sidebar'); ?>
                </div>
            </div>
            <div class="col-lg-10 col-md-12 col-sm-12 col-pad">
                <div class="content-area5">
                    <div class="dashboard-content">
                        <div class="dashboard-list">
                            <div class="row">
                             <div class="col-md-8">
                             <h3 class="heading" style="border-bottom:0px;"> My Preferences</h3>
                             </div>
                             <div class="col-md-4">
                             <div class="pull-right">
                                    <button type="button" class="btn btn-primary m-2" data-toggle="modal" data-target="#addModal">+ Add Preference</button>
                                </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <hr/>
                            <div class="dashboard-message contact-2 bdr clearfix">
                                <div class="table-responsive">
                                    <table id="preferences-table" class="table small table-striped dt-responsive" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>For</th>
                                                <th>Price Range ($)</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="sub-banner-2 text-center">© Copyright 2019. All rights reserved</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open('preferences/add', 'id="addForm" class=""'); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Preference</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="name" class="col-form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title"/>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="col-form-label">Property For</label>
                            <select name="for[]" class="form-control" multiple>
                                <option>Sale</option>
                                <option>Rent</option>
                                <option>Short term rent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label">Property Types</label>
                            <select class="form-control custom-select" name="types[]" multiple>
                                <option>House</option>
                                <option>Apartment</option>
                                <option>Duplex</option>
                                <option>Office</option>
                                <option>Others</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="col-form-label">Minimum Price ($)</label>
                            <input type="text" class="form-control" name="price_min">
                        </div>
                        <div class="col-md-6">
                            <label class="col-form-label">Maximum Price ($)</label>
                            <input type="text" class="form-control" name="price_max">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="mobile" class="col-form-label">Area</label>
                    <select name="area[]" class="form-control custom-select" multiple>
                        <?php foreach ($areas as $key => $value) : ?>
                            <option value="<?php echo $value['id'] ?>"><?php echo $value['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" id="submitBtn" class="btn btn-primary">Add</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php $this->load->view('common/front_end_layout/bottom'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    $(function() {
        window.DT = $('#preferences-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "<?php echo site_url('preferences/json'); ?>"
        });
    });

    $('#addForm').ajaxForm({
        dataType: 'json',
        beforeSubmit: function() {
            event.preventDefault();
            $('#submitBtn').prop('disabled', 'disabled');
        },
        success: function(arg) {
            toastr[arg.type](arg.text);
            $('.fa-spinner').prop('display', 'block');
            $('#submitBtn').removeAttr('disabled');
            if (arg.type == 'success') {
                DT.ajax.reload();
                $('#addModal').modal('hide');
            }
        },
        error: function() {
            $('#submitBtn').prop('disabled', 'disabled');
        }
    });
</script>