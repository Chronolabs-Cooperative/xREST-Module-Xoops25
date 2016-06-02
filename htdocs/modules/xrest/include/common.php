<?php
function obj2array($objects) {
	$ret = array();
	foreach($objects as $key => $value) {
		if (is_a($value, 'stdClass')) {
			$ret[$key] = (array)obj2array($value);
		} elseif (is_array($value)) {
			$ret[$key] = obj2array($value);
		} else {
			$ret[$key] = $value;
		}
	}
	return $ret;
}

function validateuser($username, $password){
	global $xoopsDB;
	$sql = "select * from ".$xoopsDB->prefix('users'). " WHERE uname = '$username' and pass = ".(strlen($password)==32&&strtolower($password)==$password?"'$password'":"md5('$password')");
	$ret = $xoopsDB->query($sql);
	if (!$xoopsDB->getRowsNum($ret)) {
		return false;
	} else {
		return true;
	}
}

function user_uid($username, $password){
	global $xoopsDB;
	$sql = "select uid from ".$xoopsDB->prefix('users'). " WHERE uname = '$username' and pass = ".(strlen($password)==32&&strtolower($password)==$password?"'$password'":"md5('$password')");
	$ret = $xoopsDB->query($sql);
	if (!$xoopsDB->getRowsNum($ret)) {
		return false;
	} else {
		$row = $xoopsDB->fetchArray($ret);
		return $row['uid'];
	}
}

function checkright($function_file, $username, $password){
	$uid = user_uid($username,$password);
	$module_handler = xoops_gethandler('module');
	$gperm_handler =& xoops_gethandler('groupperm');
	$member_handler =& xoops_gethandler('member');
	$online_handler =& xoops_gethandler('online');		

	$GLOBALS['xrestModule'] = $module_handler->getByDirname('xrest');
	$GLOBALS['xrestPlugin']['user'] = $username;

	$plugin_handler = xoops_getmodulehandler('plugins', 'xrest');
	$plugin = $plugin_handler->getPluginWithFile($function_file);
	$item_id = $plugin->getVar('plugin_id');
	$modid = $GLOBALS['xrestModule']->getVar('mid');
	
	if ($uid <> 0){
		$rUser = new XoopsUser($uid);
		$groups = is_object($rUser) ? $rUser->getGroups() : array(XOOPS_GROUP_ANONYMOUS);
		$online_handler->write($uid, $username, time(), $modid, (string)$_SERVER["REMOTE_ADDR"]);
		@ini_set( 'session.gc_maxlifetime', $xoopsConfig['session_expire'] * 60 );
		session_set_save_handler( array( &$sess_handler, 'open' ), array( &$sess_handler, 'close' ), array( &$sess_handler, 'read' ), array( &$sess_handler, 'write' ), array( &$sess_handler, 'destroy' ), array( &$sess_handler, 'gc' ) );
		session_start();
		$_SESSION['xoopsUserId'] = $uid;
		$GLOBALS['xoopsUser'] = &$member_handler->getUser( $uid );
		$_SESSION['xoopsUserGroups'] = $GLOBALS['xoopsUser']->getGroups();
		$GLOBALS['sess_handler']->update_cookie();
		return $gperm_handler->checkRight('plugin_call',$item_id,$groups, $modid);
	} else {
		$gperm_handler =& xoops_gethandler('groupperm');
		$groups = array(XOOPS_GROUP_ANONYMOUS);
		return $gperm_handler->checkRight('plugin_call',$item_id,$groups, $modid);
	}
}

if (!function_exists('xrest_isIPv6')) {
	function xrest_isIPv6($ip = "")  
	{  
		if ($ip == "")  
			return false; 
			 
		if (substr_count($ip,":") > 0){  
			return true;  
		} else {  
			return false;  
		}  
	} 
}

