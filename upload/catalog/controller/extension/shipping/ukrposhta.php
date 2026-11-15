<?php
class ControllerExtensionShippingUkrposhta extends Controller {
	public function index() {
		// Shipping method quotes are provided via model getQuote().
	}

	public function cities() {
		$this->load->model('extension/shipping/ukrposhta');
		$term = isset($this->request->get['term']) ? $this->request->get['term'] : (isset($this->request->get['q']) ? $this->request->get['q'] : '');
		$cities = $this->model_extension_shipping_ukrposhta->searchCities($term);

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode($cities));
	}

	public function offices() {
		$this->load->model('extension/shipping/ukrposhta');
		$city_id = isset($this->request->get['city_id']) ? $this->request->get['city_id'] : '';
		$offices = $city_id ? $this->model_extension_shipping_ukrposhta->getOffices($city_id) : array();

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode($offices));
	}

	public function save() {
		$json = array('success' => false);

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$data = array(
				'method_code' => isset($this->request->post['method_code']) ? $this->request->post['method_code'] : '',
				'city_id' => isset($this->request->post['city_id']) ? $this->request->post['city_id'] : '',
				'city_name' => isset($this->request->post['city_name']) ? $this->request->post['city_name'] : '',
				'region_name' => isset($this->request->post['region_name']) ? $this->request->post['region_name'] : '',
				'postcode' => isset($this->request->post['postcode']) ? preg_replace('/\D+/', '', $this->request->post['postcode']) : '',
				'office_id' => isset($this->request->post['office_id']) ? $this->request->post['office_id'] : '',
				'office_name' => isset($this->request->post['office_name']) ? $this->request->post['office_name'] : '',
				'street' => isset($this->request->post['street']) ? $this->request->post['street'] : '',
				'house' => isset($this->request->post['house']) ? $this->request->post['house'] : '',
				'apartment' => isset($this->request->post['apartment']) ? $this->request->post['apartment'] : ''
			);

			$this->session->data['ukrposhta'] = $data;
			$json['success'] = true;
			$json['data'] = $data;
		}

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode($json));
	}

	public function fields() {
		$this->load->language('extension/shipping/ukrposhta');

		$data['text_city'] = $this->language->get('text_city');
		$data['text_office'] = $this->language->get('text_office');
		$data['text_postcode'] = $this->language->get('text_postcode');
		$data['text_street'] = $this->language->get('text_street');
		$data['text_house'] = $this->language->get('text_house');
		$data['text_apartment'] = $this->language->get('text_apartment');
		$data['text_select_city'] = $this->language->get('text_select_city');
		$data['text_select_office'] = $this->language->get('text_select_office');
		$data['cities_url'] = $this->url->link('extension/shipping/ukrposhta/cities', '', true);
		$data['offices_url'] = $this->url->link('extension/shipping/ukrposhta/offices', '', true);
		$data['save_url'] = $this->url->link('extension/shipping/ukrposhta/save', '', true);
		$data['ukrposhta'] = isset($this->session->data['ukrposhta']) ? $this->session->data['ukrposhta'] : array();

		return $this->load->view('extension/shipping/ukrposhta_fields', $data);
	}

	/**
	 * Persist session Ukrposhta selection after order is created.
	 * Called from OCMOD hook on checkout/success or confirm.
	 */
	public function persistOrder($order_id = 0) {
		if (!$order_id && isset($this->session->data['order_id'])) {
			$order_id = (int)$this->session->data['order_id'];
		}

		if (!$order_id || empty($this->session->data['ukrposhta'])) {
			return;
		}

		$this->load->model('extension/shipping/ukrposhta');

		$data = $this->session->data['ukrposhta'];
		if (empty($data['method_code']) && !empty($this->session->data['shipping_method']['code'])) {
			$data['method_code'] = $this->session->data['shipping_method']['code'];
		}

		$this->model_extension_shipping_ukrposhta->saveOrderData($order_id, $data);
	}
}
