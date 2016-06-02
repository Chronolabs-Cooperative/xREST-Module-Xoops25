<?php 

	/*
	 *   Plugin function (Must have same case and filename as plugin)
  	 */
	function tweet($username, $password, $nick, $message)
	{
		if (strlen($message)<10) {
			return array('CODE' => 300);
		}
	
		global $xoopsModuleConfig, $xoopsConfig;
	
		if ($xoopsModuleConfig['site_user_auth']==1){
			if ($ret = check_for_lock(basename(__FILE__),$username,$password)) { return $ret; }
			if (!checkright(basename(__FILE__),$username,$password)) {
				mark_for_lock(basename(__FILE__),$username,$password);
				return array('ErrNum'=> 9, "ErrDesc" => 'No Permission for plug-in');
			}
		}
	
		include($GLOBALS['xoops']->path('modules/twitterbomb/include/functions.php'));
		xoops_load('cache');
		set_time_limit(480);
	
		$GLOBALS['myts'] = MyTextSanitizer::getInstance();
	
		$module_handler = xoops_gethandler('module');
		$config_handler = xoops_gethandler('config');
		$GLOBALS['twitterbombModule'] = $module_handler->getByDirname('twitterbomb');
		$GLOBALS['twitterbombModuleConfig'] = $config_handler->getConfigList($GLOBALS['twitterbombModule']->getVar('mid'));
	
		$tweet = '#'.str_replace(array('@', '+', '%'), '', $nick).' - '.twitterbomb_TweetString(htmlspecialchars_decode($message), $GLOBALS['twitterbombModuleConfig']['scheduler_aggregate'], $GLOBALS['twitterbombModuleConfig']['scheduler_wordlength']);
		$log_handler=xoops_getmodulehandler('log', 'twitterbomb');
		$scheduler_handler=xoops_getmodulehandler('scheduler', 'twitterbomb');
		$oauth_handler=xoops_getmodulehandler('oauth', 'twitterbomb');
		$urls_handler=xoops_getmodulehandler('urls', 'twitterbomb');
	
		$oauth = $oauth_handler->getRootOauth(true);
	
		$ret = XoopsCache::read('tweetbomb_scheduler_'.md5('2'.'2'));
		if (!is_array($ret)) $ret=array();
	
		$schedule = $scheduler_handler->create();
		$schedule->setVar('cid', '2');
		$schedule->setVar('catid', '2');
		$schedule->setVar('mode', 'direct');
		$schedule->setVar('pre', '#sex');
		$schedule->setVar('text', $tweet);
		$schedule->setVar('uid', user_uid($username, $password));
		$schedule = $scheduler_handler->get($scheduler_handler->insert($schedule));
		$url = $urls_handler->getUrl($schedule->getVar('cid'), $schedule->getVar('catid'));
		$link = XOOPS_URL.'/modules/twitterbomb/go.php?sid='.$schedule->getVar('sid').'&cid='.$schedule->getVar('cid').'&catid='.$schedule->getVar('catid').'&uri='.urlencode( sprintf($url, urlencode(str_replace(array('#', '@'), '',$tweet))));
		$log = $log_handler->create();
		$log->setVar('provider', 'scheduler');
		$log->setVar('cid', $schedule->getVar('cid'));
		$log->setVar('catid', $schedule->getVar('catid'));
		$log->setVar('sid', $schedule->getVar('sid'));
		$log->setVar('url', $link);
		$log->setVar('tweet', substr($tweet,0,139));
		$log->setVar('tags', twitterbomb_ExtractTags($tweet));
		$log = $log_handler->get($lid = $log_handler->insert($log, true));
		$link = XOOPS_URL.'/modules/twitterbomb/go.php?sid='.$schedule->getVar('sid').'&cid='.$schedule->getVar('cid').'&lid='.$lid.'&catid='.$schedule->getVar('catid').'&uri='.urlencode( sprintf($url, urlencode(str_replace(array('#', '@'), '',$tweet))));
		$link = twitterbomb_shortenurl($link);
		$log->setVar('url', $link);
		$log = $log_handler->get($lid = $log_handler->insert($log, true));
		if ($id = $oauth->sendTweet($schedule->getVar('pre').' '.$tweet, $link, true)) {
			if ($GLOBALS['twitterbombModuleConfig']['tags']) {
				$tag_handler = xoops_getmodulehandler('tag', 'tag');
				$tag_handler->updateByItem(twitterbomb_ExtractTags($tweet), $lid, $GLOBALS['twitterbombModule']->getVar("dirname"), $schedule->getVar('catid'));
			}
			$log->setVar('id', $id);
			$log->setVar('alias', $nick);
			$log_handler->insert($log, true);
			$schedule->setVar('when', time());
			$schedule->setVar('tweeted', time());
			$scheduler_handler->insert($schedule);
			$ret[]['title'] = $tweet;
			$ret[sizeof($ret)]['link'] = $link;
			$ret[sizeof($ret)]['description'] = htmlspecialchars_decode($tweet);
			$ret[sizeof($ret)]['lid'] = $lid;
			$ret[sizeof($ret)]['sid'] = $schedule->getVar('sid');
			if (count($ret)>$GLOBALS['twitterbombModuleConfig']['scheduler_items']) {
				foreach($ret as $key => $value) {
					if (count($ret)>$GLOBALS['twitterbombModuleConfig']['scheduler_items'])
						unset($ret[$key]);
				}
			}
			XoopsCache::write('tweetbomb_scheduler_'.md5('2'.'2'), $ret, $GLOBALS['twitterbombModuleConfig']['interval_of_cron']+$GLOBALS['twitterbombModuleConfig']['scheduler_cache']);
			return array('CODE' => 200);
		} else {
			$schedule->setVar('when', time());
			$scheduler_handler->insert($schedule);
			@$log_handler->delete($log, true);
			return array('CODE' => 100);
		}
	
	}

	/*
	 *   XSD Variable Definitions (REST JSON/XML/SERIALISATION ONLY)
	 */
	function tweet_xsd_rest($parser){
		$xsd = array();
		$i=0;
		$xsd['request'][$i++] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");
		$xsd['request'][$i++] = array("name" => "nick", "items" => array(
												array("name" => "password", "type" => "string"),
												array("name" => "password", "type" => "string")));
		$xsd['request'][$i++] = array("name" => "message", "type" => "string");
	
		$i=0;
		$xsd['response'][$i++] = array("name" => "ERRNUM", "type" => "integer");
		$xsd['response'][$i++] = array("name" => "RESULT", "type" => "string");
		$xsd['response'][$i++] = array("name" => "CODE", "type" => "string");
	
		return $xsd;
	}
	
	/* 
	 *   XSD Variable Definitions - See Templating *.XML (SOAP ONLY)
	 */
	function tweet_xsd_soap($parser){
		$xsd = array();
		$i=0;
		$xsd['request'][$i++] = array("name" => "username", "type" => "string");
		$xsd['request'][$i++] = array("name" => "password", "type" => "string");	
		$xsd['request'][$i++] = array("name" => "nick", "type" => "string");
		$xsd['request'][$i++] = array("name" => "message", "type" => "string");
				
		$i=0;
		$xsd['response'][$i++] = array("name" => "ERRNUM", "type" => "integer");
		$xsd['response'][$i++] = array("name" => "RESULT", "type" => "string");
		$xsd['response'][$i++] = array("name" => "CODE", "type" => "string");
				
		return $xsd;
	}
	
	/*   
	 *   Enabled to true if base default WSDL is issued (SOAP ONLY)
	 *   
	 *   See: http://www.w3.org/TR/wsdl#_wsdl
	 */
	function tweet_wsdl(){
		return false;
	}
	
	/*
	 *   Enabled to true if services default WSDL is issued (SOAP ONLY)
	 *
	 *   See: http://www.w3.org/TR/wsdl#_style
	 */
	function tweet_wsdl_service(){
		return false;
	}
	
	/*
	 *   Enabled to true if services default WSDL is issued (SOAP ONLY)
	 *
	 *   See: http://www.w3.org/TR/wsdl#_documentation
	 */
	function tweet_wsdl_documentation($plugin, $parser){
		$doc 	=		'';
		$doc 	.=		'';
		$doc 	.=		'';
		$doc 	.=		'';
		$doc 	.=		'';
		return $doc;
	}
	
?>