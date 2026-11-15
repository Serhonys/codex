<?php if (!empty($is_ukrposhta)) { ?>
<div class="panel panel-default" id="ukrposhta-order-panel">
  <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-truck"></i> <?php echo $text_ukrposhta; ?></h3></div>
  <div class="panel-body">
    <p><button type="button" id="button-ukrposhta-create" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo $text_create; ?></button></p>
    <?php if (!empty($shipments)) { ?>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead><tr><td><?php echo $column_barcode; ?></td><td><?php echo $column_status; ?></td><td><?php echo $column_action; ?></td></tr></thead>
        <tbody>
        <?php foreach ($shipments as $shipment) { ?>
          <tr>
            <td><?php echo $shipment['barcode']; ?></td>
            <td><?php echo $shipment['status']; ?></td>
            <td>
              <a class="btn btn-info btn-xs" target="_blank" href="<?php echo $sticker_url; ?>&shipment_id=<?php echo $shipment['shipment_id']; ?>"><?php echo $text_sticker; ?></a>
              <button type="button" class="btn btn-default btn-xs button-ukrposhta-track" data-id="<?php echo $shipment['shipment_id']; ?>"><?php echo $text_track; ?></button>
              <button type="button" class="btn btn-danger btn-xs button-ukrposhta-delete" data-id="<?php echo $shipment['shipment_id']; ?>"><?php echo $text_delete; ?></button>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
    <?php } else { ?>
    <p><?php echo $text_no_shipments; ?></p>
    <?php } ?>
    <div id="ukrposhta-order-alert"></div>
  </div>
</div>
<script type="text/javascript"><!--
$('#button-ukrposhta-create').on('click', function() {
  $.post('<?php echo $create_url; ?>', {}, function(json) {
    if (json.error) { $('#ukrposhta-order-alert').html('<div class="alert alert-danger">'+json.error+'</div>'); }
    if (json.success) { location.reload(); }
  }, 'json');
});
$('.button-ukrposhta-track').on('click', function() {
  $.getJSON('<?php echo $track_url; ?>&shipment_id=' + $(this).data('id'), function(json) {
    if (json.success) { location.reload(); }
    if (json.error) { alert(json.error); }
  });
});
$('.button-ukrposhta-delete').on('click', function() {
  if (!confirm('Delete?')) return;
  $.getJSON('<?php echo $delete_url; ?>&shipment_id=' + $(this).data('id'), function() { location.reload(); });
});
//--></script>
<?php } ?>
