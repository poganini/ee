<?php
class EGraduaties
// version: 1.7
// date: 2010-02-17

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
    
    function EGraduaties($global_params, $params, $module_id, $node_id, $module_uri) {
        global $Engine, $Auth, $DB;
        $this->node_id = $node_id;
        $this->module_id = $module_id;
        $this->module_uri = $module_uri;
        
        //$this->output["mode"] = $this->mode = $params;
        $this->output["user_displayed_name"] = $Auth->user_displayed_name;
        $this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
        
        $parts = explode("/", $this->module_uri);
        
            if (!empty($parts[0]) && $parts[0]=="edit" && !empty($parts[1]))
        $this->mode = "graduaties_edit";
            elseif (!empty($parts[0]) && $parts[0]=="delete" && !empty($parts[1]))
        $this->mode = "graduaties_delete";
            elseif (!empty($parts[0]) && $parts[0]=="add")
        $this->mode = "graduaties_add";
            else
        $this->mode = "graduaties";
        
        switch ($this->mode) 
        {
            case "graduaties":
                $this->output["mode"] = "graduaties";
                $this->graduaties($params);
            break;
            
            case "graduaties_edit":
            if ($Engine->OperationAllowed(11, "graduaties.handle", 0, $Auth->usergroup_id)) {
                $this->output["mode"] = "graduaties_edit";
                $this->graduaties_edit($parts[1]);
            }
            break;
            
            case "graduaties_delete":
            if ($Engine->OperationAllowed(11, "graduaties.handle", 0, $Auth->usergroup_id)) {
                $this->output["mode"] = "graduaties";
                $this->graduaties_delete($parts[1]);
            }
            break;
            
            case "graduaties_add":
            if ($Engine->OperationAllowed(11, "graduaties.handle", 0, $Auth->usergroup_id)) {
                $this->output["mode"] = "graduaties_add";
                $this->graduaties_add();
            }
        }
    
    }
    
    function graduaties_add() {
        global $DB, $Engine;
        $parts = explode("/", $this->module_uri);
        
        if (isset($_GET["lastnamepatronymic"]))
        {
            $fio = explode(" ", trim($_GET["lastnamepatronymic"]));
            $DB->SetTable("nsau_people", "p");
            $DB->AddTable("nsau_specialities", "sp");
            $DB->AddTable("nsau_groups", "g");
            $DB->AddField("p.year"); 
            $DB->AddField("p.id");
            $DB->AddField("p.last_name"); 
            $DB->AddField("p.name"); 
            $DB->AddField("p.patronymic");
            $DB->AddField("sp.code");
            $DB->AddField("sp.name", "spec");
            $DB->AddField("g.name", "group");
            foreach($fio as $part)
                {
                    $DB->AddAltFS("p.last_name", "LIKE", "%".$part."%");
                    $DB->AddAltFS("p.name", "LIKE", "%".$part."%");
                    $DB->AddAltFS("p.patronymic", "LIKE", "%".$part."%");        
                    $DB->AppendAlts();
                } 
            $DB->AddCondFF("p.id_group","=","g.id"); 
            $DB->AddCondFF("p.id_speciality","=","sp.code"); 
            $res_people=$DB->Select();
            $count=0;
            while ($row = $DB->FetchAssoc($res_people)) 
            {
                $this->output["people"][] = $row;
                $count++; 
            } 
            
            $this->output["count"]=$count;
            
            
        }
        
        
        if (isset($_POST["add_id"])) {
            $DB->SetTable("nsau_graduaties");
            $DB->AddValue("id_people", $_POST["add_id"]); 
            $DB->AddValue("type_tuition", $_POST["add_type_tuition"]);
            $DB->AddValue("average_grades", $_POST["add_average_grades"]);
            $DB->AddValue("theme", $_POST["add_theme"]);
            $DB->AddValue("year_out", $_POST["add_year_out"]);
            $DB->AddValue("marital_status", $_POST["add_marital_status"]);
            $DB->AddValue("residence", $_POST["add_residence"]);
            $DB->AddValue("operational_experience", $_POST["add_operational_experience"]);    
            $DB->AddValue("additional_information", $_POST["add_additional_information"]);
            $DB->AddValue("operating_schedule", $_POST["add_operating_schedule"]);
            $DB->AddValue("place_of_employment", $_POST["add_place_of_employment"]);
            if ($DB->Insert())
            {
                $this->output["messages"]["good"][] = "Данные выпускника успешно добавлены";
            }
            else
            {
                 $this->output["messages"]["bad"][] = "Неверные данные"; 
            }
            $DB->SetTable("nsau_people");
            $DB->AddValue("status_id", 5); 
            $DB->AddCondFS("id", "=", $_POST["add_id"]);
            $DB->Update();
        }

        $parts = explode("/", $this->module_uri);
        if (!empty($parts[0]) && $parts[0]=="add" && !empty($parts[1]) && $parts[1]=="people")
        {
             $this-> output["action"]="add_people";
             
            $DB->SetTable("nsau_groups");
            $DB->AddField("id");
            $DB->AddField("name");
            $res_groups = $DB->Select();
            while ($row = $DB->FetchAssoc($res_groups)) 
            {
                $this->output["group"][] = $row;
            }
        }
        
        if (isset($_POST["action"]) && $_POST["action"]=="new_people") {
            $DB->SetTable("nsau_people");
            $DB->AddValue("user_id", 0); 
            $DB->AddValue("last_name", $_POST["last_name"]);
            $DB->AddValue("name", $_POST["name"]);
            $DB->AddValue("patronymic", $_POST["patronymic"]);
            $DB->AddValue("male", $_POST["male"]); 
            $DB->AddValue("status_id", 6);
            $DB->AddValue("year", $_POST["year"]);
            $DB->AddValue("id_group", $_POST["id_group"]);
            $DB->AddValue("subgroup", $_POST["subgroup"]);
            $DB->AddValue("id_speciality", $_POST["id_speciality"]);     
            if ($DB->Insert())
            {
                $this->output["messages"]["good"][] = "Данные нового человека успешно добавлены";
            }
            else
            {
                 $this->output["messages"]["bad"][] = "Неверные данные"; 
            }
            $DB->SetTable("nsau_people", "p");
            $DB->AddTable("nsau_specialities", "sp");
            $DB->AddTable("nsau_groups", "g");
            $DB->AddField("p.year"); 
            $DB->AddField("p.id");
            $DB->AddField("p.last_name"); 
            $DB->AddField("p.name"); 
            $DB->AddField("p.patronymic");
            $DB->AddField("sp.code");
            $DB->AddField("sp.name", "spec");
            $DB->AddField("g.name", "group"); 
            $DB->AddCondFS("p.last_name", "=", $_POST["last_name"]);
            $DB->AddCondFS("p.name", "=", $_POST["name"]); 
            $DB->AddCondFS("p.patronymic", "=", $_POST["patronymic"]);
            $DB->AddCondFF("p.id_group","=","g.id"); 
            $DB->AddCondFF("p.id_speciality","=","sp.code"); 
            $res_people=$DB->Select();
            $count=0;
            while ($row = $DB->FetchAssoc($res_people)) 
            {
                $this->output["people"][] = $row;
                $count++; 
            } 
            
            $this->output["count"]=$count;
            
        }
        
        $parts = explode("/", $this->module_uri);
        if (!empty($parts[0]) && $parts[0]=="add" && !empty($parts[1]))
        {
            $DB->SetTable("nsau_people", "p");
            $DB->AddTable("nsau_statuses", "st"); 
            $DB->AddTable("nsau_specialities", "sp");
            $DB->AddTable("nsau_groups", "g");
            $DB->AddTable("nsau_faculties", "f"); 
            $DB->AddField("p.id");
            $DB->AddField("p.last_name"); 
            $DB->AddField("p.name"); 
            $DB->AddField("p.patronymic");
            $DB->AddField("p.male");
            $DB->AddField("f.name", "faculty"); 
            $DB->AddField("st.name", "status");
            $DB->AddField("sp.name", "spec");
            $DB->AddField("g.name", "group"); 
            $DB->AddCondFS("p.id", "=", $parts[1]);
            $DB->AddCondFF("p.status_id","=","st.id");
            $DB->AddCondFF("p.id_group","=","g.id"); 
            $DB->AddCondFF("p.id_speciality","=","sp.code");
            $DB->AddCondFF("f.id","=","g.id_faculty");
            $res_graduaty=$DB->Select();
            while ($row = $DB->FetchAssoc($res_graduaty)) 
            {
                $this->output["graduaty"][] = $row;
            }   
        } 
           
            $DB->SetTable("nsau_faculties");
            $DB->AddField("id");
            $DB->AddField("name");
            $res_fac = $DB->Select();
            
        while ($row = $DB->FetchAssoc($res_fac)) {
            $this->output["faculties"][] = $row;
        }
    }
    
    function graduaties_add_people()
    {
       global $DB, $Engine;
        
    }
    
    function graduaties_delete ($id) 
    {
        global $DB, $Engine;
        
        $DB->SetTable("nsau_graduaties");
        $DB->AddCondFS("id_people", "=", $id);
        
        if ($DB->Delete())         
        {
            $DB->SetTable("nsau_people");
            $DB->AddValue("status_id", 6);
            $DB->AddCondFS("id", "=", $id);
            $DB->Update();
            
            CF::Redirect($_SERVER["HTTP_REFERER"]);
        }
    }
    
    function graduaties_edit($id) 
    {
        global $DB;
        if (!empty($_POST["id"]))
       {

            $DB->SetTable("nsau_graduaties");
            $DB->AddValue("type_tuition", $_POST["edit_type_tuition"]);
            $DB->AddValue("average_grades", $_POST["edit_average_grades"]);
            $DB->AddValue("theme", $_POST["edit_theme"]);
            $DB->AddValue("year_out", $_POST["edit_year_out"]);
            $DB->AddValue("marital_status", $_POST["edit_marital_status"]);
            $DB->AddValue("residence", $_POST["edit_residence"]);
            $DB->AddValue("operational_experience", $_POST["edit_operational_experience"]);
            $DB->AddValue("additional_information", $_POST["edit_additional_information"]);
            $DB->AddValue("operating_schedule", $_POST["edit_operating_schedule"]);
            $DB->AddValue("place_of_employment", $_POST["edit_place_of_employment"]);
            $DB->AddCondFS("id_people", "=", $id);
            if ($DB->Update())
                $this->output["messages"]["good"][] = "Данные выпускника успешно отредактированы";
        }

            $DB->SetTable("nsau_people", "p");
            $DB->AddTable("nsau_specialities", "sp");
            $DB->AddTable("nsau_groups", "g");
            $DB->AddTable("nsau_faculties", "f"); 
            $DB->AddField("p.id");
            $DB->AddField("p.last_name"); 
            $DB->AddField("p.name"); 
            $DB->AddField("p.patronymic");
            $DB->AddField("p.male");
            $DB->AddField("f.name", "faculty"); 
            $DB->AddField("sp.name", "spec");
            $DB->AddField("g.name", "group"); 
            $DB->AddCondFS("p.id", "=", $id);
            $DB->AddCondFF("p.id_group","=","g.id"); 
            $DB->AddCondFF("p.id_speciality","=","sp.code");
            $DB->AddCondFF("f.id","=","g.id_faculty");
            $res_graduaty_view=$DB->Select();
            while ($row = $DB->FetchAssoc($res_graduaty_view)) {
                $this->output["graduaty_edit_view"][] = $row;
            }
        
        $DB->SetTable("nsau_graduaties");
        $DB->AddCondFS("id_people", "=", $id);
        $res = $DB->Select();
        
        if ($row = $DB->FetchAssoc($res)) {
            $this->output["graduaties_edit"][] = $row;
            
            $DB->SetTable("nsau_specialities");
            $DB->AddField("code");
            $DB->AddField("name");
            $res_spec = $DB->Select();
            
            while ($row = $DB->FetchAssoc($res_spec)) {
                $this->output["specialities"][] = $row;
            }
            
        }
        else
            $this->output["messages"]["bad"][] = "Вы ввели неверные данные";
    }
    
    function graduaties($params) 
    {
        global $DB, $Engine, $Auth;
        
        require_once INCLUDES . "Pager.class";
        
        $DB->SetTable("nsau_graduaties", "ng");
        $DB->AddTable("nsau_people", "np");
        $DB->AddTable("nsau_faculties", "nf");
        $DB->AddTable("nsau_specialities", "st");
        $DB->AddTable("nsau_groups", "g");
        $DB->AddField("ng.id_people"); 
        $DB->AddField("np.last_name");
        $DB->AddField("np.name");
        $DB->AddField("g.id");
        $DB->AddField("np.patronymic");
        $DB->AddField("st.name", "name_st");
        $DB->AddField("nf.name", "name_ft");
        $DB->AddField("ng.type_tuition");
        $DB->AddField("ng.average_grades");
        $DB->AddField("ng.theme");
        $DB->AddCondFF("np.id_group","=","g.id"); 
        $DB->AddCondFF("nf.id","=","g.id_faculty");
        $DB->AddCondFF("np.id","=","ng.id_people");
        $DB->AddCondFF("st.code","=","np.id_speciality"); 
        $res = $DB->Select();
        $num = 0;
        
        while ($row = $DB->FetchAssoc($res)) {
            $num++;
        }
        
        $parts = explode(";", $params);
        $per_page_input = (isset($parts[0]) && $parts[0]) ? $parts[0] : "50"; // строка настройки для Pager
        
        $Pager = new Pager($num, $per_page_input);
        $this->output["pager_output"] = $result = $Pager->Act();
        
        $DB->SetTable("nsau_graduaties", "ng");
        $DB->AddTable("nsau_people", "np");
        $DB->AddTable("nsau_faculties", "nf");
        $DB->AddTable("nsau_specialities", "st");
        $DB->AddTable("nsau_groups", "g");
        $DB->AddField("ng.id_people"); 
        $DB->AddField("np.last_name");
        $DB->AddField("np.name");
        $DB->AddField("g.id");
        $DB->AddField("np.patronymic");
        $DB->AddField("st.name", "name_st");
        $DB->AddField("nf.name", "name_ft");
        $DB->AddField("ng.type_tuition");
        $DB->AddField("ng.average_grades");
        $DB->AddField("ng.theme");
        $DB->AddField("ng.year_out");
        $DB->AddField("ng.operational_experience");
        $DB->AddCondFF("np.id_group","=","g.id"); 
        $DB->AddCondFF("nf.id","=","g.id_faculty");
        $DB->AddCondFF("np.id","=","ng.id_people");
        $DB->AddCondFF("st.code","=","np.id_speciality");  
        $res = $DB->Select($result["db_limit"], $result["db_from"]); 
        
        while ($row = $DB->FetchAssoc($res)) {
            $this->output["graduaties"][] = $row;
        } 
        
        if ($Engine->OperationAllowed(11, "graduaties.handle", 0, $Auth->usergroup_id)) 
            $this->output["show_manage"] = 1;
        else
            $this->output["show_manage"] = 0;
    }
    
    function Output()
    {
        return $this->output;
    }
    
}