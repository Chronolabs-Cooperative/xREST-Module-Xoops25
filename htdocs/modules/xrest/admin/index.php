<?php
include 'admin_header.php';

$op = (isset($_REQUEST['op'])?$_REQUEST['op']:'dashboard');
$tbl_id = (isset($_REQUEST['tbl_id'])?$_REQUEST['tbl_id']:0);
    
switch ($op){
default:
case "dashboard":
	
	xoops_load('xoopscache');
	xoops_cp_header();
	loadModuleAdminMenu(0);
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation('index.php?op=dashboard');
		
	$plugins_handler = xoops_getmodulehandler('plugins', 'xrest');
	$tables_handler = xoops_getmodulehandler('tables', 'xrest');
	$fields_handler = xoops_getmodulehandler('fields', 'xrest');

	$indexAdmin = new ModuleAdmin();	
    $indexAdmin->addInfoBox(_XREST_AM_XREST_SUMANDTOTAL);
    $indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_PLUGINS."</label>", $plugins_handler->getCount(), ($plugins_handler->getCount()>0?'Green':'Red'));
    $indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_ACTIVE_PLUGINS."</label>", $plugins_handler->getCount(new Criteria('`active`', '1','=')), ($plugins_handler->getCount(new Criteria('`active`', '1','='))>0?'Green':'Red'));
    $indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_INACTIVE_PLUGINS."</label>", $plugins_handler->getCount(new Criteria('`active`', '0','=')), ($plugins_handler->getCount(new Criteria('`active`', '0','='))>0?'Green':'Red'));
    $indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_FAILED_PLUGINS."</label>", intval(XoopsCache::read('xrest_plugins_failed')), (intval(XoopsCache::read('xrest_plugins_failed'))>0?'Red':'Green'));
	$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_TABLES."</label>", $tables_handler->getCount(new Criteria('`view`', '0','=')), ($tables_handler->getCount(new Criteria('`view`', '0','='))>0?'Green':'Red'));
	$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_VIEWS."</label>", $tables_handler->getCount(new Criteria('`view`', '1','=')), ($tables_handler->getCount(new Criteria('`view`', '1','='))>0?'Green':'Red'));
	$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_TOTAL_FIELDS."</label>", $fields_handler->getCount(), ($tables_handler->getCount()>0?'Green':'Red'));
	$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_SUMANDTOTAL, "<label>"._XREST_AM_XREST_AVERAGE_FIELDS."</label>", number_format(($fields_handler->getCount()+1)/($tables_handler->getCount(new Criteria('`view`', '0','='))+1),2), (number_format(($fields_handler->getCount()+1)/($tables_handler->getCount(new Criteria('`view`', '0','='))+1),2)>0?'Green':'Red'));
	$lastplugin = XoopsCache::read('xrest_plugins_last');
	if (sizeof($lastplugin)>=5) {
		$indexAdmin->addInfoBox(_XREST_AM_XREST_LASTANDDATE);
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_LASTANDDATE, "<label>"._XREST_AM_XREST_LAST_PLUGINS_CALLED."</label>", $lastplugin['plugin'], 'Blue');
		if (!empty($lastplugin['user']))
			$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_LASTANDDATE, "<label>"._XREST_AM_XREST_LAST_PLUGINS_CALLEDBY."</label>", $lastplugin['user'], 'Blue');
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_LASTANDDATE, "<label>"._XREST_AM_XREST_LAST_PLUGINS_CALLEDWHEN."</label>", date(_DATESTRING, $lastplugin['when']), 'Blue');
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_LASTANDDATE, "<label>"._XREST_AM_XREST_LAST_PLUGINS_TOOKTOEXECUTE."</label>", $lastplugin['took'], 'Blue');
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_LASTANDDATE, "<label>"._XREST_AM_XREST_LAST_PLUGINS_EXECUTED."</label>", $lastplugin['executed'], 'Green');
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_LASTANDDATE, "<label>"._XREST_AM_XREST_LAST_PLUGINS_EXECUTION."</label>", $lastplugin['execution'], 'Green');
	}
	$lastcleanup = XoopsCache::read('xrest_cleanup_last');
	if (sizeof($lastcleanup)==3) {
		$indexAdmin->addInfoBox(_XREST_AM_XREST_CLEANUPANDDATE);
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_CLEANUPANDDATE, "<label>"._XREST_AM_XREST_LAST_CLEANUP_WHEN."</label>", date(_DATESTRING, $lastcleanup['when']), 'Purple');
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_CLEANUPANDDATE, "<label>"._XREST_AM_XREST_LAST_CLEANUP_FILES."</label>", $lastcleanup['files'], 'Purple');
		$indexAdmin->addInfoBoxLine(_XREST_AM_XREST_CLEANUPANDDATE, "<label>"._XREST_AM_XREST_LAST_CLEANUP_TOOKTOEXECUTE."</label>", $lastcleanup['took'], 'Purple');
	}
	echo $indexAdmin->renderIndex();
	xoops_cp_footer();
	break;
