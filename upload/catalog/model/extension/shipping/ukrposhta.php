<?php
class ModelExtensionShippingUkrposhta extends Model {
	public function getApi() {
		require_once DIR_SYSTEM . 'library/ukrposhta/api.php';

		$config = array(
			'bearer_ecom' => $this->config->get('shipping_ukrposhta_bearer_ecom'),
			'bearer_tracking' => $this->config->get('shipping_ukrposhta_bearer_tracking'),
			'token' => $this->config->get('shipping_ukrposhta_token'),
			'sandbox' => $this->config->get('shipping_ukrposhta_sandbox'),
			'timeout' => (int)$this->config->get('shipping_ukrposhta_timeout') ? (int)$this->config->get('shipping_ukrposhta_timeout') : 20,
			'debug' => $this->config->get('shipping_ukrposhta_debug'),
			'log' => $this->log
		);

		return new UkrposhtaApi($config);
	}

	public function getQuote($address) {
		$this->load->language('extension/shipping/ukrposhta');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_ukrposhta_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_ukrposhta_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!$this->config->get('shipping_ukrposhta_status')) {
			$status = false;
		}

		$method_data = array();

		if (!$status) {
			return $method_data;
		}

		$sub_total = $this->cart->getSubTotal();
		$min_order = (float)$this->config->get('shipping_ukrposhta_min_order');
		$max_order = (float)$this->config->get('shipping_ukrposhta_max_order');

		if ($min_order > 0 && $sub_total < $min_order) {
			return $method_data;
		}
		if ($max_order > 0 && $sub_total > $max_order) {
			return $method_data;
		}

		$weight = $this->getCartWeightGrams();
		$length = (int)$this->config->get('shipping_ukrposhta_default_length') ? (int)$this->config->get('shipping_ukrposhta_default_length') : 30;
		$width = (int)$this->config->get('shipping_ukrposhta_default_width') ? (int)$this->config->get('shipping_ukrposhta_default_width') : 20;
		$height = (int)$this->config->get('shipping_ukrposhta_default_height') ? (int)$this->config->get('shipping_ukrposhta_default_height') : 10;

		$from_postcode = $this->config->get('shipping_ukrposhta_sender_postcode');
		$to_postcode = '';

		if (!empty($this->session->data['ukrposhta']['postcode'])) {
			$to_postcode = $this->session->data['ukrposhta']['postcode'];
		} elseif (!empty($address['postcode'])) {
			$to_postcode = preg_replace('/\D+/', '', $address['postcode']);
		}

		$quote_data = array();
		$methods = $this->getEnabledMethods();

		foreach ($methods as $code => $meta) {
			$cost = $this->calculateCost($from_postcode, $to_postcode, $meta['type'], $meta['delivery_type'], $weight, $length, $width, $height, $sub_total);

			$free_from = (float)$this->config->get('shipping_ukrposhta_free_total');
			$title = $this->language->get('text_' . $code);
			if ($title === 'text_' . $code) {
				$title = $meta['title'];
			}

			if ($free_from > 0 && $sub_total >= $free_from) {
				$cost = 0;
				$free_text = $this->language->get('text_free');
				if ($free_text && $free_text !== 'text_free') {
					$title .= ' (' . $free_text . ')';
				}
			}

			if ($cost === false) {
				continue;
			}

			$quote_data[$code] = array(
				'code' => 'ukrposhta.' . $code,
				'title' => $title,
				'cost' => $cost,
				'tax_class_id' => $this->config->get('shipping_ukrposhta_tax_class_id'),
				'text' => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_ukrposhta_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);
		}

		if ($quote_data) {
			$method_data = array(
				'code' => 'ukrposhta',
				'title' => $this->language->get('text_title'),
				'quote' => $quote_data,
				'sort_order' => $this->config->get('shipping_ukrposhta_sort_order'),
				'error' => false
			);
		}

