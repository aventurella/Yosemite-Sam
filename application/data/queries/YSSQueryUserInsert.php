<?php
class YSSQueryUserInsert extends AMQuery
{
	protected function initialize()
	{
		//$date      = new DateTime("now", new DateTimeZone("UTC"));
		//$timestamp = $date->format(DateTime::ISO8601);
		$timestamp = YSSApplication::timestamp_now();
		
		$level     = $this->dbh->real_escape_string($this->options['level']);
		$domain    = $this->dbh->real_escape_string($this->options['domain']);
		$username  = $this->dbh->real_escape_string($this->options['username']);
		$email     = $this->dbh->real_escape_string($this->options['email']);
		$firstname = $this->dbh->real_escape_string($this->options['firstname']);
		$lastname  = $this->dbh->real_escape_string($this->options['lastname']);
		$password  = $this->dbh->real_escape_string($this->options['password']);
		$active    = (int) $this->dbh->real_escape_string($this->options['active']);
		
		$this->sql = <<<SQL
		INSERT INTO user (level, domain, username, email, firstname, lastname, password, active, `timestamp`) VALUES ('$level', '$domain', '$username', '$email', '$firstname', '$lastname', '$password', '$active', '$timestamp');
SQL;
	}
}

?>