case "about":
	xoops_cp_header();
	loadModuleAdminMenu(6);
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation('index.php?op=about');
	
	$paypalitemno='XRESTABOUT100';
	$aboutAdmin = new ModuleAdmin();
	$about = $aboutAdmin->renderabout($paypalitemno, false);
	$donationform = array(	0 => '<form name="donation" id="donation" action="http://www.chronolabs.coop/modules/xpayment/" method="post" onsubmit="return xoopsFormValidate_donation();">',
							1 => '<table class="outer" cellspacing="1" width="100%"><tbody><tr><th colspan="2">'.constant('_XREST_AM_MAKE_DONATION').'</th></tr><tr align="left" valign="top"><td class="head"><div class="xoops-form-element-caption-required"><span class="caption-text">Donation Amount</span><span class="caption-marker">*</span></div></td><td class="even"><select size="1" name="item[A][amount]" id="item[A][amount]" title="Donation Amount"><option value="5">5.00 AUD</option><option value="10">10.00 AUD</option><option value="20">20.00 AUD</option><option value="40">40.00 AUD</option><option value="60">60.00 AUD</option><option value="80">80.00 AUD</option><option value="90">90.00 AUD</option><option value="100">100.00 AUD</option><option value="200">200.00 AUD</option></select></td></tr><tr align="left" valign="top"><td class="head"></td><td class="even"><input class="formButton" name="submit" id="submit" value="'._SUBMIT.'" title="'._SUBMIT.'" type="submit"></td></tr></tbody></table>',
							2 => '<input name="op" id="op" value="createinvoice" type="hidden"><input name="plugin" id="plugin" value="donations" type="hidden"><input name="donation" id="donation" value="1" type="hidden"><input name="drawfor" id="drawfor" value="Chronolabs Co-Operative" type="hidden"><input name="drawto" id="drawto" value="%s" type="hidden"><input name="drawto_email" id="drawto_email" value="%s" type="hidden"><input name="key" id="key" value="%s" type="hidden"><input name="currency" id="currency" value="AUD" type="hidden"><input name="weight_unit" id="weight_unit" value="kgs" type="hidden"><input name="item[A][cat]" id="item[A][cat]" value="XDN%s" type="hidden"><input name="item[A][name]" id="item[A][name]" value="Donation for %s" type="hidden"><input name="item[A][quantity]" id="item[A][quantity]" value="1" type="hidden"><input name="item[A][shipping]" id="item[A][shipping]" value="0" type="hidden"><input name="item[A][handling]" id="item[A][handling]" value="0" type="hidden"><input name="item[A][weight]" id="item[A][weight]" value="0" type="hidden"><input name="item[A][tax]" id="item[A][tax]" value="0" type="hidden"><input name="return" id="return" value="http://www.chronolabs.coop/modules/donations/success.php" type="hidden"><input name="cancel" id="cancel" value="http://www.chronolabs.coop/modules/donations/success.php" type="hidden"></form>',																'D'=>'',
							3 => '',
							4 => '<!-- Start Form Validation JavaScript //-->
<script type="text/javascript">
<!--//
function xoopsFormValidate_donation() { var myform = window.document.donation; 
var hasSelected = false; var selectBox = myform.item[A][amount];for (i = 0; i < selectBox.options.length; i++ ) { if (selectBox.options[i].selected == true && selectBox.options[i].value != \'\') { hasSelected = true; break; } }if (!hasSelected) { window.alert("Please enter Donation Amount"); selectBox.focus(); return false; }return true;
}
//--></script>
<!-- End Form Validation JavaScript //-->');
	$paypalform = array(	0 => '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">',
							1 => '<input name="cmd" value="_s-xclick" type="hidden">',
							2 => '<input name="hosted_button_id" value="%s" type="hidden">',
							3 => '<img alt="" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" height="1" border="0" width="1">',
							4 => '<input src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" name="submit" alt="PayPal - The safer, easier way to pay online!" border="0" type="image">',
							5 => '</form>');
	for($key=0;$key<=4;$key++) {
		switch ($key) {
			case 2:
				$donationform[$key] =  sprintf($donationform[$key], $GLOBALS['xoopsConfig']['sitename'] . ' - ' . (strlen($GLOBALS['xoopsUser']->getVar('name'))>0?$GLOBALS['xoopsUser']->getVar('name'). ' ['.$GLOBALS['xoopsUser']->getVar('uname').']':$GLOBALS['xoopsUser']->getVar('uname')), $GLOBALS['xoopsUser']->getVar('email'), XOOPS_LICENSE_KEY, strtoupper($GLOBALS['xrestModule']->getVar('dirname')),  strtoupper($GLOBALS['xrestModule']->getVar('dirname')). ' '.$GLOBALS['xrestModule']->getVar('name'));
				break;
		}
	}
	
	$istart = strpos($about, ($paypalform[0]), 1);
	$iend = strpos($about, ($paypalform[5]), $istart+1)+strlen($paypalform[5])-1;
	echo (substr($about, 0, $istart-1));
	echo implode("\n", $donationform);
	echo (substr($about, $iend+1, strlen($about)-$iend-1));
	xoops_cp_footer();
	break;
case "fields":
	
	if (!$tbl_id)
		$tbl_id=1;
		
	xoops_cp_header();
	loadModuleAdminMenu(2);
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation('index.php?op=fields');
	
	echo xrest_admin_form_select_table($tbl_id);
	echo "<div style='clear:both;'></div>";
	echo xrest_admin_form_select_fields($tbl_id);
	xoops_cp_footer();
	break;

case "views":

	xoops_cp_header();
	loadModuleAdminMenu(3);
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation('index.php?op=views');
	
	echo xrest_admin_form_select_views(XOOPS_DB_NAME);
	xoops_cp_footer();
	break;		

case "plugins":
	
	xoops_cp_header();
	loadModuleAdminMenu(4);
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation('index.php?op=plugins');
	
	echo xrest_admin_form_select_plugins();
	xoops_cp_footer();
	break;	

case "tables":
	
	xoops_cp_header();
	loadModuleAdminMenu(1);
	$indexAdmin = new ModuleAdmin();
	echo $indexAdmin->addNavigation('index.php?op=tables');
	
	echo xrest_admin_form_select_tables(XOOPS_DB_NAME, XOOPS_DB_PREFIX);
	xoops_cp_footer();
	break;	
	
	
case "savefields":
	$fields_handler = xoops_getmodulehandler('fields', 'xrest');
	foreach ($_POST['id'] as $id => $fld_id){
		switch ($fld_id){
		case "new":
			$field = $fields_handler->create();
			$field->setVars($_POST[$id]);
			$field->setVar('tbl_id', $_POST['tbl_id']);
			$fields_handler->insert($field);
			break;
		default:
			$field = $fields_handler->get($fld_id);
			$field->setVars($_POST[$id]);
			$fields_handler->insert($field);
		}
	}
	redirect_header("index.php?op=fields&tbl_id=".$tbl_id,2,_XREST_AM_MSG_SAVEFIELDS_DATABASE_UPDATED);
	break;
	
case "savetables":

	$tables_handler = xoops_getmodulehandler('tables', 'xrest');
	foreach ($_POST['id'] as $id => $tbl_id){
		switch ($tbl_id){
		case "new":
			$table = $tables_handler->create();
			$table->setVars($_POST[$id]);
			$tables_handler->insert($table);
			break;
		default:
			$table = $tables_handler->get($tbl_id);
			$table->setVars($_POST[$id]);
			$tables_handler->insert($table);
		}
	}
	redirect_header("index.php?op=tables",2,_XREST_AM_MSG_SAVETABLES_DATABASE_UPDATED);
	break;
case "saveviews":

	$tables_handler = xoops_getmodulehandler('tables', 'xrest');
	foreach ($_POST['id'] as $id => $tbl_id){
		switch ($tbl_id){
		case "new":
			$table = $tables_handler->create();
			$table->setVars($_POST[$id]);
			$table->setVar('view', true);
			$tables_handler->insert($table);
			break;
		default:
			$table = $tables_handler->get($tbl_id);
			$table->setVars($_POST[$id]);
			$tables_handler->insert($table);
		}
	}
	redirect_header("index.php?op=views",2,_XREST_AM_MSG_SAVEVIEWS_DATABASE_UPDATED);
	break;

case "saveplugins":
	
	$plugins_handler = xoops_getmodulehandler('plugins', 'xrest');
	foreach ($_POST['id'] as $id => $plugin_id){
		switch ($plugin_id){
		case "new":
			$plugin = $plugins_handler->create();
			$plugin->setVars($_POST[$id]);
			$plugins_handler->insert($plugin);
			break;
		default:
			$plugin = $plugins_handler->get($plugin_id);
			$plugin->setVars($_POST[$id]);
			$plugins_handler->insert($plugin);
		}
	}
	redirect_header("index.php?op=plugins",2,_XREST_AM_MSG_SAVEPLUGINS_DATABASE_UPDATED);
	break;

}




?>