<?php
if (!function_exists('json_encode')){
	function json_encode($data) {
		static $json = NULL;
		if (!class_exists('Services_JSON')) include_once $GLOBALS['xoops']->path('/modules/xrest/include/JSON.php');
			$json = new Services_JSON();
		return $json->encode($data);
	}
}

if (!function_exists("xrest_toXml")) { 
	function xrest_toXml($array, $name, $standalone=false, $beginning=true, $nested) {
		
		if ($beginning) {
			if ($standalone)
				header("content-type:text/xml;charset="._CHARSET);
			$output .= '<'.'?'.'xml version="1.0" encoding="'._CHARSET.'"'.'?'.'>' . "\n";    
			$output .= '<' . $name . '>' . "\n";
			$nested = 0;
		}    
		
		if (is_array($array)) {
			foreach ($array as $key=>$value) {
				$nested++;	
				if (is_array($value)) {
					$output .= str_repeat("\t", (1 * $nested)) . '<' . (is_string($key) ? $key : $name.'_' . $key) . '>' . "\n";
					$nested++;				
					$output .= xrest_toXml($value, $name, false, false, $nested);
					$nested--;
					$output .= str_repeat("\t", (1 * $nested)) . '</' . (is_string($key) ? $key : $name.'_' . $key) . '>' . "\n";
				} else {
					if (strlen($value)>0) {
					$nested++;				
						$output .= str_repeat("\t", (1 * $nested)) . '  <' . (is_string($key) ? $key : $name.'_' . $key) . '>' . trim($value) . '</' . (is_string($key) ? $key : $name.'_' . $key) . '>' . "\n";
						$nested--;
					}
				}
				$nested--;
			}
		} elseif (strlen($array)>0) {
			$nested++; 
			$output .= str_repeat("\t", (1 * $nested)) . trim($array) ."\n";
			$nested--;
		}
			
		if ($beginning) {
			$output .= '</' . $name . '>';
			return $output;
		} else {
			return $output;
		}
	} 
}

if (!function_exists("xrest_object2array")) {
	function xrest_object2array($object) {
	  if (is_object($object)) {
	    foreach ($object as $key => $value) {
	    	if (is_object($value)) {
	    		$array[$key] = object2array($value);
	    	} else {
	    		$array[$key] = $value;
	    	}
	    }
	  }
	  else {
	    $array = $object;
	  }
	
	  return $array;
	}
}

if (!function_exists("xrest_strip_prefix")) {
	function xrest_strip_prefix($raw_tablename){
		return str_replace(XOOPS_DB_PREFIX."_",'',$raw_tablename);
	}
}
?>