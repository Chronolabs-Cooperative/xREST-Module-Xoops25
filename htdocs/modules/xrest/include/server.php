<?php
$GLOBALS['xrestPlugin']['when'] = microtime(true);
$GLOBALS['xoopsLogger']->activated = false;

$xoopsPreload =& XoopsPreload::getInstance();
$xoopsPreload->triggerEvent('api.server.bootstrap');

$result = array();
xoops_load('xoopscache');
require_once('common.php');

// Set Globals
$module_handler = xoops_gethandler('module');
$config_handler = xoops_gethandler('config');
$GLOBALS['xrestModule'] = $module_handler->getByDirname('xrest');
$GLOBALS['xrestModuleConfig'] = $config_handler->getConfigList($GLOBALS['xrestModule']->getVar('mid')); 

// Gets Execution Mode
$mode = (isset($_REQUEST['outputmode'])?(string)$_REQUEST['outputmode']:'json');
$parser = (isset($_REQUEST['parser'])?(string)$_REQUEST['parser']:'http');
$plugin = $_REQUEST['xrestplugin'];
$GLOBALS['xrestPlugin'] = $plugin;

// Gets URI Values and POST and GET Values
$path = parse_url(XOOPS_URL.$_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (substr($path,0,1)!='\\')
	$path .= '\\' .$path;
if (substr($path,strlen($path)-1,1)!='\\')
	$path .= $path . '\\';
$request = parse_url(XOOPS_URL.$_SERVER['REQUEST_URI'], PHP_URL_QUERY);
$values = array();
parse_str($request, $values);
if (isset($_POST)) {
	foreach($_POST as $field => $value) {
		$values[$field] = $value;
	}
}
if (isset($_GET)) {
	foreach($_GET as $field => $value) {
		$values[$field] = $value;
	}
}

$xoopsPreload =& XoopsPreload::getInstance();
$xoopsPreload->triggerEvent('api.server.start', array($mode, $plugin, $path, $request, $values));

switch($mode) {
	case 'soap':
		// Loads Specific Plugin
		$plugins_handler = xoops_getmodulehandler('plugins', 'xrest');
		$pluginObj = $plugins_handler->getPluginWithName($plugin);
		if (is_object($pluginObj)) {
			require_once($GLOBALS['xoops']->path('modules/xrest/plugins/'.$pluginObj->getVar('plugin_file')));
		}
		
		// Gets the WSDL Checking Functions
		$wsdlfunc = $plugin . '_wsdl';
		$wsdlservicefunc = $plugin . '_wsdl_service';
		
		// Intialises SOAP Object
		if (function_exists($wsdlfunc))
			if ($GLOBALS['xrestModuleConfig']['wsdl']==1 && $wsdlfunc()==true){
				if (function_exists($wsdlservicefunc)) {
					if ($wsdlservicefunc()==false) {
						$server = new SoapServer(XOOPS_URL.str_replace('soap', 'wsdl', $path) . 'http.wsdl', array('uri' => XOOPS_URL.$path));
					} else {
						$server = new SoapServer(XOOPS_URL.str_replace('soap', 'wsdl', $path) . $plugin . '.service', array('uri' => XOOPS_URL.$path));
					}
				} else {
					$server = new SoapServer(XOOPS_URL.str_replace('soap', 'wsdl', $path) . $plugin . '.service', array('uri' => XOOPS_URL.$path));
				}
			} else {
				$server = new SoapServer(NULL, array('uri' => XOOPS_URL.$path));
		} else {
			$server = new SoapServer(NULL, array('uri' => XOOPS_URL.$path));
		}
		
		// Adds SOAP Function
		$server->addFunction($plugin);
		
		// Calls SOAP Handler
		$server->handle();
		break;
	case 'wsdl':
		
		if (!isset($GLOBALS['xoopsTpl']) || !is_object($GLOBALS['xoopsTpl'])) {
			include_once(XOOPS_ROOT_PATH."/class/template.php");
			$GLOBALS['xoopsTpl'] = new XoopsTpl();
		}
		
		$GLOBALS['xoopsTpl']->assign('mode', $mode);
		$GLOBALS['xoopsTpl']->assign('path', $path);
		$GLOBALS['xoopsTpl']->assign('plugin', $plugin);
		$GLOBALS['xoopsTpl']->assign('values', $values);
		
		// Loads Specific Plugin
		$plugins_handler = xoops_getmodulehandler('plugins', 'xrest');
		$pluginObj = $plugins_handler->getPluginWithName($plugin);
		if (is_object($pluginObj)) {
			require_once($GLOBALS['xoops']->path('modules/xrest/plugins/'.$pluginObj->getVar('plugin_file')));
		}
		
		if (function_exists($plugin . '_xsd_soap'))
			$xsdfunc = $plugin . '_xsd_soap';
		elseif (function_exists($plugin . '_xsd_rest'))
			$xsdfunc = $plugin . '_xsd_rest';
		elseif (function_exists($plugin . '_xsd'))
			$xsdfunc = $plugin . '_xsd';
		if (isset($xsdfunc))
			$GLOBALS['xoopsTpl']->assign('xsd', $xsdfunc($parser));
		
		$docfunc = $plugin . '_wsdl_documentation';
		if (isset($docfunc))
			$GLOBALS['xoopsTpl']->assign('plugin_document', $docfunc($parser));
		
		header('Content-Type:text/xml; charset=utf-8');
		
		switch ($parser) {
			case 'xsd':
				$GLOBALS['xoopsTpl']->display('db:plugin_xsd.xml');
				break;
			case 'wsdl':
				$GLOBALS['xoopsTpl']->display('db:plugin_wsdl.xml');
				break;
			case 'services':
				$GLOBALS['xoopsTpl']->display('db:plugin_wsdl.xml');
				break;
			default:
			case 'http':
				$GLOBALS['xoopsTpl']->display('db:wsdl.xml');
				break;
		}
		
		break;
	default:
						
		if (isset($plugin)) {

			// Load Plugin Object
			$plugins_handler = xoops_getmodulehandler('plugins', 'xrest');
			$pluginObj = $plugins_handler->getPluginWithName($plugin);
			if (is_object($pluginObj)) {
				require_once($GLOBALS['xoops']->path('modules/xrest/plugins/'.$pluginObj->getVar('plugin_file')));
				
				// Checks for Cached Result
				if ((!$result = XoopsCache::read('xrest_results_'.$plugin.'_'.md5(implode(':',$values))))&&$pluginObj->getVar('active')==true) {
					$result = array();
					$opfunc = $plugin;
					if (function_exists($plugin . '_xsd_rest'))
						$xsdfunc = $plugin . '_xsd_rest';
					elseif (function_exists($plugin . '_xsd'))
						$xsdfunc = $plugin . '_xsd';
					elseif (function_exists($plugin . '_xsd_soap'))
						$xsdfunc = $plugin . '_xsd_soap';				
					$opxsd = $xsdfunc();
					$tmp=array();
					if (!empty($opfunc)) {
						$fields=0;
						
						// Formulises Variables
						foreach($opxsd['request'] as $ii => $field) {
							if (!empty($field['items'])) {
								$tmp[$fields] = $values[$field['items']['objname']]		;
								$fields++;
							} elseif (!empty($field['name'])&&!empty($field['type'])) {
								switch($field['type']) {
								default:
								case "string":
									$tmp[$fields] = (string)$values[$field['name']];
									break;
								case "integer":
									$tmp[$fields] = (integer)$values[$field['name']];					
									break;
								case "array":
									$tmp[$fields] = (array)$values[$field['name']];
									break;
								case "object":
									$tmp[$fields] = (object)$values[$field['name']];
									break;
								case "boolean":
									$tmp[$fields] = (boolean)$values[$field['name']];
									break;
								case "float":
									$tmp[$fields] = (float)$values[$field['name']];
									break;
								case "double":
									$tmp[$fields] = (double)$values[$field['name']];
									break;
								}
								$fields++;				
							}
						}
						
						// Calls Function and Gets result from Forumulised XSD Definition
						switch($fields) {
						case 0:
							$result = $opfunc();
							break;
						case 1:
							$result = $opfunc($tmp[0]);
							break;
						case 2:
							$result = $opfunc($tmp[0], $tmp[1]);
							break;
						case 3:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2]);
							break;
						case 4:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3]);
							break;
						case 5:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4]);
							break;
						case 6:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5]);
							break;
						case 7:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6]);
							break;
						case 8:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7]);
							break;
						case 9:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8]);
							break;
						case 10:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9]);
							break;
						case 11:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10]);
							break;
						case 12:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11]);
							break;		
						case 13:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12]);
							break;		
						case 14:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13]);
							break;		
						case 15:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14]);
							break;		
						case 16:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15]);
							break;		
						case 17:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16]);
							break;		
						case 18:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16], $tmp[17]);
							break;		
						case 19:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16], $tmp[17], $tmp[18]);
							break;		
						case 20:
							$result = $opfunc($tmp[0], $tmp[1], $tmp[2], $tmp[3], $tmp[4], $tmp[5], $tmp[6], $tmp[7], $tmp[8], $tmp[9], $tmp[10], $tmp[11], $tmp[12], $tmp[13], $tmp[14], $tmp[15], $tmp[16], $tmp[17], $tmp[18], $tmp[19]);
							break;		
						}
						// Cache's Result Set
						XoopsCache::write('xrest_results_'.$plugin.'_'.md5(implode(':',$values)), $result, $GLOBALS['xrestModuleConfig']['cache_seconds']);
					}
				}	
			}
		}
		
		// Output Result Set
		switch ($mode) {
			default:
			case 'json':
				echo json_encode($result);
				break;
			case 'serial':
				echo serialize($result);
				break;
			case 'xml':
				echo xrest_toXml($result, strtolower($plugin));
				break;
				
		}
		
		// Set Cache Result Set Timing
		$GLOBALS['xrestPlugin']['took'] = microtime(true) - $GLOBALS['xrestPlugin']['when'];
		$lastplugin = XoopsCache::read('xrest_plugins_last');
		$GLOBALS['xrestPlugin']['executed'] = $lastplugin['executed']+1;
		$GLOBALS['xrestPlugin']['execution'] = $lastplugin['execution']+$GLOBALS['xrestPlugin']['took'];
		XoopsCache::write('xrest_plugins_last', $GLOBALS['xrestPlugin'], $GLOBALS['xrestModuleConfig']['run_cleanup']*2);
		break;
	}
	
	$xoopsPreload =& XoopsPreload::getInstance();
	$xoopsPreload->triggerEvent('api.server.end');
	exit(0);
	
?>