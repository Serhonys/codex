<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-shipping" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3></div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-shipping" class="form-horizontal">
          <p class="help-block"><?php echo $help_tokens; ?></p>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="shipping_ukrposhta_status" class="form-control">
                <?php if ($shipping_ukrposhta_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_bearer_ecom; ?></label>
            <div class="col-sm-10"><input type="text" name="shipping_ukrposhta_bearer_ecom" value="<?php echo $shipping_ukrposhta_bearer_ecom; ?>" class="form-control"/></div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_bearer_tracking; ?></label>
            <div class="col-sm-10"><input type="text" name="shipping_ukrposhta_bearer_tracking" value="<?php echo $shipping_ukrposhta_bearer_tracking; ?>" class="form-control"/></div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_token; ?></label>
            <div class="col-sm-10"><input type="text" name="shipping_ukrposhta_token" value="<?php echo $shipping_ukrposhta_token; ?>" class="form-control"/></div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_sender_uuid; ?></label>
            <div class="col-sm-10"><input type="text" name="shipping_ukrposhta_sender_uuid" value="<?php echo $shipping_ukrposhta_sender_uuid; ?>" class="form-control"/></div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_sender_postcode; ?></label>
            <div class="col-sm-10"><input type="text" name="shipping_ukrposhta_sender_postcode" value="<?php echo $shipping_ukrposhta_sender_postcode; ?>" class="form-control"/></div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10"><input type="text" name="shipping_ukrposhta_sort_order" value="<?php echo $shipping_ukrposhta_sort_order; ?>" class="form-control"/></div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_methods; ?></label>
            <div class="col-sm-10">
              <label class="checkbox-inline"><input type="checkbox" name="shipping_ukrposhta_method_express_w2w" value="1" <?php if ($shipping_ukrposhta_method_express_w2w) { ?>checked<?php } ?>/> <?php echo $entry_method_express_w2w; ?></label>
              <label class="checkbox-inline"><input type="checkbox" name="shipping_ukrposhta_method_express_w2d" value="1" <?php if ($shipping_ukrposhta_method_express_w2d) { ?>checked<?php } ?>/> <?php echo $entry_method_express_w2d; ?></label>
              <label class="checkbox-inline"><input type="checkbox" name="shipping_ukrposhta_method_standard_w2w" value="1" <?php if ($shipping_ukrposhta_method_standard_w2w) { ?>checked<?php } ?>/> <?php echo $entry_method_standard_w2w; ?></label>
              <label class="checkbox-inline"><input type="checkbox" name="shipping_ukrposhta_method_standard_w2d" value="1" <?php if ($shipping_ukrposhta_method_standard_w2d) { ?>checked<?php } ?>/> <?php echo $entry_method_standard_w2d; ?></label>
            </div>
          </div>
          <p class="text-muted">Повні налаштування доступні у Twig-шаблоні для OpenCart / OcStore 3.x.</p>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
