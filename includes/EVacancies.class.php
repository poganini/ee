<?php
class EVacancies
// version: 1.9
// date: 2010-03-23
{
    var $module_id;
    var $node_id;
    var $module_uri;
    var $privileges;
    var $output;

    var $mode;
    var $db_prefix;
    var $maintain_cache;
    var $item_id;
	
	function EVacancies($global_params, $params, $module_id, $node_id, $module_uri) {
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->module_uri = $module_uri;
		
		//$this->output["mode"] = $this->mode = $params;
		$this->output["user_displayed_name"] = $Auth->user_displayed_name;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		
		$parts = explode("/", $this->module_uri);

		if (!empty($parts[0]) && $parts[0]=="edit" && !empty($parts[1]))
			$this->mode = "vacancy_edit";
		elseif (!empty($parts[0]) && $parts[0]=="delete" && !empty($parts[1]))
			$this->mode = "vacancy_delete";
		elseif (!empty($parts[0]) && $parts[0] == "add") 
			$this->mode = "vacancy_add";
		elseif (!empty($parts[0]))
			$this->mode = "vacancy_view";
		else
			$this->mode = "vacancies_list";
		
		switch ($this->mode) {
			case "vacancies_list":
				$this->output["mode"] = "vacancies_list";
				$this->vacancies_list($params);
			break;
			
			case "vacancy_edit":
			if ($Engine->OperationAllowed(10, "vacancies.handle", 0, $Auth->usergroup_id)) {
				$this->output["mode"] = "vacancy_edit";
				$this->vacancy_edit($parts[1]);
			}
			break;
			
			case "vacancy_delete":
			if ($Engine->OperationAllowed(10, "vacancies.handle", 0, $Auth->usergroup_id)) {
				$this->output["mode"] = "vacancies_list";
				$this->vacancy_delete($parts[1]);
			}
			break;
			
			case "vacancy_add":
			if ($Engine->OperationAllowed(10, "vacancies.handle", 0, $Auth->usergroup_id)) {
				$this->output["mode"] = "vacancy_add";
				$this->vacancy_add();
			}
			break;
			
			case "vacancy_view":
				$this->output["mode"] = "vacancy_view";
				$this->vacancy_view($parts[0]);
			break;
		}
	
	}
	
	function vacancies_list($params) {
		global $DB, $Engine, $Auth;
		
		require_once INCLUDES . "Pager.class";
		
		$DB->SetTable("nsau_vacancies_lodging");
		$res = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res)) {
			$lodging[$row["id"]] = $row["lodging"];
			$this->output["lodgings"][] = $row;
		}
		
		$res = $DB->Exec("select distinct district from `nsau_vacancies` order by district");
		while ($row = $DB->FetchAssoc($res)) {
			$this->output["districts"][] = $row;
		}
		
		/*$DB->SetTable("nsau_vacancies");
		$res = $DB->Select();
		$num = 0;
		
		while ($row = $DB->FetchAssoc($res)) {
			$num++;
		}*/
		
		$parts = explode(";", $params);
		$per_page_input = (isset($parts[0]) && $parts[0]) ? $parts[0] : "50"; // строка настройки для Pager
		
		$DB->SetTable("nsau_vacancies");
		if (isset($_POST[$this->node_id]["filter"]["district"]) && $_POST[$this->node_id]["filter"]["district"])
			$_SESSION["filter_district"] = $_POST[$this->node_id]["filter"]["district"];
		elseif (isset($_POST[$this->node_id]["filter"]["district"]))
			unset($_SESSION["filter_district"]);
		
		if (isset($_POST[$this->node_id]["filter"]["salary_from"]) && $_POST[$this->node_id]["filter"]["salary_from"])
			$_SESSION["filter_salary_from"] = $_POST[$this->node_id]["filter"]["salary_from"];
		elseif (isset($_POST[$this->node_id]["filter"]["salary_from"]))
			unset($_SESSION["filter_salary_from"]);
			
		if (isset($_POST[$this->node_id]["filter"]["salary_to"]) && $_POST[$this->node_id]["filter"]["salary_to"])
			$_SESSION["filter_salary_to"] = $_POST[$this->node_id]["filter"]["salary_to"];
		elseif (isset($_POST[$this->node_id]["filter"]["salary_to"]))
			unset($_SESSION["filter_salary_to"]);
			
		if (isset($_POST[$this->node_id]["filter"]["lodging"]) && $_POST[$this->node_id]["filter"]["lodging"])
			$_SESSION["filter_lodging"] = $_POST[$this->node_id]["filter"]["lodging"];
		elseif (isset($_POST[$this->node_id]["filter"]["lodging"]))
			unset($_SESSION["filter_lodging"]);
		
		if (isset($_SESSION["filter_district"]))
			$DB->AddCondFS("district", "=", $_SESSION["filter_district"]);
		if (isset($_SESSION["filter_lodging"]))
			$DB->AddCondFS("lodging_id", "=", $_SESSION["filter_lodging"]);
		if (isset($_SESSION["filter_salary_from"]))
			$DB->AddCondFS("salary", ">=", $_SESSION["filter_salary_from"]);
		if (isset($_SESSION["filter_salary_to"]))
			$DB->AddCondFS("salary", "<=", $_SESSION["filter_salary_to"]);
		
		if (isset($_GET["sortby"]))
			$DB->AddOrder($_GET["sortby"], $_GET["desc"]);
			
		$res_count = $DB->Select();
		$num = 0;
		while ($row = $DB->FetchAssoc($res_count))
			$num++;
		
		$Pager = new Pager($num, $per_page_input);
		$this->output["pager_output"] = $result = $Pager->Act();
		
		$DB->SetTable("nsau_vacancies");
		if (isset($_SESSION["filter_district"]))
			$DB->AddCondFS("district", "=", $_SESSION["filter_district"]);
		if (isset($_SESSION["filter_lodging"]))
			$DB->AddCondFS("lodging_id", "=", $_SESSION["filter_lodging"]);
		if (isset($_SESSION["filter_salary_from"]))
			$DB->AddCondFS("salary", ">=", $_SESSION["filter_salary_from"]);
		if (isset($_SESSION["filter_salary_to"]))
			$DB->AddCondFS("salary", "<=", $_SESSION["filter_salary_to"]);
		
		if (isset($_GET["sortby"]))
			$DB->AddOrder($_GET["sortby"], $_GET["desc"]);
		
		$res = $DB->Select($result["db_limit"], $result["db_from"]);
		
		$num = 0;
		while ($row = $DB->FetchAssoc($res)) {
			$row["lodging"] = $lodging[$row["lodging_id"]];
			$this->output["vacancies_list"][] = $row;
			$num++;
		}			
		
		if ($Engine->OperationAllowed(10, "vacancies.handle", 0, $Auth->usergroup_id))
			$this->output["show_manage"] = 1;
		else
			$this->output["show_manage"] = 0;
	}
	
	function vacancy_edit($id) {
		global $DB;
		
		if (isset($_POST[$this->node_id]["edit_vacancy"])) {
			$DB->SetTable("nsau_vacancies");
			$DB->AddValue("district", $_POST[$this->node_id]["edit_vacancy"]["district"]);
			$DB->AddValue("vacancy", $_POST[$this->node_id]["edit_vacancy"]["vacancy"]);
			$DB->AddValue("company", $_POST[$this->node_id]["edit_vacancy"]["company"]);
			$DB->AddValue("education_id", $_POST[$this->node_id]["edit_vacancy"]["education_id"]);
			$DB->AddValue("requirements", $_POST[$this->node_id]["edit_vacancy"]["requirements"]);
			$DB->AddValue("salary", $_POST[$this->node_id]["edit_vacancy"]["salary"]);
			$DB->AddValue("lodging_id", $_POST[$this->node_id]["edit_vacancy"]["lodging_id"]);
			$DB->AddValue("contacts", $_POST[$this->node_id]["edit_vacancy"]["contacts"]);
			$DB->AddValue("faculty_id", $_POST[$this->node_id]["edit_vacancy"]["faculty_id"]);
			$DB->AddValue("speciality_id", $_POST[$this->node_id]["edit_vacancy"]["speciality_id"]);
			$DB->AddCondFS("id", "=", $id);
			if ($DB->Update())
				$this->output["messages"]["good"][] = "Вакансия обновлена";
		}
		
		$DB->SetTable("nsau_vacancies");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		
		if ($row = $DB->FetchAssoc($res)) {
			$row["company"] = str_replace('"', "&quot;", $row["company"]);
			$this->output["vacancy"] = $row;
			
			$DB->SetTable("nsau_vacancies_education");
			$res_edu = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res_edu)) {
				$this->output["educations"][] = $row;
			}
			
			$DB->SetTable("nsau_vacancies_lodging");
			$res_lodg = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res_lodg)) {
				$this->output["lodgings"][] = $row;
			}
			
			$DB->SetTable("nsau_faculties");
			$DB->AddField("id");
			$DB->AddField("name");
			$res_lodg = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res_lodg)) {
				$this->output["faculties"][] = $row;
			}
			
			$DB->SetTable("nsau_specialities");
			$DB->AddField("code");
			$DB->AddField("name");
			$res_lodg = $DB->Select();
				
			while ($row = $DB->FetchAssoc($res_lodg)) {
				$this->output["specialities"][] = $row;
			}
		}
		else
			$this->output["messages"]["bad"][] = "Неверный номер вакансии";
	}
	
	function vacancy_add() {
		global $DB, $Engine;

		if (isset($_POST[$this->node_id]["add_vacancy"])) {
			$DB->SetTable("nsau_vacancies");
			$DB->AddValue("district", $_POST[$this->node_id]["add_vacancy"]["district"]);
			$DB->AddValue("vacancy", $_POST[$this->node_id]["add_vacancy"]["vacancy"]);
			$DB->AddValue("company", $_POST[$this->node_id]["add_vacancy"]["company"]);
			$DB->AddValue("education_id", $_POST[$this->node_id]["add_vacancy"]["education_id"]);
			$DB->AddValue("requirements", $_POST[$this->node_id]["add_vacancy"]["requirements"]);
			$DB->AddValue("salary", $_POST[$this->node_id]["add_vacancy"]["salary"]);
			$DB->AddValue("lodging_id", $_POST[$this->node_id]["add_vacancy"]["lodging_id"]);
			$DB->AddValue("contacts", $_POST[$this->node_id]["add_vacancy"]["contacts"]);
			$DB->AddValue("faculty_id", $_POST[$this->node_id]["add_vacancy"]["faculty_id"]);
			$DB->AddValue("speciality_id", $_POST[$this->node_id]["add_vacancy"]["speciality_id"]);
			if ($DB->Insert()) {
				$this->output["messages"]["good"][] = "Вакансия добавлена";
				CF::Redirect($Engine->engine_uri);
			}
		}
		
		$DB->SetTable("nsau_vacancies_education");
		$res_edu = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res_edu)) {
			$this->output["educations"][] = $row;
		}
		
		$DB->SetTable("nsau_vacancies_lodging");
		$res_lodg = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res_lodg)) {
			$this->output["lodgings"][] = $row;
		}
			
		$DB->SetTable("nsau_faculties");
		$DB->AddField("id");
		$DB->AddField("name");
		$res_lodg = $DB->Select();
			
		while ($row = $DB->FetchAssoc($res_lodg)) {
			$this->output["faculties"][] = $row;
		}
		
		$DB->SetTable("nsau_specialities");
		$DB->AddField("code");
		$DB->AddField("name");
		$res_lodg = $DB->Select();
			
		while ($row = $DB->FetchAssoc($res_lodg)) {
			$this->output["specialities"][] = $row;
		}
	}
	
	function vacancy_view ($id) {
		global $DB;

		$DB->SetTable("nsau_vacancies");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		
		if ($row = $DB->FetchAssoc($res)) {			
			$DB->SetTable("nsau_vacancies_lodging");
			$DB->AddCondFS("id", "=", $row["lodging_id"]);
			$res = $DB->Select();
			$row_lodg = $DB->FetchAssoc($res);
			$row["lodging"] = $row_lodg["lodging"];
			
			$DB->SetTable("nsau_vacancies_education");
			$DB->AddCondFS("id", "=", $row["education_id"]);
			$res = $DB->Select();
			$row_ed = $DB->FetchAssoc($res);
			$row["education"] = $row_ed["education"];
			
			$DB->SetTable("nsau_faculties");
			$DB->AddCondFS("id", "=", $row["faculty_id"]);
			$res = $DB->Select();
			$row_fac = $DB->FetchAssoc($res);
			$row["faculty"] = $row_fac["name"];
			
			$DB->SetTable("nsau_specialities");
			$DB->AddCondFS("code", "=", $row["speciality_id"]);
			$res = $DB->Select();
			$row_spec = $DB->FetchAssoc($res);
			$row["speciality"] = $row_spec["name"];
			
			$this->output["vacancy"] = $row;
		}
		else
			$this->output["messages"]["bad"][] = "Неверный номер вакансии";
	}
	
	function vacancy_delete ($id) {
		global $DB, $Engine;
		
		$DB->SetTable("nsau_vacancies");
		$DB->AddCondFS("id", "=", $id);
		
		if ($DB->Delete())
			CF::Redirect($_SERVER["HTTP_REFERER"]);
	}
	
	function Output()
	{
		return $this->output;
	}
	
}