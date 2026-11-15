<?php
/**
 * Ukrposhta eCom / Classifier / StatusTracking / Forms API client.
 * Compatible with OpenCart 2.x / 3.x / OcStore 3.x (PHP 5.6+).
 */
class UkrposhtaApi {
	const URL_ECOM_PROD = 'https://www.ukrposhta.ua/ecom/0.0.1/';
	const URL_FORMS_PROD = 'https://www.ukrposhta.ua/forms/ecom/0.0.1/';
	const URL_TRACK_PROD = 'https://www.ukrposhta.ua/status-tracking/0.0.1/';
	const URL_CLASSIFIER = 'https://www.ukrposhta.ua/address-classifier-ws/';
	const URL_ECOM_SANDBOX = 'https://dev.ukrposhta.ua/ecom/0.0.1/';
	const URL_FORMS_SANDBOX = 'https://dev.ukrposhta.ua/forms/ecom/0.0.1/';
	const URL_TRACK_SANDBOX = 'https://dev.ukrposhta.ua/status-tracking/0.0.1/';

	private $bearer_ecom;
	private $bearer_tracking;
	private $token;
	private $sandbox;
	private $timeout;
	private $last_error = '';
	private $last_http_code = 0;
	private $last_raw = '';
	private $debug = false;
	private $log;

	public function __construct($config = array()) {
		$this->bearer_ecom = isset($config['bearer_ecom']) ? $config['bearer_ecom'] : '';
		$this->bearer_tracking = isset($config['bearer_tracking']) ? $config['bearer_tracking'] : $this->bearer_ecom;
		$this->token = isset($config['token']) ? $config['token'] : '';
		$this->sandbox = !empty($config['sandbox']);
		$this->timeout = isset($config['timeout']) ? (int)$config['timeout'] : 20;
		$this->debug = !empty($config['debug']);
		$this->log = isset($config['log']) ? $config['log'] : null;
	}

	public function getLastError() {
		return $this->last_error;
	}

	public function getLastHttpCode() {
		return $this->last_http_code;
	}

	public function getLastRaw() {
		return $this->last_raw;
	}

	private function ecomUrl() {
		return $this->sandbox ? self::URL_ECOM_SANDBOX : self::URL_ECOM_PROD;
	}

	private function formsUrl() {
		return $this->sandbox ? self::URL_FORMS_SANDBOX : self::URL_FORMS_PROD;
	}

	private function trackUrl() {
		return $this->sandbox ? self::URL_TRACK_SANDBOX : self::URL_TRACK_PROD;
	}

