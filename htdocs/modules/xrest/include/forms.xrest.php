<?php
	
	
	function xrest_admin_form_select_table($tbl_id) {
		xoops_loadLanguage('forms', 'xrest');
		$tables_handler = xoops_getmodulehandler('tables', 'xrest');
		$criteria = new Criteria('`view`', '0');
		
		$form_sel = new XoopsThemeForm(_XREST_FRM_SELECTTABLE, "seltable", $_SERVER['PHP_SELF'] ."");
		$form_sel->setExtra( "enctype='multipart/form-data'" ) ;
		
		$table_sel = new XoopsFormSelect(_XREST_FRM_SELECTTABLE_SELECT, 'select');
		$table_sel->setExtra('onchange="window.location=\''.XOOPS_URL.'/modules/xrest/admin/\'+this.options[this.selectedIndex].value"');
		foreach($tables_handler->getObjects($criteria, true) as $tblid => $table) {
			$table_sel->addOption("index.php?op=fields&tbl_id=".$tblid, $table->getVar('tablename'));
			if ($tbl_id == $tblid)
				$table_sel->setValue("index.php?op=fields&tbl_id=".$tblid);
		}
		$form_sel->addElement($table_sel);
		return $form_sel->render();
			
	}
	
	function xrest_admin_form_select_fields($tbl_id) {
		xoops_loadLanguage('forms', 'xrest');
		$tables_handler = xoops_getmodulehandler('tables', 'xrest');
		$fields_handler = xoops_getmodulehandler('fields', 'xrest');
		
		$table = $tables_handler->get($tbl_id);
		
		if (!is_object($table)) {
			redirect_header(XOOPS_URL.'/modules/xrest/admin/index.php?op=tables', 10, _XREST_AM_MSG_NEEDTOSAVETABLES_FIRST);
			exit;
		}

		$fields = $fields_handler->getFieldFromTable($table->getVar('tablename'));
		
		$form_fld = new XoopsThemeForm(sprintf(_XREST_FRM_FIELDOPTIONSFOR, $table->getVar('tablename')), "fields", $_SERVER['PHP_SELF'] ."");
		$form_fld->setExtra( "enctype='multipart/form-data'" ) ;
		
		$field=0;
		$ele_tray = array();
		
		foreach($fields as $field => $fieldinfo){
			$int = 0;
			$string = 0;
			$float = 0;
			$text = 0;
			$other = 0;		
			$key = 0;
			if (strpos(' '.$fieldinfo->getVar('Type'),'int')>0){
				$int = 1;
			} elseif (strpos(' '.$fieldinfo->getVar('Type'),'char')>0){
				$string = 1;
			} elseif (strpos(' '.$fieldinfo->getVar('Type'),'float')>0||strpos(' '.$fieldinfo->getVar('type'),'real')>0){
				$float = 1;
			} elseif (strpos(' '.$fieldinfo->getVar('Type'),'text')>0){
				$text = 1;
			} else {
				$other = 1;
			}
			
			if ($fieldinfo->getVar('Key') == "PRI"){
				$key = 1;
			}
			
			$fielddata = $fields_handler->getFieldWithNameAndTableID($fieldinfo->getVar('Field'), $tbl_id);
	
			if (!is_object($fielddata)){
				$new++;
				$ele_tray[$field] = new XoopsFormElementTray($fieldinfo->getVar('Field')._XREST_FRM_NEW,'&nbsp;',$fieldinfo->getVar('Field'));
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", "new"));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[key]", $key));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[string]", $string));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[int]", $int));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[float]", $float));			
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[text]", $text));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[other]", $other));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[fieldname]", $fieldinfo->getVar('Field')));
				
				$post[$field] = new XoopsFormRadioYN(_XREST_FRM_POST_FIELD, intval($field)."[allowpost]", $table->getVar('allowpost'));
				$retrieve[$field] = new XoopsFormRadioYN(_XREST_FRM_RETRIEVE_FIELD, intval($field)."[allowretrieve]", $table->getVar('allowretrieve'));
				$update[$field] = new XoopsFormRadioYN(_XREST_FRM_UPDATE_FIELD, intval($field)."[allowupdate]", $table->getVar('allowupdate'));
				$visible[$field] = new XoopsFormRadioYN(_XREST_FRM_VISIBLE_FIELD, intval($field)."[visible]", $table->getVar('visible'));
				$crc[$field] = new XoopsFormRadioYN(_XREST_FRM_CRC_FIELD, intval($field)."[crc]");												
	
				if ($key==1) 
					$post[$field]->setExtra('disabled="disabled"');
				elseif ($table->getVar('allowpost')==true)
					$post[$field]->setValue(1);			
				$ele_tray[$field]->addElement($post[$field]);
														
				if ($table->getVar('allowretrieve')==true)
					$retrieve[$field]->setValue(1);
				$ele_tray[$field]->addElement($retrieve[$field]);
				
				if ($key==1) 
					$update[$field]->setExtra('disabled="disabled"');
				elseif ($table->getVar('allowupdate')==true)
					$update[$field]->setValue(1);			
				$ele_tray[$field]->addElement($update[$field]);
	
				if ($table->getVar('visible')==true)
					$visible[$field]->setValue(1);
				$ele_tray[$field]->addElement($visible[$field]);
	
				if ($key==1) 
					$crc[$field]->setExtra('disabled="disabled"');
				elseif ($table->getVar('crc')==true)
					$crc[$field]->setValue(1);			
				$ele_tray[$field]->addElement($crc[$field]);
				
			} else { 
				
				$ele_tray[$field] = new XoopsFormElementTray($fieldinfo->getVar('Field'),'&nbsp;',$fieldinfo->getVar('Field'));
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", $fielddata->getVar('fld_id')));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[key]", $key));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[string]", $string));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[int]", $int));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[float]", $float));			
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[text]", $text));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[other]", $other));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[fieldname]", $fieldinfo->getVar('Field')));
						
				$post[$field] = new XoopsFormRadioYN(_XREST_FRM_POST_FIELD, intval($field)."[allowpost]", $fielddata->getVar('allowpost'));
				$retrieve[$field] = new XoopsFormRadioYN(_XREST_FRM_RETRIEVE_FIELD, intval($field)."[allowretrieve]", $fielddata->getVar('allowretrieve'));
				$update[$field] = new XoopsFormRadioYN(_XREST_FRM_UPDATE_FIELD, intval($field)."[allowupdate]", $fielddata->getVar('allowupdate'));
				$visible[$field] = new XoopsFormRadioYN(_XREST_FRM_VISIBLE_FIELD, intval($field)."[visible]", $fielddata->getVar('visible'));
				$crc[$field] = new XoopsFormRadioYN(_XREST_FRM_CRC_FIELD, intval($field)."[crc]", $fielddata->getVar('crc'));												
	
				if ($key==1) 
					$post[$field]->setExtra('disabled="disabled"');
				$ele_tray[$field]->addElement($post[$field]);
														
				$ele_tray[$field]->addElement($retrieve[$field]);
				
				if ($key==1) 
					$update[$field]->setExtra('disabled="disabled"');
				$ele_tray[$field]->addElement($update[$field]);
	
				$ele_tray[$field]->addElement($visible[$field]);
	
				if ($key==1) 
					$crc[$field]->setExtra('disabled="disabled"');
	
				$ele_tray[$field]->addElement($crc[$field]);
				 
	 		} 
			
			$form_fld->addElement(	$ele_tray[$field] );	
		} 
	 
	 	$form_fld->addElement(new XoopsFormHidden("tbl_id", $tbl_id));
	 	$form_fld->addElement(new XoopsFormHidden("op", "savefields"));
	 	$form_fld->addElement(new XoopsFormHidden("new", $new));	 
		$form_fld->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
			
		return $form_fld->render();
	}
	
	function xrest_admin_form_select_views($database) {
		xoops_loadLanguage('forms', 'xrest');
		$tables_handler = xoops_getmodulehandler('tables', 'xrest');
		$views = $tables_handler->getViewsInDatabase($database); 
	
		$ele_tray = array();
		$form_view = new XoopsThemeForm(sprintf(_XREST_FRM_VIEWSFOR, $database), "views", $_SERVER['PHP_SELF'] ."");
		$form_view->setExtra( "enctype='multipart/form-data'" ) ;
				
		foreach($views as $field => $view){
			$table = $tables_handler->getViewWithName($view->getVar('Name'));		
			if (!is_object($table)){
				$new++;
				$ele_tray[$field] = new XoopsFormElementTray(xrest_strip_prefix($view->getVar('Name'))._XREST_FRM_NEW,'&nbsp;',xrest_strip_prefix($view->getVar('Name')));
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", "new"));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[tablename]", xrest_strip_prefix($view->getVar('Name'))));
				
				$retrieve[$field] = new XoopsFormRadioYN(_XREST_FRM_RETRIEVE_VIEW, intval($field)."[allowretrieve]");
				$visible[$field] = new XoopsFormRadioYN(_XREST_FRM_VISIBLE_VIEW, intval($field)."[visible]");
	
				$ele_tray[$field]->addElement($visible[$field]);
				$ele_tray[$field]->addElement($retrieve[$field]);
				
			} else { 
	
				$ele_tray[$field] = new XoopsFormElementTray(xrest_strip_prefix($view->getVar('Name')).'','&nbsp;',xrest_strip_prefix($view->getVar('Name')));
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", $table->getVar('tbl_id')));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[tablename]", xrest_strip_prefix($view->getVar('Name'))));
				
				$retrieve[$field] = new XoopsFormRadioYN(_XREST_FRM_RETRIEVE_VIEW, intval($field)."[allowretrieve]", $table->getVar('allowretrieve'));
				$visible[$field] = new XoopsFormRadioYN(_XREST_FRM_VISIBLE_VIEW, intval($field)."[visible]", $table->getVar('visible'));
				
				$ele_tray[$field]->addElement($visible[$field]);
				$ele_tray[$field]->addElement($retrieve[$field]);
	  	
	 		} 
	
			$form_view->addElement(	$ele_tray[$field] );	 
		 } 
		 
	 	$form_view->addElement(new XoopsFormHidden("op", "saveviews"));
	 	$form_view->addElement(new XoopsFormHidden("new", $new));	 
		$form_view->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
		
		return $form_view->render();
	}
	
	function xrest_admin_form_select_plugins() {
		xoops_loadLanguage('forms', 'xrest');
		$plugins_handler = xoops_getmodulehandler('plugins', 'xrest');
		$FunctionDefine = $plugins_handler->getServerExtensions();

		$ele_tray = array();
		$form_plugin = new XoopsThemeForm(_XREST_FRM_PLUGINAVAILABLE, "plugins", $_SERVER['PHP_SELF'] ."");
		$form_plugin->setExtra( "enctype='multipart/form-data'" ) ;
		
		foreach($FunctionDefine as $field => $func) { 
			$plugin = $plugins_handler->getPluginWithFile($func);
			if (!is_object($plugin)){
				$new++;
				$ele_tray[$field] = new XoopsFormElementTray($func._XREST_FRM_NEW,'&nbsp;',$func);
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", "new"));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[plugin_name]", substr($func, 0, strlen($func)-4)));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[plugin_file]", $func));
				$active[$field] = new XoopsFormRadioYN(_XREST_FRM_ACTIVE_PLUGIN, intval($field)."[active]", false);
				$ele_tray[$field]->addElement($active[$field]);
			} else { 
				$ele_tray[$field] = new XoopsFormElementTray($func.'','&nbsp;',$func);
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", $plugin->getVar('plugin_id')));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[plugin_name]", substr($func, 0, strlen($func)-4)));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[plugin_file]", $func));
				$active[$field] = new XoopsFormRadioYN(_XREST_FRM_ACTIVE_PLUGIN, intval($field)."[active]", $plugin->getVar('active'));
				$ele_tray[$field]->addElement($active[$field]);
	   		} 
			$form_plugin->addElement(	$ele_tray[$field] );	 
		 }
 
	 	$form_plugin->addElement(new XoopsFormHidden("op", "saveplugins"));
	 	$form_plugin->addElement(new XoopsFormHidden("new", $new));	 
		$form_plugin->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
			
		return $form_plugin->render();
	}
	
	function xrest_admin_form_select_tables($database, $prefix) {
		xoops_loadLanguage('forms', 'xrest');
		$tables_handler = xoops_getmodulehandler('tables', 'xrest');
		$tables = $tables_handler->getTablesInDatabase($database, $prefix); 
	
		$ele_tray = array();
		$form_tables = new XoopsThemeForm(sprintf(_XREST_FRM_TABLESFOR, $database, $prefix), "tables", $_SERVER['PHP_SELF'] ."");
		$form_tables->setExtra( "enctype='multipart/form-data'" ) ;
				
		foreach($tables as $field => $table){
	
			$tableinfo = $tables_handler->getTableWithName(xrest_strip_prefix($table->getVar('Name')));

			if (!is_object($tableinfo)){
				$new++;
				$ele_tray[$field] = new XoopsFormElementTray(xrest_strip_prefix($table->getVar('Name'))._XREST_FRM_NEW,'&nbsp;',xrest_strip_prefix($table->getVar('Name')));
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", 'new'));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[tablename]", xrest_strip_prefix($table->getVar('Name'))));
				
				$post[$field] = new XoopsFormRadioYN(_XREST_FRM_POST_TABLE, intval($field)."[allowpost]", 0);
				$retrieve[$field] = new XoopsFormRadioYN(_XREST_FRM_RETRIEVE_TABLE, intval($field)."[allowretrieve]", 0);
				$update[$field] = new XoopsFormRadioYN(_XREST_FRM_UPDATE_TABLE, intval($field)."[allowupdate]", 0);
				$visible[$field] = new XoopsFormRadioYN(_XREST_FRM_VISIBLE_TABLE, intval($field)."[visible]", 0);
	
				$ele_tray[$field]->addElement($post[$field]);
				$ele_tray[$field]->addElement($retrieve[$field]);
				$ele_tray[$field]->addElement($update[$field]);
				$ele_tray[$field]->addElement($visible[$field]);
	
			} else {
			
				$ele_tray[$field] = new XoopsFormElementTray(xrest_strip_prefix($table->getVar('Name')).'','&nbsp;',xrest_strip_prefix($table->getVar('Name')));
				$ele_tray[$field]->addElement(new XoopsFormHidden("id[".intval($field)."]", $tableinfo->getVar('tbl_id')));
				$ele_tray[$field]->addElement(new XoopsFormHidden(intval($field)."[tablename]", xrest_strip_prefix($table->getVar('Name'))));
	
				$post[$field] = new XoopsFormRadioYN(_XREST_FRM_POST_TABLE, intval($field)."[allowpost]", $tableinfo->getVar('allowpost'));
				$retrieve[$field] = new XoopsFormRadioYN(_XREST_FRM_RETRIEVE_TABLE, intval($field)."[allowretrieve]", $tableinfo->getVar('allowretrieve'));
				$update[$field] = new XoopsFormRadioYN(_XREST_FRM_UPDATE_TABLE, intval($field)."[allowupdate]", $tableinfo->getVar('allowupdate'));
				$visible[$field] = new XoopsFormRadioYN(_XREST_FRM_VISIBLE_TABLE, intval($field)."[visible]", $tableinfo->getVar('visible'));
				
	
				$ele_tray[$field]->addElement($post[$field]);
				$ele_tray[$field]->addElement($retrieve[$field]);
				$ele_tray[$field]->addElement($update[$field]);
				$ele_tray[$field]->addElement($visible[$field]);
	
			} 
			$form_tables->addElement(	$ele_tray[$field] );	 
		 } 
	 
	 	$form_tables->addElement(new XoopsFormHidden("op", "savetables"));
	 	$form_tables->addElement(new XoopsFormHidden("new", $new));	 
		$form_tables->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
			
		return $form_tables->render();
	}