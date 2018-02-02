<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class activity_model extends MY_Model {
	public function __construct(){
		parent::__construct();
	}

	public function getAllSchedules(){
		$this->db->select("activity.*, user.avatar, user.checkpoint");
		$this->db->from(INSTAGRAM_ACTIVITY." as activity");
		$this->db->join(INSTAGRAM_ACCOUNTS." as user", 'user.id = activity.account_id');
		$this->db->where("activity.uid = '".session("uid")."'");
		$keyword = clean(get('keyword'));
		if($keyword != ""){
			$this->db->like('account_name', $keyword);
		}

		switch (get('filter')) {
			case 'started':
				$this->db->where(" activity.status = 5  ");
				break;

			case 'stoped':
				$this->db->where(" activity.status = 3  ");
				break;

			case 'stoped':
				$this->db->where(" activity.status = 1 ");
				break;
		}

		switch (get('sort')) {
			case 'username':
				$this->db->order_by("activity.account_name", "asc");
				break;

			case 'time':
				$this->db->order_by("activity.created", "desc");
				break;

			default:
				$this->db->order_by("activity.id", "asc");
				break;
		}

		$query = $this->db->get();
		$result = $query->result();

		if(!empty($result)){
			foreach ($result as $key => $row) {
				$data = array();
				$query = $this->db->query('SELECT type, COUNT(type) AS jobcount FROM `'.INSTAGRAM_HISTORY.'` WHERE account_id = '.(int)$row->account_id.' GROUP BY type ORDER BY jobcount DESC');

				if ($query->num_rows() > 0){
				    foreach ($query->result() as $item){
				        $data[$item->type] = $item->jobcount;
				    }
				}

				$result[$key]->like_count = isset($data['like'])?$data['like']:0;
				$result[$key]->comment_count = isset($data['comment'])?$data['comment']:0;
				$result[$key]->follow_count = isset($data['follow'])?$data['follow']:0;
				$result[$key]->like_follow_count = isset($data['like_follow'])?$data['like_follow']:0;
				$result[$key]->followback_count = isset($data['followback'])?$data['followback']:0;
				$result[$key]->unfollow_count = isset($data['unfollow'])?$data['unfollow']:0;
				$result[$key]->repost_count = isset($data['repost'])?$data['repost']:0;
				$result[$key]->deletemedia_count = isset($data['deletemedia'])?$data['deletemedia']:0;
			}
		}
		return $result;
	}
}
