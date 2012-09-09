<?php

class libraryConfigurationManager {
    
    public static function getObjectConfigurationChain ($resourceName, $objectMeta) {
        if (empty($objectMeta['TYPE']) &&
            empty($objectMeta['NAME']) &&
            empty($objectMeta['PATH_DEF']) &&
            empty($objectMeta['PATH_DEF']))
        throw new Exception('libraryStaticResourceManager: getObjectTemplate: Wrong $objectMeta passed');
        return self::getConfigurationChain($objectMeta['TYPE'], $objectMeta['NAME'], $resourceName, $objectMeta);
    }

    public static function getConfigurationChain ($owner, $name, $resourceName, $preDefinedPaths = array()) {
        debug('libraryConfigurationManager', 'getConfigurationPath', true);
        $resPath = 'config' . DS . $resourceName . '.ini';
        $_default  = DR . '/web/default/' . MPWS_VERSION . DS . $resPath;
        if (isset($preDefinedPaths['PATH_DEF']))
            $_default = $preDefinedPaths['PATH_DEF'] . DS . $resPath;
        $_owner = DR . '/web/' . $owner . DS . $name . DS . $resPath;
        if (isset($preDefinedPaths['PATH_OWN']))
            $_owner = $preDefinedPaths['PATH_OWN'] . DS . $resPath;
        $chains = array();
        if (file_exists($_owner))
            $chains[0] = $_owner;
        if (file_exists($_default))
            $chains[1] = $_default;
        if (count($chains) == 0)
            throw new Exception('libraryConfigurationManager: getConfigurationPath: requrested configuration does not exsist: ' . $resourceName);
        return $chains;
    }
    
    public static function getConfigurationValue ($chains, $configKey) {
        debug('libraryConfigurationManager', 'getConfigurationValue', true);
        
        foreach ($chains as $configFilePath) {
            if (!file_exists($configFilePath))
                throw new Exception('libraryConfigurationManager: getConfigurationValue: Config file does not exsist: ' . $configFilePath);
            //debug('libraryConfigurationManager: getConfigurationValue: ' . $configFilePath);
            $props = parse_ini_file($configFilePath);
            //debug($props);
            if (isset($props[$configKey]))
                return libraryUtils::convValue($props[$configKey]);
            
            // will return top-level configuration key value
            
        }
        
        throw new Exception('libraryConfigurationManager: getConfigurationValue: Requested property key does not exist: ' . $configKey);
    }
    
    
    
}

?>