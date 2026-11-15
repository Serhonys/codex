<?php
class ControllerExtensionShippingUkrposhta extends Controller {
	private $error = array();

	private function tokenKey() {
		return isset($this->session->data['user_token']) ? 'user_token' : 'token';
	}

	private function token() {
		$key = $this->tokenKey();
		return isset($this->session->data[$key]) ? $this->session->data[$key] : '';
	}

	private function extensionsLink() {
		$route = isset($this->session->data['user_token']) ? 'marketplace/extension' : 'extension/extension';
		return $this->url->link($route, $this->tokenKey() . '=' . $this->token() . '&type=shipping', true);
	}

	public function index() {
		$lang = $this->load->language('extension/shipping/ukrposhta');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		$this->load->model('extension/shipping/ukrposhta');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_ukrposhta', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->extensionsLink());
		}

		$data = is_array($lang) ? $lang : array();
		foreach (array('heading_title', 'text_edit', 'text_enabled', 'text_disabled', 'text_all_zones', 'text_none', 'text_home', 'text_extension', 'button_save', 'button_cancel', 'tab_general', 'tab_api', 'tab_sender', 'tab_methods', 'tab_payment', 'entry_status', 'entry_sort_order', 'entry_geo_zone', 'entry_tax_class', 'entry_bearer_ecom', 'entry_bearer_tracking', 'entry_token', 'entry_sender_uuid', 'entry_sender_postcode', 'entry_sandbox', 'entry_debug', 'entry_timeout', 'entry_api_calc', 'entry_fallback_cost', 'entry_free_total', 'entry_min_order', 'entry_max_order', 'entry_default_weight', 'entry_min_weight', 'entry_default_length', 'entry_default_width', 'entry_default_height', 'entry_min_declared', 'entry_description', 'entry_middlename', 'entry_paid_by_recipient', 'entry_postpay', 'entry_cod_codes', 'entry_sms', 'entry_check_on_delivery', 'entry_add_tracking', 'entry_methods', 'entry_method_express_w2w', 'entry_method_express_w2d', 'entry_method_standard_w2w', 'entry_method_standard_w2d', 'help_tokens', 'help_description', 'help_cod_codes') as $key) {
			$data[$key] = $this->language->get($key);
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		$tk = $this->tokenKey() . '=' . $this->token();

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $tk, true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->extensionsLink()
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/ukrposhta', $tk, true)
		);

		$data['action'] = $this->url->link('extension/shipping/ukrposhta', $tk, true);
		$data['cancel'] = $this->extensionsLink();

		$fields = array(
			'shipping_ukrposhta_status',
			'shipping_ukrposhta_sort_order',
			'shipping_ukrposhta_geo_zone_id',
			'shipping_ukrposhta_tax_class_id',
			'shipping_ukrposhta_bearer_ecom',
			'shipping_ukrposhta_bearer_tracking',
			'shipping_ukrposhta_token',
			'shipping_ukrposhta_sender_uuid',
			'shipping_ukrposhta_sender_postcode',
			'shipping_ukrposhta_sandbox',
			'shipping_ukrposhta_debug',
			'shipping_ukrposhta_timeout',
			'shipping_ukrposhta_api_calc',
			'shipping_ukrposhta_fallback_cost',
			'shipping_ukrposhta_free_total',
			'shipping_ukrposhta_min_order',
			'shipping_ukrposhta_max_order',
			'shipping_ukrposhta_default_weight',
			'shipping_ukrposhta_min_weight',
			'shipping_ukrposhta_default_length',
			'shipping_ukrposhta_default_width',
			'shipping_ukrposhta_default_height',
			'shipping_ukrposhta_min_declared',
			'shipping_ukrposhta_description',
			'shipping_ukrposhta_default_middlename',
			'shipping_ukrposhta_paid_by_recipient',
			'shipping_ukrposhta_postpay_enabled',
			'shipping_ukrposhta_cod_payment_codes',
			'shipping_ukrposhta_sms',
			'shipping_ukrposhta_check_on_delivery',
			'shipping_ukrposhta_add_tracking_to_order',
			'shipping_ukrposhta_method_express_w2w',
			'shipping_ukrposhta_method_express_w2d',
			'shipping_ukrposhta_method_standard_w2w',
			'shipping_ukrposhta_method_standard_w2d',
			'shipping_ukrposhta_hide_without_price'
		);

