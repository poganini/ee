<?php

class Auth
// version: 4.0.3
// date: 2012-08-09
{
	var $db_prefix;

	var $sid;
	var $ip;
	var $ua;

	var $attempts_to_block;
	var $session_handler_varname;
	var $valid;
	var $logged_in;
	var $user_id;
	var $username;
	var $user_displayed_name;
	var $usergroup_id;
	//var $usergroup_name;
	var $usergroup_comment;
	var $usergroup_session_lifetime;
	var $afm_tmp;
	var $co_domains;



	function Auth($db_prefix = "auth_", $session_handler_varname = NULL)
	{
		$this->db_prefix = $db_prefix;
		$this->session_handler_varname = $session_handler_varname;
		$this->SetAttemptsToBlock();
		$this->SetValid();
		
		ini_set('session.cookie_domain', '.'.APPLIED_DOMAIN);
		session_start();
		$this->sid = session_id();
		
		$this->ip = $_SERVER["REMOTE_ADDR"];
		$this->ua = htmlspecialchars(getenv("HTTP_USER_AGENT"));
		$this->afm_tmp = $_SERVER['DOCUMENT_ROOT'].'/scripts/tiny_mce/plugins/ajaxfilemanager/afm.tmp';
		$this->CheckSessions();
	}

	

	function SetAttemptsToBlock($attempts_to_block = NULL)
	{
		$this->attempts_to_block = (int)CF::Seek4NaturalNumeric($attempts_to_block, 10);
	}



	function SetValid($username_min_len = NULL, $username_max_len = NULL, $username_valid_chars = NULL, $password_min_len = NULL, $password_max_len = NULL, $password_valid_chars = NULL)
	{
		$this->valid = array(
			"username" => array(
				"min_len" => CF::Seek4NaturalNum($username_min_len, 3),
				"max_len" => CF::Seek4NaturalNum($username_max_len, 15),
				"valid_chars" => CF::Seek4NonEmptyStr($username_valid_chars, "a-zA-Z0-9_-")
				),

			"password" => array(
				"min_len" => CF::Seek4NaturalNum($password_min_len, 6),
				"max_len" => CF::Seek4NaturalNum($password_max_len, 50),
				"valid_chars" => CF::Seek4NonEmptyStr($password_valid_chars, true)
				)
			);
	}



	function CheckSessions()
	{
		$this->logged_in = false;

		if (array_key_exists("user_id", $_SESSION)
			&& array_key_exists("user_displayed_name", $_SESSION)
			&& array_key_exists("usergroup_id", $_SESSION)
			//&& array_key_exists("usergroup_name", $_SESSION)
			&& array_key_exists("usergroup_comment", $_SESSION)
			&& array_key_exists("usergroup_session_lifetime", $_SESSION))
		{
			$this->user_id = $_SESSION["user_id"];
			$this->username = $_SESSION["username"];
			$this->user_displayed_name = $_SESSION["user_displayed_name"];
			$this->usergroup_id = $_SESSION["usergroup_id"];
			//$this->usergroup_name = $_SESSION["usergroup_name"];
			$this->usergroup_comment = $_SESSION["usergroup_comment"];
			$this->usergroup_session_lifetime = $_SESSION["usergroup_session_lifetime"];

			$this->SetSessionLifetime();
			if ($_SESSION["user_id"])
			{
				$this->logged_in = true;
			
			}
		}

		else
		{
			$this->user_id = 0;
			$this->username = "";
			$this->user_displayed_name = "";
			$this->usergroup_id = 0;
			//$this->usergroup_name = "";
			$this->usergroup_comment = "";
			$this->usergroup_session_lifetime = 0;
			
		}
		$this->co_domains = explode(';', SATELLITE_DOMAINS);
		if (in_array(APPLIED_DOMAIN, $this->co_domains))
			$this->co_domains = array();
		setcookie('cache', ($this->logged_in === true ? 1 : 0), 0, '/');
		
		if (!isset($_COOKIE['PHPSESSID'])) {
			setcookie('PHPSESSID', $this->sid, 0, '/');
		
		}
			
	}



	function CheckIP($ip)
	{
		global $DB;
		$ip = substr($ip, 0, 255);

		$DB->SetTable($this->db_prefix . "ips");
		$DB->AddField("is_blocked");
		$DB->AddCondFS("ip", "LIKE", $ip);
		$DB->Select(1);

		if ($DB->NumRows())
		{
			list($is_blocked) = $DB->FetchRow();
			$DB->FreeRes();
			return !$is_blocked;
		}

		else
		{
			$DB->FreeRes();
			$DB->SetTable($this->db_prefix . "ips");
			$DB->QuickInsert(array("ip" => $ip));
			return true;
		}
	}



	function AggravateIP($ip, $time = NULL, $username = NULL, $ua = NULL)
	{
		global $DB;
		$ip = substr($ip, 0, 255);

		$DB->SetTable($this->db_prefix . "ips");
		$DB->AddFields(array("is_blocked", "num_attempts"));
		$DB->AddCondFS("ip", "LIKE", $ip);
		$DB->Select(1);
		$row = $DB->FetchObject();
		$DB->FreeRes();

		$DB->SetTable($this->db_prefix . "ips");

		if (is_string($time))
		{
			$DB->AddValue("last_attempt_time", $time);
		}

		elseif ($time === true)
		{
			$DB->AddValue("last_attempt_time", "NOW()", "X");
		}

		if (!is_null($username))
		{
			$DB->AddValue("last_attempt_username", substr($username, 0, 255));
		}

		if (!is_null($ua))
		{
			$DB->AddValue("last_attempt_ua", substr($ua, 0, 255));
		}

		if ((++$row->num_attempts >= $this->attempts_to_block) && !$row->is_blocked)
		{
			$this->BlockIP($ip);
		}

		$DB->AddCondFS("ip", "LIKE", $ip);
		return $DB->QuickUpdate(array("num_attempts" => $row->num_attempts), 1);
	}



	function BlockIP($ip)
	{
		global $DB;
		$DB->SetTable($this->db_prefix . "ips");
		$DB->AddCondFS("ip", "LIKE", $ip);
		return $DB->QuickUpdate(array("is_blocked" => 1), 1);
	}



	function ReliefIP($ip, $time = NULL, $username = NULL, $ua = NULL)
	{
		global $DB;
		$DB->SetTable($this->db_prefix . "ips");

		if (is_string($time))
		{
			$DB->AddValue("last_attempt_time", $time);
		}

		elseif ($time === true)
		{
			$DB->AddValue("last_attempt_time", "NOW()", "X");
		}

		if (!is_null($username))
		{
			$DB->AddValue("last_attempt_username", $username);
		}

		if (!is_null($ua))
		{
			$DB->AddValue("last_attempt_ua", $ua);
		}

		$DB->AddCondFS("ip", "LIKE", $ip);
		return $DB->QuickUpdate(array("is_blocked" => 0, "num_attempts" => 0), 1);
	}



	function UserExists($username)
	{
		global $DB;
		$DB->SetTable($this->db_prefix . "users");
		$DB->AddExp("COUNT(*)");
		$DB->AddCondFS("username", "LIKE", $username);
		$DB->Select(1);
		list($num) = $DB->FetchRow();
		$DB->FreeRes();
		return (bool)$num;
	}



	function CheckPassword($username, $password, $ip = NULL, $time = NULL, $ua = NULL)
	{
		if ($this->CheckIP($ip))
		{
			global $DB;
			$DB->SetTable($this->db_prefix . "users");
			$DB->AddField("id");
			$DB->AddCondFP("is_active");
			$DB->AddCondFS("username", "LIKE", $username);
			$DB->AddCondFS("password", "LIKE", md5($password));
			$DB->Select(1);

			if ($DB->NumRows())
			{
				$row = $DB->FetchObject();
				$DB->FreeRes();
				$this->ReliefIP($ip, $time, $username, $ua);
				return (int)$row->id;
			}

			$DB->FreeRes();
		}

		else
		{
			$this->AggravateIP($ip, $time, $username, $ua);
			return false;
		}
	}



	function CheckPasswordByUserID($password, $user_id = NULL)
	{
		global $DB;
		$user_id = CF::Seek4NaturalNumeric($user_id, $this->user_id);
		$DB->SetTable($this->db_prefix . "users");
		$DB->AddExp("COUNT(*)");
		$DB->AddCondFP("is_active");
		$DB->AddCondFX("id", "=", $user_id);
		$DB->AddCondFS("password", "LIKE", md5($password));
		$DB->Select(1);
		list($num) = $DB->FetchRow();
		$DB->FreeRes();
		return (bool)$num;
	}



	function GetUserData($fields = NULL, $user_id = NULL)
	{
		global $DB;

		if (!is_array($fields))
		{
			$fields = array();
		}

		if ($flag = !CF::IsNaturalNumeric($user_id))
		{
			$user_id = $this->user_id;
		}

		$DB->SetTable($this->db_prefix . "users");

		if ($flag)
		{
			$DB->AddJoin($this->db_prefix . "usergroups.id", $this->db_prefix . "users.usergroup_id");
			//$DB->AddField($this->db_prefix . "usergroups.name", "usergroup_name");
			$DB->AddField($this->db_prefix . "usergroups.comment", "usergroup_comment");
			$DB->AddField($this->db_prefix . "usergroups.session_lifetime", "usergroup_session_lifetime");
		}

		$DB->AddFields(array_merge($fields, $flag ? array("username", "displayed_name", "usergroup_id") : array()));
		$DB->AddCondFS("id", "=", $user_id);
		$DB->Select(1);

		if ($DB->NumRows())
		{
			$row = $DB->FetchAssoc();
			$DB->FreeRes();

			if ($flag)
			{
				$this->username = $row["username"];

				if (!in_array("username", $fields))
				{
					unset($row["username"]);
				}


				$this->user_displayed_name = $row["displayed_name"];

				if (!in_array("displayed_name", $fields))
				{
					unset($row["displayed_name"]);
				}


				$this->usergroup_id = (int)$row["usergroup_id"];

				if (!in_array("usergroup_id", $fields))
				{
					unset($row["usergroup_id"]);
				}


				/*$this->usergroup_name = $row["usergroup_name"];

				if (!in_array("usergroup_name", $fields))
				{
					unset($row["usergroup_name"]);
				}*/


				$this->usergroup_comment = $row["usergroup_comment"];

				if (!in_array("usergroup_comment", $fields))
				{
					unset($row["usergroup_comment"]);
				}


				$this->usergroup_session_lifetime = (int)$row["usergroup_session_lifetime"];

				if (!in_array("usergroup_session_lifetime", $fields))
				{
					unset($row["usergroup_session_lifetime"]);
				}
			}

			return $row;
		}

		else
		{
			$DB->FreeRes();
			return false;
		}
	}



	function SetSessionLifetime()
	{
		if (CF::IsNonEmptyStr($this->session_handler_varname) && $this->usergroup_session_lifetime)
		{
			$GLOBALS[$this->session_handler_varname]->SetSessionLifetime($this->usergroup_session_lifetime);
		}
	}



	function Login($username, $password)
	{
		if (/*$username && $password && */($this->user_id = $this->CheckPassword($username, $password, $this->ip, true, $this->ua)))
		{
			global $DB, $Engine;
			$this->GetUserData();

			$DB->SetTable($this->db_prefix . "users");
			$DB->AddValue("prev_login_time", "last_login_time", "F");
			$DB->AddValue("prev_ip", "last_ip", "F");
			$DB->AddValue("prev_ua", "last_ua", "F");
			$DB->AddValue("last_login_time", "NOW()", "X");
			$DB->AddValues(array("last_ip" => $this->ip, "last_ua" => $this->ua));
			$DB->AddCondFX("id", "=", $this->user_id);
			$DB->Update(1);

			$_SESSION["user_id"] = $this->user_id;
			$_SESSION["username"] = $this->username;
			$_SESSION["user_displayed_name"] = $this->user_displayed_name;
			$_SESSION["usergroup_id"] = $this->usergroup_id;
			//$_SESSION["usergroup_name"] = $this->usergroup_name;
			$_SESSION["usergroup_comment"] = $this->usergroup_comment;
			$this->SetSessionLifetime();
			$_SESSION["usergroup_session_lifetime"] = $this->usergroup_session_lifetime;

			$this->logged_in = true;
			
			$Engine->LogAction(0, "auth", $this->user_id, "login");
			return true;
		}

		else
		{
			$this->logged_in = false;
			return false;
		}
	}



	function Logout()
	{
		global $Engine;
	
		if (!$this->session_handler_varname)
		{
			session_unset();
		}
		$this->ClearAFMRequestData();
		if (session_destroy())
		{
			$this->logged_in = false;
			$this->user_id = 0;
			$this->username = "";
			$this->usergroup_id = 0;
			return true;
		}

		else
		{
			return false;
		}
	}



	function ValidateUsername($input)
	{
		return $this->Validate(
			$input,
			$this->valid["username"]["valid_chars"],
			$this->valid["username"]["min_len"],
			$this->valid["username"]["max_len"]
			);
	}



	function ValidatePassword($input)
	{
		return $this->Validate(
			$input,
			$this->valid["password"]["valid_chars"],
			$this->valid["password"]["min_len"],
			$this->valid["password"]["max_len"]
			);
	}



	function CreateUser($username, $password, $other_values = array())
	{
		global $DB;
		$DB->SetTable($this->db_prefix . "users");
		$DB->AddValues(array("username" => $username, "password" => md5($password)));
		$DB->AddValues($other_values);
		$DB->AddValue("create_time", "NOW()", "X");
		$DB->AddValue("create_user_id", $this->user_id);
		return $DB->Insert();
	}



	function DeleteUser($username, $password)
	{
		if ($result = $this->CheckPassword($username, $password))
		{
			global $DB;
			$DB->SetTable($this->db_prefix . "users");
			$DB->AddCondFX("id", "=", $result["id"]);
			return $DB->Delete(1);
		}

		else
		{
			return false;
		}
	}
	
	
	function ClearAFMRequestData()
	{
		$content = file_exists($this->afm_tmp) ? file_get_contents($this->afm_tmp) : '';
			
		if ($content && strpos($content, '%'.strval($this->user_id).'=') !== false) 
		{
			$content = preg_replace('/%'.$this->user_id.'=\S+%/', '', $content);
			$f = fopen($this->afm_tmp, 'w+');
			fwrite($f, $content);
			fclose($f);
		}
	}
	
	function GetAFMRequestData()
	{
		$content = file_exists($this->afm_tmp) ? file_get_contents($this->afm_tmp) : '';
		
			
		if ($content && strpos($content, '%'.strval($this->user_id).'=') !== false) 
		{
			preg_match('/%'.$this->user_id.'=(.*?)%/', $content, $matches);
			$hash = isset($matches[1]) ? $matches[1] : "";
		} else 
		{
			$hash = md5($this->user_id.rand(0,10000));
			$content.= '%'.$this->user_id.'='.$hash.'%';
			$f = fopen($this->afm_tmp, 'w+');
			fwrite($f, $content);
			fclose($f);
		}
		
				
		return $this->logged_in ? "?afm=".$hash : "";
	}

}




class AuthDummy
// version: 1.2
// date: 2010-02-01
{
	var $logged_in;
	var $user_id;
	var $username;
	var $user_displayed_name;
	var $usergroup_id;
	//var $usergroup_name;
	var $usergroup_comment;
	var $usergroup_session_lifetime;

	function AuthDummy()
	{
		$this->logged_in = false;
		$this->user_id = 0;
		$this->user_displayed_name = "";
		$this->usergroup_id = 0;
		//$this->usergroup_name = "";
		$this->usergroup_comment = "";
		$this->usergroup_session_lifetime = 0;
	}
}

?>