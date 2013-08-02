<? 
class wotapi{
	function search_player($nick, $limit=10, $region='ru'){
		$search_str = "http://api.worldoftanks.$region/community/accounts/api/1.1/?source_token=WG-WoT_Assistant-1.3.2&search=$nick&offset=0&limit=$limit";
		$search_data = json_decode(file_get_contents ($search_str));
		return $search_data->data;
	}
	
	function player_info($id, $region='RU'){
		$search_str = "http://api.worldoftanks.$region/community/accounts/$id/api/1.9/?source_token=WG-WoT_Assistant-1.3.2";
		$search_data = json_decode(file_get_contents ($search_str));
		return $search_data->data;
	
	}
	
}



?>