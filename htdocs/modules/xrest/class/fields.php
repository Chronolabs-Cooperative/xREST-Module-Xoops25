<?php

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
/**
 * Class for Blue Room XRest 1.52
 * @author Simon Roberts <simon@chronolabs.coop>
 * @copyright copyright (c) 2012-2011 chronolabs.coop
 * @package kernel
 */
class XrestFields extends XoopsObject
{
	
    function XrestFields($id = null)
    {
        $this->initVar('fld_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('tbl_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('key', XOBJ_DTYPE_INT, null, false);
		$this->initVar('fieldname', XOBJ_DTYPE_TXTBOX, null, false, 220);
		$this->initVar('allowpost', XOBJ_DTYPE_INT, null, false);
		$this->initVar('allowretrieve', XOBJ_DTYPE_INT, null, false);
		$this->initVar('allowupdate', XOBJ_DTYPE_INT, null, false);	
		$this->initVar('visible', XOBJ_DTYPE_INT, null, false);
		$this->initVar('string', XOBJ_DTYPE_INT, null, false);
		$this->initVar('int', XOBJ_DTYPE_INT, null, false);
		$this->initVar('float', XOBJ_DTYPE_INT, null, false);
		$this->initVar('text', XOBJ_DTYPE_INT, null, false);
		$this->initVar('other', XOBJ_DTYPE_INT, null, false);
		$this->initVar('crc', XOBJ_DTYPE_INT, null, false);
	}

}

/**
 * Class for Blue Room XRest 1.52
 * @author Simon Roberts <simon@chronolabs.coop>
 * @copyright copyright (c) 2012-2011 chronolabs.coop
 * @package kernel
 */
class XrestMysqlFields extends XoopsObject
{
	
    function XrestMysqlFields()
    {
		$this->initVar('Field', XOBJ_DTYPE_OTHER, null, false);
		$this->initVar('Type', XOBJ_DTYPE_OTHER, null, false);
		$this->initVar('Null', XOBJ_DTYPE_OTHER, null, false);
		$this->initVar('Key', XOBJ_DTYPE_OTHER, null, false);
		$this->initVar('Default', XOBJ_DTYPE_OTHER, null, false);
		$this->initVar('Extra', XOBJ_DTYPE_OTHER, null, false);
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
class XrestFieldsHandler extends XoopsPersistableObjectHandler
{
    public function __construct(&$db) 
    {
		$this->db = $db;
        parent::__construct($db, 'rest_fields', 'XrestFields', "fld_id", "fieldname");
    }
    
    public function getFieldFromTable($table) {
    	$sql = "SHOW FIELDS FROM `".$GLOBALS['xoopsDB']->prefix($table).'`';
		$result = $GLOBALS['xoopsDB']->queryF($sql);
		$ret = array();
		$i=1;
		while($row = $GLOBALS['xoopsDB']->fetchArray($result)) {
			$ret[$i] = new XrestMysqlFields();
			$ret[$i]->assignVars($row);
			$i++;	
		}
		return $ret;
    }
    
    public function getFieldWithNameAndTableID($fieldname, $tbl_id) {
    	$criteria = new CriteriaCompo(new Criteria('`fieldname`', $fieldname));
    	$criteria->add(new Criteria('`tbl_id`', $tbl_id));
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