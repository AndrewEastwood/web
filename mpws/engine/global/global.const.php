<?php

    // Text Formatters

    define ("NLN", "\n");

    define ("TAB", "    ");

    // HyperText Formatters

    define ("DQUO", '"');

    define ("SQOU", "'");

    // Log Formatters

    define ("RUNLOG", "[mpws:] %s".NLN);

    define ("HRUNLOG", "[mpws:] %s <br />".NLN);

    // Path Formatters

    define ("DOT", ".");
    
    define ("DOG", "@");
    
    define ("SHARP", "#");
    
    define ("STAR", "*");

    define ("DS", "/");

    define ("BS", "_", true);
    
    define ("EQ", "=", true);

    define ("US", "..", true);
    
    define ("COLON", ":", true);

    // Object Types
    
    define ("OBJECT_T_NONE", '', true);
    define ("OBJECT_T_PLUGIN", 'plugin', true);
    define ("OBJECT_T_CUSTOMER", "customer", true);
    define ("OBJECT_T_CONTEXT", "context", true);
 
    // Connection Type
    
    define ("T_CONNECT_DB", 'database', true);
    
    // Scripts
    
    define("EXT_SCRIPT", DOT."php");
    define("EXT_TEMPLATE", DOT."html");
    define("EXT_JS", DOT."js");
    
    // GLOB SELECTORS
    
    define("gEXT_ALL_SCRIPT", DS.'*'.DOT."php");
    define("gEXT_ALL_TEMPLATE", DS.'*'.DOT."html");
    define("gEXT_ALL_JS", DS.'*'.DOT."js");
    
    // render components
    
    define ("renderFLD_NAME", 'mpws'.BS.'field'.BS);

?>
