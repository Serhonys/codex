<div id="ukrposhta-fields" style="display:none;margin:15px 0;padding:12px;border:1px solid #ddd;">
  <div class="form-group">
    <label><?php echo $text_city; ?></label>
    <input type="text" id="ukrposhta-city" class="form-control" value="<?php echo isset($ukrposhta['city_name']) ? $ukrposhta['city_name'] : ''; ?>" />
    <input type="hidden" id="ukrposhta-city-id" value="<?php echo isset($ukrposhta['city_id']) ? $ukrposhta['city_id'] : ''; ?>" />
    <input type="hidden" id="ukrposhta-region-name" value="<?php echo isset($ukrposhta['region_name']) ? $ukrposhta['region_name'] : ''; ?>" />
    <div id="ukrposhta-city-suggest" class="list-group" style="display:none;"></div>
  </div>
  <div class="form-group" id="ukrposhta-office-wrap">
    <label><?php echo $text_office; ?></label>
    <select id="ukrposhta-office" class="form-control"><option value=""><?php echo $text_select_office; ?></option></select>
  </div>
  <div id="ukrposhta-address-wrap" style="display:none;">
    <div class="form-group"><label><?php echo $text_postcode; ?></label><input type="text" id="ukrposhta-postcode" class="form-control" value="<?php echo isset($ukrposhta['postcode']) ? $ukrposhta['postcode'] : ''; ?>" /></div>
    <div class="form-group"><label><?php echo $text_street; ?></label><input type="text" id="ukrposhta-street" class="form-control" value="<?php echo isset($ukrposhta['street']) ? $ukrposhta['street'] : ''; ?>" /></div>
    <div class="form-group"><label><?php echo $text_house; ?></label><input type="text" id="ukrposhta-house" class="form-control" value="<?php echo isset($ukrposhta['house']) ? $ukrposhta['house'] : ''; ?>" /></div>
    <div class="form-group"><label><?php echo $text_apartment; ?></label><input type="text" id="ukrposhta-apartment" class="form-control" value="<?php echo isset($ukrposhta['apartment']) ? $ukrposhta['apartment'] : ''; ?>" /></div>
  </div>
</div>
<script src="catalog/view/javascript/ukrposhta/checkout.js" type="text/javascript"></script>
<script type="text/javascript">
window.UKRPOSHTA_CFG = {
  citiesUrl: '<?php echo $cities_url; ?>',
  officesUrl: '<?php echo $offices_url; ?>',
  saveUrl: '<?php echo $save_url; ?>',
  selectOffice: '<?php echo $text_select_office; ?>'
};
</script>
