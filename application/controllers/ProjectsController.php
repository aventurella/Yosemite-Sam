<?php
class ProjectsController extends YSSController
{
	protected $requiresAuthorization  = false;
	protected $requiresPermission     = false;
	
	public    $data					  = array();
	
	protected function initialize()
	{ 
		$this->data = $this->get_data();
	}
	
	protected function verifyPermission()
	{
		return ($this->session->currentUser->level & YSSuserLevel::kCreateProjects) > 0;
	}
	
	protected function verifyPermissionFailed() 
	{
		header("Location:/dashboard");
	}

	private function get_data()
	{
		return json_decode(file_get_contents("http://yss.com/api/projects"));
	}
}
?>