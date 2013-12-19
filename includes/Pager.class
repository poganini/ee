<?php

class Pager
// version: beta 1.7
// date: 2007-11-02
{
	var $data_array;            // массив данных
	var $var_name;              // им€ переменной в нЄм
	var $preceding_uri;         // предшествующий URI веб-страницы (например, /news/)

	var $items_num;             // общее число элементов
	var $items_per_page;        // элементов на страницу
	var $pages_num;             // общее число страниц

	var $current_page;          // номер текущей страницы
	var $prev_page;             // номер предыдущей страницы
	var $next_page;             // номер следующей страницы

	var $pages_display_limit;   // отображать страниц не более... ( 1 2 3 4 5 6 7 8 9 ...)
	var $pages_data;            // массив данных обо всех страницах (номер, ссылка...)

	var $items_from;            // номер первого элемента на текущей странице
	var $items_to;              // номер последнего –≈јЋ№Ќќ√ќ элемента на текущей странице

	var $sort_enabled;
	var $sort_by_var_name;
	var $sort_by;
	var $sort_order_var_name;
	var $sort_order_asc;
	var $sort_order_desc;
	var $sort_order_default;
	var $sort_order;
	var $sort_data;
	var $callback;

	function Pager($items_num, $items_per_page = NULL, $pages_display_limit = NULL, $preceding_uri = NULL, $var_name = NULL, $data_array = NULL, $callback = NULL)
	{
		$this->Set($items_num, $items_per_page, $pages_display_limit, $preceding_uri, $var_name, $data_array, $callback);
		$this->sort_enabled = false;
	}



	function PerPageExtremes($input_from, $input_to, &$from, &$to)
	{		$from = $input_from;
		$to = $input_to;	}



	function Set($items_num, $items_per_page = NULL, $pages_display_limit = NULL, $preceding_uri = NULL, $var_name = NULL, $data_array = NULL, $callback)
	{
		$this->data_array = is_array($data_array) ? $data_array : $GLOBALS["_GET"];
		$this->var_name = CF::Seek4NonEmptyStr($var_name, "page");
		$this->items_num = intval($items_num);
		$this->pages_display_limit = $pages_display_limit;
		$this->callback = $callback;

		if (CF::IsNonEmptyStr($preceding_uri)) // задан (непуста€ строка)
		{
			$this->preceding_uri = $preceding_uri;
		}

		else                                              // иначе берЄм текущую страницу: ...
		{
			$parts = explode("?", $_SERVER["REQUEST_URI"]); // ... рубим текущий путь по знаку вопроса ...
			$this->preceding_uri = $parts[0];               // ... и берЄм первую часть
		}


		if (CF::IsNaturalNumeric($items_per_page)) // натуральное число
		{
			$this->items_per_page = intval($items_per_page);
		}

		elseif (is_array($items_per_page) && CF::IsNonEmptyStr($items_per_page[0]) && CF::IsNonEmptyStr($items_per_page[1])) // переменна€ из массива данных + допустимые значени€
		{
			$per_page_var_name = $items_per_page[0];
			$parts = explode(",", $items_per_page[1]);
			$legal_per_page = array();

			foreach ($parts as $value)
			{
				$value = trim($value); // убираем лишние пробелы

				if (CF::IsNaturalNumeric($value)) // значени€ в виде числа
				{
					$legal_per_page[] = intval($value);
				}

				elseif (preg_match("/^\d+-\d+$/", $value)) // значени€ в виде интервала (например: 5-20)
				{
					preg_replace("/^(\d+)-(\d+)$/e", "\$this->PerPageExtremes(\\1, \\2, \$from, \$to)", $value);

					if ($from <= $to)
					{
						for ($i = $from; $i <= $to; $i++)
						{
							$legal_per_page[] = $i;
						}
					}
				}
			}

			// если в массиве данных есть соответствущий ключ и его значение находитс€ среди допустимых, принимаем его, иначе же берЄм первое из допустимых значений
			$this->items_per_page = (array_key_exists($per_page_var_name, $this->data_array) && in_array($this->data_array[$per_page_var_name], $legal_per_page)) ? $this->data_array[$per_page_var_name] : reset($legal_per_page);
		}


		if (is_null($this->items_per_page)) // если первые два варианта не подошли
		{
			$this->items_per_page = 10;
		}
	}



	function SetSort($sort_by_values, $sort_by_default = NULL, $sort_order_default = true, $sort_by_var_name = "sort_by", $sort_order_var_name = "sort_order", $sort_order_asc = 1, $sort_order_desc = 0)
	{		if (CF::IsRichArr($sort_by_values))
		{			$this->sort_enabled = true;
			$this->sort_by_values = $sort_by_values;
			$this->sort_by_var_name = $sort_by_var_name;
			$this->sort_order_var_name = $sort_order_var_name;
			$this->sort_order_default = $sort_order_default;


			if (!$sort_by_default || !array_key_exists($sort_by_default, $this->sort_by_values))
			{
				$sort_by_default = reset($this->sort_by_values);			}


			if (array_key_exists($this->sort_by_var_name, $this->data_array) && in_array($this->data_array[$this->sort_by_var_name], $sort_by_values))
			{				$this->sort_by = $this->data_array[$this->sort_by_var_name];			}

			else
			{				$this->sort_by = $sort_by_default;			}


			if (array_key_exists($this->sort_order_var_name, $this->data_array) && in_array($this->data_array[$this->sort_order_var_name], array($sort_order_asc, $sort_order_desc)))
			{				$this->sort_order = ($this->data_array[$this->sort_order_var_name] == $sort_order_asc);			}

			else
			{				$this->sort_order = $sort_order_default;			}
		}
	}



	function Extremes($page, $items_per_page, $items_num)
	{
		// считаем номер первого элемента на странице (дл€ отображени€, а не дл€ Ѕƒ!)
		$from = (($page - 1) * $items_per_page) + 1;

		// считаем номер последнего элемента на странице и выбираем наименьшее между ним и общим числом элементов
		$to = min($from + $items_per_page - 1, $items_num);

		return array($from, $to);
	}



	function Act()
	{		if ($this->sort_enabled)
		{			$this->sort_data = array();
			$this->data_array[$this->sort_by_var_name] = $this->sort_by;
			$this->data_array[$this->sort_order_var_name] = $this->sort_order;

			foreach ($this->sort_by_values as $value)
			{				$is_active = ($value == $this->sort_by);
				$order = $is_active ? !$this->sort_order : $this->sort_order_default;

				$array = $this->data_array;
				unset($array[$this->var_name]);
				$array[$this->sort_by_var_name] = $value;
				$array[$this->sort_order_var_name] = $order ? 1 : 0;
				$this->sort_data[$value] = array(
					"is_active" => $is_active,
					"click_order" => $order,
					"link" => $this->preceding_uri . "?" . http_build_query($array)
					);			}
		}


		$this->pages_num = ceil($this->items_num / $this->items_per_page);
		$this->current_page = intval(CF::Seek4NaturalNumeric(@$this->data_array[$this->var_name], 1));


		$this->prev_page = $this->current_page - 1;

		if ($this->prev_page < 1)
		{
			$this->prev_page = false;
		}


		$this->next_page = $this->current_page + 1;

		if ($this->next_page > $this->pages_num)
		{
			$this->next_page = false;
		}


		list($this->items_from, $this->items_to) = $this->Extremes($this->current_page, $this->items_per_page, $this->items_num);


    $displayed_pages = array();

		if (!$this->pages_display_limit || ($this->pages_num <= $this->pages_display_limit)) // не задан лимит, или число страниц его не превышает - выводим все страницы
		{
			for ($i = 1; $i <= $this->pages_num; $i++)
			{
				$displayed_pages[] = $i;
			}
		}

		else
		{
			$from = $this->current_page - floor(($this->pages_display_limit - 1) / 2);
			$to = $from + $this->pages_display_limit - 2;

			if ($from < 1)
			{
				$delta = 1 - $from;
				$from = 1;
				$to += $delta;
			}

			elseif ($to > $this->pages_num)
			{
				$delta = $to - $this->pages_num;
				$from -= $delta;
				$to = $this->pages_num;
			}

			if ($from != 1)
			{
				$displayed_pages[] = "before";
			}

			for ($i = intval($from); $i <= intval($to); $i++)
			{
				$displayed_pages[] = $i;
			}

			if ($to != $this->pages_num)
			{
				$displayed_pages[] = "after";
			}
		}


		$this->pages_data = array();

		foreach ($displayed_pages as $value)
		{			if (is_int($value))
			{
				$array = $this->data_array;
				$array[$this->var_name] = $value;

				list($from, $to) = $this->Extremes($value, $this->items_per_page, $this->items_num);

				$this->pages_data[$value] = array(
					"is_current" => $value == $this->current_page,
					"data_array" => $array,
					"link" => $this->preceding_uri . "?" . http_build_query($array),
					"db_from" => $from - 1,
					"from" => $from,
					"to" => $to,
					);
			}

			else
			{				$this->pages_data[$value] = false;			}
		}


		return array(
			"items_num" => $this->items_num,
			"pages_num" => $this->pages_num,
			"current_page" => $this->current_page,
			"prev_page" => $this->prev_page,
			"next_page" => $this->next_page,
			"db_from" => $this->items_from - 1,
			"db_limit" => $this->items_per_page,
			"from" => $this->items_from,
			"to" => $this->items_to,
			"pages_data" => $this->pages_data,
			"sort_by" => $this->sort_by,
			"sort_order" => $this->sort_order,
			"sort_data" => $this->sort_data,
			"pages_limit" => $this->pages_display_limit,
			"callback" => $this->callback
			);
	}
}

?>