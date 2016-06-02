<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

xoops_load('xoopscache');
/**
 * Class for Blue Room XRest 1.52
 * @author Simon Roberts <simon@chronolabs.coop>
 * @copyright copyright (c) 2012-2011 chronolabs.coop
 * @package kernel
 */
class XrestPlugins extends XoopsObject
{
	
    function XrestPlugins($id = null)
    {
        $this->initVar('plugin_id', XOBJ_DTYPE_INT, null, false);
		$this->initVar('plugin_name', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('plugin_file', XOBJ_DTYPE_TXTBOX, null, false, 255);
		$this->initVar('active', XOBJ_DTYPE_INT, null, false);
	}

}


/**
* XOOPS policies handler class.
* This class is responsible for providing data access mechanisms to the data source
* of XOOPS user class objects.
*
* @author  Simon Roberts <simon@chronolabs.coop>
* @package kernel
*/
class XrestPluginsHandler extends XoopsPersistableObjectHandler
{
    public function __construct(&$db) 
    {
		$this->db = $db;
        parent::__construct($db, 'rest_plugins', 'XrestPlugins', "plugin_id", "plugin_name");
    }
    
	public function getServerExtensions() {
		return $this->getFileListAsArray($GLOBALS['xoops']->path('modules/xrest/plugins/'));
	}
	
	private function getDirListAsArray( $dirname ) {
		$ignored = array();
		$list = array();
		if ( substr( $dirname, -1 ) != '/' ) {
			$dirname .= '/';
		}
		$u=0;
		if ( $handle = opendir( $dirname ) ) {
			while ( $file = readdir( $handle ) ) {
				if ( substr( $file, 0, 1 ) == '.' || in_array( strtolower( $file ), $ignored ) )	continue;
				if ( is_dir( $dirname . $file ) ) {
					$list[$u++] = $file;
				}
			}
			closedir( $handle );
			asort( $list );
			reset( $list );
		}
		//print_r($list);
		return $list;
	}

	private function getFileListAsArray($dirname, $prefix="", $extension = '.php' )
	{
		$filelist = array();
		if (substr($dirname, -1) == '/') {
			$dirname = substr($dirname, 0, -1);
		}
		$u=0;
		if (is_dir($dirname) && $handle = opendir($dirname)) {
			while (false !== ($file = readdir($handle))) {
				if (!preg_match("/^[\.]{1,2}$/",$file) && is_file($dirname.'/'.$file)) {
					$file = $prefix.$file;$extension;
					if (strtolower(substr($file, strlen($file) - strlen($extension), strlen($extension)))==strtolower($extension)) {
						$filelist[$u++] = $file;
					} elseif ($extension == '*.*') {
						$filelist[$u++] = $file;
					}
				}
			}
			closedir($handle);
			asort($filelist);
			reset($filelist);
		}
		return $filelist;
	}
	
    public function getPluginWithName($plugin_name) {
    	$criteria = new CriteriaCompo(new Criteria('`plugin_name`', $plugin_name));
    	if ($this->getCount($criteria)==0) {
    		return false;
    	} elseif ($objects = $this->getObjects($criteria, false)) {
    		if (isset($objects[0]))
    			return $objects[0];
    		else 
    			return false;
    	}
    	return false;
    }
    
	public function getPluginWithFile($plugin_file) {
    	$criteria = new CriteriaCompo(new Criteria('`plugin_file`', $plugin_file));
    	if ($this->getCount($criteria)==0) {
    		return false;
    	} elseif ($objects = $this->getObjects($criteria, false)) {
    		if (isset($objects[0]))
    			return $objects[0];
    		else 
    			return false;
    	}
    	return false;
    }
}

?>