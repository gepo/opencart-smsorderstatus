<?php
class Smsnotify {
	protected $registry;
	private $order_model;
	
	public function __construct($registry, $appname = 'front') {
		$this->registry = $registry;
		
		switch ($appname) {
		case 'front':
			$this->load->model('account/order');
			$this->order_model = $this->model_account_order;
			break;
		case 'admin':
			$this->load->model('sale/order');
			$this->order_model = $this->model_sale_order;
			break;
		}
	}

	public function __get($key) {
        return $this->registry->get($key);
    }

    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }
	
	public function clientSendStatusChange($order_id, $data) {
		return $this->sendStatusChange($order_id, $data);
	}
	
	public function clientSendNewOrder($order_id, $data) {
		return $this->sendTplMessage($order_id, $data, $this->config->get('config_sms_new_order_message'));
	}
	
	public function adminSendNewOrder($order_id, $data) {
		return $this->sendTplMessage($order_id, $data, $this->config->get('config_sms_message'), $this->config->get('config_sms_to'), $this->config->get('config_sms_copy'));
	}
	
	private function sendStatusChange($order_id, $data) {
		$message_settings = $this->config->get('config_sms_new_order_status_message');
		$template = $message_settings[ $data['order_status_id'] ]['message'];
		
		if (/*$order['order_status_id'] != $data['order_status_id']
			&&*/isset($message_settings[ $data['order_status_id'] ]['enabled'])
			&& $message_settings[ $data['order_status_id'] ]['enabled'] == 'on'
		) {
			return $this->sendTplMessage($order_id, $data, $template);
		}
	}
	
	private function sendTplMessage($order_id, $data, $template, $to = '', $copy = '') {
		$GLOBALS['modelSaleOrder'] = $this->order_model;
		$order = $GLOBALS['order'] = $this->order_model->getOrder($order_id);
		
		$orderProducts = $this->order_model->getOrderProducts($order_id);
		
		if (!function_exists("getoptionstring")) {
			function getoptionstring($item) {
				return $item['name'] . ': ' . $item['value'];
			}
		}
		
		if (!function_exists("getproductname")) {
			function getproductname($item) {
				$options = $GLOBALS['modelSaleOrder']->getOrderOptions($GLOBALS['order']['order_id'], $item['order_product_id']);
			
				if (count($options) > 0) {
					if ($item['quantity'] > 1) {
						return $item['quantity'] . ' x ' . $item['name'] . '(' . implode(',', array_map("getoptionstring", $options)) . ')';
					} else {
						return $item['name'] . '(' . implode(',', array_map("getoptionstring", $options)) . ')';
					}
				} else {
					if ($item['quantity'] > 1) {
						return $item['quantity'] . ' x ' . $item['name'];
					} else {
						return $item['name'];
					}
				}
			}
		}
		
		$products = implode(
			';',
			array_map(
				"getproductname", // without lamda function for compatibility with php < 5.3s
				$orderProducts
			)
		);
		
		
		$total_sub_total = 0;
		$total_tax = 0;
		$total_shipping = 0;
					
		$orderTotals = $this->order_model->getOrderTotals($order_id);
		foreach ($orderTotals as $total) {
			if ($total['code'] == 'tax') {
				$total_tax = $total['text'];
			} else
			if ($total['code'] == 'sub_total') {
				$total_sub_total = $total['text'];
			} else
			if ($total['code'] == 'shipping') {
				$total_shippnig = $total['text'];
			}
		}
		
		if (isset($data['order_status_id'])) {
			$status_name = $this->getStatusName($data['order_status_id']);
		} else {
			$status_name = $this->getStatusName($order['order_status_id']);
		}
		
		$options = array(
			'to'       => $order['telephone'],
			'from'     => $this->config->get('config_sms_from'),
			'username' => $this->config->get('config_sms_gate_username'),
			'password' => $this->config->get('config_sms_gate_password'),
			'message'  => str_replace(
				array(
					'{ID}',
					'{DATE}',
					'{TIME}',
					'{SUM}',
					'{PHONE}',
					'{STATUS}',
					'{FIRSTNAME}',
					'{LASTNAME}',
					'{COMMENT}',
					'{PRODUCTS}',
					'{PAYMENT_METHOD}',
					'{SHIPPING_METHOD}',
					'{SHIPPING_ADDRESS_1}',
					'{SHIPPING_ADDRESS_2}',
					'{SHIPPING_CITY}',
					'{SHIPPING_POSTCODE}',
					'{SHIPPING_COUNTRY}',
					'{SHIPPING_COMPANY}',
					'{TOTAL_SUB_SUM}',
					'{TOTAL_TAX}',
					'{TOTAL_SHIPPING}',
					'{STORE_NAME}',
				),
				array(
					$order['order_id'],
					date('d.m.Y'),
					date('H:i'),
					floatval($order['total']),
					$order['telephone'],
					$status_name,
					$order['firstname'],
					$order['lastname'],
					$data['comment'],
					$products,
					$order['payment_method'],
					$order['shipping_method'],
					$order['shipping_address_1'],
					$order['shipping_address_2'],
					$order['shipping_city'],
					$order['shipping_postcode'],
					$order['shipping_country'],
					$order['shipping_company'],
					$total_sub_total,
					$total_tax,
					$total_shipping,
					$order['store_name'],
				),
				$template
			)
		);
		$this->load->library('sms');
		
		if ($to != '') {
			$options['to'] = $to;
		}
		
		if ($copy != '') {
			$options['copy'] = $copy;
		}
		// only for clickatell:
		$options['api_key'] = $this->config->get('config_sms_gate_api_key');
		$sms = new Sms($this->config->get('config_sms_gatename'), $options);
		$sms->send();
	}
	
	private function getStatusName($order_status_id) {
		$query = $this->db->query(
			"SELECT name
			FROM " . DB_PREFIX . "order_status
			WHERE order_status_id = '" . (int)$order_status_id . "'
			AND language_id = '" . (int)$this->config->get('config_language_id') . "'
			LIMIT 1"
		);
		
		if (!$query->rows) {
			$this->log->write('Not found name for order_status_id = ' . $order_status_id);
      return 'New';
		}
		return $query->row['name'];
	}
}
?>