/* global jQuery, window */
(function ($) {
  'use strict';

  function cfg() {
    return window.UKRPOSHTA_CFG || {};
  }

  function selectedCode() {
    return $('input[name="shipping_method"]:checked').val() || '';
  }

  function isUkrposhta() {
    return selectedCode().indexOf('ukrposhta.') === 0;
  }

  function isDoor() {
    return selectedCode().indexOf('_w2d') !== -1;
  }

  function toggle() {
    if (isUkrposhta()) {
      $('#ukrposhta-fields').show();
      if (isDoor()) {
        $('#ukrposhta-office-wrap').hide();
        $('#ukrposhta-address-wrap').show();
      } else {
        $('#ukrposhta-office-wrap').show();
        $('#ukrposhta-address-wrap').hide();
      }
      save();
    } else {
      $('#ukrposhta-fields').hide();
    }
  }

  function save() {
    if (!isUkrposhta() || !cfg().saveUrl) {
      return;
    }
    var $opt = $('#ukrposhta-office option:selected');
    $.post(cfg().saveUrl, {
      method_code: selectedCode(),
      city_id: $('#ukrposhta-city-id').val(),
      city_name: $('#ukrposhta-city').val(),
      region_name: $('#ukrposhta-region-name').val(),
      postcode: isDoor() ? $('#ukrposhta-postcode').val() : ($opt.data('postcode') || ''),
      office_id: $opt.val() || '',
      office_name: $opt.data('name') || $opt.text(),
      street: $('#ukrposhta-street').val(),
      house: $('#ukrposhta-house').val(),
      apartment: $('#ukrposhta-apartment').val()
    });
  }

  function loadOffices(cityId) {
    if (!cityId || !cfg().officesUrl) {
      return;
    }
    $.getJSON(cfg().officesUrl, { city_id: cityId }, function (rows) {
      var $sel = $('#ukrposhta-office').empty();
      $sel.append($('<option>').val('').text(cfg().selectOffice || 'Select'));
      (rows || []).forEach(function (row) {
        $sel.append(
          $('<option>')
            .val(row.office_id)
            .text(row.label)
            .attr('data-postcode', row.postcode)
            .attr('data-name', row.name)
        );
      });
      save();
    });
  }

  var timer = null;

  $(document)
    .on('change', 'input[name="shipping_method"]', toggle)
    .on('input', '#ukrposhta-city', function () {
      var term = $(this).val();
      clearTimeout(timer);
      if (!cfg().citiesUrl || term.length < 2) {
        $('#ukrposhta-city-suggest').hide().empty();
        return;
      }
      timer = setTimeout(function () {
        $.getJSON(cfg().citiesUrl, { term: term }, function (rows) {
          var $box = $('#ukrposhta-city-suggest').empty();
          (rows || []).slice(0, 15).forEach(function (row) {
            $box.append(
              $('<a href="#" class="list-group-item"></a>').text(row.label).data(row)
            );
          });
          $box.toggle(!!(rows && rows.length));
        });
      }, 300);
    })
    .on('click', '#ukrposhta-city-suggest a', function (e) {
      e.preventDefault();
      var row = $(this).data();
      $('#ukrposhta-city').val(row.city_name);
      $('#ukrposhta-city-id').val(row.city_id);
      $('#ukrposhta-region-name').val(row.region_name);
      $('#ukrposhta-city-suggest').hide().empty();
      loadOffices(row.city_id);
    })
    .on('change input', '#ukrposhta-office, #ukrposhta-postcode, #ukrposhta-street, #ukrposhta-house, #ukrposhta-apartment', save);

  $(function () {
    toggle();
  });
})(jQuery);
