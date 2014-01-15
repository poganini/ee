<?php

class MySQLhandle
// version: 4.1.3
// date: 2013-11-25
{
	var $handle;
	var $debug_level;

	var $query;
	var $res;

	var $tables;
	var $multitable_mode;
	var $fields;
	var $values;
	var $joins;
	var $alts;
	var $conds;
	var $groupings;
	var $orders;
	var $custom_strings;

	var $id_field;
	var $pos_field;
	var $last_posed_id;
	var $last_posed_pos;
	var $queries_num;
	

	function MySQLhandle($host, $user, $pass, $base, $port = NULL, $debug_level = 0)
	{
		$this->SetDebugLevel($debug_level);
		$this->Connect($host, $user, $pass, $port);
		$this->SelectDB($base);
		$this->Init();
	}




	// Setting debug level. Handling connection & selecting DB

	function SetDebugLevel($debug_level) // 0 - none; 1 - error msg; 2 - error msg & failed queries; 3 - error msg & all queries
	{
		$this->debug_level = $debug_level;
	}



	function Connect($host, $user, $pass, $port = NULL)
	{
		$this->handle = @mysql_connect($host . (CF::IsNaturalNumeric($port) ? ":$port" : ""), $user, $pass, true);
		if(!$this->handle) throw new Exception($this->debug_level ? "MySQLhandle error #%s. Can't connect to database server. ".$user : "", 1001);
		return $this->handle;
	}
	
	/* function Connected() 
	{
		return $this->handle ? true : false;
	} */

	function Disconnect()
	{
		return mysql_close($this->handle);
	}



	function Close()
	{
		return $this->Disconnect();
	}



	function SelectDB($name)
	{
		$result = mysql_select_db($name, $this->handle); 
		if(!$result)
			throw new Exception($this->debug_level ? "MySQLhandle error #%s. Can't select database." : "", 1002);
	}




	// Setting up & initializing

	function Init($leave_res = false)
	{
		$this->query = false;

		if (!$leave_res)
		{
			$this->ResetRes();
			$this->queries_num = 0;
		}

		$this->tables = array();
		$this->multitable_mode = false;
		$this->ResetFields();
		$this->values = array();
		$this->joins = array();
		$this->ResetAlts();
		$this->conds = array();
		$this->orders = array();
		$this->custom_strings = array(
			"t" => array(),
			"f" => array(),
			"v" => array(),
			"j" => array(),
			"c" => array(),
			"g" => array(),
			"o" => array(),
			"l" => array(),
			);
		$this->SetPoser();
	}


	function ResetRes()
	{
		$this->res = false;
		$this->groupings = null;/**/
	}



	function ResetFields()
	{
		$this->fields = array();
	}



	function ResetAlts()
	{
		$this->alts = array();
		$this->groupings = null;/**/
		
	}



	function SetPoser($pos_field = NULL, $id_field = NULL)
	{
		$this->id_field = CF::Seek4NonEmptyStr($id_field, "id");
		$this->pos_field = CF::Seek4NonEmptyStr($pos_field, "pos");
		$this->last_posed_id = NULL;
		$this->last_posed_pos = NULL;
	}




	// Processing data

	function Escape($value, $quote_char = NULL)
	{
		if (get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}

		$value = mysql_real_escape_string($value, $this->handle);
		return $quote_char . $value . $quote_char;
	}



	function SmartTime($year, $month, $day, $hours, $minutes, $seconds, $now_interval_offset = NULL)
	{
		if (!CF::IsNaturalNumeric($year) || !CF::IsNaturalNumeric($month) || !CF::IsNaturalNumeric($day))
		{
			return array("NOW()" . ($now_interval_offset ? " + INTERVAL $now_interval_offset" : ""), "X");
		}

		else
		{
			if ($year < 2000)
			{
				$year += 2000;
			}

			elseif ($year < 1900)
			{
				$year += 1900;
			}

			$date = sprintf("%04d-%02d-%02d ", $year, $month, $day);

			if (!CF::IsNaturalOrZeroNumeric($hours) || ($hours > 23))
			{
				return array($date, "S");
			}

			elseif (!CF::IsNaturalOrZeroNumeric($minutes) || ($minutes > 59))
			{
				return array($date . sprintf("%02d", $hours) . ":00:00", "S");
			}

			elseif (!CF::IsNaturalOrZeroNumeric($seconds) || ($seconds > 59))
			{
				return array($date . sprintf("%02d:%02d", $hours, $minutes) . ":00", "S");
			}

			else
			{
				//return array($date . sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds), "S");
				return array($date . sprintf("%02d:%02d", $hours, $minutes) . ":00", "S");
			}
		}
	}


	function FieldFullName($input, $return_parts = false)
	{
		if (CF::IsNonEmptyStr($input))
		{
			$data = array_reverse(explode(".", $input));

			if (count($data) == 1)
			{
				$data[] = $this->tables[0]["name"];
			}
			
			$parts = explode(" ", $data[1]);
			if(count($parts) > 1)
			{
				$data[1] = $parts[1];
				$data[] = $parts[0];//Alias
			}

			$output = $this->Escape($data[0], "`");

			if ($this->multitable_mode)
			{
				$output = $this->Escape($data[1], "`") . ".$output";
			}
			
			$result = array($output, $data[0], $data[1]);
			if(isset($data[2])) $result[] = $data[2];

			return $return_parts ? $result : $output;
		}

		return false;
	}



	function EscapeExp($exp, $do_escape = false)
	{
		return $do_escape ? $this->Escape($exp) : $exp;
	}



	function Error()
	{
		return mysql_errno($this->handle) ? (mysql_errno($this->handle) . ": " . mysql_error($this->handle)) : "";
	}




	// Adding tables

	function SetTable($name, $alias = NULL, $pos_field = NULL, $id_field = NULL)
	{
		$this->tables = array();
		$this->AddTable($name, $alias);
		$this->SetPoser($pos_field, $id_field);
	}



	function AddTable($table, $alias = NULL)
	{
		$this->tables[] = array(
			"name" => $table,
			"alias" => $alias,
			);

		if (count($this->tables) > 1)
		{
			$this->multitable_mode = true;
		}
	}




	// Adding fields and values

	function AddField($field, $alias = NULL, $exp = NULL)
	{
		$this->fields[] = array(
			"field" => $field,
			"alias" => $alias,
			"exp" => $exp
			);
	}



	function AddExp($exp, $alias = NULL, $field = NULL)
	{
		$this->AddField($field, $alias, $exp);
	}



	function AddFields($fields)
	{
		foreach ($fields as $field)
		{
			$this->AddField($field);
		}
	}



	function AddValue($field, $val, $type = "S")
	{
		$this->values[] = array(
			"field" => $field,
			"val" => $val,
			"type" => $type
			);
	}



	function AddValues($values)
	{
		foreach ($values as $field => $value)
		{
			$this->AddValue($field, $value);
		}
	}




	// Adding joins

	function AddJoin($joint_field, $field)
	{
		$this->joins[] = array(
			"joint_field" => $joint_field,
			"field" => $field,
			);

		$this->multitable_mode = true;
	}




	// Adding alternatives & conditions

	function AddAlt($data, $type)
	// Add an alternative (... OR ...) to "WHERE" part
	{
		$this->alts[] = array(
			"data" =>	$data,
			"type" =>	$type
			);
	}



	function AddAltFF($left_field, $op, $right_field)
	// Add an alternative comparing a field with another field: `field1` = `field2`
	{
		$this->AddAlt(array(
			"left" => $left_field,
			"op" => $op,
			"right" => $right_field
			), "FF");
	}



	function AddAltFS($field, $op, $str)
	// Add an alternative comparing a field with a string: `field1` LIKE 'string'
	{
		$this->AddAlt(array(
			"left" => $field,
			"op" => $op,
			"right" => $str
			), "FS");
	}



	function AddAltFX($field, $op, $exp, $do_escape_exp = true)
	// Add an alternative comparing a field with an expression: `field1` = (`field2` / 2)
	{
		$this->AddAlt(array(
			"left" => $field,
			"op" => $op,
			"right" => $exp,
			"do_escape_right" => $do_escape_exp
			), "FX");
	}



	function AddAltFP($field)
	// Add an alternative representing field as a condition: `field1`
	{
		$this->AddAlt(array(
			"left" => $field
			), "FP");
	}



	function AddAltFN($field)
	// Add an alternative representing "not field" as a condition: !`field1`
	{
		$this->AddAlt(array(
			"left" => $field
			), "FN");
	}



	function AddAltFO($field, $op)
	// Add an alternative representing a field with an operator: `field1` IS NOT NULL
	{
		$this->AddAlt(array(
			"left" => $field,
			"op" => $op
			), "FO");
	}



	function AddAltXF($exp, $op, $field, $do_escape_exp = true)
	// Add an alternative comparing an expression with a field: (`field1` / 2) = `field2`
	{
		$this->AddAlt(array(
			"left" => $exp,
			"do_escape_left" => $do_escape_exp,
			"op" => $op,
			"right" => $field
			), "XF");
	}



	function AddAltXS($exp, $op, $str)
	// Add an alternative comparing an expression with a string: (`field1` / 2) LIKE 'string'
	{
		$this->AddAlt(array(
			"left" => $exp,
			"do_escape_left" => $do_escape_exp,
			"op" => $op,
			"right" => $str
			), "XS");
	}



	function AddAltXX($left_exp, $op, $right_exp, $do_escape_left_exp = true, $do_escape_right_exp = true)
	// Add an alternative comparing an expression with another expression: (`field1` / 2) = (`field2` + 1)
	{
		$this->AddAlt(array(
			"left" => $left_exp,
			"do_escape_left" => $do_escape_left_exp,
			"op" => $op,
			"right" => $right_exp,
			"do_escape_right" => $do_escape_right_exp
			), "XX");
	}



	function AddAltXP($exp, $do_escape = true)
	// Add an alternative representing an expression as a condition: (`field1` + `field2`)
	{
		$this->AddAlt(array(
			"left" => $exp,
			"do_escape_left" => $do_escape
			), "XP");
	}



	function AddAltXN($exp, $do_escape = true)
	// Add an alternative representing "not expression" as a condition: !(`field1` + `field2`)
	{
		$this->AddAlt(array(
			"left" => $exp,
			"do_escape_left" => $do_escape
			), "XN");
	}



	function AddAltXO($exp, $op, $do_escape_left = true)
	// Add an alternative representing an expression with an operator: (`field1` + `field2`) IS NOT NULL
	{
		$this->AddAlt(array(
			"left" => $exp,
			"do_escape_left" => $do_escape,
			"op" => $op
			), "XO");
	}



	function AppendAlts()
	// Form a condition (... AND ...) from collected alternatives, then flush collected alternatives
	{
		if (count($this->alts))
		{
			$this->conds[] = $this->alts;
			$this->ResetAlts();
		}
	}



	function AddCondFF($left_field, $op, $right_field)
	// Add a condition comparing a field with another field: `field1` = `field2`
	{
		$this->AppendAlts();
		$this->AddAltFF($left_field, $op, $right_field);
		$this->AppendAlts();
	}



	function AddCondFS($field, $op, $str)
	// Add a condition comparing a field with a string: `field1` LIKE 'string'
	{
		$this->AppendAlts();
		$this->AddAltFS($field, $op, $str);
		$this->AppendAlts();
	}



	function AddCondFX($field, $op, $exp, $do_escape_exp = true)
	// Add a condition comparing a field with an expression: `field1` = (`field2` / 2)
	{
		$this->AppendAlts();
		$this->AddAltFX($field, $op, $exp, $do_escape_exp);
		$this->AppendAlts();
	}



	function AddCondFP($field)
	// Add a condition representing field as a condition: `field1`
	{
		$this->AppendAlts();
		$this->AddAltFP($field);
		$this->AppendAlts();
	}



	function AddCondFN($field)
	// Add a condition representing "not field" as a condition: !`field1`
	{
		$this->AppendAlts();
		$this->AddAltFN($field);
		$this->AppendAlts();
	}



	function AddCondFO($field, $op)
	// Add a condition representing a field with an operator: `field1` IS NOT NULL
	{
		$this->AppendAlts();
		$this->AddAltFO($field, $op);
		$this->AppendAlts();
	}



	function AddCondXF($exp, $op, $field, $do_escape_exp = true)
	// Add a condition comparing an expression with a field: (`field1` / 2) = `field2`
	{
		$this->AppendAlts();
		$this->AddAltXF($exp, $op, $field, $do_escape_exp);
		$this->AppendAlts();
	}



	function AddCondXS($exp, $op, $str, $do_escape_exp = true)
	// Add a condition comparing an expression with a string: (`field1` / 2) LIKE 'string'
	{
		$this->AppendAlts();
		$this->AddAltXS($exp, $op, $str, $do_escape_exp);
		$this->AppendAlts();
	}



	function AddCondXX($left_exp, $op, $right_exp, $do_escape_left_exp = true, $do_escape_right_exp = true)
	// Add a condition comparing an expression with another expression: (`field1` / 2) = (`field2` + 1)
	{
		$this->AppendAlts();
		$this->AddAltXX($left_exp, $op, $right_exp, $do_escape_left_exp, $do_escape_right_exp);
		$this->AppendAlts();
	}



	function AddCondXP($exp, $do_escape = true)
	// Add a condition representing an expression as a condition: (`field1` + `field2`)
	{
		$this->AppendAlts();
		$this->AddAltXP($exp, $do_escape);
		$this->AppendAlts();
	}



	function AddCondXN($exp, $do_escape = true)
	// Add a condition representing "not expression" as a condition: !(`field1` + `field2`)
	{
		$this->AppendAlts();
		$this->AddAltXN($exp, $do_escape);
		$this->AppendAlts();
	}



	function AddCondXO($exp, $op, $do_escape = true)
	// Add a condition representing an expression with an operator: (`field1` + `field2`) IS NOT NULL
	{
		$this->AppendAlts();
		$this->AddAltXO($exp, $op, $do_escape);
		$this->AppendAlts();
	}




// Adding group

	function AddGrouping($field, $is_desc = false, $exp = NULL, $rollup = false)
	// Add an group (GROUP BY ..., ...)
	// Use first parameter to define a field used for ordering;
	// set second parameter true for DESC order;
	// provide third parameter to order by an expression (instead of field)
	{
		$this->groupings[] = array
		(
			"field" => $field,
			"exp" => $exp,
			"is_desc" => (bool)$is_desc,
			"rollup" => (bool)$rollup,
		);
	}



	function AddExpGrouping($exp, $is_desc = false, $field = NULL, $rollup = false)
	// Add an expression order (a shortcut for AddGroup() function)
	// Use first parameter to define an expression used for ordering;
	// set second parameter true for DESC order;
	// the third param is useless currently, reserved for future improvements
	{
		$this->groupings[] = array
		(
			"field" => $field,
			"exp" => $exp,
			"is_desc" => (bool)$is_desc,
			"rollup" => (bool)$rollup,
		);
	}



	function AddGroupings($fields)
	// Batch adding field group (all ASC)
	// Provide an array of fieldnames as a param
	{
		foreach ($fields as $field)
		{
			$this->AddGrouping($field);
		}
	}



	function AddDescGroupings($fields)
	// Batch adding field group (all DESC)
	// Provide an array of fieldnames as a param
	{
		foreach ($fields as $field)
		{
			$this->AddGrouping($field, true);
		}
	}



// Adding orders

	function AddOrder($field, $is_desc = false, $exp = NULL)
	// Add an order (ORDER BY ..., ...)
	// Use first parameter to define a field used for ordering;
	// set second parameter true for DESC order;
	// provide third parameter to order by an expression (instead of field)
	{
		$this->orders[] = array
		(
			"field" => $field,
			"exp" => $exp,
			"is_desc" => (bool)$is_desc
		);
	}



	function AddExpOrder($exp, $is_desc = false, $field = NULL)
	// Add an expression order (a shortcut for AddOrder() function)
	// Use first parameter to define an expression used for ordering;
	// set second parameter true for DESC order;
	// the third param is useless currently, reserved for future improvements
	{
		$this->orders[] = array
		(
			"field" => $field,
			"exp" => $exp,
			"is_desc" => (bool)$is_desc
		);
	}



	function AddOrders($fields)
	// Batch adding field orders (all ASC)
	// Provide an array of fieldnames as a param
	{
		foreach ($fields as $field)
		{
			$this->AddOrder($field);
		}
	}



	function AddDescOrders($fields)
	// Batch adding field orders (all DESC)
	// Provide an array of fieldnames as a param
	{
		foreach ($fields as $field)
		{
			$this->AddOrder($field, true);
		}
	}



	function AddCustomString($string, $after_part = "l")
	// Add a custom query substring
	{
		$this->custom_strings[$after_part][] = $string;
	}




// Building query


	function FieldsQueryPart($insert_query_mode = false)
	// Fields query part ("SELECT ..., ... FROM `table1`" or "INSERT INTO `table1` (..., ...)")
	// Set first param true for INSERT query mode (as in second example)
	{
		if (count($this->fields))
		// having some fields and so listing them
		{
			$parts = array();

			foreach ($this->fields as $data)
			{
				$field_full_name = $this->FieldFullName($data["field"]);

				if ($insert_query_mode)
				{
					$parts[] = $field_full_name;
				}

				else
				{
					$parts[] = ($data["exp"] ? $this->EscapeExp($data["exp"]) : $field_full_name) . ($data["alias"] ? " AS " . $this->Escape($data["alias"], "'") : "");
				}
			}

			$output = implode(", ", $parts);
			return $insert_query_mode ? " ($output)" : $output;
		}

		elseif ($insert_query_mode)
		// INSERT query mode with no fields
		{
			return "";
		}

		else
		// SELECT query mode with no fields
		{
			return "*";
		}
	}



	function TablesQueryPart($single_table = false)
	// Build tables query part (`table1` AS 't1', `table2`...")
	{
		$parts = array();

		foreach ($this->tables as $data)
		{
			$string = $this->Escape($data["name"], "`") . (CF::IsNonEmptyStr($data["alias"]) ? (" AS " . $this->Escape($data["alias"], "`")) : "");

			if ($single_table)
			{
				return $string;
			}

			$parts[] = $string;
		}

		return implode(", ", $parts);
	}



	function JoinsQueryPart()
	// Build joins query part (LEFT JOIN `cats`.`id` ON `cats`.`id` = `products`.`cat_id`, LEFT JOIN...)
	{
		$output = "";

		foreach ($this->joins as $data)
		{
			$result = $this->FieldFullName($data["joint_field"], true); 
			if(count($result) < 4) $output .= "\nLEFT JOIN `{$result[2]}` ON " . $this->FieldFullName($data["field"]) . " = {$result[0]}";
			else $output .= "\nLEFT JOIN `{$result[3]}` {$result[2]} ON " . $this->FieldFullName($data["field"]) . " = {$result[0]}";
		}

		return $output;
	}



	function CondsQueryPart()
	// Build conditions query part (WHERE ...)
	{
		$this->AppendAlts();

		if (count($this->conds))
		{
			$parts = array();

			foreach ($this->conds as $data)
			{
				$parts2 = array();

				foreach ($data as $data2)
				{
					switch (strtoupper($data2["type"]))
					{
						case "FF":
							$parts2[] = implode(" ", array($this->FieldFullName($data2["data"]["left"]), $this->Escape($data2["data"]["op"]), $this->FieldFullName($data2["data"]["right"])));
							break;

						case "FS":
							$parts2[] = implode(" ", array($this->FieldFullName($data2["data"]["left"]), $this->Escape($data2["data"]["op"]), $this->Escape($data2["data"]["right"], "'")));
							break;

						case "FX":
							$parts2[] = implode(" ", array($this->FieldFullName($data2["data"]["left"]), $this->Escape($data2["data"]["op"]), $this->EscapeExp($data2["data"]["right"], $data2["data"]["do_escape_right"])));
							break;

						case "FP":
							$parts2[] = $this->FieldFullName($data2["data"]["left"]);
							break;

						case "FN":
							$parts2[] = "!" . $this->FieldFullName($data2["data"]["left"]);
							break;

						case "FO":
							$parts2[] = $this->FieldFullName($data2["data"]["left"]) . " " . $this->Escape($data2["data"]["op"]);
							break;

						case "XF":
							$parts2[] = implode(" ", array($this->EscapeExp($data2["data"]["left"], $data2["data"]["do_escape_left"]), $this->Escape($data2["data"]["op"]), $this->FieldFullName($data2["data"]["right"])));
							break;

						case "XS":
							$parts2[] = implode(" ", array($this->EscapeExp($data2["data"]["left"], $data2["data"]["do_escape_left"]), $this->Escape($data2["data"]["op"]), $this->Escape($data2["data"]["right"], "'")));
							break;

						case "XX":
							$parts2[] = implode(" ", array($this->EscapeExp($data2["data"]["left"], $data2["data"]["do_escape_left"]), $this->Escape($data2["data"]["op"]), $this->EscapeExp($data2["data"]["right"], $data2["data"]["do_escape_right"])));
							break;

						case "XP":
							$parts2[] = $this->EscapeExp($data2["data"]["left"], $data2["data"]["do_escape_left"]);
							break;

						case "XN":
							$parts2[] = "!" . $this->EscapeExp($data2["data"]["left"], $data2["data"]["do_escape_left"]);
							break;

						case "XO":
							$parts2[] = $this->EscapeExp($data2["data"]["left"], $data2["data"]["do_escape_left"]) . " " . $this->Escape($data2["data"]["op"]);
							break;
					}
				}

				switch (count($parts2))
				{
					case 0:
						break;

					case 1:
						$parts[] = $parts2[0];
						break;

					default:
						$parts[] = "((" . implode(") OR (", $parts2) . "))";
						break;
				}
			}

			return "\nWHERE " . implode(" AND ", $parts);
		}

		else
		{
			return "";
		}
	}



	function GroupingsQueryPart()
	// Build groupings query part (GROUP BY...[ DESC], ...)
	{
		if (count($this->groupings))
		{
			$parts = array();

			foreach ($this->groupings as $data)
			{
				$field_full_name = $this->FieldFullName($data["field"]);
				$parts[] = (($data["exp"] ? $this->EscapeExp($data["exp"]) : $field_full_name) . ($data["is_desc"] ? " DESC" : "")) . ($data["rollup"] ? " WITH ROLLUP" : "");
			}

			return "\nGROUP BY " . implode(", ", $parts);
		}

		else
		{
			return "";
		}
	}



	function OrdersQueryPart()
	// Build orders query part (ORDER BY...[ DESC], ...)
	{
		if (count($this->orders))
		{
			$parts = array();

			foreach ($this->orders as $data)
			{
				$field_full_name = $this->FieldFullName($data["field"]);
				$parts[] = ($data["exp"] ? $this->EscapeExp($data["exp"]) : $field_full_name) . ($data["is_desc"] ? " DESC" : "");
			}

			return "\nORDER BY " . implode(", ", $parts);
		}

		else
		{
			return "";
		}
	}



	function LimitQueryPart($limit = NULL, $from = NULL)
	// Build limit query part (LIMIT x[, y])
	{
		if ($limit)
		{
			return "\nLIMIT " . CF::ImplodeNonEmpty(array($from, $limit), ", ");
		}

		return "";
	}



	function CustomStringsQueryPart($after_part)
	{
		if (isset($this->custom_strings[$after_part]))
		{
			return "\n" . implode(" ", $this->custom_strings[$after_part]);
		}
	}



	function ValueString($value, $type)
	// Escape and quote a provided value depending on its type
	{
		switch (strtoupper($type))
		{
			case "F":
				return $this->FieldFullName($value);
				break;

			case "S":
				return $this->Escape($value, "'");
				break;

			case "X":
				return $this->EscapeExp($value);
				break;

			default:
				return false;
				break;
		}
	}



	function InsertQuery($ignor = false)
	// Build and collect an INSERT...VALUES query (single insert)
	{
		// Query begin
		if($ignor)
			$this->query = "INSERT IGNORE INTO ";
		else
			$this->query = "INSERT INTO ";

		// Tables part
		$this->query .= $this->TablesQueryPart(true);
		$this->query .= $this->CustomStringsQueryPart("t");
		$this->query .= $this->CustomStringsQueryPart("j");

		// Fields & values part
		$parts = array();

		foreach ($this->values as $data) {
			if(is_null($data["val"])) {
				$parts[] = $this->FieldFullName($data["field"]) . " = NULL";
			} else {
				$parts[] = $this->FieldFullName($data["field"]) . " = " . $this->ValueString($data["val"], $data["type"]);
			}
			//$parts[$this->FieldFullName($data["field"])] =  $this->ValueString($data["val"], $data["type"]);
		}

		//$this->query .= " (" . implode(", ", array_keys($parts)) . ")";
		//$this->query .= "\nVALUES (" . implode(", ", $parts) . ")";
		
		$this->query .= "\nSET " . implode(", ", $parts);
		$this->query .= $this->CustomStringsQueryPart("f");
		$this->query .= $this->CustomStringsQueryPart("v");

		$this->query .= $this->CustomStringsQueryPart("c");
		$this->query .= $this->CustomStringsQueryPart("g");
		$this->query .= $this->CustomStringsQueryPart("o");
		$this->query .= $this->CustomStringsQueryPart("l");

		return $this->query;
	}



	function QuickInsertQuery($values)
	// Add values (provided as array), then build and collect an INSERT...VALUES query (single insert)
	{
		$this->AddValues($values);
		return $this->InsertQuery();
	}



	function InsertSelectQuery($select_query)
	// Build and collect an INSERT...SELECT query
	// Please provide a ready SELECT query string as first param
	{
		// Query begin
		$this->query = "INSERT INTO ";

		// Tables part
		$this->query .= $this->TablesQueryPart(true);
		$this->query .= $this->CustomStringsQueryPart("t");
		$this->query .= $this->CustomStringsQueryPart("j");

		// Fields part
		$this->query .= $this->FieldsQueryPart(true);
		$this->query .= $this->CustomStringsQueryPart("f");
		$this->query .= $this->CustomStringsQueryPart("v");

		$this->query .= $this->CustomStringsQueryPart("c");
		$this->query .= $this->CustomStringsQueryPart("g");
		$this->query .= $this->CustomStringsQueryPart("o");
		$this->query .= $this->CustomStringsQueryPart("l");

		// SELECT query part
		$this->query .= "\n" . $select_query;

		return $this->query;
	}



	function UpdateQuery($limit = NULL, $from = NULL)
	// Build and collect an UPDATE query
	{
		// Query begin
		$this->query = "UPDATE ";

		// Tables part
		$this->query .= $this->TablesQueryPart();
		$this->query .= $this->CustomStringsQueryPart("t");

		// Joins part
		$this->query .= $this->JoinsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("j");

		// Values part
		$parts = array();

		foreach ($this->values as $data) {			
			if(is_null($data["val"])) {
				$parts[] = $this->FieldFullName($data["field"]) . " = NULL";
			} else {
				$parts[] = $this->FieldFullName($data["field"]) . " = " . $this->ValueString($data["val"], $data["type"]);
			}
		}

		$this->query .= "\nSET " . implode(", ", $parts);
		$this->query .= $this->CustomStringsQueryPart("f");
		$this->query .= $this->CustomStringsQueryPart("v");

		// Conditions part
		$this->query .= $this->CondsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("c");

		// Groupings part
		$this->query .= $this->GroupingsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("g");

		// Orders part
		$this->query .= $this->OrdersQueryPart();
		$this->query .= $this->CustomStringsQueryPart("o");

		// Limit part
		$this->query .= $this->LimitQueryPart($limit, $from);
		$this->query .= $this->CustomStringsQueryPart("l");

		return $this->query;
	}



	function QuickUpdateQuery($values, $limit = NULL, $from = NULL)
	// Add values (provided as array), then build and collect an UPDATE query
	{
		$this->AddValues($values);
		return $this->UpdateQuery($limit, $from);
	}



	function SelectQuery($limit = NULL, $from = NULL, $distinct = false)
	// Build and collect a SELECT query
	{
		// Query begin
		if($distinct)
			$this->query = "SELECT DISTINCT ";
		else
			$this->query = "SELECT ";

		// Fields part
		$this->query .= $this->FieldsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("f");
		$this->query .= $this->CustomStringsQueryPart("v");

		// Tables part
		$this->query .= "\nFROM ";
		$this->query .= $this->TablesQueryPart();
		$this->query .= $this->CustomStringsQueryPart("t");

		// Joins part
		$this->query .= $this->JoinsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("j");

		// Conditions part
		$this->query .= $this->CondsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("c");

		// Groupings part
		$this->query .= $this->GroupingsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("g");

		// Orders part
		$this->query .= $this->OrdersQueryPart();
		$this->query .= $this->CustomStringsQueryPart("o");

		// Limit part
		$this->query .= $this->LimitQueryPart($limit, $from);
		$this->query .= $this->CustomStringsQueryPart("l");

		return $this->query;
	}



	function DeleteQuery($limit = NULL, $from = NULL)
	// Build and collect a DELETE query
	{
		// Query begin
		$this->query = "DELETE FROM ";

		// Tables part
		$this->query .= $this->TablesQueryPart();
		$this->query .= $this->CustomStringsQueryPart("t");

		// Joins part
		$this->query .= $this->JoinsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("j");

		$this->query .= $this->CustomStringsQueryPart("f");
		$this->query .= $this->CustomStringsQueryPart("v");

		// Conditions part
		$this->query .= $this->CondsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("c");

		// Groupings part
		$this->query .= $this->GroupingsQueryPart();
		$this->query .= $this->CustomStringsQueryPart("g");

		// Orders part
		$this->query .= $this->OrdersQueryPart();
		$this->query .= $this->CustomStringsQueryPart("o");

		// Limit part
		$this->query .= $this->LimitQueryPart($limit, $from);
		$this->query .= $this->CustomStringsQueryPart("l");

		return $this->query;
	}




	// Executing query

	function Exec($query = NULL, $collect_result = false, $init_after = false)
	// Execute any query on database
	// First param is for query; if none provided, trying to use one collected by class
	// Set second param true to collect queries result (good for SELECT queries)
	// Set third param true to initialize class after query execution (leaving collected result if 2nd param is true)
	{
		if (is_null($query))
		{
			$query = $this->query;
		}

		$result = @mysql_query($query, $this->handle);
		
		
		
		$this->queries_num++;

		if ($collect_result)
		{
			$this->res = $result;
		}

		if ($init_after)
		{
			$this->Init($collect_result);
		}

		$error = $this->Error();

		if (($this->debug_level >= 3) || ($error && ($this->debug_level > 1)))
		{
			CF::Debug($query);
		}

		if ($this->debug_level && $error)
		{
			echo $error;
		}

		return $result;
	}



	function Insert($init_after = true, $ignor = false)
	// Execute an INSERT...VALUES query (single insert)
	{
		return $this->Exec(
			$this->InsertQuery($ignor),
			false,
			$init_after
			);
	}



	function QuickInsert($values, $init_after = true)
	// Add values (provided as array), then execute an INSERT...VALUES query (single insert)
	{
		return $this->Exec(
			$this->QuickInsertQuery($values),
			false,
			$init_after
			);
	}



	function InsertSelect($select_query, $init_after = true)
	// Execute an INSERT...SELECT query
	// Please provide a ready SELECT query string as first param
	{
		return $this->Exec(
			$this->InsertSelectQuery($select_query),
			false,
			$init_after
			);
	}



	function Update($limit = NULL, $from = NULL, $init_after = true)
	// Execute an UPDATE query
	{
		return $this->Exec(
			$this->UpdateQuery($limit, $from),
			false,
			$init_after
			);
	}



	function QuickUpdate($values, $limit = NULL, $from = NULL, $init_after = true)
	// Add values (provided as array), then execute an UPDATE query
	{
		return $this->Exec(
			$this->QuickUpdateQuery($values, $limit, $from),
			false,
			$init_after
			);
	}



	function Select($limit = NULL, $from = NULL, $collect_result = true, $init_after = true, $distinct = false)
	// Execute a SELECT query and collect its result
	{
		return $this->Exec(
			$this->SelectQuery($limit, $from, $distinct),
			$collect_result,
			$init_after
			);
	}



	function Delete($limit = NULL, $from = NULL, $init_after = true)
	// Execute a DELETE query
	{
		return $this->Exec(
			$this->DeleteQuery($limit, $from),
			false,
			$init_after
			);
	}




	// Handling results

	function LastInsertID()
	// Get the auto-increment ID generated by recent database INSERT
	{
		return @mysql_insert_id($this->handle);
	}



	function NumRows($res = NULL)
	// Get the number of rows in provided SELECT result
	// If no result provided, trying to use one collected by class
	{
		return @mysql_num_rows(is_resource($res) ? $res : $this->res);
	}



	function AffectedRows()
	// Get the number of rows affected by last INSERT/UPDATE/DELETE operation
	{
		return @mysql_affected_rows($this->handle);
	}



	function FetchRow($res = NULL)
	// Get a row from provided SELECT result as a scalar (enumerated) array
	// If no result provided, trying to use one collected by class
	{
		return @mysql_fetch_row(is_resource($res) ? $res : $this->res);
	}



	function FetchAssoc($res = NULL)
	// Get a row from provided SELECT result as an associative array
	// If no result provided, trying to use one collected by class
	{
		return @mysql_fetch_assoc(is_resource($res) ? $res : $this->res);
	}



	function FetchArray($res = NULL)
	// Get a row from provided SELECT result as a combined (enumerated + associative) array
	// If no result provided, trying to use one collected by class
	{
		return @mysql_fetch_array(is_resource($res) ? $res : $this->res);
	}



	function FetchObject($res = NULL)
	// Get a row from provided SELECT result as an object
	// If no result provided, trying to use one collected by class
	{
		return @mysql_fetch_object(is_resource($res) ? $res : $this->res);
	}



	function FreeRes($res = NULL)
	// Free provided SELECT result
	// If no result provided, trying to use one collected by class (and then reset it)
	{
		$flag = !is_resource($res);
		$result = @mysql_free_result($flag ? $this->res : $res);

		if ($flag)
		{
			$this->ResetRes();
		}

		return $result;
	}




	// Poser functions

	function CollatePosed($put_id = NULL, $after_id = NULL, $init_after = true)
	// Give a position value to every of chosen rows (ordering them by position, and then by ID)
	// First param is to define an ID to put on certain place
	// Second param is to define that certain place (if not defined, will be put after the last posed ID)
	// Set third param to false to leave data collected by class (i.e. not no initialize after collation)
	{

		
		$ids = array();
		$pos = 0;

		if ($put_id && !is_numeric($after_id))
		{ 
			$after_id = $this->last_posed_id;
		}

		elseif ($put_id && is_numeric($after_id) && !$after_id)
		{
			$id = $put_id;
			$ids[$put_id] = ++$pos;
		}

		$this->AddField($this->id_field);
		$this->AddField($this->pos_field);

		if ($put_id)
		{
			$this->AddCondFS($this->id_field, "!=", $put_id);
		}

		$this->AddOrder($this->pos_field);
		$this->AddOrder($this->id_field);
		$this->Select(NULL, NULL, true, false/*$init_after*/);

		while ($row = $this->FetchObject())
		{ 
			$id = $row->{$this->id_field};
			$db_pos = $row->{$this->pos_field};
			$ids[$id] = ++$pos;

			if ($id == $after_id)
			{
				$id = $put_id;
				$ids[$id] = ++$pos;
			}
		}

		$this->FreeRes();

		array_pop($this->fields);
		array_pop($this->conds);

		if ($put_id)
		{
			array_pop($this->conds);
		}

		array_pop($this->orders);
		array_pop($this->orders);


		if (!is_null($put_id)) {

			$this->Exec("
				UPDATE `" . $this->tables[0]["name"] . "`	
				SET `$this->pos_field` = `$this->pos_field` + 1
				WHERE `$this->pos_field` > ".$this->last_posed_pos
			);
			$this->Exec("
				UPDATE `" . $this->tables[0]["name"] . "`	
				SET `$this->pos_field` = ".($this->last_posed_pos+1)." 
				WHERE id = ".$put_id
			);
		}
		
		$this->last_posed_pos = $db_pos;				
		$this->last_posed_id = isset($id) ? $id : 0;	
		
		 
		/*foreach ($ids as $id => $pos)
		{
			$this->Exec("
				UPDATE `" . $this->tables[0]["name"] . "`
				SET `$this->pos_field` = '$pos'
				WHERE `$this->id_field` = '$id'
				LIMIT 1
				");
		}*/


				if ($init_after)
				{
						$this->Init();
				}
	}



	function InsertPosed($after_id = NULL, $init_after = true)
	{
		$this->CollatePosed(NULL, NULL, false);
		$result = $this->Exec($this->InsertQuery());  
		$id = $this->LastInsertID();
		//CF::Debug($this->tables);
		$this->CollatePosed($id, $after_id, $init_after);
		return ($result === true) ? $id : $result;
	}



	function QuickInsertPosed($values = NULL, $after_id = NULL, $init_after = true)
	{
		$this->CollatePosed(NULL, NULL, false);
		$result = $this->Exec($this->QuickInsertQuery($values));
		$id = $this->LastInsertID();
		$this->CollatePosed($id, $after_id, $init_after);
		return ($result === true) ? $id : $result;
	}



	function DeletePosed($limit = NULL, $from = NULL, $init_after = true)
	{
		$result = $this->Exec($this->DeleteQuery($limit, $from));
		$this->CollatePosed(NULL, NULL, $init_after);
		return $result;
	}



	function DeleteSinglePosed($id, $init_after = true)
	{
		$this->AddCondFX($this->id_field, "=", $id);
		$result = $this->Exec($this->DeleteQuery(1));
		$this->CollatePosed(NULL, NULL, $init_after);
		return $result;
	}



	function MovePosed($id, $after_id = NULL, $init_after = true)
	{
		$this->CollatePosed(NULL, NULL, false);
		$this->CollatePosed($id, $after_id, $init_after);
	}



	function MoveUpPosed($id, $init_after = true)
	{
		$this->CollatePosed(NULL, NULL, false);
		$this->AddField($this->pos_field);
		$this->AddCondFX($this->id_field, "=", $id);
		$this->Select(1, NULL, true, false);

		if ($row = $this->FetchObject())
		{
			$this->FreeRes();
			array_pop($this->fields);
			$this->AddField($this->id_field);
			array_pop($this->conds);
			$this->AddCondFX($this->pos_field, "=", $row->pos - 1);
			$this->Select(1, NULL, true, false);

			if ($row = $this->FetchObject())
			{
				$this->FreeRes();
				array_pop($this->fields);
				array_pop($this->conds);
				$this->MovePosed($row->id, $id, $init_after);
				return true;
			}

			elseif ($init_after)
			{
				$this->Init();
			}
		}

		elseif ($init_after)
		{
			$this->Init();
		}

		return false;
	}



	function MoveDownPosed($id, $init_after = true)
	{
		$this->CollatePosed(NULL, NULL, false);
		$this->AddField($this->pos_field);
		$this->AddCondFX($this->id_field, "=", $id);
		$this->Select(1, NULL, true, false);

		if ($row = $this->FetchObject())
		{
			$this->FreeRes();
			array_pop($this->fields);
			$this->AddField($this->id_field);
			array_pop($this->conds);
			$this->AddCondFX($this->pos_field, "=", $row->pos + 1);
			$this->Select(1, NULL, true, false);

			if ($row = $this->FetchObject())
			{
				$this->FreeRes();
				array_pop($this->fields);
				array_pop($this->conds);
				$this->MovePosed($id, $row->id, $init_after);
				return true;
			}

			elseif ($init_after)
			{
				$this->Init();
			}
		}

		elseif ($init_after)
		{
			$this->Init();
		}

		return false;
	}
	
	
	function GetQueriesNum()
	//returns a number of queries
	{
		return $this->queries_num;
	}
	
}

?>