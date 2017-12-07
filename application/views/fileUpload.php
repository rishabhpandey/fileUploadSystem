<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <i class="fa fa-upload"></i> File Upload
      <!-- Use of anchor url_helper example -->
    </h1>
  </section>
    
  <section class="content">
    <?php if($this->session->flashdata('success')){ ?>
      <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert">&times;</a>
        <strong>Success!</strong> <?php echo $this->session->flashdata('success'); ?>
      </div>
    <?php } ?>

    <div class="row">
      <!-- left column -->
      <div class="col-md-8">
        <!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">File Upload</h3>
          </div>
          <!-- form start -->
          <form role="form" id="fileUpload" action="<?php echo base_url() ?>submitFileUpload"  method="post" role="form" enctype="multipart/form-data">
            <div class="box-body">
              <div class="form-group">
                <label for="exampleInputFile">File input<span style="color:red;">*</span></label>
                <input type="file"  class="required" id="userFile" name="userFile" >
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary" value="Upload">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </section>

</div>
<script src="<?php echo base_url(); ?>assets/js/fileUpload.js" type="text/javascript"></script>
