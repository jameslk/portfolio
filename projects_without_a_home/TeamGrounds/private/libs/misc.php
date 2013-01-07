<?php

/**
 * Call a user function using named instead of positional parameters.
 * If some of the named parameters are not present in the original function, they
 * will be silently discarded.
 * Does no special processing for call-by-ref functions...
 * @author http://www.php.net/manual/en/function.call-user-func-array.php#66121
 * @param string $function name of function to be called
 * @param array $params array containing parameters to be passed to the function using their name (ie array key)
 */
function call_user_func_named($function, $params) {
    // make sure we do not throw exception if function not found: raise error instead...
    // (oh boy, we do like php 4 better than 5, don't we...)
    if(!function_exists($function)) {
        if(!is_array($function)) {
            MakeError(ERROR_PHP, 'Call to unexisting function '.$function);
            return NULL;
        }
        else if(!is_object($function[0]) || !method_exists($function[0], $function[1])) {
            MakeError(ERROR_PHP, 'Call to unexisting function '.get_class($function[0]).'::'.$function[1]);
            return NULL;
        }
    }
    
    if(is_array($function) && is_object($function[0]))
        $reflect = new ReflectionMethod(get_class($function[0]), $function[1]);
    else
        $reflect = new ReflectionFunction($function);
    
    $func_params = $reflect->getParameters();
    $ordered_params = array();
    foreach($func_params as $i => $param) {
        $pname = $param->getName();
        
        if ($param->isPassedByReference()) {
            /// @todo shall we raise some warning?
        }
        
        if (array_key_exists($pname, $params)) {
            $ordered_params[] = $params[$pname];
        }
        else if ($param->isDefaultValueAvailable()) {
            $ordered_params[] = $param->getDefaultValue();
        }
        else {
            // missing required parameter: mark an error and exit
            //return new Exception('call to '.$function.' missing parameter nr. '.$i+1);
            MakeError(sprintf('Call to %s missing parameter nr. %d: "%s"', $function, $i+1, $pname),
                compact($function, $params));
            return NULL;
        }
    }
    
    return call_user_func_array($function, $ordered_params);
}