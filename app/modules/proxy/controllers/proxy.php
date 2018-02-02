<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class proxy extends MX_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
	}

	public function index(){
		$data = array(
			"result" => $this->model->fetch("*", PROXY, "uid = '".session("uid")."'", "id", "DESC")
		);
		$this->template->title(l('Proxy management'));
		$this->template->build('index', $data);
	}

	public function update(){
		$data = array(
			"result"  => $this->model->get("*", PROXY, "id = '".get("id")."'"),
		);
		$this->template->title(l('Proxy management'));
		$this->template->build('update', $data);
	}

	public function ajax_update(){
		$id = (int)post("id");

		if(post("name") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Name is required')
			));
		}

		if(post("proxy") == ""){
			ms(array(
				"st"    => "error",
				"label" => "bg-red",
				"txt"   => l('Proxy is required')
			));
		}

		$data = array(
			"name"            => post("name"),
			"proxy"           => post("proxy"),
			"uid"             => session("uid"),
			"status"          => (int)post("status"),
			"changed"         => NOW
		);

		if($id == 0){
			$data["created"]  = NOW;
			$this->db->insert(PROXY, $data);
			$id = $this->db->insert_id();
		}else{
			$this->db->update(PROXY, $data, array("id" => $id));
		}

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Update successfully')
		));
	}

	public function ajax_action_item(){
		$id = (int)post('id');
		$POST = $this->model->get('*', PROXY, "id = '{$id}'");
		if(!empty($POST)){
			switch (post("action")) {
				case 'delete':
					$instagram_accounts = $this->model->fetch("*",INSTAGRAM_ACCOUNTS,"proxy = '{$id}'");

					if(!empty($instagram_accounts)){
						foreach ($instagram_accounts as $key => $value) {
							$setting = $this->model->get("proxy_default",SETTINGS);
							if(!empty($setting)){
								$user_admin = $this->model->get("*",USER_MANAGEMENT,"admin = 1");
								$proxy_default = json_decode($setting->proxy_default);
								$proxy_default_igaccount = json_decode($proxy_default->proxy_default_igaccount);
								$proxy_item = $this->model->get("*",PROXY,"ig_accounts < '".$proxy_default_igaccount."' AND uid = '".$user_admin->id."' AND id !='".$id."'  AND status = 1","ig_accounts","DESC");
								if(!empty($proxy_item)){
									$this->db->where("id",$value->id);
									$this->db->set("proxy",$proxy_item->id,FALSE);
									$this->db->update(INSTAGRAM_ACCOUNTS);

									$this->db->where("id",$proxy_item->id);
									$this->db->set("ig_accounts","ig_accounts+1",FALSE);
									$this->db->update(PROXY);

								}else{
									$this->db->where("id",$value->id);
									$this->db->set("proxy",0,FALSE);
									$this->db->update(INSTAGRAM_ACCOUNTS);
								}
							}
						}
					}
					$this->db->delete(PROXY, "id = '{$id}'");
					break;
				
				case 'active':
					$this->db->update(PROXY, array("status" => 1), "id = '{$id}'");
					break;

				case 'disable':
					$this->db->update(PROXY, array("status" => 0), "id = '{$id}'");
					break;
			}
		}

		ms(array(
			"st"    => "success",
			"label" => "bg-light-green",
			"txt"   => l('Update successfully')
		));
	}

	public function ajax_action_multiple(){
		$ids =$this->input->post('id');
		if(!empty($ids)){
			foreach ($ids as $id) {
				$POST = $this->model->get('*', PROXY, "id = '{$id}'");
				if(!empty($POST)){
					switch (post("action")) {
						case 'delete':
							$instagram_accounts = $this->model->fetch("*",INSTAGRAM_ACCOUNTS,"proxy = '{$id}'");
							if(!empty($instagram_accounts)){
								foreach ($instagram_accounts as $key => $value) {
									$setting = $this->model->get("proxy_default",SETTINGS);
									if(!empty($setting)){
										$user_admin = $this->model->get("*",USER_MANAGEMENT,"admin = 1");
										$proxy_default = json_decode($setting->proxy_default);
										$proxy_default_igaccount = json_decode($proxy_default->proxy_default_igaccount);
										$proxy_item = $this->model->get("*",PROXY,"ig_accounts < '".$proxy_default_igaccount."' AND uid = '".$user_admin->id."' AND id !='".$id."'  AND status = 1","ig_accounts","DESC");
										if(!empty($proxy_item)){
											$this->db->where("id",$value->id);
											$this->db->set("proxy",$proxy_item->id,FALSE);
											$this->db->update(INSTAGRAM_ACCOUNTS);

											$this->db->where("id",$proxy_item->id);
											$this->db->set("ig_accounts","ig_accounts+1",FALSE);
											$this->db->update(PROXY);

										}else{
											$this->db->where("id",$value->id);
											$this->db->set("proxy",0,FALSE);
											$this->db->update(INSTAGRAM_ACCOUNTS);
										}
									}
								}
							}
							$this->db->delete(PROXY, "id = '{$id}'");
							break;
						case 'active':
							$this->db->update(PROXY, array("status" => 1), "id = '{$id}'");
							break;

						case 'disable':
							$this->db->update(PROXY, array("status" => 0), "id = '{$id}'");
							break;
					}
				}
			}
		}

		print_r(json_encode(array(
			'st' 	=> 'success',
			'txt' 	=> l('Update successfully')
		)));
	}
	public function ajax_action_proxy_detail(){
		$id = (int)post("id");
		$data = $this->model->fetch("*",INSTAGRAM_ACCOUNTS,"proxy = '".$id."'");
		if(!empty($data)){
			foreach ($data as $key => $value) {
				$user = $this->model->get("*",USER_MANAGEMENT,"id = '".$value->uid."'");
				if(!empty($user)){
					$data[$key]->fullname = $user->fullname;
					$data[$key]->email 	= $user->email;
				}
			}
		}
		print_r(json_encode(array(
			'st' 	=> "success",
			'data' 	=> json_encode($data),
		)));
	}





}