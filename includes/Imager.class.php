<?php

class Imager
// version: alpha 0.4
// date: 2012-03-01
{
	var $files_dir;
	var $http_files_dir;
	var $icons_dir;
	var $http_icons_dir;
	var $naming_type;
	var $recommended_input_image_max_dim;
	var $input_file_max_size;
	var $memory_limit;
	var $allow_image_clip;
	var $allow_image_enlarge;
	var $allow_image_leave;
	var $allow_nonimages;
	var $input_image_formats;
	var $output_image_formats;
	var $nonimage_formats;
	var $nonimage_exts;
	var $output_files;
	var $main_output_file;

	var $input_file;
	var $original_filename;

	var $is_image;
	var $base_filename;
	var $ext;
	var $width;
	var $height;
	var $format;




	function Imager($config_file, $files_dir, $http_files_dir, $http_icons_dir = NULL)
	{
		if (!$config_file)
		{
			die("Imager module error #1001: config file not specified.");
		}

		elseif (!$array = @parse_ini_file(INCLUDES . $config_file, true))
		{
			die("Imager module error #1002: cannot process config file '$config_file'.");
		}


		$this->files_dir = $files_dir;
		$this->http_files_dir = $http_files_dir;
		$this->http_icons_dir = $http_icons_dir;
		$this->input_file_max_size = (int) $array["general_settings"]["input_file_max_size"];
		$this->memory_limit = $array["general_settings"]["memory_limit"] *  1024 * 1024;
		$this->recommended_input_image_max_dim = (int) floor(sqrt($this->memory_limit * 1024 * 1024));
		$this->allow_image_clip = (bool) $array["general_settings"]["allow_image_clip"];
		$this->allow_image_enlarge = (bool) $array["general_settings"]["allow_image_enlarge"];
		$this->allow_image_leave = (bool) $array["general_settings"]["allow_image_leave"];
		$this->allow_nonimages = (bool) $array["general_settings"]["allow_nonimages"];


		$this->input_image_formats = array();

		foreach ($array["input_image_formats"] as $key => $data)
		{
			$parts = explode(",", $data);
			$this->input_image_formats[$key] = array(
				"name" => trim($parts[0]),
				"read_function" => trim($parts[1]),
				"output_format" => (int) trim($parts[2]),
				);
		}


		$this->output_image_formats = array();

		foreach ($array["output_image_formats"] as $key => $data)
		{
			$parts = explode(",", $data);
			$this->output_image_formats[$key] = array(
				"output_function" => trim($parts[0]),
				"ext" => trim($parts[1]),
				"use_interlacing" => isset($parts[2]) ? (bool) trim($parts[2]) : false,
				"quality" => isset($parts[3]) ? (int) trim($parts[3]) : false,
				);
		}


		$this->nonimage_formats = array();
		$this->nonimage_exts = array();

		if ($this->allow_nonimages)
		{
			foreach ($array["nonimage_formats"] as $key => $data)
			{
				$parts = explode(",", $data);
				$parts2 = explode("|", $parts[0]);

				foreach ($parts2 as $elem)
				{
					$this->nonimage_exts[$elem] = $key;
				}

				$parts2 = explode("|", $parts[2]);

				$icons = array();

				foreach ($parts2 as $elem)
				{
					$parts3 = explode(":", $elem);
					$icons[(int) $parts3[0]] = $this->http_icons_dir . $parts3[1];
				}

				$this->nonimage_formats[$key] = array(
					"name" => trim($parts[1]),
					"icons" => $icons,
					);
			}
		}


		$this->output_files = array();

		foreach ($array["output_files"] as $key => $data)
		{
			$parts = explode(",", $data);

			$this->output_files[$key] = array(
				"width" => (int) trim($parts[0]),
				"height" => (int) trim($parts[1]),
				"name_suffix" => isset($parts[2]) ? trim($parts[2]) : false,
				"allow_upload" => isset($parts[3]) ? (bool) trim($parts[3]) : false,
				);
		}

		$this->main_output_file = $array["general_settings"]["main_output_file"];
	}



	function SetBaseFilename($base_filename)
	{
		$this->base_filename = $base_filename;
	}



	function SetExt($ext)
	{
		$this->ext = $ext;
	}



	function SetIsImage($is_image)
	{
		$this->is_image = $is_image;
	}



	function SetSize($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
	}


	function SetFormat($format)
	{
		$this->format = $format;
	}



	function SetProps($base_filename, $ext, $is_image = true, $width = NULL, $height = NULL, $format = NULL)
	{
		$this->SetBaseFilename($base_filename);
		$this->SetExt($ext);
		$this->SetIsImage($is_image);
		$this->SetSize($width, $height);
		$this->SetFormat($format);
	}



	function GrabUploadedFile($FILE_DATA)
	{
		$status = true;
		$messages = array(
			"good" => array(),
			"bad" => array()
			);

		if (!($FILE_DATA["size"]))
		{
			$status = false;
			$messages["bad"][] = 901; // не выбран файл для добавления
		}

		elseif ($FILE_DATA["size"] > $this->input_file_max_size)
		{
			$status = false;
			$messages["bad"][] = 902; // размер файла превышает максимально допустимый
		}

		elseif (!file_exists($FILE_DATA["tmp_name"]))
		{
			$status = false;
			$messages["bad"][] = 903; // не удалось найти закачанный файл
		}

		elseif (!is_uploaded_file($FILE_DATA["tmp_name"]))
		{
			$status = false;
			$messages["bad"][] = 904; // непредвиденная ошибка (файл не является закачанным)
		}


		return $status ? $this->GrabFile($FILE_DATA["tmp_name"], $FILE_DATA["name"]) : $messages;
	}



	function GrabFile($input_file, $original_filename = NULL)
	{
		$status = true;
		$messages = array(
			"good" => array(),
			"bad" => array()
			);

		$this->input_file = $input_file;
		$this->original_file_name = $original_filename;

		if (!CF::IsNonEmptyStr($original_filename))
		{
			$original_filename = array_pop(explode("/", $input_file));
		}

		$result = @getimagesize($input_file);

		if ($result && isset($this->input_image_formats[$result[2]]))
		{
			$this->SetIsImage(true);
			$this->SetSize($result[0], $result[1]);
			$this->SetFormat($result[2]);

			if (!$this->width || !$this->height)
			{
				$status = false;
				$messages["bad"][] = 905; // не удаётся определить высоту и/или ширину
			}

			elseif (($this->width * $this->height * 4) > $this->memory_limit) // 4 (= 32 бита) сделать опцией!
			{
				$status = false;
				$messages["bad"][] = 906; // размеры изображения превышают максимально допустимые
			}

			else
			{
				$this->SetExt($this->output_image_formats[$this->format]["ext"]);
			}
		}

		elseif ($this->allow_nonimages)
		{
			$this->SetIsImage(false);
			$parts = explode(".", $original_filename);

			if (count($parts) < 2)
			{
				$status = false;
				$messages["bad"][] = 907; // файл не-изображения без расширения
			}

			else
			{
				$result = array_pop($parts);

				if (isset($this->nonimage_exts[$result]) || isset($this->nonimage_exts["*"]))
				{
					$this->SetExt($result);
				}

				else
				{
					$status = false;
					$messages["bad"][] = 908; // неизвестное расширение файла не-изображения
				}
			}
		}

		else
		{
			$this->SetIsImage(false);
			$status = false;
			$messages["bad"][] = 909; // неизвестный формат файла
		}

		return $status ? true : $messages;
	}



	function CreateOutputFiles($base_filename)
	{
		$this->SetBaseFilename($base_filename);

		$status = true;
		$messages = array(
			"good" => array(),
			"bad" => array()
			);


		if ($this->is_image)
		{
			foreach ($this->output_files as $key => $data)
			{
				if (!$this->ResizeImage(
					$this->input_file,
					$this->files_dir . $base_filename . $data["name_suffix"] . "." . $this->ext,
					$this->format,
					$this->width,
					$this->height,
					$data["width"] ? $data["width"] : $this->width,
					$data["height"] ? $data["height"] : $this->height,
					$this->output_image_formats[$this->format]["use_interlacing"],
					$this->output_image_formats[$this->format]["quality"]
					))
				{
					$status = false;
					$messages["bad"][] = 910; // ошибка создания файла изображения
					break;
				}
			}


			if (!$status) // если были проблемы, удаляем созданные файлы
			{
				$this->DeleteOutputFiles();
			}
		}

		elseif ($this->allow_nonimages)
		{
			$result = $this->MainOutputFile();

			if (!copy($FILE_DATA["tmp_name"], $result))
			{
				$status = false;
				$messages["bad"][] = 911; // ошибка копирования файла не-изображения
			}


			if (!$status) // если были проблемы, удаляем созданные файлы
			{
				@unlink($result);
			}
		}

		else
		{
			$status = false;
			$messages["bad"][] = 909; // неизвестный формат файла
		}

		return $status ? true : $messages;
	}



	function DeleteOutputFiles()
	{
		if ($this->is_image)
		{
			foreach ($this->output_files as $data)
			{
				@unlink($this->files_dir . $this->base_filename . $data["name_suffix"] . "." . $this->ext);
			}
		}

		elseif ($this->allow_nonimages)
		{
			@unlink($this->MainOutputFile());
		}
	}



	function MainOutputSize()
	{
		if ($this->is_image && ($result = @getimagesize($this->MainOutputFile())))
		{
			return $result;
		}

		else
		{
			return false;
		}
	}



	function ResizeImage($input_file, $output_file, $format, $input_width, $input_height, $output_width, $output_height, $use_interlace, $quality, $input_x = null, $input_y = null)
	{
		if ($this->allow_image_clip)
		{
			$width_ratio = $input_width / $output_width;
			$height_ratio = $input_height / $output_height;
			$do_resize = (/*$this->allow_image_enlarge || */($input_width != $output_width || $input_height != $output_height) || ($width_ratio != $height_ratio));
		}

		else
		{
			$do_resize = (/*$this->allow_image_enlarge || */($input_width > $output_width) || ($input_height > $output_height));
		}


		if (!$do_resize && $this->allow_image_leave)
		{
			return copy($input_file, $output_file);
		}

		else
		{
			$input_image = $this->input_image_formats[$format]["read_function"]($input_file);

			if (!$this->allow_image_clip)
			{
				//if (/*$this->allow_image_enlarge || */($input_width > $output_width) || ($input_height > $output_height))
				$resized_width = $input_width;
				$resized_height = $input_height;

				$ratio = max($input_width / $output_width, $input_height / $output_height);
			
				$output_width = $input_width / $ratio;
				$output_height = $input_height / $ratio;
				if(is_null($input_x) && is_null($input_y)) {
					$input_x = $input_y = 0;
				} else {
					$input_x = round($input_x*$height_ratio);
					$input_y = round($input_y*$width_ratio);
				}			
			}
			elseif ($width_ratio < $height_ratio) {
				$resized_width = $input_width;
				$resized_height = round($output_height * $width_ratio);
				if(is_null($input_x) && is_null($input_y)) {
					$input_x = 0;
					$input_y = round(($input_height - $resized_height) / 2);
				} else {
					$input_x = round($input_x*$height_ratio);
					$input_y = round($input_y*$width_ratio);
				}
			}
			elseif($width_ratio > $height_ratio) {
				$resized_width = round($output_width * $height_ratio);
				$resized_height = $input_height;
				if(is_null($input_x) && is_null($input_y)) {
					$input_x = round(($input_width - $resized_width) / 2);
					$input_y = 0;
				} else {
					$input_x = round($input_x*$height_ratio);
					$input_y = round($input_y*$width_ratio);
				}
			}
			else {
				$resized_width = $input_width;
				$resized_height = $input_height;
				if(is_null($input_x) && is_null($input_y)) {
					$input_x = 0;
					$input_y = 0;
				} else {
					$input_x = round($input_x*$height_ratio);
					$input_y = round($input_y*$width_ratio);
				}
			}


			if (!$function = CF::Seek4Function("imagecreatetruecolor", "imagecreate"))
			{
				return false;
			}

			$output_image = $function($output_width, $output_height);
			imageinterlace($output_image, $use_interlace);


			if (!$function = CF::Seek4Function("imagecopyresampled", "imagecopyresized"))
			{
				return false;
			}
			$function($output_image, $input_image, 0, 0, $input_x, $input_y, $output_width, $output_height, $resized_width, $resized_height);

			return is_int($quality) ? $this->output_image_formats[$format]["output_function"]($output_image, $output_file, $quality) : $this->output_image_formats[$format]["output_function"]($output_image, $output_file);
		}
	}



	function MainOutputFile()
	{
		return $this->files_dir . $this->base_filename . $this->output_files[$this->main_output_file]["name_suffix"] . "." . $this->ext;
	}



	function ListOutputFiles()
	{
		$output = array();

		foreach ($this->output_files as $key => $data)
		{
			$output[$key] = ($this->is_image || ($key == $this->main_output_file)) ? ($this->http_files_dir . $this->base_filename . $data["name_suffix"] . "." . $this->ext) : ($this->nonimage_formats[$this->nonimage_exts[$this->ext]]["icons"][$key]);
		}

		return $output;
	}
}

?>