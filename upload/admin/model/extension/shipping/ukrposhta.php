<?php
class ModelExtensionShippingUkrposhta extends Model {
	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ukrposhta_shipment` (
			`shipment_id` INT(11) NOT NULL AUTO_INCREMENT,
			`order_id` INT(11) NOT NULL,
			`uuid` VARCHAR(64) NOT NULL DEFAULT '',
			`barcode` VARCHAR(64) NOT NULL DEFAULT '',
			`type` VARCHAR(32) NOT NULL DEFAULT '',
			`delivery_type` VARCHAR(16) NOT NULL DEFAULT '',
			`delivery_price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
			`declared_price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
			`postpay` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
			`status` VARCHAR(64) NOT NULL DEFAULT '',
			`postcode` VARCHAR(16) NOT NULL DEFAULT '',
			`office_name` VARCHAR(255) NOT NULL DEFAULT '',
			`address` TEXT,
			`payload` MEDIUMTEXT,
			`response` MEDIUMTEXT,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`shipment_id`),
			KEY `order_id` (`order_id`),
			KEY `barcode` (`barcode`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ukrposhta_order` (
			`order_id` INT(11) NOT NULL,
			`method_code` VARCHAR(64) NOT NULL DEFAULT '',
			`city_id` VARCHAR(32) NOT NULL DEFAULT '',
			`city_name` VARCHAR(255) NOT NULL DEFAULT '',
			`region_name` VARCHAR(255) NOT NULL DEFAULT '',
			`postcode` VARCHAR(16) NOT NULL DEFAULT '',
			`office_id` VARCHAR(64) NOT NULL DEFAULT '',
			`office_name` VARCHAR(255) NOT NULL DEFAULT '',
			`street` VARCHAR(255) NOT NULL DEFAULT '',
			`house` VARCHAR(64) NOT NULL DEFAULT '',
			`apartment` VARCHAR(64) NOT NULL DEFAULT '',
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`order_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ukrposhta_shipment`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ukrposhta_order`");
	}

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

	public function getOrderData($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ukrposhta_order` WHERE `order_id` = '" . (int)$order_id . "'");

		return $query->row;
	}

