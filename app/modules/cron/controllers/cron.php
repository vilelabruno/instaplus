<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class cron extends MX_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
	}

	public function post(){
		$spintax = new Spintax();
		ini_set('max_execution_time', 300000);
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status !=', 2)
      	->where('status !=', 3)
      	->where('status !=', 4)
	    ->where('category', 'post')
	    ->where('time_post <= ', NOW)
	    ->get()->result();

		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$row->url         = $spintax->process($row->url);
				$row->message     = $spintax->process($row->message);
				$row->title       = $spintax->process($row->title);
				$row->description = $spintax->process($row->description);
				$row->image       = $spintax->process($row->image);
				$row->caption     = $spintax->process($row->caption);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "username = '".$row->account_name."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$row->password = $account->password;
					$row->username = $account->username;
					$row->fid = $account->fid;

					//Add Proxy
					$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
					if(!empty($proxy_item)){
						$row->proxy = $proxy_item->proxy;
					}else{
						$row->proxy = "";
					}

					$response = (object)Instagram_Post((object)$row);
					$arr_update = array(
						'status' => ($response->st == "success")?3:4,
						'result' => (isset($response->id) && $response->id != "")?$response->id:"",
						'message_error' => (isset($response->txt) && $response->txt != "")?$response->txt:""
					);

					if($repeat == 1 && $time_post_day <= $repeat_end){
						$arr_update['status']    = 5;
						$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

						$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
						if(!empty($user)){
							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}else{
							$arr_update['time_post_show'] = date("Y-m-d H:i:s", $time_post);
						}
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}else{
					$arr_update = array(
						'status' => 4,
						'message_error' => l('Instagram account not exist')
					);
					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function like(){
		ini_set('max_execution_time', 300000);
        $limit_item = divisible_cronjob($category='like',segment(3));
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'like')
	    ->where('time_post <= ', NOW)
	    ->limit($limit_item['limit_per_page'],$limit_item['start_index'])
	    ->get()->result();
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);
				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}
						$row->description = unset_match_values($row->description,$row->blacklists);
						echo $row->proxy."<br>";
						$response = (object)Instagram_Post((object)$row);
						
					
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}else{
							print_r($response);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					//$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function comment(){
		ini_set('max_execution_time', 300000);

        $limit_item = divisible_cronjob($category='comment',segment(3));
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'comment')
	    ->where('time_post <= ', NOW)
	    ->limit($limit_item['limit_per_page'],$limit_item['start_index'])
	    ->get()->result();
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}
						$row->description = unset_match_values($row->description,$row->blacklists);
						$response = (object)Instagram_Post((object)$row);
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function follow(){
		ini_set('max_execution_time', 300000);

        $limit_item = divisible_cronjob($category='follow',segment(3));

		if(is_null($limit_item['limit_per_page'])){
			 $limit_item['limit_per_page'] = 30;
			 $limit_item['start_index'] = 0;
		}
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'follow')
	    ->where('time_post <= ', NOW)
	    ->limit($limit_item['limit_per_page'],$limit_item['start_index'])
	    ->get()->result();
	    var_dump($result);
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}

						$row->description = unset_match_values($row->description,$row->blacklists);

						$response = (object)Instagram_Post((object)$row);

						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

  	public function like_follow(){
		ini_set('max_execution_time', 300000);

        $limit_item = divisible_cronjob($category='like_follow',segment(3));
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'like_follow')
	    ->where('time_post <= ', NOW)
	    ->limit($limit_item['limit_per_page'],$limit_item['start_index'])
	    ->get()->result();

		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}
						$row->description = unset_match_values($row->description,$row->blacklists);
						$response = (object)Instagram_Post((object)$row);
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function unfollow(){
		ini_set('max_execution_time', 300000);
		$limit_item = divisible_cronjob($category='unfollow',segment(3));
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'unfollow')
	    ->where('time_post <= ', NOW)
	    ->get()->result();
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}
						$row->description = unset_match_values($row->description,$row->blacklists);
						$response = (object)Instagram_Post((object)$row);
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);
							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function followback(){
		ini_set('max_execution_time', 300000);
        $limit_item = divisible_cronjob($category='followback',segment(3));
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'followback')
	    ->where('time_post <= ', NOW)
	    ->limit($limit_item['limit_per_page'],$limit_item['start_index'])
	    ->get()->result();
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}
						$row->description = unset_match_values($row->description,$row->blacklists);
						$response = (object)Instagram_Post((object)$row);
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function repost(){
		ini_set('max_execution_time', 300000);

	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'repost')
	    ->where('time_post <= ', NOW)
	    ->get()->result();
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}

						$row->description = unset_match_values($row->description,$row->blacklists);

						$response = (object)Instagram_Post((object)$row);
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function deletemedia(){
		ini_set('max_execution_time', 300000);

	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status', 5)
	    ->where('category', 'deletemedia')
	    ->where('time_post <= ', NOW)
	    ->get()->result();
		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "id = '".$row->account_id."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
					if(!empty($user)){
						$row->password = $account->password;
						$row->username = $account->username;
						$row->fid = $account->fid;
						$row->timezone = $user->timezone;

						//Add Proxy
						$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
						if(!empty($proxy_item)){
							$row->proxy = $proxy_item->proxy;
						}else{
							$row->proxy = "";
						}

						$response = (object)Instagram_Post((object)$row);
						$arr_update = array();
						if(isset($response->st) && $response->st == "success"){
							$this->db->insert(
								INSTAGRAM_HISTORY,
								array(
									"uid" => $row->uid,
									"account_id" => $row->account_id,
									"type" => $row->type,
									"pk" => $response->code,
									"data" => $response->data,
									"created" => NOW
								)
							);

							$arr_update = array(
								'status' => 5,
								'result' => $response->code,
								'message_error' => $response->txt
							);
						}

						if($repeat == 1 && $time_post_day <= $repeat_end){
							$arr_update['status']    = 5;
							$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}
					}else{
						$this->db->delete(INSTAGRAM_SCHEDULES, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_ACTIVITY, "uid = '".$row->uid."'");
						$this->db->delete(INSTAGRAM_HISTORY, "uid = '".$row->uid."'");
						$this->db->delete(SAVE, "uid = '".$row->uid."'");
						$this->db->delete(CATEGORIES, "uid = '".$row->uid."'");
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}

	public function message(){
		$spintax = new Spintax();
		ini_set('max_execution_time', 300000);
	 	$result = $this->db
	    ->select('*')
	    ->from(INSTAGRAM_SCHEDULES)
	    ->where('status != ', 2)
	    ->where('status != ', 3)
	    ->where('status != ', 4)
	    ->where('category', 'message')
	    ->where('time_post <= ', NOW)
	    ->get()->result();

		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$row->url         = $spintax->process($row->url);
				$row->message     = $spintax->process($row->message);
				$row->title       = $spintax->process($row->title);
				$row->description = $spintax->process($row->description);
				$row->image       = $spintax->process($row->image);
				$row->caption     = $spintax->process($row->caption);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "username = '".$row->account_name."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$row->password = $account->password;
					$row->username = $account->username;
					$row->fid = $account->fid;

					//Add Proxy
					$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
					if(!empty($proxy_item)){
						$row->proxy = $proxy_item->proxy;
					}else{
						$row->proxy = "";
					}
					$response = (object)Instagram_Post((object)$row);
					$arr_update = array(
						'status' => ($response->st == "success")?3:4,
						'result' => (isset($response->id) && $response->id != "")?$response->id:"",
						'message_error' => (isset($response->txt) && $response->txt != "")?$response->txt:""
					);

					if($repeat == 1 && $time_post_day <= $repeat_end){
						$arr_update['status']    = 5;
						$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

						$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
						if(!empty($user)){
							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}else{
							$arr_update['time_post_show'] = date("Y-m-d H:i:s", $time_post);
						}
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}else{
					$arr_update = array(
						'status' => 4,
						'message_error' => l('Instagram account not exist')
					);
					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}	



	public function paypal(){

		$spintax = new Spintax();
		ini_set('max_execution_time', 300000);
	 	$result = $this->db
	    ->select('*')
	    ->from("ipn")
	    ->where('payment_status=', "completed")
     //  	->where('status !=', 3)
     //  	->where('status !=', 4)
	    // ->where('category', 'post')
	    // ->where('time_post <= ', NOW)
	    ->get()->result();

	    pr($result,1);





		if(!empty($result)){
			foreach ($result as $key => $row) {
				$delete       = $row->delete_post;
				$repeat       = $row->repeat_post;
				$repeat_time  = $row->repeat_time;
				$repeat_end   = $row->repeat_end;
				$time_post    = $row->time_post;
				$deplay       = $row->deplay;

				$time_post          = strtotime(NOW) + $repeat_time;
				$time_post_only_day = date("Y-m-d", $time_post);
				$time_post_day      = strtotime($time_post_only_day);
				$repeat_end         = strtotime($repeat_end);

				$row->url         = $spintax->process($row->url);
				$row->message     = $spintax->process($row->message);
				$row->title       = $spintax->process($row->title);
				$row->description = $spintax->process($row->description);
				$row->image       = $spintax->process($row->image);
				$row->caption     = $spintax->process($row->caption);

				$account = $this->model->get("*", INSTAGRAM_ACCOUNTS, "username = '".$row->account_name."' AND uid = '".$row->uid."' AND checkpoint = '0'");
				if(!empty($account)){
					$row->password = $account->password;
					$row->username = $account->username;
					$row->fid = $account->fid;

					//Add Proxy
					$proxy_item = $this->model->get("*", PROXY, "id = '".$account->proxy."'");
					if(!empty($proxy_item)){
						$row->proxy = $proxy_item->proxy;
					}else{
						$row->proxy = "";
					}

					$response = (object)Instagram_Post((object)$row);
					$arr_update = array(
						'status' => ($response->st == "success")?3:4,
						'result' => (isset($response->id) && $response->id != "")?$response->id:"",
						'message_error' => (isset($response->txt) && $response->txt != "")?$response->txt:""
					);

					if($repeat == 1 && $time_post_day <= $repeat_end){
						$arr_update['status']    = 5;
						$arr_update['time_post'] = date("Y-m-d H:i:s", $time_post);

						$user = $this->model->get("*", USER_MANAGEMENT, "id = '".$row->uid."'");
						if(!empty($user)){
							$date = new DateTime(date("Y-m-d H:i:s", $time_post), new DateTimeZone(TIMEZONE_SYSTEM));
							$date->setTimezone(new DateTimeZone($user->timezone));
							$time_post_show = $date->format('Y-m-d H:i:s');
							$arr_update['time_post_show'] = $time_post_show;
						}else{
							$arr_update['time_post_show'] = date("Y-m-d H:i:s", $time_post);
						}
					}

					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}else{
					$arr_update = array(
						'status' => 4,
						'message_error' => l('Instagram account not exist')
					);
					$this->db->update(INSTAGRAM_SCHEDULES ,$arr_update , "id = {$row->id}");
				}
			}
		}
		echo l('Successfully');
	}
}