		foreach ($fields as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} else {
				$data[$field] = $this->config->get($field);
			}
		}

		if ($data['shipping_ukrposhta_timeout'] === null || $data['shipping_ukrposhta_timeout'] === '') {
			$data['shipping_ukrposhta_timeout'] = 20;
		}
		if ($data['shipping_ukrposhta_min_weight'] === null || $data['shipping_ukrposhta_min_weight'] === '') {
			$data['shipping_ukrposhta_min_weight'] = 250;
		}
		if ($data['shipping_ukrposhta_default_weight'] === null || $data['shipping_ukrposhta_default_weight'] === '') {
			$data['shipping_ukrposhta_default_weight'] = 500;
		}
		if ($data['shipping_ukrposhta_default_length'] === null || $data['shipping_ukrposhta_default_length'] === '') {
			$data['shipping_ukrposhta_default_length'] = 30;
		}
		if ($data['shipping_ukrposhta_default_width'] === null || $data['shipping_ukrposhta_default_width'] === '') {
			$data['shipping_ukrposhta_default_width'] = 20;
		}
		if ($data['shipping_ukrposhta_default_height'] === null || $data['shipping_ukrposhta_default_height'] === '') {
			$data['shipping_ukrposhta_default_height'] = 10;
		}
		if ($data['shipping_ukrposhta_fallback_cost'] === null || $data['shipping_ukrposhta_fallback_cost'] === '') {
			$data['shipping_ukrposhta_fallback_cost'] = 50;
		}
		if ($data['shipping_ukrposhta_cod_payment_codes'] === null || $data['shipping_ukrposhta_cod_payment_codes'] === '') {
			$data['shipping_ukrposhta_cod_payment_codes'] = 'cod';
		}
		if ($data['shipping_ukrposhta_description'] === null || $data['shipping_ukrposhta_description'] === '') {
			$data['shipping_ukrposhta_description'] = 'Замовлення #{order_id}';
		}

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/ukrposhta', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/ukrposhta')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('extension/shipping/ukrposhta');
		$this->model_extension_shipping_ukrposhta->install();

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('shipping_ukrposhta', array(
			'shipping_ukrposhta_status' => 0,
			'shipping_ukrposhta_sort_order' => 1,
			'shipping_ukrposhta_api_calc' => 1,
			'shipping_ukrposhta_fallback_cost' => 50,
			'shipping_ukrposhta_method_express_w2w' => 1,
			'shipping_ukrposhta_method_express_w2d' => 1,
			'shipping_ukrposhta_method_standard_w2w' => 1,
			'shipping_ukrposhta_method_standard_w2d' => 0,
			'shipping_ukrposhta_timeout' => 20,
			'shipping_ukrposhta_min_weight' => 250,
			'shipping_ukrposhta_default_weight' => 500,
			'shipping_ukrposhta_default_length' => 30,
			'shipping_ukrposhta_default_width' => 20,
			'shipping_ukrposhta_default_height' => 10,
			'shipping_ukrposhta_description' => 'Замовлення #{order_id}',
			'shipping_ukrposhta_add_tracking_to_order' => 1,
			'shipping_ukrposhta_cod_payment_codes' => 'cod'
		));

		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/shipping/ukrposhta');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/shipping/ukrposhta');
	}

	public function uninstall() {
		$this->load->model('extension/shipping/ukrposhta');
		$this->model_extension_shipping_ukrposhta->uninstall();
	}

	public function order() {
		$this->load->language('extension/shipping/ukrposhta');
		$this->load->model('extension/shipping/ukrposhta');
		$this->load->model('sale/order');

		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;
		$order = $this->model_sale_order->getOrder($order_id);

		if (!$order) {
			return '';
		}

		$is_ukrposhta = (strpos($order['shipping_code'], 'ukrposhta') === 0);
		$order_data = $this->model_extension_shipping_ukrposhta->getOrderData($order_id);
		$shipments = $this->model_extension_shipping_ukrposhta->getShipmentsByOrder($order_id);

		$data['order_id'] = $order_id;
		$data['is_ukrposhta'] = $is_ukrposhta || !empty($order_data) || !empty($shipments);
		$data['order_data'] = $order_data;
		$data['shipments'] = $shipments;
		$data['shipping_method'] = $order['shipping_method'];
		$data['shipping_code'] = $order['shipping_code'];
		$tk = $this->tokenKey() . '=' . $this->token();
		$data['user_token'] = $this->token();
		$data['create_url'] = $this->url->link('extension/shipping/ukrposhta/createShipment', $tk . '&order_id=' . $order_id, true);
		$data['delete_url'] = $this->url->link('extension/shipping/ukrposhta/deleteShipment', $tk, true);
		$data['sticker_url'] = $this->url->link('extension/shipping/ukrposhta/sticker', $tk, true);
		$data['track_url'] = $this->url->link('extension/shipping/ukrposhta/track', $tk, true);
		$data['text_ukrposhta'] = $this->language->get('heading_title');
		$data['text_create'] = $this->language->get('button_create_shipment');
		$data['text_sticker'] = $this->language->get('button_sticker');
		$data['text_track'] = $this->language->get('button_track');
		$data['text_delete'] = $this->language->get('button_delete_shipment');
		$data['text_no_shipments'] = $this->language->get('text_no_shipments');
		$data['column_barcode'] = $this->language->get('column_barcode');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_price'] = $this->language->get('column_price');
		$data['column_date'] = $this->language->get('column_date');
		$data['column_action'] = $this->language->get('column_action');

		return $this->load->view('extension/shipping/ukrposhta_order', $data);
	}

	public function createShipment() {
		$this->load->language('extension/shipping/ukrposhta');
		$this->load->model('extension/shipping/ukrposhta');

		$json = array();
		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;

		if (!$this->user->hasPermission('modify', 'extension/shipping/ukrposhta')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$result = $this->model_extension_shipping_ukrposhta->createShipmentForOrder($order_id, $this->request->post);
			if (!empty($result['success'])) {
				$json['success'] = sprintf($this->language->get('text_shipment_created'), $result['barcode']);
				$json['barcode'] = $result['barcode'];
				$json['uuid'] = $result['uuid'];
			} else {
				$json['error'] = isset($result['error']) ? $result['error'] : 'Unknown error';
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function deleteShipment() {
		$this->load->language('extension/shipping/ukrposhta');
		$this->load->model('extension/shipping/ukrposhta');

		$json = array();
		$shipment_id = isset($this->request->get['shipment_id']) ? (int)$this->request->get['shipment_id'] : 0;

		if (!$this->user->hasPermission('modify', 'extension/shipping/ukrposhta')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			$shipment = $this->model_extension_shipping_ukrposhta->getShipment($shipment_id);
			if ($shipment) {
				$api = $this->model_extension_shipping_ukrposhta->getApi();
				if (!empty($shipment['uuid'])) {
					$api->deleteShipment($shipment['uuid']);
				}
				$this->model_extension_shipping_ukrposhta->deleteShipmentRecord($shipment_id);
				$json['success'] = $this->language->get('text_shipment_deleted');
			} else {
				$json['error'] = 'Shipment not found';
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function sticker() {
		$this->load->model('extension/shipping/ukrposhta');
		$shipment_id = isset($this->request->get['shipment_id']) ? (int)$this->request->get['shipment_id'] : 0;
		$shipment = $this->model_extension_shipping_ukrposhta->getShipment($shipment_id);

		if (!$shipment) {
			exit('Shipment not found');
		}

		$api = $this->model_extension_shipping_ukrposhta->getApi();
		$id = $shipment['uuid'] ? $shipment['uuid'] : $shipment['barcode'];
		$size = isset($this->request->get['size']) ? $this->request->get['size'] : '';
		$pdf = $api->getSticker($id, $size);

		if ($pdf === false) {
			exit('Sticker error: ' . $api->getLastError());
		}

		header('Content-Type: application/pdf');
		header('Content-Disposition: inline; filename="ukrposhta-' . preg_replace('/\W+/', '', $shipment['barcode']) . '.pdf"');
		echo $pdf;
		exit;
	}

	public function track() {
		$this->load->model('extension/shipping/ukrposhta');
		$json = array();
		$shipment_id = isset($this->request->get['shipment_id']) ? (int)$this->request->get['shipment_id'] : 0;
		$shipment = $this->model_extension_shipping_ukrposhta->getShipment($shipment_id);

		if (!$shipment || !$shipment['barcode']) {
			$json['error'] = 'Barcode not found';
		} else {
			$api = $this->model_extension_shipping_ukrposhta->getApi();
			$status = $api->getLastStatus($shipment['barcode']);
			if ($status === false) {
				$json['error'] = $api->getLastError();
			} else {
				$label = isset($status['eventName']) ? $status['eventName'] : '';
				if (!empty($status['date'])) {
					$label .= ' (' . $status['date'] . ')';
				}
				$this->model_extension_shipping_ukrposhta->updateShipmentStatus($shipment_id, $label);
				$json['success'] = $label;
				$json['data'] = $status;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