		return $method_data;
	}

	private function getEnabledMethods() {
		$all = array(
			'express_w2w' => array('type' => 'EXPRESS', 'delivery_type' => 'W2W', 'title' => 'Укрпошта Експрес (відділення)'),
			'express_w2d' => array('type' => 'EXPRESS', 'delivery_type' => 'W2D', 'title' => 'Укрпошта Експрес (курʼєр)'),
			'standard_w2w' => array('type' => 'STANDARD', 'delivery_type' => 'W2W', 'title' => 'Укрпошта Стандарт (відділення)'),
			'standard_w2d' => array('type' => 'STANDARD', 'delivery_type' => 'W2D', 'title' => 'Укрпошта Стандарт (курʼєр)'),
		);

		$enabled = array();
		foreach ($all as $code => $meta) {
			if ($this->config->get('shipping_ukrposhta_method_' . $code)) {
				$enabled[$code] = $meta;
			}
		}

		if (!$enabled) {
			$enabled['express_w2w'] = $all['express_w2w'];
		}

		return $enabled;
	}

	public function calculateCost($from_postcode, $to_postcode, $type, $delivery_type, $weight, $length, $width, $height, $declared_price = 0) {
		$use_api = $this->config->get('shipping_ukrposhta_api_calc');
		$fallback = (float)$this->config->get('shipping_ukrposhta_fallback_cost');

		if ($use_api && $from_postcode && $to_postcode) {
			$api = $this->getApi();
			$payload = array(
				'addressFrom' => array('postcode' => (string)$from_postcode),
				'addressTo' => array('postcode' => (string)$to_postcode),
				'type' => $type,
				'deliveryType' => $delivery_type,
				'weight' => (int)$weight,
				'length' => (int)$length,
				'width' => (int)$width,
				'height' => (int)$height,
				'parcels' => array(
					array(
						'weight' => (int)$weight,
						'length' => (int)$length,
						'width' => (int)$width,
						'height' => (int)$height
					)
				),
				'validate' => true
			);

			if ($declared_price > 0) {
				$payload['declaredPrice'] = (float)$declared_price;
			}

			$result = $api->calculateDomestic($payload);
			if ($result && isset($result['deliveryPrice'])) {
				return (float)$result['deliveryPrice'];
			}

			if ($this->config->get('shipping_ukrposhta_debug')) {
				$this->log->write('[Ukrposhta] calc failed: ' . $api->getLastError());
			}
		}

		if ($this->config->get('shipping_ukrposhta_hide_without_price') && $use_api && (!$to_postcode || !$from_postcode)) {
			// show with fallback so customer can pick method before selecting office
		}

		return $fallback;
	}

	public function getCartWeightGrams() {
		$weight = $this->cart->getWeight();
		$class_id = (int)$this->config->get('config_weight_class_id');
		$query = $this->db->query("SELECT unit FROM `" . DB_PREFIX . "weight_class_description` WHERE weight_class_id = '" . $class_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		$unit = $query->num_rows ? strtolower($query->row['unit']) : 'kg';

		if (strpos($unit, 'g') !== false && strpos($unit, 'kg') === false) {
			$grams = (float)$weight;
		} else {
			$grams = (float)$weight * 1000;
		}

		$min = (int)$this->config->get('shipping_ukrposhta_min_weight');
		if ($min < 1) {
			$min = 250;
		}

		$default = (int)$this->config->get('shipping_ukrposhta_default_weight');
		if ($grams < 1 && $default > 0) {
			$grams = $default;
		}

		if ($grams < $min) {
			$grams = $min;
		}

		return (int)ceil($grams);
	}

	public function saveOrderData($order_id, $data) {
		$now = date('Y-m-d H:i:s');
		$exists = $this->db->query("SELECT order_id FROM `" . DB_PREFIX . "ukrposhta_order` WHERE order_id = '" . (int)$order_id . "'");

		$fields = array(
			'method_code' => isset($data['method_code']) ? $data['method_code'] : '',
			'city_id' => isset($data['city_id']) ? $data['city_id'] : '',
			'city_name' => isset($data['city_name']) ? $data['city_name'] : '',
			'region_name' => isset($data['region_name']) ? $data['region_name'] : '',
			'postcode' => isset($data['postcode']) ? $data['postcode'] : '',
			'office_id' => isset($data['office_id']) ? $data['office_id'] : '',
			'office_name' => isset($data['office_name']) ? $data['office_name'] : '',
			'street' => isset($data['street']) ? $data['street'] : '',
			'house' => isset($data['house']) ? $data['house'] : '',
			'apartment' => isset($data['apartment']) ? $data['apartment'] : ''
		);

		if ($exists->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "ukrposhta_order` SET
				`method_code` = '" . $this->db->escape($fields['method_code']) . "',
				`city_id` = '" . $this->db->escape($fields['city_id']) . "',
				`city_name` = '" . $this->db->escape($fields['city_name']) . "',
				`region_name` = '" . $this->db->escape($fields['region_name']) . "',
				`postcode` = '" . $this->db->escape($fields['postcode']) . "',
				`office_id` = '" . $this->db->escape($fields['office_id']) . "',
				`office_name` = '" . $this->db->escape($fields['office_name']) . "',
				`street` = '" . $this->db->escape($fields['street']) . "',
				`house` = '" . $this->db->escape($fields['house']) . "',
				`apartment` = '" . $this->db->escape($fields['apartment']) . "',
				`date_modified` = '" . $this->db->escape($now) . "'
				WHERE `order_id` = '" . (int)$order_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "ukrposhta_order` SET
				`order_id` = '" . (int)$order_id . "',
				`method_code` = '" . $this->db->escape($fields['method_code']) . "',
				`city_id` = '" . $this->db->escape($fields['city_id']) . "',
				`city_name` = '" . $this->db->escape($fields['city_name']) . "',
				`region_name` = '" . $this->db->escape($fields['region_name']) . "',
				`postcode` = '" . $this->db->escape($fields['postcode']) . "',
				`office_id` = '" . $this->db->escape($fields['office_id']) . "',
				`office_name` = '" . $this->db->escape($fields['office_name']) . "',
				`street` = '" . $this->db->escape($fields['street']) . "',
				`house` = '" . $this->db->escape($fields['house']) . "',
				`apartment` = '" . $this->db->escape($fields['apartment']) . "',
				`date_added` = '" . $this->db->escape($now) . "',
				`date_modified` = '" . $this->db->escape($now) . "'");
		}
	}

	public function searchCities($term) {
		$api = $this->getApi();
		$term = trim($term);
		if (mb_strlen($term) < 2) {
			return array();
		}

		$cities = $api->getCities($term);
		$result = array();

		foreach ($cities as $city) {
			$result[] = array(
				'city_id' => isset($city['CITY_ID']) ? $city['CITY_ID'] : '',
				'city_name' => isset($city['CITY_UA']) ? $city['CITY_UA'] : '',
				'region_name' => isset($city['REGION_UA']) ? $city['REGION_UA'] : '',
				'district_name' => isset($city['DISTRICT_UA']) ? $city['DISTRICT_UA'] : '',
				'city_type' => isset($city['SHORTCITYTYPE_UA']) ? $city['SHORTCITYTYPE_UA'] : '',
				'label' => trim(
					(isset($city['SHORTCITYTYPE_UA']) ? $city['SHORTCITYTYPE_UA'] . ' ' : '') .
					(isset($city['CITY_UA']) ? $city['CITY_UA'] : '') .
					(isset($city['REGION_UA']) ? ', ' . $city['REGION_UA'] : '')
				)
			);
		}

		return $result;
	}

	public function getOffices($city_id) {
		$api = $this->getApi();
		$offices = $api->getPostOfficesByCityId($city_id);
		$result = array();

		foreach ($offices as $office) {
			$postcode = '';
			if (isset($office['POSTCODE'])) {
				$postcode = $office['POSTCODE'];
			} elseif (isset($office['POSTINDEX'])) {
				$postcode = $office['POSTINDEX'];
			} elseif (isset($office['PO_POSTCODE'])) {
				$postcode = $office['PO_POSTCODE'];
			}

			$name = '';
			if (isset($office['PO_LONG'])) {
				$name = $office['PO_LONG'];
			} elseif (isset($office['POSTOFFICE_UA'])) {
				$name = $office['POSTOFFICE_UA'];
			} elseif (isset($office['NAME'])) {
				$name = $office['NAME'];
			}

			$address = '';
			if (isset($office['ADDRESS'])) {
				$address = $office['ADDRESS'];
			} elseif (isset($office['STREET_UA'])) {
				$address = trim($office['STREET_UA'] . ' ' . (isset($office['HOUSENUMBER']) ? $office['HOUSENUMBER'] : ''));
			}

			// skip closed / not for delivery if flags present
			if (isset($office['ISLOCKED']) && (string)$office['ISLOCKED'] === '1') {
				continue;
			}

			$result[] = array(
				'office_id' => isset($office['ID']) ? $office['ID'] : (isset($office['POSTOFFICE_ID']) ? $office['POSTOFFICE_ID'] : $postcode),
				'postcode' => $postcode,
				'name' => $name,
				'address' => $address,
				'label' => trim($postcode . ' — ' . $name . ($address ? ' (' . $address . ')' : ''))
			);
		}

		return $result;
	}
}
