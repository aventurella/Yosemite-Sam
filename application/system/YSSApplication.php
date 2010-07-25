<?php
class YSSApplication
{
	private static $application;
	
	public static function sharedApplication()
	{
		return YSSApplication::$application;
	}
	
	public static function basePath()
	{
		static $path;
		
		if($path == null)
		{
			$path = realpath(__DIR__.'/../../');
		}
		
		return $path;
	}
	
	public static function current_language()
	{
		return 'en-US';
	}
	
	public static function timestamp_now()
	{
		$date = new DateTime("now", new DateTimeZone("UTC"));
		return $date->format(DateTime::ISO8601);
	}
	
	public function startSession()
	{
		session_set_cookie_params(0, '/', '.'.YSSConfiguration::applicationDomain(), false);
		if (session_id() == "") session_start();
		
		
		$configuration = YSSConfiguration::standardConfiguration();
		if($configuration['currentUser'])
		{
			$session = YSSSession::sharedSession();
			$session->currentUser = $configuration['currentUser'];
		}
	}
	
	public function __construct()
	{
		new YSSConfiguration('config.ini');
		YSSApplication::$application = $this;
	}
	
	
}
?>