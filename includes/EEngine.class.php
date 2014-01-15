<?php

class EEngine
// version: 3.3.6
// date: 2012-08-07
{
	var $debug_level;        // ������� �������
	var $root_folder_id;     // �������� �����

	var $folder_id;          // ID ������� �����
	var $uri;                // ������ ������� URI
	var $unqueried_uri;      // URI ��� GET-������ (�� ������� ��������������� �����)
	var $engine_uri;         // URI, ����������� � ���� ������
	var $module_uri;         // URI, ����������� � ������

	var $folder_nodes;       // ����������� ��� ��� ������� �����
	var $nodes_folder_id;    // ID �����, ��� ������� ����������� � ��������� ���
	var $parser_node_id;     // ID ���-�����������

	var $modules_data;       // ������, ���������� ��� ���������� ������� �� ��������
	var $current_module_id;  // ID �������� ������
	var $current_node_id;    // ID ������� ���
	var $modules_privileges; // ������������� ���������� ��� �������, ������������ �� ��������

	var $redirect_to;        // ��������������� � ������� ��������
	var $main_template;      // �������� ������ ��� ������� ��������

	var $language;           // ����
	var $footsteps;          // "����" ������������ ����� �����
	var $meta_descr;         // ���������� ����-���� description
	var $keywords;           // �������� ����� ��� ����-���� keywords

	var $theme_id;           // ID ������� ����
	var $theme;              // FS-���� � ������� ����
	var $http_theme;         // HTTP-���� � ������� ����
	var $folderoptions;      // ���������� ��������� ��� ������� ��������
	
	function EEngine($debug_level = 0, $root_folder_id = 0) {
		$this->debug_level = $debug_level;
		$this->root_folder_id = $root_folder_id;
	}

	function CheckIfAjaxRequest() {
		if (isset($_REQUEST['is_ajax'])) {
			$_SERVER['REQUEST_URI'] = AJAX_URL . substr($_SERVER['REQUEST_URI'], 1);
		}
	}

	function Act($allow_parser_node = 0) {
		global $DB, $Auth, $EE;
		
		if ($_SERVER['REQUEST_URI'] != AJAX_URL) $this->CheckIfAjaxRequest();
		
		//$this->LogAction(18, "mailbox", 0, "test");
		// ������ ���-���� ����� ��� � ����� � � ���
//		$parts = parse_url($_SERVER["REQUEST_URI"]);

//		if (!$_GET && isset($parts["query"]) && $parts["query"])
//		{
//			parse_str($parts["query"], $_GET);
//		}

		// ������ ������ ������� ��� ����� � ����� �����
		$this->folder_nodes = array();

		// ��������� ������ ������� ��������
		$DB->SetTable(ENGINE_DB_PREFIX . "nodegroups");
		$DB->AddField("name");
		$DB->AddOrder("pos");
		$DB->Select();

		while (list($name) = $DB->FetchRow()) {
			$this->folder_nodes[$name] = array();
		}

		$DB->FreeRes();

		// ������ ������ ������� ��� ����� ����� � �������� ����
		$this->folderoptions = array();
		$this->keywords = array();

		// ��������� ������ ������� ���������� - ��� ������� � ��������
/*		$DB->SetTable(ENGINE_DB_PREFIX . "folderoptions");
		$DB->AddField("name");
		$DB->Select();

		while (list($name) = $DB->FetchRow())
		{
			$this->folderoptions[$name] = false;
		}

		$DB->FreeRes(); */

		// ��������� ����		
		$DB->Exec("
			SELECT `id`, `path`
			FROM `" . ENGINE_DB_PREFIX . "themes`
			WHERE `is_default`
			LIMIT 1
			", true); // !!! �������� ��� ��������, ����� ����� �������� ����������� ��� ����� ����
		list($this->theme_id, $this->theme_dir) = $DB->FetchRow();
		$DB->FreeRes();

		$this->theme = THEMES . $this->theme_dir;
		$this->http_theme = HTTP_THEMES . $this->theme_dir;
	
		// ������ ���
		$this->FullParse();
		
//die(print_r($this));

		// ���� �� �� �������� ������ ��� ��� ������� �����, ���� �������� :)
		if (!SIMPLE_NODEGROUPS_INHERITANCE || ($this->nodes_folder_id != $this->folder_id)) {
			$this->GetFolderNodes($this->folder_id, true);
		}

		// �������� �������� ���������� ���������� ��� ������� �����
		if (ALLOW_FOLDEROPTIONS) {
			$this->GetFolderOptions($this->folder_id);
		}

		// ���������� ������ � ��������� ���
		$this->ExecNodes();

		/*if (isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] == AJAX_REQUEST_MARKER) {
			foreach($this->modules_data as $nodegroup_data)
				foreach($this->nodegroup_data as $node_data)
					return $this->node_data["output"];
		}*/

		// ��������� ������ ������ ��� ������� � ��������
		$this->BuildDataArray();

		if (ALLOW_PRETEMPLATES) {
			$ptpl_module_scripts_list = array();
			$plugin_list = array();
			$ptpl_file_list = array();
			
			foreach ($this->modules_data as $NODEGROUP => $NODEGROUP_DATA) {
				foreach ($NODEGROUP_DATA as $NODE_ID => $MODULE_DATA) {
					$MODULE_NAME = $MODULE_DATA["name"];
					$MODULE_OUTPUT = $MODULE_DATA["output"];
					if(file_exists($plugin_name = $this->theme.'_plugin_scripts.tpl') && isset($MODULE_OUTPUT['plugins'])) {
						foreach($MODULE_OUTPUT['plugins'] as $plugin) {
							if(!in_array($plugin, $plugin_list)) {
								$plugin_list[] = $plugin;
								$EE['plugin'] = $plugin;
								include $plugin_name;
							}
						}
					}
					
					if(file_exists($filename = $this->theme . $MODULE_NAME . PRETEMPLATE_EXT)) {
						if(isset($MODULE_OUTPUT['scripts_mode'])) {
							if(is_array($MODULE_OUTPUT['scripts_mode'])) {
								$scripts_mode_array = $MODULE_OUTPUT['scripts_mode'];
								foreach($scripts_mode_array as $scripts_mode) {
									if (!in_array($scripts_mode/*$MODULE_NAME*/, $ptpl_module_scripts_list)/*(!isset($ptpl_module_scripts_list[$MODULE_NAME]) || !in_array($MODULE_DATA["output"]["mode"], $ptpl_module_scripts_list[$MODULE_NAME]))*/) {
										$MODULE_OUTPUT['scripts_mode'] = $ptpl_module_scripts_list[] = $scripts_mode;
										include $filename;
									}
								}
							} else {
								if (!in_array($MODULE_OUTPUT['scripts_mode']/*$MODULE_NAME*/, $ptpl_module_scripts_list)/*(!isset($ptpl_module_scripts_list[$MODULE_NAME]) || !in_array($MODULE_DATA["output"]["mode"], $ptpl_module_scripts_list[$MODULE_NAME]))*/) {
									$ptpl_module_scripts_list[] = $MODULE_OUTPUT['scripts_mode'];
									//$ptpl_module_scripts_list[] = $MODULE_NAME;
									//$ptpl_module_scripts_list[$MODULE_NAME][] = $MODULE_DATA["output"]["mode"];
									include $filename;
								}
							}
						} else {
							if (!in_array($MODULE_NAME, $ptpl_file_list)/*(!isset($ptpl_module_scripts_list[$MODULE_NAME]) || !in_array($MODULE_DATA["output"]["mode"], $ptpl_module_scripts_list[$MODULE_NAME]))*/) {
								$ptpl_file_list[] = $MODULE_NAME;
								//$ptpl_module_scripts_list[] = $MODULE_NAME;
								//$ptpl_module_scripts_list[$MODULE_NAME][] = $MODULE_DATA["output"]["mode"];
								include $filename;
							}
						}
					}
				}
				 
			}
			unset($EE['plugin']);
			unset($ptpl_file_list);
		}

		//���������� ����������� ������
		if (defined("INHERIT_TEMPLATES") && INHERIT_TEMPLATES && $this->folder_id && !$this->main_template) {
			$reached = 0;
			$current_id = $this->folder_id;
			do {
				$DB->SetTable("engine_folders");
				$DB->AddField("id");
				$DB->AddField("pid");
				$DB->AddField("main_template");
				$DB->AddCondFS("id", "=", $current_id);
				$res = $DB->Select();
				
				$row = $DB->FetchAssoc($res);
				if ($row["main_template"]) {
					$this->main_template = $row["main_template"];
					$reached = 1;
				}
				elseif (!$row["id"])
					$reached = 1;
				else
					$current_id = $row["pid"];
			}
			while (!$reached);
		}
		
		// ��������� ����
		if (!include $this->theme . ($this->main_template ? $this->main_template : DEFAULT_MAIN_TEMPLATE) . TEMPLATE_EXT) { 
			die($this->debug_level ? ("EEngine error #1002. Main template file (" . $this->theme . ($this->main_template ? $this->main_template : DEFAULT_MAIN_TEMPLATE) . TEMPLATE_EXT . ") doesn't exist or is inaccessible.") : "");
		}
		// !!! ����������� �� �� - ����� ����, ������?
		//$DB->Disconnect();
	}

	function FullParse($allow_parser_node = 0) {
		$this->ParseURI($allow_parser_node);
	
		//$this->uri = preg_replace('/\+/', '%20', $this->uri); 
//die($this->parser_node_id);
		// ���� ��� �������� ����� ������� ���������������, ��������� ���
		//if ($allow_parser_node && $this->uri) die($this->uri);
		//echo $this->uri."<br>";
	
		if (CF::IsNonEmptyStr($this->redirect_to)) { 
			CF::Redirect(CF::IsNaturalOrZeroNumeric($this->redirect_to) ? $this->FolderURIbyID($this->redirect_to) : $this->redirect_to);
		}
		
		// ���� URI �������, �������� ���������
		elseif (!CF::URIis($this->uri, NULL, true) && !CF::URIis(str_replace('+', '%20', $this->uri), NULL, true)) {
			/*if(CF::URIis(iconv("utf-8", "cp1251", urldecode($this->uri)), NULL, true)) {
				$this->uri = urldecode($this->uri);//echo iconv("utf-8", "cp1251", urldecode($this->uri))."<br>";
				//CF::Redirect($this->uri);
			}
			else*/if (CF::URIis($this->uri)
				|| CF::URIis($this->uri, $_SERVER["REQUEST_URI"] . "/") )
			// ���� URI ���������� ������ ��������� ��� ����������� �����, �������������� �� ���� �������� :)
			// (!!! ����� ����, ������� ������������������� ��������� ������?)
			{
				CF::Redirect($this->uri);
			} elseif ($this->parser_node_id && !$allow_parser_node)
			//���� �������� ����� ������������ - ���������, ���� �� ���������� ���������� � ������ ������ � ����������� �����������
			{ 
				$this->FullParse(1);
			} else
			// ����� ������ 404
			{ 
				$this->HTTP404();
			}
		}  
	}

	function ParseURI($allow_parser_node = 0) {
		global $DB, $Auth;
		$this->engine_uri = HTTP_ROOT;
		$this->current_module_id = 0;
		$this->current_node_id = 0;
		$this->modules_privileges = array();

		$parts = explode("/", substr($_SERVER["REQUEST_URI"], strlen(HTTP_ROOT) - 1)); // !!! �����������, ������ ��� �������� ������������ ������ �����
		$this->folder_id = NULL;
		$this->footsteps = array();
		
		//����������, ������� �� ����������� ��� ��������� ����� �������� ���������� ����������
		$pid = 0;
		$parsers_sum = 0; //����� ��������� � URL ����� � ����������� �������������
		$parsers_count = 0; //������� ����� �����, ����� ����������� ������������
		
		foreach ($parts as $key => $folder_name) {
			if ($folder_name) {
				$DB->SetTable(ENGINE_DB_PREFIX . "folders");
				$DB->AddCondFS("uri_part", "=", $folder_name);
				$DB->AddCondFS("pid", "=", $pid);
				$res = $DB->Select();
				
				if ($row = $DB->FetchAssoc($res)) {
					$pid = $row["id"];
					if ($row["parser_node_id"])
						$parsers_sum++;
				}
			}
		}
		$q=0;
		foreach ($parts as $key => $folder_name) {
			if (($flag = is_null($this->folder_id)) || $folder_name) {
				$DB->SetTable(ENGINE_DB_PREFIX . "folders");
				$DB->AddFields(array("id", "uri_part", "title", "descr", "language", "meta_descr", "keywords", "redirect_to", "main_template", "parser_node_id", "transmit_nodes", "transmit_folderoptions"));
				if ($allow_parser_node)
					$parser_node_id = $this->parser_node_id;
				if ($flag) { // ����������� ������ ��� �������� �����
					$DB->AddCondFS("id", "=", $this->root_folder_id);
				} else { // ����������� ������ ��� ���������� �����
					if (iconv("utf-8", "cp1251", urldecode($folder_name)) == "�����") die(iconv("utf-8", "cp1251", urldecode($folder_name)));
					$DB->AddCondFS("pid", "=", $this->folder_id);
					$DB->AddCondFS("uri_part", "LIKE", iconv("utf-8", "cp1251", urldecode($folder_name)));
					$DB->AddCondFP("is_active");
				}
				/*if ($folder_name != "cit" && $folder_name != "about" && $folder_name != "department")
					die($DB->SelectQuery());*/
				$selectq = $DB->SelectQuery();
				$DB->Select(1);
				
				if ($row = $DB->FetchObject()) {
					$DB->FreeRes();
					$row->id = (int)$row->id;

					if (!$this->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id)) {
						$this->HTTP403();
					}

					$this->folder_id = $row->id;

					if (!$flag) {// ������ � footsteps �� �����
						$this->engine_uri .= $row->uri_part . "/";

						$this->AddFootstep(
							$this->engine_uri,
							$row->title,
							$row->descr,
							true,
							!$this->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id),
							0,
							$row->id
							);
					}

					if ($row->language) {
						$this->SetLanguage($row->language);
					}

					if ($row->meta_descr) {
						$this->SetMetaDescr($row->meta_descr);
					}

					if ($row->keywords) {
						$this->AddKeywords($row->keywords);
					}

					$this->redirect_to = $row->redirect_to;
					$this->main_template = $row->main_template;

					if ($row->transmit_nodes) {
						$this->GetFolderNodes($row->id);
					}

					if ($row->parser_node_id) {												
						if ($parsers_count < $parsers_sum - 1) {  //����� ������������ ���� ��������� �� ��������
							$parsers_count++;
							continue;
						}
						
						if (!isset($parser_node_id) || !$parser_node_id)
							$this->parser_node_id = $row->parser_node_id;
							
						if ($allow_parser_node) { //���� ��������� ������������ ���������� ���������� (��� �������� ����� � ����� ������)
							$array = explode("?", implode("/", array_slice($parts, $key + 1)));
							$this->module_uri = $array[0];
							break;
						}
					}
					
					if ($row->transmit_folderoptions) {
						$this->GetFolderOptions($row->id, true);
					}
				} else {	$q++;
					//if ($q>0) die($selectq);
					$DB->FreeRes();
					break;
				}
			}
		}

		$this->unqueried_uri = $this->engine_uri . $this->module_uri;
		$this->uri = $this->unqueried_uri . ($_GET ? ("?" . http_build_query($_GET)) : "");
		//$this->uri = iconv("utf-8", "cp1251", urldecode($this->uri));
	}

	function HTTP403() {
		$this->LogAccessError(403);
		include_once $this->theme . HTTP403_TEMPLATE . TEMPLATE_EXT;
		exit();
	}

	function HTTP404() {
		$this->LogAccessError(404);
		//die(print_r($this));
		include_once $this->theme . HTTP404_TEMPLATE . TEMPLATE_EXT;
		exit();
	}

	function AddFootstep($uri, $title, $descr = "", $is_folder = true, $is_hidden = false, $module_id = 0, $folder_id = false) {
		if(!$folder_id) {
			$this->footsteps[] = array(
				"uri" => $uri,
				"title" => $title,
				"descr" => $descr,
				"is_folder" => $is_folder,
				"is_hidden" => $is_hidden,
				"module_id" => $module_id,
			);
		} else {
			$this->footsteps[] = array(
				"uri" => $uri,
				"title" => $title,
				"descr" => $descr,
				"is_folder" => $is_folder,
				"is_hidden" => $is_hidden,
				"folder_id" => $folder_id,
				"module_id" => $module_id,
			);
		}
	}

	function SetLanguage($input) {
		if ($input) {
			$this->language = $input;
		}
	}

	function SetMetaDescr($input) {
		if ($input) {
			$this->meta_descr = $input;
		}
	}

	function AddKeywords($input) {
		if (is_string($input)) {
			$input = CF::BreakByLbr($input);
		}

		if (is_array($input)) {
			if (APPEND_KEYWORDS === "before") {
				$input = array_merge($input, $this->keywords);
			} elseif (APPEND_KEYWORDS === "after") {
				$input = array_merge($this->keywords, $input);
			}

			$this->keywords = $input;
		}
	}

	function LogAccessError($code) {
		global $DB, $Auth;
		$DB->SetTable(ENGINE_DB_PREFIX . "access_errors");
		$DB->AddValue("time", "NOW()", "X");
		$DB->AddValues(array(
			"code" => $code,
			"requested_uri" => $_SERVER["REQUEST_URI"],
			"module_id" => $this->current_module_id,
			"node_id" => $this->current_node_id,
			"ip" => $Auth->ip,
			"referrer" => isset($_SERVER["HTTP_REFERER"]) ? htmlspecialchars($_SERVER["HTTP_REFERER"]) : "",
			"ua" => $Auth->ua,
			"user_id" => $Auth->user_id,
			"usergroup_id" => $Auth->usergroup_id,
			));
		$DB->Insert();
	}

	function GetFolderNodes($folder_id, $final_folder = false) {
		global $DB;

		if (!SIMPLE_NODEGROUPS_INHERITANCE && !$final_folder) {
			$inherited_nodegroups = array();

			$DB->SetTable(ENGINE_DB_PREFIX . "nodegroups_inheritance");
			$DB->AddField("nodegroup_id");
			$DB->AddCondFS("folder_id", "=", $folder_id);
			$DB->Select();

			while (list($row) = $DB->FetchRow()) {
				$inherited_nodegroups[] = $row;
			}
			
			$DB->FreeRes();
		}

		$array = array();

		$DB->SetTable(ENGINE_DB_PREFIX . "nodes");
		$DB->AddJoin(ENGINE_DB_PREFIX . "nodegroups.id", ENGINE_DB_PREFIX . "nodes.nodegroup_id");
		$DB->AddFields(array("id", ENGINE_DB_PREFIX . "nodegroups.name", ENGINE_DB_PREFIX . "nodes.nodegroup_id", "module_id"));
		$DB->AddCondFS("folder_id", "=", $folder_id);
		$DB->AddCondFP("is_active");
		$DB->AddOrder("pos");
		$DB->Select();

		while ($row = $DB->FetchObject()) {
			if (SIMPLE_NODEGROUPS_INHERITANCE) {
				if ($row->module_id) {
					$array[$row->name][] = $row->id;
				} else { // � ������, ���� � ����� ���������� ����-����, ������� ��������������� ������ ��� �����
					$this->folder_nodes[$row->name] = array();
				}
			} elseif ($final_folder || in_array($row->nodegroup_id, $inherited_nodegroups)) {
				if ($row->module_id) {
					$array[$row->name][] = (int)$row->id;
				} else { // � ������, ���� � ����� ���������� ����-����, ������� ��������������� ������ ��� �����
					$array[$row->name] = array();
				}

			}
		}

		$DB->FreeRes();
		
		foreach ($array as $key => $elem) {
			$this->folder_nodes[$key] = $elem;
		}

		$this->nodes_folder_id = $folder_id;
	}

	function GetFolderOptions($folder_id, $transmitted_only = false) {
		global $DB;
		$array = array();

		$DB->SetTable(ENGINE_DB_PREFIX . "folderoptions_values");
		$DB->AddFields(array(ENGINE_DB_PREFIX . "folderoptions.name", "value"));
		$DB->AddJoin(ENGINE_DB_PREFIX . "folderoptions.id", ENGINE_DB_PREFIX . "folderoptions_values.folderoption_id");
		$DB->AddCondFS("folder_id", "=", $folder_id);
		$DB->AddAltFS("theme_id", "=", $this->theme_id);
		$DB->AddAltFS("theme_id", "=", -1);
		$DB->AppendAlts();

		if ($transmitted_only) {
			$DB->AddAltFP("transmit");
		}

		$DB->Select();

		while ($row = $DB->FetchObject()) {
			$array[$row->name] = $row->value;
		}

		$DB->FreeRes();

		foreach ($array as $key => $elem) {
			$this->folderoptions[$key] = $elem;
		}
	}

	function ResetAdminUser() {
		global $Auth, $ADMIN_USER;
		$ADMIN_USER = ($Auth->usergroup_id == ADMIN_USERGROUP_ID);
	}

	function GetPrivileges($module_id = NULL, $operation_name = NULL, $entry_id = NULL, $usergroup_id = NULL) {
		global $DB;
		$array = array(); // ������ ��� ��������� ��������
		$DB->Init(true);
		$DB->SetTable(ENGINE_DB_PREFIX . "privileges");
		$DB->AddFields(array("module_id", "operation_name", "entry_id", "usergroup_id", "is_allowed"));

		if (!is_null($module_id)) {
			$DB->AddCondFS("module_id", "=", $module_id);
		}

		if (!is_null($operation_name)) {
			$DB->AddCondFS("operation_name", "=", $operation_name);
		}

		if (!is_null($entry_id)) {
			if ($entry_id != -1) {
				$DB->AddAltFS("entry_id", "=", $entry_id);
			}

			$DB->AddAltFS("entry_id", "=", -1);
			$DB->AppendAlts();
		}

		if (!is_null($usergroup_id)) {
			if ($usergroup_id != -1) {
				$DB->AddAltFS("usergroup_id", "=", $usergroup_id);
			}

			$DB->AddAltFS("usergroup_id", "=", -1);
			$DB->AppendAlts();
		}

		$DB->Select();

		while ($row = $DB->FetchObject()) { // ����������� "�����" ������ �� ��
			$array[] = array(
				"module_id" => (int)$row->module_id,
				"operation_name" => $row->operation_name,
				"entry_id" => (int)$row->entry_id,
				"usergroup_id" => (int)$row->usergroup_id,
				"is_allowed" => (bool)$row->is_allowed,
				);
		}

		$DB->FreeRes();
//if ($operation_name == 'folder.subFolders.handle') die(print_r($entry_id));
		$array = $this->OrganizePrivileges($array, "module_id");
		$array = $this->OrganizePrivileges($array, "operation_name");
		$array = $this->OrganizePrivileges($array, "entry_id");
		$array = $this->OrganizePrivileges($array, "usergroup_id", true);
		
		return $array;
	}

	function OrganizePrivileges($input, $by_key, $final_mode = false) {
		if (is_array($input)) {
			$output = array();

			foreach ($input as $key => $elem) {
				if (is_array($elem) && isset($elem[$by_key])) {
					$array = $elem;
					unset($array[$by_key]);
					$output[$elem[$by_key]][] = $array;

					if ($final_mode) {
						$output[$elem[$by_key]] = $output[$elem[$by_key]][0]["is_allowed"];
					}
				} elseif (is_array($elem)) {
					$output[$key] = $this->OrganizePrivileges(
						$elem,
						$by_key,
						$final_mode
						);
				} else {
					$output[] = $elem;
				}
			}
		} else {
			$output = $input;
		}
		return $output;
	}

	function ModuleOperationAllowed($operation_name, $entry_id = -1) {
		return $this->OperationAllowed(
			$this->current_module_id,
			$operation_name,
			$entry_id,
			$GLOBALS["Auth"]->usergroup_id,
			$this->modules_privileges
			);
	}

	/* �������� ���� */
	function OperationAllowed($module_id, $operation_name, $entry_id = -1, $usergroup_id = -1, $privileges = NULL) {
		global $ADMIN_USER;
//if ($operation_name == 'folder.subFolders.handle') die($module_id.'...'.$operation_name.'...'.$entry_id.'...'.$usergroup_id.'...');
		
		if (is_null($privileges)) {
			$privileges = $this->GetPrivileges($module_id, $operation_name, $entry_id, $usergroup_id);
		}
//die(print_r($usergroup_id));
		if (!isset($privileges[$module_id][$operation_name])) {
			// ���������� ����� �� ���� ���������� �� ������ ��������
			//die($this->debug_level ? "EEngine error #1003. Unknown privilege '$operation_name' for module #$module_id." : "");
			return 0;
		} elseif ($ADMIN_USER) {
			// ������������-�����
			return true;
		} elseif (isset($privileges[$module_id][$operation_name][$entry_id][$usergroup_id])) {
			// ������ ���������� ������� ������ (����������)
			return $privileges[$module_id][$operation_name][$entry_id][$usergroup_id];
		} elseif (isset($privileges[$module_id][$operation_name][$entry_id][-1]) || isset($privileges[$module_id][$operation_name][-1][$usergroup_id])) {
			// ������ ���������� ������� ������ (������������)
			$is_allowed = true;

			if (isset($privileges[$module_id][$operation_name][$entry_id][-1])) {
				$is_allowed = $is_allowed && $privileges[$module_id][$operation_name][$entry_id][-1];
			}

			if (isset($privileges[$module_id][$operation_name][-1][$usergroup_id])) {
				$is_allowed = $is_allowed && $privileges[$module_id][$operation_name][-1][$usergroup_id];
			}

			return $is_allowed;
		} elseif (isset($privileges[$module_id][$operation_name][-1][-1])) {
			// ������ ���������� �������� ������ (�����)
			return $privileges[$module_id][$operation_name][-1][-1];
		} else {
			// ���������� ����� �� ���� ���������� ���������� �� ������ ��������
			die($this->debug_level ? "EEngine error #1003. Privilege '$operation_name' for module #$module_id." : "");
		}
	}

	function ExecNodes() {
		global $DB, $Auth;

		$this->modules_data = array();
		//die(print_r($this->folder_nodes));
		
		foreach ($this->folder_nodes as $key => $data) {
			$this->modules_data[$key] = array(); // ����� ������ ������ ���� ����������

			foreach ($data as $node_id) {
				if ($this->OperationAllowed(0, "node.view", $node_id, $Auth->usergroup_id)) {
					$DB->SetTable(ENGINE_DB_PREFIX . "nodes");
					$DB->AddJoin(ENGINE_DB_PREFIX . "modules.id", "module_id");
					$DB->AddFields(array("module_id", ENGINE_DB_PREFIX . "modules.name", "params", ENGINE_DB_PREFIX . "modules.global_params"));
					$DB->AddCondFS("id", "=", $node_id);
					//echo $DB->SelectQuery();
					$DB->Select(1);

					if ($DB->NumRows()) {
						$row = $DB->FetchObject();
						$DB->FreeRes();

						$this->current_module_id = $row->module_id = (int)$row->module_id;
						$this->current_node_id = $node_id;

						if (!isset($this->modules_privileges[$this->current_module_id])) {
							$this->modules_privileges += $this->GetPrivileges(
								$this->current_module_id,
								NULL,
								NULL,
								$Auth->usergroup_id
								);
						}

						if (!include_once INCLUDES . $row->name . CLASS_EXT) {
							die($this->debug_level ? ("EEngine error #1001: " . $row->name . " module file (" . INCLUDES . $row->name . CLASS_EXT . ") doesn't exist or is inaccessible.") : "");
						}
//echo $row->params."<br>";

						$Module = new $row->name(
							$row->global_params,
							$row->params,
							$this->current_module_id,
							$this->current_node_id,
							$this->module_uri
							);

						$this->modules_data[$key][$node_id] = array(
							"name" => $row->name,
							"output" => $Module->Output()
							);
					} else {
						$DB->FreeRes();
					}
				}
			}
		}
	}

	function BuildDataArray() {
		$GLOBALS["EE"] = array(
			"folder_id" => $this->folder_id,                    // ID ������� �����
			"engine_uri" => $this->engine_uri,                  // URI, ����������� � ����
			"unqueried_uri" => $this->unqueried_uri,            // URI ��� GET-������ (�� ������� ��������������� �����)

			"footsteps" => $this->footsteps,                    // "����" ���������� ������������ ����� �����
			"language" => $this->language,                      // ���� ��������
			"meta_descr" => $this->meta_descr,                  // ���������� ����-���� description
			"meta_keywords" => implode(                         // ���������� ����-���� keywords
				KEYWORDS_GLUE,
				$this->keywords
				),
			"head_extra" => array(),                            // �������������� ���������� ���������� head (������ ����� - ��������, ��� CSS-������)
			"head_extra2" => array(),                           // �������������� ���������� ���������� head (������ ����� - ��������, ��� JS-������)
			"body_attribs" => array(),                          // �������� ���� body

			"theme" => $this->theme,                            // FS-���� � ������� ����
			"images" => $this->theme . IMAGES_DIR,              // FS-���� � ����� ����������� ������� ����
			"http_theme" => $this->http_theme,                  // HTTP-���� � ������� ����
			"http_images" => $this->http_theme . IMAGES_DIR,    // HTTP-���� � ����� ����������� ������� ����
			"http_styles" => $this->http_theme . STYLES_DIR,    // HTTP-���� � ����� ������ ������� ����
			"http_scripts" => $this->http_theme . SCRIPTS_DIR,  // HTTP-���� � ����� �������� ������� ����

			"folderoptions" => $this->folderoptions,            // ���������� ��������� ��������
			"modules_data" => $this->modules_data,              // ������, ���������� ��� ���������� ������� �� ��������
			);
	}

	function EngineURI($footsteps = NULL, $add_http_root = true, $uripart_mode = false) {
		if (!is_array($footsteps)) {
			$footsteps = $this->footsteps;
		}

		$output = $add_http_root ? HTTP_ROOT : "";

		foreach ($footsteps as $data) {
			if (!$data["module_id"]) {
				$output = $uripart_mode ? ($output . $data["uri_part"] . "/") : $data["uri"];
			}
		}

		return $output;
	}

	function FolderURIbyID($id = NULL, $add_http_root = true) {
		global $DB;
		$id = CF::Seek4NaturalOrZeroNumeric($id, $this->folder_id);

		$footsteps = array();

		while ($id != $this->root_folder_id && $id > 0) {
			$DB->Exec("
				SELECT `pid`, `uri_part`
				FROM `" . ENGINE_DB_PREFIX . "folders`
				WHERE `id` = '$id'
				LIMIT 1
				", true);

			if ($row = $DB->FetchObject()) {
				$DB->FreeRes();
				$footsteps[] = array(
					"uri_part" => $row->uri_part,
					"module_id" => 0,
					);
				$id = (int)$row->pid;
			} else {
				$DB->FreeRes();
				return false;
			}
		}
		return $this->EngineURI(array_reverse($footsteps), $add_http_root, true);
	}

	function FolderDataByID($id, $add_http_root = true) {
		if (CF::IsNaturalOrZeroNumeric($id)) {
			global $DB;
			$path = array();
			$title = NULL;
			$descr = NULL;
			$uri = $this->FolderURIbyID($id, $add_http_root);

			while ($id != -1) { // ����� ��� �������� ����� ������ ����� ���� ���������
				$DB->Exec("
					SELECT `id`, `pid`, `uri_part`, `title`, `descr`
					FROM `" . ENGINE_DB_PREFIX . "folders`
					WHERE `id` = '$id'
					LIMIT 1
					", true);

				if ($row = $DB->FetchObject()) {
					$DB->FreeRes();

					if ($row->id) {
						$DB->FreeRes();
						$footsteps[] = array(
							"uri_part" => $row->uri_part,
							"module_id" => 0,
							);
						$id = (int)$row->pid;
					} else {
						$id = -1;
					}

					if (is_null($title)) {
						$title = $row->title;
					}

					if (is_null($descr)) {
						$descr = $row->descr;
					}
				} else {
					$DB->FreeRes();
					return false;
				}
			}

			return array(
				"uri" => $uri,
				"title" => $title,
				"descr" => $descr
				);
		}
	}

	function ListSubfolders($pid, $flat = false, $include_pid = false) {
		global $DB, $Auth;
		$output = array();

		if ($flat && $include_pid) { // !!! ��������, ��� ������, ���� !$flat
			$output[] = (int)$pid;
		}
		
		$res = $DB->Exec("
			SELECT `id`, `title`, `is_active`
			FROM `" . ENGINE_DB_PREFIX . "folders`
			WHERE `pid` = '" . $DB->Escape($pid) . "' AND `pid` != `id`
			", true);	

		while ($row = $DB->FetchObject($res)) {			
			if ($this->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id) && $this->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id) && $row->is_active == 1) {
				if ($flat) {					
					$output[] = (int)$row->id;
					$output = array_merge($output, $this->ListSubfolders($row->id, true));					
				} else {
					$output[$row[0]] = array(
						"subitems" => $this->ListSubfolders($row->id, false),
						);
				}
			}
		}

		$DB->FreeRes($res);
		return $output;
	}

	function LogAction($module_id, $entry_type, $entry_id, $action, $username = NULL, $user_id = NULL, $usergroup_id = NULL, $ip = NULL) {
		global $DB, $Auth;
		$username = CF::Seek4NonEmptyStr($username, $Auth->username);
		$user_id = CF::Seek4NaturalOrZeroNumeric($user_id, $Auth->user_id);
		$usergroup_id = CF::Seek4NaturalOrZeroNumeric($usergroup_id, $Auth->usergroup_id);
		$ip = CF::Seek4NonEmptyStr($ip, $Auth->ip);

		$DB->SetTable(ENGINE_DB_PREFIX . "actions_log");
		$DB->AddValue("time", "NOW()", "X");
		$DB->AddValues(array(
            "module_id" => $module_id,
			"entry_type" => $entry_type,
			"entry_id" => $entry_id,
			"action" => $action,
			"username" => $username,
			"user_id" => $user_id,
			"usergroup_id" => $usergroup_id,
			"ip" => $ip,
			));
		return $DB->Insert();
	}
	
	function GetLogs($action, $entry_type = 0, $entry_id = 0, $user_id = 0, $ret_count = 0) {
		global $DB;
		$DB->SetTable("engine_actions_log");
		$DB->AddCondFS("action", "=", $action);
		if ($entry_id)
			$DB->AddCondFS("entry_id", "=", $entry_id);
		if ($user_id)
			$DB->AddCondFS("user_id", "=", $user_id);
		if ($entry_type)
			$DB->AddCondFS("entry_type", "=", $entry_type);
		$DB->AddOrder("time", "desc");
		
		if ($ret_count) {
			$DB->AddExp("count(DISTINCT ip)", "xcount");
			$res = $DB->Select();
			$row = $DB->FetchAssoc($res);
			$result = $row["xcount"];
		}
		else {
			while ($row = $DB->FetchAssoc($res))
				$result[] = $row;
		}
		
		return $result;
	}
	
	function AddPrivilege($module_id, $operation_name, $entry_id, $usergroup_id, $is_allowed, $comment) {
		global $DB, $Auth;
		//die($operation_name);
		if (!($this->OperationAllowed($module_id, $operation_name, $entry_id, $usergroup_id) && $is_allowed) || !(!$this->OperationAllowed($module_id, $operation_name, $entry_id, $usergroup_id) && !$is_allowed)) {
			$DB->SetTable("engine_privileges");
			$DB->AddCondFS("module_id", "=", $module_id);
			$DB->AddCondFS("operation_name", "=", $operation_name);
			$DB->AddCondFS("entry_id", "=", $entry_id);
			$DB->AddCondFS("usergroup_id", "=", $usergroup_id);
			//$DB->AddCondFS("is_allowed", "=", $is_allowed);
			$res = $DB->Select();
			if(($row = $DB->FetchObject($res))) {
				$DB->SetTable("engine_privileges");
				$DB->AddCondFS("module_id", "=", $module_id);
				$DB->AddCondFS("operation_name", "=", $operation_name);
				$DB->AddCondFS("entry_id", "=", $entry_id);
				$DB->AddCondFS("usergroup_id", "=", $usergroup_id);
				$DB->Delete();
			}
			//if(!($row = $DB->FetchObject($res))) {
				$DB->SetTable("engine_privileges");
				$DB->AddValue("module_id", $module_id);
				$DB->AddValue("operation_name", $operation_name);
				$DB->AddValue("entry_id", $entry_id);
				$DB->AddValue("usergroup_id", $usergroup_id);
				$DB->AddValue("is_allowed", $is_allowed);
				if ($DB->Insert()) {
					$this->LogAction($module_id, "privilege", $entry_id, $comment);
					return true;
				}
			//}			
		}		
		return false;
	}
	
	function DeletePrivilege($module_id, $operation_name, $entry_id, $usergroup_id, $is_allowed) {
		global $DB, $Auth;
		
		$DB->SetTable("auth_usergroups");
		$DB->AddCondFS("id", "=", $usergroup_id);
		$res = $DB->Select();
		
		$row = $DB->FetchAssoc($res);
		
		$DB->SetTable("engine_privileges");
		$DB->AddCondFS("module_id", "=", $module_id);
		$DB->AddCondFS("operation_name", "=", $operation_name);
		$DB->AddCondFS("entry_id", "=", $entry_id);
		$DB->AddCondFS("usergroup_id", "=", $usergroup_id);
		$DB->AddCondFS("is_allowed", "=", $is_allowed);
		
		$do = $is_allowed ? "����������" : "������";

		if ($DB->Delete()) {
			$this->LogAction($module_id, "disallow", $entry_id, $operation_name.";".$usergroup_id);
			return true;
		} else {
			return false;
		}
	}
}

?>