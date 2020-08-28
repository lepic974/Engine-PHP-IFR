<?php
if (!function_exists('array_intersect_key')){
   function array_intersect_key ($isec, $arr2)
   {
       $argc = func_num_args();
        
       for ($i = 1; !empty($isec) && $i < $argc; $i++)
       {
             $arr = func_get_arg($i);
            
             foreach ($isec as $k =>& $v)
                 if (!isset($arr[$k]))
                     unset($isec[$k]);
       }
      
       return $isec;
   }
}

if (!function_exists('array_change_value_case')){
	function array_change_value_case($input, $case = CASE_LOWER)
	{
		$aRet = array();
	   
		if (!is_array($input))
		{
			return $aRet;
		}
	   
		foreach ($input as $key => $value)
		{
			if (is_array($value))
			{
				$aRet[$key] = array_change_value_case($value, $case);
				continue;
			}
		   
			$aRet[$key] = ($case == CASE_UPPER ? strtoupper($value) : strtolower($value));
		}
	   
		return $aRet;
	}
}

if (!function_exists('array_diff_key')) {
    function array_diff_key()
    {
        $arrs = func_get_args();
        $result = array_shift($arrs);
        foreach ($arrs as $array) {
            foreach ($result as $key => $v) {
                if (array_key_exists($key, $array)) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
   }
}

if(!function_exists('hash')) {
   function hash($algo, $data, $raw_output = 0)
   {
      if($algo == 'md5') return(md5($data, $raw_output));
      if($algo == 'sha1') return(sha1($data, $raw_output));
   }
}

if(!function_exists('imageantialias')) {
	function imageantialias(){ //disable anti-aliasing because Debian/Ubuntu PHP Version is not supported
		return true;
	}
}


?>