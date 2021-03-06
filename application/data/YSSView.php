<?php
class YSSView extends YSSCouchObject
{
	public $name;
	public $description;
	public $project;
	public $states;
	
	public $type = "view";
	
	public static function viewWithId($id)
	{
		$object    = null;
		$session   = YSSSession::sharedSession();
		$database  = YSSDatabase::connection(YSSDatabase::kCouchDB, $session->currentUser->domain);
			
		$response = $database->document($id);
		
		if(!isset($response['error']))
		{
			$object = YSSView::hydrateWithArray($response);
		}
		
		return $object;
	}
	
	public static function taskWithJson($jsonString)
	{
		return YSSView::hydrateWithArray(json_decode($jsonString, true));
	}
	
	public function addState(YSSState $state)
	{
		if(!$this->_rev)
			$this->save();
		
		if(strpos($state->_id, $this->_id) !== 0)
			$state->_id = $this->_id.'/'.$state->_id;
		
		return $state->save();
	}
	
	private static function hydrateWithArray($array)
	{
		$object  = new YSSView();
		foreach($array as $key=>$value)
		{
			$object->{$key} = $value;
		}
		
		return $object;
	}
}
?>