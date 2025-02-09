<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class instagram_accounts extends MX_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
	}

	public function index(){
		$data = array(
			"result" => $this->model->getAccounts()
		);
		$this->template->title(l('Instagram accounts'));
		$this->template->build('index', $data);
	}

	public function add(){
		$data = array(
			"result" => $this->model->getAccounts()
		);
		$this->template->title(l('Instagram accounts'));
		$this->template->build('index', $data);
	}

	public function add_account(){
		$accounts = $this->model->fetch("*", INSTAGRAM_ACCOUNTS, getDatabyUser(0));
		$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".(int)get("id")."'".getDatabyUser());
		if(session('admin')){
			$setting = $this->model->get("proxy_default",SETTINGS);
			if(!empty($setting)){
				$user_admin = $this->model->get("*",USER_MANAGEMENT,"admin = 1");
				$proxy_default = json_decode($setting->proxy_default);
				$proxy_default_igaccount = json_decode($proxy_default->proxy_default_igaccount);
				$proxy = $this->model->fetch("*",PROXY,"ig_accounts < '".$proxy_default_igaccount."' AND uid = '".$user_admin->id."' AND status = 1","ig_accounts","DESC");
			}
		}else{
			$proxy = $this->model->fetch("*", PROXY, "uid ='".session("uid")."' AND status = 1","id","DESC");
		}
		
		$data = array(
			'result' => $account,
			'count'  => count($accounts),
			"proxy"  => $proxy,
		);
		$this->load->view("add_account", $data);
	}

	public function update(){
		$accounts = $this->model->fetch("*", INSTAGRAM_ACCOUNTS, getDatabyUser(0));
		$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".(int)get("id")."'".getDatabyUser());
		$data = array(
			'result' => $account,
			'count'  => count($accounts),
			"proxy"  => $this->model->fetch("*", PROXY, "uid = '".session("uid")."'", "id", "DESC")
		);
		$this->template->title(l('Instagram accounts'));
		$this->template->build('update', $data);
	}

	public function ajax_update(){
		$username = post('username');
		$password = post('password');
		$proxy_id = (int)post('proxy');
		if($username == "" || $password == ""){
			ms(array(
				"st"  => "error",
				"label" => "bg-red",
				"txt" => l('Please input all fields')
			));
		}

		if(session('admin')==1&&post('proxy') == ""){
			ms(array(
				"st"  => "error",
				"label" => "bg-red",
				"txt" => l('Please select a proxy')
			));
		}

		$proxy = "";

		if($proxy_id!=0){
			$proxy_item = $this->model->get("*", PROXY, "id = '".$proxy_id."'");
			if(!empty($proxy_item)){
				$proxy = $proxy_item->proxy;
			}
		}else{
			// Proxy is to be automated (proxy of admin)
			$setting = $this->model->get("proxy_default",SETTINGS);
			if(!empty($setting)){
				$user_admin = $this->model->get("*",USER_MANAGEMENT,"admin = 1");
				$proxy_default = json_decode($setting->proxy_default);
				$proxy_default_igaccount = json_decode($proxy_default->proxy_default_igaccount);
				$proxy_item = $this->model->get("*",PROXY,"ig_accounts < '".$proxy_default_igaccount."' AND uid = '".$user_admin->id."' AND status = 1","ig_accounts","DESC");
				if(!empty($proxy_item)){
					$proxy = $proxy_item->proxy;
					$proxy_id = $proxy_item->id;
				}
			}
		}

		$ig = Instagram_Login($username, $password, $proxy);
		if(is_array($ig) && isset($ig['st'])){
			ms($ig);
		}

		//Instagram Account Info 
		$info = $ig->getCurrentUser();
		if($info->status != "ok"){
			ms(array(
				"st"  => "error",
				"label" => "bg-red",
				"txt" => l('Connect failure')
			));
		}
		
		$user = $info->user;
		$fid  = $user->pk;
		$data = array(
			"uid"           => session("uid"),
			"fid"           => $fid,
			"proxy"         => $proxy_id,
			"avatar"        => $user->profile_pic_url,
			"username"      => $user->username,
			"password"      => $password,
			"checkpoint"    => 0,
		);
				
		$id = (int)post("id");
		$accounts = $this->model->fetch("*", INSTAGRAM_ACCOUNTS, "uid = ".session("uid"));
		if($id == 0){
			if(count($accounts) < getMaximumAccount()){
				$checkAccount = $this->model->get("*", INSTAGRAM_ACCOUNTS, "fid = '".$fid."' AND uid = ".session("uid"));
				if(!empty($checkAccount)){
					ms(array(
						"st"    => "error",
						"label" => "bg-red",
						"txt"   => l('This instagram account already exists')
					));
				}
				// Increase 1 IG account on proxy

				$this->db->where("id",$proxy_id);
				$this->db->set("ig_accounts","ig_accounts+1",FALSE);
				$this->db->update(PROXY);

				$this->db->insert(INSTAGRAM_ACCOUNTS, $data);
				$id = $this->db->insert_id();
			}else{
				ms(array(
					"st"    => "error",
					"label" => "bg-orange",
					"txt"   => l('Oh sorry! You have exceeded the number of accounts allowed, You are only allowed to update your account')
				));
			}
		}else{
			$checkAccount = $this->model->get("*", INSTAGRAM_ACCOUNTS, "fid = '".$fid."' AND id != '".$id."' AND uid = ".session("uid"));
			if(!empty($checkAccount)){
				ms(array(
					"st"    => "error",
					"label" => "bg-red",
					"txt"   => l('This instagram account already exists')
				));
			}

			$account = $this->model->get("*",INSTAGRAM_ACCOUNTS,"id = '".$id."' AND uid = ".session("uid"));
			if(!empty($account)){
				if($account->proxy!=$data["proxy"]){
					// Increase 1 IG account on new proxy.
					$this->db->where("id",$data["proxy"]);
					$this->db->set("ig_accounts","ig_accounts+1",FALSE);
					$this->db->update(PROXY);

					// Decrease 1 IG account on old proxy.
					$proxy_item = $this->model->get("*",PROXY,"id = '".$account->proxy."'");
					if (!empty($proxy_item)&&$proxy_item->ig_accounts!=0) {
						$this->db->where("id",$account->proxy);
						$this->db->set("ig_accounts","ig_accounts-1",FALSE);
						$this->db->update(PROXY);
					}
				}
			}

			$this->db->update(INSTAGRAM_ACCOUNTS, $data, array("id" => post("id")));
		}

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Update successfully')
		));
	}

	public function ajax_get_groups(){
		$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".post("id")."'".getDatabyUser());
		if(!empty($account)){

			//Add Proxy
			$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
			if(!empty($proxy_item)){
				$proxy = $proxy_item->proxy;
			}else{
				$proxy = "";
			}

			switch (post("type")) {
				case 'page':
					$IG_Oauth = Instagram_Login($account->username, $account->password, $proxy);
					if(is_array($IG_Oauth) && isset($IG_Oauth['st'])){
						ms($IG_Oauth);
					}else{
						//IG Info 
						$IG_Info = $IG_Oauth->getCurrentUser();
						if($IG_Info->status != "ok"){
							ms(array(
								"st"  => "error",
								"label" => "bg-red",
								"txt" => l('Connect failure')
							));
						}

						$data = array(
							"checkpoint" => 0,
							"avatar" => $IG_Info->user->profile_pic_url
						);

						$this->db->update(INSTAGRAM_ACCOUNTS, $data, array("id" => post("id")));
						ms(array(
							"st"    => "success",
							"label" => "bg-light-green",
							"txt"   => l('Update successfully')
						));
					}
					break;
			}
			ms(array(
				'st' 	=> 'success',
				"label" => "bg-light-green",
				'txt' 	=> l('Successfully')
			));
		}else{
			ms(array(
				'st' 	=> 'error',
				"label" => "bg-red",
				'txt' 	=> l('Update failure')
			));
		}
	}

	public function ajax_action_item(){
		$id = (int)post('id');
		$POST = $this->model->get('*', INSTAGRAM_ACCOUNTS, "id = '{$id}'".getDatabyUser());
		if(!empty($POST)){
			switch (post("action")) { 
				case 'delete':
					// decrease IG account on proxy
					
					$proxy_item = $this->model->get("*",PROXY,"id = '".$POST->proxy."'");
					if (!empty($proxy_item)&&$proxy_item->ig_accounts!=0) {
						$this->db->where("id",$POST->proxy);
						$this->db->set("ig_accounts","ig_accounts-1",FALSE);
						$this->db->update(PROXY);
					}


					$this->db->delete(INSTAGRAM_ACCOUNTS, "id = '{$id}'".getDatabyUser());
					$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."'".getDatabyUser());
					$this->db->delete(INSTAGRAM_HISTORY, "account_id = '".$id."'".getDatabyUser());
					$this->db->delete(INSTAGRAM_ACTIVITY, "account_id = '".$id."'".getDatabyUser());
					break;
				
				case 'active':
					$this->db->update(INSTAGRAM_ACCOUNTS, array("status" => 1), "id = '{$id}'".getDatabyUser());
					$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'like' OR category = 'comment' OR category = 'follow' OR category = 'followback' OR category = 'unfollow' OR category = 'repost')".getDatabyUser());
					$this->db->delete(INSTAGRAM_ACTIVITY, "account_id = '".$id."'".getDatabyUser());
					$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'post' OR category = 'message')".getDatabyUser());
					break;

				case 'disable':
					$this->db->update(INSTAGRAM_ACCOUNTS, array("status" => 0), "id = '{$id}'".getDatabyUser());
					$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'like' OR category = 'comment' OR category = 'follow' OR category = 'followback' OR category = 'unfollow' OR category = 'repost')".getDatabyUser());
					$this->db->delete(INSTAGRAM_ACTIVITY, "account_id = '".$id."'".getDatabyUser());
					$this->db->delete(INSTAGRAM_HISTORY, "account_id = '".$id."'".getDatabyUser());
					$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'post' OR category = 'message')".getDatabyUser());
					break;
			}
		}

		ms(array(
			'st' 	=> 'success',
			'txt' 	=> l('Successfully')
		));
	}

	public function ajax_action_multiple(){
		$ids =$this->input->post('id');
		if(!empty($ids)){
			foreach ($ids as $id) {
				$POST = $this->model->get('*', INSTAGRAM_ACCOUNTS, "id = '{$id}'".getDatabyUser());
				if(!empty($POST)){
					switch (post("action")) {
						case 'delete':
							// decrease IG account on proxy
							$proxy_item = $this->model->get("*",PROXY,"id = '".$POST->proxy."'");
							if (!empty($proxy_item)&&$proxy_item->ig_accounts!=0) {
								$this->db->where("id",$POST->proxy);
								$this->db->set("ig_accounts","ig_accounts-1",FALSE);
								$this->db->update(PROXY);
							}


							$this->db->delete(INSTAGRAM_ACCOUNTS, "id = '{$id}'".getDatabyUser());
							$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."'".getDatabyUser());
							$this->db->delete(INSTAGRAM_HISTORY, "account_id = '".$id."'".getDatabyUser());
							$this->db->delete(INSTAGRAM_ACTIVITY, "account_id = '".$id."'".getDatabyUser());
							break;
						case 'active':
							$this->db->update(INSTAGRAM_ACCOUNTS, array("status" => 1), "id = '{$id}'".getDatabyUser());
							$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'like' OR category = 'comment' OR category = 'follow' OR category = 'followback' OR category = 'unfollow' OR category = 'repost')".getDatabyUser());
							$this->db->delete(INSTAGRAM_ACTIVITY, "account_id = '".$id."'".getDatabyUser());
							$this->db->delete(INSTAGRAM_HISTORY, "account_id = '".$id."'".getDatabyUser());
							$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'post' OR category = 'message')".getDatabyUser());
							break;

						case 'disable':
							$this->db->update(INSTAGRAM_ACCOUNTS, array("status" => 0), "id = '{$id}'".getDatabyUser());
							$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'like' OR category = 'comment' OR category = 'follow' OR category = 'followback' OR category = 'unfollow' OR category = 'repost')".getDatabyUser());
							$this->db->delete(INSTAGRAM_ACTIVITY, "account_id = '".$id."'".getDatabyUser());
							$this->db->delete(INSTAGRAM_SCHEDULES, "account_id = '".$id."' AND (category = 'post' OR category = 'message')".getDatabyUser());
							break;
					}
				}
			}
		}

		print_r(json_encode(array(
			'st' 	=> 'success',
			'txt' 	=> l('Successfully')
		)));
	}
}