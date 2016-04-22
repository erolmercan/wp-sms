<?php
	class smss extends WP_SMS {
		private $wsdl_link = "http://app.smss.co.il/index.php?app=ws";
		public $tariff = "http://www.app.smss.co.il/";
		public $unitrial = false;
		public $unit;
		public $flash = "enable";
		public $isflash = false;
		
		public function __construct() {
			parent::__construct();
			$this->validateNumber = "97xxxxxxxxxxx";
		}
		
		public function SendSMS() {
			// Check credit for the gateway
			if(!$this->GetCredit()) return;
			
			/**
			 * Modify sender number
			 *
			 * @since 3.4
			 * @param string $this->from sender number.
			 */
			$this->from = apply_filters('wp_sms_from', $this->from);
			
			/**
			 * Modify Receiver number
			 *
			 * @since 3.4
			 * @param array $this->to receiver number
			 */
			$this->to = apply_filters('wp_sms_to', $this->to);
			
			/**
			 * Modify text message
			 *
			 * @since 3.4
			 * @param string $this->msg text message.
			 */
			$this->msg = apply_filters('wp_sms_msg', $this->msg);
			
			$to = implode(',', $this->to);
			$msg = urlencode($this->msg);
			
			$result = file_get_contents($this->wsdl_link.'&u='.$this->username.'&h='.$this->password.'&op=pv&to='.$to.'&msg='.$msg);
			$result = json_decode($result);
			
			if($result->data[0]->status == 'ERR')
				return;
			
			$this->InsertToDB($this->from, $this->msg, $this->to);
			
			/**
			 * Run hook after send sms.
			 *
			 * @since 2.4
			 * @param string $result result output.
			 */
			do_action('wp_sms_send', $result);
			
			return $result;
		}
		
		public function GetCredit() {
			$result = file_get_contents($this->wsdl_link.'&u='.$this->username.'&h='.$this->password.'&op=cr');
			$result = json_decode($result);
			
			if($result->status == 'ERR')
				return;
			
			return $result->credit;
		}
	}
?>