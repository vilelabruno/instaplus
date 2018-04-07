<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class payments extends MX_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		if(hashcheck()){
			redirect(PATH);
		}
	}

	public function index(){
		$data = array(
			"payment" => $this->model->get("*", PAYMENT),
			"package" => $this->model->fetch("*", PACKAGE, "status = 1 AND type = 2", "orders", "ASC")
		);
		$this->template->set_layout("home");
		$this->template->title(l('Pricing'));
		$this->template->build('index', $data);
	}

	public function type(){
		$package = $this->model->get("*", PACKAGE, "id = '".(int)get("package")."' AND status = 1 AND type = 2", "orders", "ASC");
		if(empty($package)) redirect(cn());

		$coupon  = $this->model->get("*", COUPON, "id = '".session("coupon")."' AND status = 1");
		if(!empty($coupon)){
			$coupon_packages = json_decode($coupon->packages);
			if(!empty($coupon_packages) && !in_array($package->id, $coupon_packages)){
				unset_session("coupon", $coupon->id);
				$coupon = array();
			}
		}

		$data = array(
			"payment" => $this->model->get("*", PAYMENT),
			"package" => $package,
			"coupon"  => $coupon
 		);
		$this->template->set_layout("home");
		$this->template->title(l('Pricing'));
		$this->template->build('type', $data);
	}

	public function ajax_coupon(){
		if(post("coupon") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Promotional code is required')
			));
		}

		//Check coupon
		$coupon = $this->model->get("*", COUPON, "code = '".post("coupon")."'");
		if(empty($coupon)){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('The promotion code entered does not exist')
			));
		}
		
		$coupon_expiration = strtotime($coupon->date_expiration." 23:59:59");
		$today             = date("Y-m-d", strtotime(NOW));
		$today_end         = strtotime($today." 00:00:00");
		if($coupon_expiration < $today_end){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('The promotion code expired')
			));
		}

		$package = $this->model->get("*", PACKAGE, "id = '".(int)post("package_id")."' AND status = 1 AND type = 2", "orders", "ASC");
		if(empty($package)){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('The package does not exist')
			));
		}

		$coupon_packages = json_decode($coupon->packages);
		if(!empty($coupon_packages) && !in_array($package->id, $coupon_packages)){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('The promotion code you entered has been applied to your package but no items qualify for the discount yet')
			));
		}


		set_session("coupon", $coupon->id);

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Successfully')
		));
	}

	public function stripe_process(){
		$payment = $this->model->get("*", PAYMENT);
		$user    = $this->model->get("*", USER_MANAGEMENT, "id = '".session("uid")."'");

		\Stripe\Stripe::setApiKey("sk_test_9R4Vr0pBLUpxALgdyIeShwts");
		$token  = $_POST['stripeToken'];

		$package_id = (int)get("package");

		//Check Package
		$package = $this->model->get("*", PACKAGE, "id = '".$package_id."' AND status = 1 AND type = 2", "orders", "ASC");
		if(empty($package)) redirect(cn("type??package=".$package_id));

		//COUPON
		$coupon  = $this->model->get("*", COUPON, "id = '".session("coupon")."' AND status = 1");
		$total_price = $package->price;
		if(!empty($coupon)){
			if($coupon->type==1){
				$discount = (float)$coupon->price;
			}else{
				$discount = ((float)$coupon->price/100)*(float)$package->price;
			}

			$total_price = $package->price - $discount;
			$total_price = ($total_price < 0)?0.01:$total_price;
			$total_price = $total_price*100;
		}

		$customer = \Stripe\Customer::create(array(
			'customer' => $user->email,
	  		'source'   => $token
		));


		$result = \Stripe\Charge::create(array(
	  		'customer' => $customer->id,
		  	'amount'   => $total_price,
		  	'currency' => $payment->currency
		));

		if($result->paid == 1){
			$data = array(
				"type"            => "stripe",
				"uid"             => session("uid"),
				"invoice"         => $result->id,
				"last_name"       => $result->customer,
				"business"        => $result->customer,
				"payer_email"     => $result->customer,
				"item_name"       => $package->name,
				"item_number"     => $package->id,
				"mc_gross"        => $result->amount,
				"feeAmount"       => $result->amount,
				"netAmount"       => $result->amount,
				"payment_date"    => date("Y-m-d H:i:s", strtotime($result->created)),
				"payment_status"  => $result->status,
				"full_data"       => json_encode($result),
				"created"         => NOW
			);

			$this->db->insert(PAYMENT_HISTORY, $data);
			$user = $this->model->get("*", USER_MANAGEMENT, "id = '".session("uid")."'");
			if(!empty($user)){
				$package_new = $package;
				$package_old = $this->model->get("*", PACKAGE, "id = '".$user->package_id."'");
				$package_id = $package_new->id;
				if(!empty($package_old)){
					if(strtotime(NOW) < strtotime($user->expiration_date)){
						$date_now = date("Y-m-d", strtotime(NOW));
						$date_expiration = date("Y-m-d", strtotime($user->expiration_date));
						$diff = abs(strtotime($date_expiration) - strtotime($date_now));
						$days = floor($diff/86400);

						$day_added = round(($package_old->price/$package_new->price)*$days);
						$total_day = $package_new->day + $day_added;
						$expiration_date = date('Y-m-d', strtotime("+".$total_day." days"));
					}else{
						$expiration_date = date('Y-m-d', strtotime("+".$package_new->day." days"));
					}
				}else{
					$expiration_date = date('Y-m-d', strtotime("+".$package_new->day." days"));
				}

				$data = array(
					"package_id"      => $package_id,
					"expiration_date" => $expiration_date
				);

				$this->db->update(USER_MANAGEMENT, $data, "id = '".session("uid")."'");
			}
		}

		redirect(PATH);
	}
	
	public function do_payment_pagseguro(){
		$payment = $this->model->get("*", PAYMENT);
		$package = $this->model->get("*", PACKAGE, "id = '".(int)get("package")."' AND status = 1");

		if(!empty($package )){

			//COUPON
			$coupon  = $this->model->get("*", COUPON, "id = '".session("coupon")."' AND status = 1");
			$total_price = $package->price;
			if(!empty($coupon)){
				if($coupon->type==1){
					$discount = (float)$coupon->price;
				}else{
					$discount = ((float)$coupon->price/100)*(float)$package->price;
				}

				$total_price = $package->price - $discount;
				$total_price = ($total_price < 0)?0.01:$total_price;
				$total_price = number_format($total_price,2);
			}
			$number = number_format($package->price,2);
			
		    $data['email'] = $payment->pagseguro_email;
			$data['token'] = $payment->pagseguro_token;
			$data['currency'] =  "BRL";
			$data['itemId1'] = $package->id;
			$data['itemDescription1'] = $package->name;
			$data['itemAmount1'] = $number;
			$data['itemQuantity1'] = '1';
			$data['reference'] = 'REF'.strtoupper(random_string(8));
			$data['redirectURL'] = cn("pagseguro_notify_payment");

		    $header = array('Content-Type' => 'application/json; charset=UTF-8;');
		    if($payment->sandbox == 0){
			    $response = curlExec("https://ws.pagseguro.uol.com.br/v2/checkout", $data, $header);
			    $json = json_decode(json_encode(simplexml_load_string($response)));
		    	header('Location: https://pagseguro.uol.com.br/v2/checkout/payment.html?code=' . $json->code);
		    }else{
		    	$response = curlExec("https://ws.sandbox.pagseguro.uol.com.br/v2/checkout", $data, $header);
			    $json = json_decode(json_encode(simplexml_load_string($response)));
		    	header('Location: https://sandbox.pagseguro.uol.com.br/v2/checkout/payment.html?code=' . $json->code);
		    }
		}else{
			redirect(cn());
		}
	}

	public function pagseguro_notify_payment(){
		$payment = $this->model->get("*", PAYMENT);
		$header = array('Content-Type' => 'application/json; charset=UTF-8;');
		if($payment->sandbox == 0){
			$response = curlExec("https://ws.pagseguro.uol.com.br/v2/transactions/".get("transaction_id")."?email=".$payment->pagseguro_email."&token=".$payment->pagseguro_token, null, $header);
	    }else{
			$response = curlExec("https://ws.sandbox.pagseguro.uol.com.br/v2/transactions/".get("transaction_id")."?email=".$payment->pagseguro_email."&token=".$payment->pagseguro_token, null, $header);
	    }
		$result = json_decode(json_encode(simplexml_load_string($response)));

		if(is_object($result)){
			switch ($result->status) {
				case 1:
					$status = "Pending";
					break;
				case 2:
					$status = "Awaiting Fulfillment";
					break;
				case 3:
					$status = "Completed";
					break;
				case 6:
					$status = "Refund";
					break;
				case 7:
					$status = "Cancel";
					break;
				
				default:
					$status = "";
					break;
			}

			$data = array(
				"type"            => "pagseguro",
				"uid"             => session("uid"),
				"invoice"         => $result->code,
				"last_name"       => $result->code,
				"business"        => $result->sender->name,
				"payer_email"     => $result->sender->email,
				"item_name"       => $result->items->item->id,
				"item_number"     => $result->items->item->description,
				"mc_gross"        => $result->grossAmount,
				"feeAmount"       => $result->feeAmount,
				"netAmount"       => $result->netAmount,
				"payment_date"    => date("Y-m-d H:i:s", strtotime($result->lastEventDate)),
				"payment_status"  => $result->status,
				"full_data"       => json_encode($result),
				"created"         => NOW
			);

			$this->db->insert(PAYMENT_HISTORY, $data);
			if($result->status == 3){
				$user = $this->model->get("*", USER_MANAGEMENT, "id = '".session("uid")."'");
				if(!empty($user)){
					$package_new = $this->model->get("*", PACKAGE, "id = '".$result->items->item->id."'");
					$package_old = $this->model->get("*", PACKAGE, "id = '".$user->package_id."'");
					$package_id = $package_new->id;
					if(!empty($package_old)){
						if(strtotime(NOW) < strtotime($user->expiration_date)){
							$date_now = date("Y-m-d", strtotime(NOW));
							$date_expiration = date("Y-m-d", strtotime($user->expiration_date));
							$diff = abs(strtotime($date_expiration) - strtotime($date_now));
							$days = floor($diff/86400);

							$day_added = round(($package_old->price/$package_new->price)*$days);
							$total_day = $package_new->day + $day_added;
							$expiration_date = date('Y-m-d', strtotime("+".$total_day." days"));
						}else{
							$expiration_date = date('Y-m-d', strtotime("+".$package_new->day." days"));
						}
					}else{
						$expiration_date = date('Y-m-d', strtotime("+".$package_new->day." days"));
					}

					$data = array(
						"package_id"      => $package_id,
						"expiration_date" => $expiration_date
					);

					$this->db->update(USER_MANAGEMENT, $data, "id = '".session("uid")."'");
				}
			}
		}

		//redirect(PATH);
	}

	public function do_payment_recurring(){
		$payment = $this->model->get("*",PAYMENT);
		$package = $this->model->get("*",PACKAGE,"id = '".(int)get("package")."'");
	
		if(is_object($payment)&&is_object($package)&&session("uid")){
			$package->currency_code = $payment->currency;
			$data = array(
				"payment" => $payment,
				"package" => $package,
			);
			$this->load->view("process",$data);

		}else{
			redirect(PATH);
		}
	}

	public function notify_payment_recurring(){

		if(isset($_REQUEST)&&$_REQUEST['txn_type']=="subscr_signup"){
			$package = $this->model->get("*",PACKAGE,"id = '".(int)get("package")."'");
			$user = $this->model->get("*",USER_MANAGEMENT,"id = '".session("uid")."'");
			if(empty($user) || empty($package) || !session("uid")) redirect(PATH);
			$data_payment_history=array(
				"uid" 				=> session("uid"),
				"first_name" 		=> $_REQUEST['first_name'],
				"last_name" 		=> $_REQUEST['last_name'],
				"business" 			=> $_REQUEST['business'],
				"receiver_email" 	=> $_REQUEST['receiver_email'],
				"payer_email"   	=> $_REQUEST['payer_email'],
				"item_name" 		=> $_REQUEST['item_name'],
				"address_street" 	=> $_REQUEST['address_street'],
				"address_city"   	=> $_REQUEST['address_city'],
				"address_country" 	=> $_REQUEST['address_country'],
				"payment_status" 	=> $_REQUEST['payer_status'],
				"payment_date" 		=> date("Y-m-d H:i:s", strtotime($_REQUEST['subscr_date'])),
				"mc_gross" 			=> $_REQUEST['mc_amount3'],
				"mc_currency" 		=> $_REQUEST['mc_currency'],
				"full_data" 		=> json_encode($_REQUEST),
				"created" 			=> NOW,
			);
			$data_payment=array(
				"package_id" 		=> (int)get("package"),
				"expiration_date" 	=> Date('Y-m-d H:i:s', strtotime("+".$package->day." days")),
			);
			$this->db->insert(PAYMENT_HISTORY,$data_payment_history);
			$user = $this->model->get("*",USER_MANAGEMENT,"id = '".session("uid")."'");
			if(!empty($user)){
				$this->db->update(USER_MANAGEMENT,$data_payment,"id = '".session("uid")."'");
			}
		}
		redirect(PATH);
	}

	public function do_payment(){
		$payment = $this->model->get("*", PAYMENT);
		$package = $this->model->get("*", PACKAGE, "id = '".(int)get("package")."'");

		if(empty($payment) || empty($package) || !session("uid")) redirect(PATH);

		$config['business'] 			= $payment->paypal_email;
		$config['cpp_header_image'] 	= ''; //Image header url [750 pixels wide by 90 pixels high]
		$config['return'] 				= cn().'notify_payment';
		$config['cancel_return'] 		= cn().'cancel_payment';
		$config['notify_url'] 			= cn().'process_payment'; //IPN Post
		$config['production'] 			= ($payment->sandbox == 1)?FALSE:TRUE; //Its false by default and will use sandbox
		$config["invoice"]				= random_string('numeric',8); //The invoice id
		$config["currency_code"]     	= $payment->currency; //The invoice id
		
		$this->load->library('paypal',$config);

		//COUPON
		$coupon  = $this->model->get("*", COUPON, "id = '".session("coupon")."' AND status = 1");
		$total_price = $package->price;
		if(!empty($coupon)){
			if($coupon->type==1){
				$discount = (float)$coupon->price;
			}else{
				$discount = ((float)$coupon->price/100)*(float)$package->price;
			}

			$total_price = $package->price - $discount;
			$total_price = ($total_price < 0)?0.01:$total_price;
			$total_price = number_format($total_price,2);
		}
		set_session("receiver_email",$payment->paypal_email);
		set_session("package_id",$package->id);
		set_session("package_name",$package->name);
		$this->paypal->add($package->name, $total_price, 1, $package->id); //Third item with code
		$this->paypal->pay(); //Proccess the payment
	}

	public function process_payment(){
		
	}

	public function notify_payment(){
		$result = $this->input->post();
		$user = $this->model->get("*", USER_MANAGEMENT, "id = '".session("uid")."'");
		if(!empty($result)&&!empty($user)){
			$data = array(
				"uid"             => session("uid"),
				"invoice"         => $result['tx'],
				"first_name"      => "",
				"last_name"       => $user->fullname,
				"receiver_email"  => session("receiver_email"),
				"payer_email"     => "",
				"item_name"       => session("package_name"),
				"item_number"     => session("package_id"),
				"address_street"  => "",
				"address_city"    => "",
				"address_country" => "",
				"mc_gross"        => $result['amt'],
				"mc_currency"     => $result['cc'],
				"payment_date"    => NOW,
				"payment_status"  => $result['st'],
				"full_data"       => json_encode($result),
				"created"         => NOW
			);
			$this->db->insert(PAYMENT_HISTORY, $data);
			if($result['st'] == "Completed"){
				$user = $this->model->get("*", USER_MANAGEMENT, "id = '".session("uid")."'");
				if(!empty($user)){
					$package_new = $this->model->get("*", PACKAGE, "id = '".session("package_id")."'");
					$package_old = $this->model->get("*", PACKAGE, "id = '".$user->package_id."'");
					$package_id = $package_new->id;
					unset_session("receiver_email");
					unset_session("package_id");
					unset_session("package_name");
					if(!empty($package_old)){
						if(strtotime(NOW) < strtotime($user->expiration_date)){
							$date_now = date("Y-m-d", strtotime(NOW));
							$date_expiration = date("Y-m-d", strtotime($user->expiration_date));
							$diff = abs(strtotime($date_expiration) - strtotime($date_now));
							$days = floor($diff/86400);

							$day_added = round(($package_old->price/$package_new->price)*$days);
							$total_day = $package_new->day + $day_added;
							$expiration_date = date('Y-m-d', strtotime("+".$total_day." days"));
						}else{
							$expiration_date = date('Y-m-d', strtotime("+".$package_new->day." days"));
						}
					}else{
						$expiration_date = date('Y-m-d', strtotime("+".$package_new->day." days"));
					}

					$data = array(
						"package_id"      => $package_id,
						"expiration_date" => $expiration_date
					);

					$this->db->update(USER_MANAGEMENT, $data, "id = '".session("uid")."'");
				}
			}
		}
		redirect(PATH);
	}

	public function cancel_payment(){
		redirect(url('payments'));
	}

}