	private function withToken($path) {
		$sep = (strpos($path, '?') === false) ? '?' : '&';

		return $path . $sep . 'token=' . rawurlencode($this->token);
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param string $bearer
	 * @param mixed  $body
	 * @param bool   $raw_binary
	 * @return mixed
	 */
	private function request($method, $url, $bearer, $body = null, $raw_binary = false) {
		$this->last_error = '';
		$this->last_http_code = 0;
		$this->last_raw = '';

		if (!$bearer) {
			$this->last_error = 'Bearer token is empty';
			return false;
		}

		$headers = array(
			'Authorization: Bearer ' . $bearer,
			'Accept: application/json',
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

		if ($body !== null) {
			$payload = is_string($body) ? $body : json_encode($body, JSON_UNESCAPED_UNICODE);
			$headers[] = 'Content-Type: application/json';
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$this->last_http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($response === false) {
			$this->last_error = curl_error($ch);
			curl_close($ch);
			$this->writeLog($method, $url, $body, null);
			return false;
		}

		curl_close($ch);
		$this->last_raw = $response;
		$this->writeLog($method, $url, $body, $response);

		if ($raw_binary) {
			if ($this->last_http_code >= 200 && $this->last_http_code < 300) {
				return $response;
			}
			$this->last_error = 'HTTP ' . $this->last_http_code;
			return false;
		}

		$data = json_decode($response, true);

		if ($this->last_http_code < 200 || $this->last_http_code >= 300) {
			$this->last_error = $this->extractError($data, $response);
			return false;
		}

		if ($data === null && $response !== '' && $response !== 'null') {
			// Some endpoints return empty body on DELETE
			if ($response === '') {
				return true;
			}
			$this->last_error = 'Invalid JSON response';
			return false;
		}

		return $data;
	}

	private function extractError($data, $raw) {
		if (is_array($data)) {
			if (isset($data['message'])) {
				return $data['message'];
			}
			if (isset($data['error']['message'])) {
				return $data['error']['message'];
			}
			if (isset($data['errors']) && is_array($data['errors'])) {
				$parts = array();
				foreach ($data['errors'] as $err) {
					if (is_string($err)) {
						$parts[] = $err;
					} elseif (isset($err['message'])) {
						$parts[] = $err['message'];
					}
				}
				if ($parts) {
					return implode('; ', $parts);
				}
			}
		}

		return 'HTTP ' . $this->last_http_code . ': ' . substr((string)$raw, 0, 300);
	}

	private function writeLog($method, $url, $request, $response) {
		if (!$this->debug || !$this->log) {
			return;
		}

		$safe_url = preg_replace('/token=[^&]+/i', 'token=***', $url);
		$msg = $method . ' ' . $safe_url . "\nREQ: " . substr(is_string($request) ? $request : json_encode($request), 0, 2000)
			. "\nHTTP: " . $this->last_http_code . "\nRES: " . substr((string)$response, 0, 2000);
		$this->log->write('[Ukrposhta] ' . $msg);
	}

	private function normalizeEntries($data) {
		if (!$data || !is_array($data)) {
			return array();
		}

		if (isset($data['Entries']['Entry'])) {
			$entries = $data['Entries']['Entry'];
			if (isset($entries[0]) || empty($entries)) {
				return is_array($entries) ? $entries : array();
			}
			return array($entries);
		}

		if (isset($data[0])) {
			return $data;
		}

		return array();
	}

	/* ===================== Address Classifier ===================== */

	public function getRegions($region_ua = '') {
		$query = array();
		if ($region_ua !== '') {
			$query['region_ua'] = $region_ua;
		}
		$url = self::URL_CLASSIFIER . 'get_regions_by_region_ua' . ($query ? '?' . http_build_query($query) : '');
		$data = $this->request('GET', $url, $this->bearer_ecom);

		return $this->normalizeEntries($data);
	}

	public function getCities($city_ua, $region_id = '', $district_id = '') {
		$query = array('city_ua' => $city_ua);
		if ($region_id !== '') {
			$query['region_id'] = $region_id;
		}
		if ($district_id !== '') {
			$query['district_id'] = $district_id;
		}
		$url = self::URL_CLASSIFIER . 'get_city_by_region_id_and_district_id_and_city_ua?' . http_build_query($query);
		$data = $this->request('GET', $url, $this->bearer_ecom);

		return $this->normalizeEntries($data);
	}

	public function getPostOfficesByCityId($city_id) {
		$url = self::URL_CLASSIFIER . 'get_postoffices_by_postcode_cityid_cityvpzid?' . http_build_query(array('city_id' => $city_id));
		$data = $this->request('GET', $url, $this->bearer_ecom);

		return $this->normalizeEntries($data);
	}

	public function getPostOfficesByPostindex($postindex) {
		$url = self::URL_CLASSIFIER . 'get_postoffices_by_postindex?' . http_build_query(array('pi' => $postindex));
		$data = $this->request('GET', $url, $this->bearer_ecom);

		return $this->normalizeEntries($data);
	}

	public function getPostcodesByCityId($city_id) {
		$url = self::URL_CLASSIFIER . 'get_postcode_by_settlement_id?' . http_build_query(array('settlement_id' => $city_id));
		$data = $this->request('GET', $url, $this->bearer_ecom);

		return $this->normalizeEntries($data);
	}

	/* ===================== Addresses / Clients ===================== */

	public function createAddress($address) {
		return $this->request('POST', $this->ecomUrl() . 'addresses', $this->bearer_ecom, $address);
	}

	public function getAddress($id) {
		return $this->request('GET', $this->ecomUrl() . 'addresses/' . rawurlencode($id), $this->bearer_ecom);
	}

	public function createClient($client) {
		return $this->request('POST', $this->ecomUrl() . $this->withToken('clients'), $this->bearer_ecom, $client);
	}

	public function getClient($uuid) {
		return $this->request('GET', $this->ecomUrl() . $this->withToken('clients/' . rawurlencode($uuid)), $this->bearer_ecom);
	}

	public function getClientByExternalId($external_id) {
		return $this->request('GET', $this->ecomUrl() . $this->withToken('clients/external-id/' . rawurlencode($external_id)), $this->bearer_ecom);
	}

	public function getClientByPhone($phone, $country = 'UA') {
		$path = 'clients/phone?' . http_build_query(array(
			'token' => $this->token,
			'countryISO3166' => $country,
			'phoneNumber' => $phone
		));

		return $this->request('GET', $this->ecomUrl() . $path, $this->bearer_ecom);
	}

	/**
	 * Find existing client by external id / phone or create a new one.
	 */
	public function ensureClient($client) {
		if (!empty($client['externalId'])) {
			$existing = $this->getClientByExternalId($client['externalId']);
			if ($existing && !empty($existing['uuid'])) {
				return $existing;
			}
		}

		if (!empty($client['phoneNumber'])) {
			$existing = $this->getClientByPhone($this->normalizePhone($client['phoneNumber']));
			if ($existing && !empty($existing['uuid'])) {
				return $existing;
			}
			if (is_array($existing) && isset($existing[0]['uuid'])) {
				return $existing[0];
			}
		}

		return $this->createClient($client);
	}

	public function normalizePhone($phone) {
		$digits = preg_replace('/\D+/', '', (string)$phone);
		if (strpos($digits, '380') === 0 && strlen($digits) === 12) {
			return substr($digits, 2);
		}
		if (strpos($digits, '0') === 0 && strlen($digits) === 10) {
			return substr($digits, 1);
		}

		return $digits;
	}

	/* ===================== Shipments ===================== */

	public function createShipment($shipment) {
		return $this->request('POST', $this->ecomUrl() . $this->withToken('shipments'), $this->bearer_ecom, $shipment);
	}

	public function getShipment($uuid_or_barcode) {
		return $this->request('GET', $this->ecomUrl() . $this->withToken('shipments/' . rawurlencode($uuid_or_barcode)), $this->bearer_ecom);
	}

	public function getShipmentByBarcode($barcode) {
		return $this->request('GET', $this->ecomUrl() . $this->withToken('shipments/barcode/' . rawurlencode($barcode)), $this->bearer_ecom);
	}

	public function deleteShipment($uuid) {
		return $this->request('DELETE', $this->ecomUrl() . $this->withToken('shipments/' . rawurlencode($uuid)), $this->bearer_ecom);
	}

	/* ===================== Price calculation ===================== */

	public function calculateDomestic($payload) {
		return $this->request('POST', $this->ecomUrl() . 'domestic/delivery-price', $this->bearer_ecom, $payload);
	}

	/* ===================== Forms ===================== */

	public function getSticker($uuid_or_barcode, $size = '') {
		$path = 'shipments/' . rawurlencode($uuid_or_barcode) . '/sticker';
		$query = array('token' => $this->token);
		if ($size) {
			$query['size'] = $size;
		}
		$url = $this->formsUrl() . $path . '?' . http_build_query($query);

		return $this->request('GET', $url, $this->bearer_ecom, null, true);
	}

	/* ===================== Status tracking ===================== */

	public function getLastStatus($barcode, $lang = '') {
		$query = array('barcode' => $barcode);
		if ($lang) {
			$query['lang'] = $lang;
		}
		$url = $this->trackUrl() . 'statuses/last?' . http_build_query($query);

		return $this->request('GET', $url, $this->bearer_tracking);
	}

	public function getStatuses($barcode, $lang = '') {
		$query = array('barcode' => $barcode);
		if ($lang) {
			$query['lang'] = $lang;
		}
		$url = $this->trackUrl() . 'statuses?' . http_build_query($query);

		return $this->request('GET', $url, $this->bearer_tracking);
	}
}