	public function saveOrderData($order_id, $data) {
		$existing = $this->getOrderData($order_id);
		$now = date('Y-m-d H:i:s');

		if ($existing) {
			$this->db->query("UPDATE `" . DB_PREFIX . "ukrposhta_order` SET
				`method_code` = '" . $this->db->escape(isset($data['method_code']) ? $data['method_code'] : '') . "',
				`city_id` = '" . $this->db->escape(isset($data['city_id']) ? $data['city_id'] : '') . "',
				`city_name` = '" . $this->db->escape(isset($data['city_name']) ? $data['city_name'] : '') . "',
				`region_name` = '" . $this->db->escape(isset($data['region_name']) ? $data['region_name'] : '') . "',
				`postcode` = '" . $this->db->escape(isset($data['postcode']) ? $data['postcode'] : '') . "',
				`office_id` = '" . $this->db->escape(isset($data['office_id']) ? $data['office_id'] : '') . "',
				`office_name` = '" . $this->db->escape(isset($data['office_name']) ? $data['office_name'] : '') . "',
				`street` = '" . $this->db->escape(isset($data['street']) ? $data['street'] : '') . "',
				`house` = '" . $this->db->escape(isset($data['house']) ? $data['house'] : '') . "',
				`apartment` = '" . $this->db->escape(isset($data['apartment']) ? $data['apartment'] : '') . "',
				`date_modified` = '" . $this->db->escape($now) . "'
				WHERE `order_id` = '" . (int)$order_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "ukrposhta_order` SET
				`order_id` = '" . (int)$order_id . "',
				`method_code` = '" . $this->db->escape(isset($data['method_code']) ? $data['method_code'] : '') . "',
				`city_id` = '" . $this->db->escape(isset($data['city_id']) ? $data['city_id'] : '') . "',
				`city_name` = '" . $this->db->escape(isset($data['city_name']) ? $data['city_name'] : '') . "',
				`region_name` = '" . $this->db->escape(isset($data['region_name']) ? $data['region_name'] : '') . "',
				`postcode` = '" . $this->db->escape(isset($data['postcode']) ? $data['postcode'] : '') . "',
				`office_id` = '" . $this->db->escape(isset($data['office_id']) ? $data['office_id'] : '') . "',
				`office_name` = '" . $this->db->escape(isset($data['office_name']) ? $data['office_name'] : '') . "',
				`street` = '" . $this->db->escape(isset($data['street']) ? $data['street'] : '') . "',
				`house` = '" . $this->db->escape(isset($data['house']) ? $data['house'] : '') . "',
				`apartment` = '" . $this->db->escape(isset($data['apartment']) ? $data['apartment'] : '') . "',
				`date_added` = '" . $this->db->escape($now) . "',
				`date_modified` = '" . $this->db->escape($now) . "'");
		}
	}

	public function getShipmentsByOrder($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ukrposhta_shipment` WHERE `order_id` = '" . (int)$order_id . "' ORDER BY `shipment_id` DESC");

		return $query->rows;
	}

	public function getShipment($shipment_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ukrposhta_shipment` WHERE `shipment_id` = '" . (int)$shipment_id . "'");

		return $query->row;
	}

	public function addShipment($data) {
		$now = date('Y-m-d H:i:s');

		$this->db->query("INSERT INTO `" . DB_PREFIX . "ukrposhta_shipment` SET
			`order_id` = '" . (int)$data['order_id'] . "',
			`uuid` = '" . $this->db->escape($data['uuid']) . "',
			`barcode` = '" . $this->db->escape($data['barcode']) . "',
			`type` = '" . $this->db->escape($data['type']) . "',
			`delivery_type` = '" . $this->db->escape($data['delivery_type']) . "',
			`delivery_price` = '" . (float)$data['delivery_price'] . "',
			`declared_price` = '" . (float)$data['declared_price'] . "',
			`postpay` = '" . (float)$data['postpay'] . "',
			`status` = '" . $this->db->escape($data['status']) . "',
			`postcode` = '" . $this->db->escape($data['postcode']) . "',
			`office_name` = '" . $this->db->escape($data['office_name']) . "',
			`address` = '" . $this->db->escape($data['address']) . "',
			`payload` = '" . $this->db->escape($data['payload']) . "',
			`response` = '" . $this->db->escape($data['response']) . "',
			`date_added` = '" . $this->db->escape($now) . "',
			`date_modified` = '" . $this->db->escape($now) . "'");

		return $this->db->getLastId();
	}

	public function deleteShipmentRecord($shipment_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ukrposhta_shipment` WHERE `shipment_id` = '" . (int)$shipment_id . "'");
	}

	public function updateShipmentStatus($shipment_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ukrposhta_shipment` SET
			`status` = '" . $this->db->escape($status) . "',
			`date_modified` = '" . $this->db->escape(date('Y-m-d H:i:s')) . "'
			WHERE `shipment_id` = '" . (int)$shipment_id . "'");
	}

	public function getOrderProductsWeight($order_id) {
		$query = $this->db->query("SELECT op.quantity, p.weight, p.weight_class_id
			FROM `" . DB_PREFIX . "order_product` op
			LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id)
			WHERE op.order_id = '" . (int)$order_id . "'");

		$weight_grams = 0;
		foreach ($query->rows as $row) {
			$w = (float)$row['weight'] * (int)$row['quantity'];
			if (!empty($row['weight_class_id'])) {
				$w = $this->weight->convert($w, $row['weight_class_id'], $this->config->get('config_weight_class_id'));
			}
			// convert store weight to grams assuming kg if class name unknown — use weight library unit if possible
			$weight_grams += $this->toGrams($w);
		}

		$min = (int)$this->config->get('shipping_ukrposhta_min_weight');
		if ($min < 1) {
			$min = 250;
		}

		$default = (int)$this->config->get('shipping_ukrposhta_default_weight');
		if ($weight_grams < 1 && $default > 0) {
			$weight_grams = $default;
		}

		if ($weight_grams < $min) {
			$weight_grams = $min;
		}

		return (int)ceil($weight_grams);
	}

	private function toGrams($weight) {
		// OpenCart default weight class is often kg or g. Prefer config.
		$class_id = (int)$this->config->get('config_weight_class_id');
		$query = $this->db->query("SELECT unit FROM `" . DB_PREFIX . "weight_class_description` WHERE weight_class_id = '" . $class_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		$unit = $query->num_rows ? strtolower($query->row['unit']) : 'kg';

		if (strpos($unit, 'g') !== false && strpos($unit, 'kg') === false) {
			return (float)$weight;
		}

		return (float)$weight * 1000;
	}

	public function parseMethodCode($code) {
		// shipping_method like "ukrposhta.express_w2w"
		$parts = explode('.', $code);
		$key = isset($parts[1]) ? $parts[1] : $code;
		$map = array(
			'express_w2w' => array('type' => 'EXPRESS', 'delivery_type' => 'W2W'),
			'express_w2d' => array('type' => 'EXPRESS', 'delivery_type' => 'W2D'),
			'standard_w2w' => array('type' => 'STANDARD', 'delivery_type' => 'W2W'),
			'standard_w2d' => array('type' => 'STANDARD', 'delivery_type' => 'W2D'),
		);

		return isset($map[$key]) ? $map[$key] : array('type' => 'EXPRESS', 'delivery_type' => 'W2W');
	}

	public function createShipmentForOrder($order_id, $override = array()) {
		$this->load->model('sale/order');
		$order = $this->model_sale_order->getOrder($order_id);

		if (!$order) {
			return array('success' => false, 'error' => 'Order not found');
		}

		$order_data = $this->getOrderData($order_id);
		$method = isset($override['method_code']) ? $override['method_code'] : ($order_data ? $order_data['method_code'] : $order['shipping_code']);
		$parsed = $this->parseMethodCode($method);

		$type = isset($override['type']) ? $override['type'] : $parsed['type'];
		$delivery_type = isset($override['delivery_type']) ? $override['delivery_type'] : $parsed['delivery_type'];

		$api = $this->getApi();

		$sender_uuid = $this->config->get('shipping_ukrposhta_sender_uuid');
		if (!$sender_uuid) {
			return array('success' => false, 'error' => 'Sender UUID is not configured');
		}

		$postcode = isset($override['postcode']) ? $override['postcode'] : ($order_data ? $order_data['postcode'] : '');
		$city = isset($override['city_name']) ? $override['city_name'] : ($order_data ? $order_data['city_name'] : $order['shipping_city']);
		$region = isset($override['region_name']) ? $override['region_name'] : ($order_data ? $order_data['region_name'] : $order['shipping_zone']);
		$street = isset($override['street']) ? $override['street'] : ($order_data ? $order_data['street'] : $order['shipping_address_1']);
		$house = isset($override['house']) ? $override['house'] : ($order_data ? $order_data['house'] : '');
		$apartment = isset($override['apartment']) ? $override['apartment'] : ($order_data ? $order_data['apartment'] : '');
		$office_name = $order_data ? $order_data['office_name'] : '';

		if (!$postcode) {
			return array('success' => false, 'error' => 'Recipient postcode is required');
		}

		$address_payload = array(
			'postcode' => (string)$postcode,
			'country' => 'UA',
			'region' => $region ? $region : 'Україна',
			'city' => $city ? $city : 'Україна',
		);

		if ($delivery_type === 'W2D' || $delivery_type === 'D2D') {
			$address_payload['street'] = $street ? $street : 'вул.';
			$address_payload['houseNumber'] = $house ? $house : '1';
			if ($apartment) {
				$address_payload['apartmentNumber'] = $apartment;
			}
		} else {
			// W2W — postcode of office is enough; street optional
			if ($street) {
				$address_payload['street'] = $street;
			}
			if ($house) {
				$address_payload['houseNumber'] = $house;
			}
			if ($office_name) {
				$address_payload['description'] = $office_name;
			}
		}

		$address = $api->createAddress($address_payload);
		if (!$address || empty($address['id'])) {
			return array('success' => false, 'error' => 'Address create failed: ' . $api->getLastError());
		}

		$name_parts = preg_split('/\s+/', trim($order['shipping_firstname'] . ' ' . $order['shipping_lastname']));
		$first = isset($name_parts[0]) ? $name_parts[0] : $order['shipping_firstname'];
		$last = isset($name_parts[1]) ? $name_parts[1] : $order['shipping_lastname'];
		$middle = isset($name_parts[2]) ? $name_parts[2] : ($this->config->get('shipping_ukrposhta_default_middlename') ? $this->config->get('shipping_ukrposhta_default_middlename') : 'Іванович');

		$client_payload = array(
			'type' => 'INDIVIDUAL',
			'name' => $last,
			'firstName' => $first,
			'lastName' => $last,
			'middleName' => $middle,
			'phoneNumber' => $api->normalizePhone($order['telephone']),
			'addressId' => $address['id'],
			'externalId' => 'oc-order-' . (int)$order_id
		);

		if (!empty($order['email'])) {
			$client_payload['email'] = $order['email'];
		}

		$recipient = $api->ensureClient($client_payload);
		if (!$recipient || empty($recipient['uuid'])) {
			return array('success' => false, 'error' => 'Recipient create failed: ' . $api->getLastError());
		}

		$weight = isset($override['weight']) ? (int)$override['weight'] : $this->getOrderProductsWeight($order_id);
		$length = isset($override['length']) ? (int)$override['length'] : ((int)$this->config->get('shipping_ukrposhta_default_length') ? (int)$this->config->get('shipping_ukrposhta_default_length') : 30);
		$width = isset($override['width']) ? (int)$override['width'] : ((int)$this->config->get('shipping_ukrposhta_default_width') ? (int)$this->config->get('shipping_ukrposhta_default_width') : 20);
		$height = isset($override['height']) ? (int)$override['height'] : ((int)$this->config->get('shipping_ukrposhta_default_height') ? (int)$this->config->get('shipping_ukrposhta_default_height') : 10);

		$declared = isset($override['declared_price']) ? (float)$override['declared_price'] : (float)$order['total'];
		$min_declared = (float)$this->config->get('shipping_ukrposhta_min_declared');
		if ($min_declared > 0 && $declared < $min_declared) {
			$declared = $min_declared;
		}

		$postpay = 0;
		if (!empty($override['postpay'])) {
			$postpay = (float)$override['postpay'];
		} elseif ($this->config->get('shipping_ukrposhta_postpay_enabled')) {
			$cod_codes = $this->config->get('shipping_ukrposhta_cod_payment_codes');
			if (is_string($cod_codes)) {
				$cod_codes = array_filter(array_map('trim', explode(',', $cod_codes)));
			}
			if (!$cod_codes) {
				$cod_codes = array('cod', 'liqpay', 'bank_transfer');
			}
			$payment_code = isset($order['payment_code']) ? $order['payment_code'] : '';
			foreach ($cod_codes as $code) {
				if ($payment_code && (strpos($payment_code, $code) !== false || $payment_code === $code)) {
					$postpay = (float)$order['total'];
					break;
				}
			}
		}

		$paid_by_recipient = (bool)$this->config->get('shipping_ukrposhta_paid_by_recipient');
		if (isset($override['paid_by_recipient'])) {
			$paid_by_recipient = (bool)$override['paid_by_recipient'];
		}

		$description = $this->config->get('shipping_ukrposhta_description');
		if (!$description) {
			$description = 'Замовлення #{order_id}';
		}
		$description = str_replace(array('{order_id}', '{total}'), array($order_id, $order['total']), $description);

		$shipment_payload = array(
			'sender' => array('uuid' => $sender_uuid),
			'recipient' => array('uuid' => $recipient['uuid']),
			'type' => $type,
			'deliveryType' => $delivery_type,
			'paidByRecipient' => $paid_by_recipient,
			'description' => mb_substr($description, 0, 100),
			'parcels' => array(
				array(
					'weight' => $weight,
					'length' => $length,
					'width' => $width,
					'height' => $height,
					'declaredPrice' => $declared
				)
			)
		);

		if ($postpay > 0) {
			$shipment_payload['postPay'] = $postpay;
		}

		if ($this->config->get('shipping_ukrposhta_sms')) {
			$shipment_payload['sms'] = true;
		}

		if ($this->config->get('shipping_ukrposhta_check_on_delivery')) {
			$shipment_payload['checkOnDelivery'] = true;
		}

		$result = $api->createShipment($shipment_payload);
		if (!$result || empty($result['uuid'])) {
			return array('success' => false, 'error' => 'Shipment create failed: ' . $api->getLastError(), 'payload' => $shipment_payload);
		}

		$status = '';
		if (isset($result['lifecycle']['status'])) {
			$status = $result['lifecycle']['status'];
		}

		$shipment_id = $this->addShipment(array(
			'order_id' => $order_id,
			'uuid' => $result['uuid'],
			'barcode' => isset($result['barcode']) ? $result['barcode'] : '',
			'type' => $type,
			'delivery_type' => $delivery_type,
			'delivery_price' => isset($result['deliveryPrice']) ? $result['deliveryPrice'] : 0,
			'declared_price' => $declared,
			'postpay' => $postpay,
			'status' => $status,
			'postcode' => $postcode,
			'office_name' => $office_name,
			'address' => trim($city . ', ' . $street . ' ' . $house),
			'payload' => json_encode($shipment_payload, JSON_UNESCAPED_UNICODE),
			'response' => json_encode($result, JSON_UNESCAPED_UNICODE)
		));

		if ($this->config->get('shipping_ukrposhta_add_tracking_to_order') && !empty($result['barcode'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET
				`shipping_method` = CONCAT(`shipping_method`, ' / ТТН: " . $this->db->escape($result['barcode']) . "')
				WHERE `order_id` = '" . (int)$order_id . "' AND `shipping_method` NOT LIKE '%" . $this->db->escape($result['barcode']) . "%'");
		}

		return array(
			'success' => true,
			'shipment_id' => $shipment_id,
			'uuid' => $result['uuid'],
			'barcode' => isset($result['barcode']) ? $result['barcode'] : '',
			'delivery_price' => isset($result['deliveryPrice']) ? $result['deliveryPrice'] : 0,
			'data' => $result
		);
	}
}
