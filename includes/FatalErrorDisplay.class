<?php

class FatalErrorDisplay
// ”ниверсальный модуль отображени€ ошибок
// version: 1.0
// date: 2008-06-29
//   params: 0:[!]config_file, 1:debug_level
{
	var $subject;
	var $error_messages;
	var $display_message;



	function FatalErrorDisplay($subject, $error_messages, $display_message = true)
	{
		$this->subject = $subject;
		$this->error_messages = $error_messages;
		$this->display_message = (bool)$display_message;
	}



	function FatalError($error_key)
	{
		$message = "";

		if ($this->display_message)
		{
			$message .= $this->subject;
			$message .= " fatal error #" . $error_key;

			if (isset($errors[$error_key]))
			{
				$args = func_get_args();
				array_shift($args);

				if ($result = @vsprintf($this->error_messages[$error_key], $args))
				{
					$message .= ":<br />\n&nbsp;&nbsp;" . vsprintf($this->error_messages[$error_key], $args) . ".";
				}
			}
		}

		die($message);
	}
}

?>