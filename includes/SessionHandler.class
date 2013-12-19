<?php

class SessionHandler
// version: 1.4 beta 2
// date: 2008-03-02
{
	var $DB;
	var $db_table;
	var $session_lifetime;


	function SessionHandler($DB, $db_table = "sessions")
	{
		$this->DB = $DB;
		$this->db_table = $db_table;
		$this->SetSessionLifetime(ini_get("session.gc_maxlifetime"));
		$this->GC();
	}


	function SetSessionLifetime($session_lifetime)
	{
		$this->session_lifetime = (int)$session_lifetime;
	}


	function Open()
	{
		return is_resource($this->DB->handle);
	}



	function Close()
	{
		return true;
	}



	function Read($sid)
	{
		$this->DB->Exec("
			SELECT `data`
			FROM `$this->db_table`
			WHERE `sid` = '" . $this->DB->Escape($sid) . "' AND `is_valid`
			LIMIT 1
			", true);

		if ($row = $this->DB->FetchRow())
		{
			$this->DB->FreeRes();
			$this->DB->Exec("
				UPDATE `$this->db_table`
				SET `valid_thru` = (NOW() + INTERVAL '$this->session_lifetime' SECOND)
				WHERE `sid` = '" . $this->DB->Escape($sid) . "' AND `is_valid`
				LIMIT 1
				");
			return $row[0];
		}

		else
		{
			$this->DB->FreeRes();
			return "";
		}
	}



	function Write($sid, $data)
	{
		$this->DB->Exec("
			SELECT `data`
			FROM `$this->db_table`
			WHERE `sid` = '" . $this->DB->Escape($sid) . "' AND `is_valid`
			LIMIT 1
			", true);

		if ($this->DB->NumRows())
		{
			$this->DB->FreeRes();
			return $this->DB->Exec("
				UPDATE `$this->db_table`
				SET `data` = '" . $this->DB->Escape($data) . "', `valid_thru` = (NOW() + INTERVAL '$this->session_lifetime' SECOND)
				WHERE `sid` = '" . $this->DB->Escape($sid) . "' AND `is_valid`
				LIMIT 1
				");
		}

		else
		{
			$this->DB->FreeRes();
			return $this->DB->Exec("
				INSERT INTO `$this->db_table` (`sid`, `is_valid`, `valid_thru`, `data`)
				VALUES ('" . $this->DB->Escape($sid) . "', 1, (NOW() + INTERVAL '$this->session_lifetime' SECOND), '" . $this->DB->Escape($data) . "')
				");
		}
	}


	function Destroy($sid)
	{
		return $this->DB->Exec("
			DELETE FROM `$this->db_table`
			WHERE `sid` = '" . $this->DB->Escape($sid) . "'
			LIMIT 1
			");

/*		return $this->DB->Exec("
			UPDATE `sessions`
			SET `is_valid` = 0
			WHERE `sid` = '" . $this->DB->Escape($sid) . "'
			LIMIT 1
			"); */
	}



	function GC()
	{
		return $this->DB->Exec("
			DELETE FROM `$this->db_table`
			WHERE !`is_valid` OR (`valid_thru` < NOW())
			");
/*		return $this->DB->Exec("
			UPDATE `sessions`
			SET `is_valid` = 0
			WHERE `valid_thru` < NOW()
			");*/
	}



	function GCdummy($max_lifetime)
	{
		return true;
	}
}

?>