if (!function_exists('xrest_getUserIP')) {
	function xrest_getUserIP($ip=false){ 
		$ret = array(); 
		if (is_object($GLOBALS['xoopsUser'])) { 
			$ret['uid'] = $GLOBALS['xoopsUser']->getVar('uid'); 
			$ret['uname'] = $GLOBALS['xoopsUser']->getVar('uname'); 
		} else { 
			$ret['uid'] = 0; 
			$ret['uname'] = $GLOBALS['xoopsConfig']['anonymous']; 
		} 
		$ret['sessionid'] = session_id(); 
		if (!$ip) { 
			if ($_SERVER["HTTP_X_FORWARDED_FOR"] != ""){  
				$ip = (string)$_SERVER["HTTP_X_FORWARDED_FOR"];  
				$ret['is_proxied'] = true; 
				$proxy_ip = $_SERVER["REMOTE_ADDR"];  
				$ret['network-addy'] = @gethostbyaddr($ip);  
				$ret['long'] = @ip2long($ip); 
				if (xrest_isipv6($ip)) { 
					$ret['ip6'] = $ip; 
					$ret['proxy-ip6'] = $proxy_ip; 
				} else { 
					$ret['ip4'] = $ip; 
					$ret['proxy-ip4'] = $proxy_ip; 
				} 
			}else{  
				$ret['is_proxied'] = false; 
				$ip = (string)$_SERVER["REMOTE_ADDR"];  
				$ret['network-addy'] = @gethostbyaddr($ip);  
				$ret['long'] = @ip2long($ip); 
				if (xrest_isipv6($ip)) { 
					$ret['ip6'] = $ip; 
				} else { 
					$ret['ip4'] = $ip; 
				} 
			}  
		} else { 
			$ret['is_proxied'] = false; 
			$ret['network-addy'] = @gethostbyaddr($ip);  
			$ret['long'] = @ip2long($ip); 
			if (xrest_isipv6($ip)) { 
				$ret['ip6'] = $ip; 
			} else { 
				$ret['ip4'] = $ip; 
			} 
		} 
		$ret['md5'] = md5($ip.$ret['long'].$ret['network-addy'].$ret['is_proxied']); 
		$ret['sha1'] = sha1($ip.$ret['long'].$ret['network-addy'].$ret['is_proxied'].$ret['uid']. $ret['uname']);     
		$ret['made'] = time();                 
		return $ret; 
	}
}

function check_for_lock($function_file, $username, $password) {
	xoops_load('xoopscache');
	$userip = xrest_getUserIP();
	$retn = false;
	if ($result = XoopsCache::read('lock_'.$function_file.'_'.$username)) {
		foreach($result as $id => $ret) {
			if ($ret['made']<time()-$GLOBALS['xrestModuleConfig']['lock_seconds'] || 
				$ret['made']<((time()-$GLOBALS['xrestModuleConfig']['lock_seconds'])+mt_rand(1, $GLOBALS['xrestModuleConfig']['lock_random_seed']))) {
				unset($result[$id]);
			} elseif ($ret['md5']==$userip['md5']) {
				$retn = array('ErrNum'=> 9, "ErrDesc" => _XC_MI_NOPERMFORPLUGIN);
			}
		}
		XoopsCache::delete('lock_'.$function_file.'_'.$username);
		XoopsCache::write('lock_'.$function_file.'_'.$username, $result, $GLOBALS['xrestModuleConfig']['lock_seconds']+mt_rand(1, $GLOBALS['xrestModuleConfig']['lock_random_seed']));
		return $retn;
	}
}

function mark_for_lock($function_file, $username, $password) {
	xoops_load('xoopscache');
	$userip = xrest_getUserIP();
	if ($result = XoopsCache::read('lock_'.$function_file.'_'.$username)) {
		XoopsCache::delete('lock_'.$function_file.'_'.$username);
		XoopsCache::write('lock_'.$function_file.'_'.$username, $result, $GLOBALS['xrestModuleConfig']['lock_seconds']+mt_rand(1, $GLOBALS['xrestModuleConfig']['lock_random_seed']));
		return array('ErrNum'=> 9, "ErrDesc" => _XC_MI_NOPERMFORPLUGIN);
	} else {
		XoopsCache::delete('lock_'.$function_file.'_'.$username);
		XoopsCache::write('lock_'.$function_file.'_'.$username, $userip, $GLOBALS['xrestModuleConfig']['lock_seconds']+mt_rand(1, $GLOBALS['xrestModuleConfig']['lock_random_seed']));
		return array('ErrNum'=> 9, "ErrDesc" => _XC_MI_NOPERMFORPLUGIN);
	}
}
?>