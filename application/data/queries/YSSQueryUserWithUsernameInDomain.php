<?php
class YSSQueryUserWithUsernameInDomain extends AMQuery
{
	protected function initialize()
	{
		$username = $this->dbh->real_escape_string($this->options['username']);
		$domain = $this->dbh->real_escape_string($this->options['domain']);
		
		$this->sql = <<<SQL
		SELECT id, level, domain, username, email, firstname, lastname, password, active, `timestamp` FROM user WHERE username = '$username' AND domain = '$domain';
SQL;
	}
}
?>