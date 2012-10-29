<?php

// dependencies
// library.Request.php

class libraryComponents {

    public static function comDataTable($config, $dbLink, $condition = '', $beforeConditionHook = '') {
        $com = array();

        $pageName = strtoupper(libraryRequest::getPage('Default'));
        $sessionSearchKey = 'MPWS_SEARCH_OF_' . $config['TABLE'] . '_' . $pageName;

        $com['SEARCHBOX'] = array();
        // detect search request
        if (libraryRequest::isPostFormAction('search')) {

            $searchbox = array();
            foreach ($_POST as $_pkey => $_pval) {
                $returnValue = strpos($_pkey, $config['SEARCH_KEY_PREFIX']);
                //echo 'prefix = ' . $config['SEARCH_KEY_PREFIX'] .'<br>';
                //echo 'post key = ' . $_pkey .'<br>';
                //echo 'result is = ' . $returnValue .'-----<br>';
                if ($returnValue === 0 && !empty($_pval))
                    $searchbox[str_replace($config['SEARCH_KEY_PREFIX'], '', $_pkey)] = '%' . mysql_escape_string($_pval) . '%';
            }
            $_SESSION[$sessionSearchKey] = $searchbox;
            $com['SEARCHBOX']['ACTIVE'] = true;
        }
        if (libraryRequest::isPostFormAction('discard')) {
            $_SESSION[$sessionSearchKey] = false;
        }

        $com['RECORDS_ALL'] = $dbLink->getCount($config['TABLE'], $condition, $beforeConditionHook);

        if (empty($_SESSION[$sessionSearchKey])) {
            $com['SEARCHBOX']['ACTIVE'] = false;
            $com['RECORDS'] = $com['RECORDS_ALL'];
        } else {

            //echo 'IS ACTIVE';

            $com['SEARCHBOX']['ACTIVE'] = true;
            $com['SEARCHBOX']['FILTER'] = $_SESSION[$sessionSearchKey];
            $_searchBoxFilterString = array();
            if (!empty($condition))
                $_searchBoxFilterString[] = $condition . ' ';
            foreach ($_SESSION[$sessionSearchKey] as $sbKey => $sbVal)
                $_searchBoxFilterString[] = ' ' . $sbKey . ' LIKE \'' . $sbVal . '\' ';

            //echo implode('AND', $_searchBoxFilterString);

            $com['RECORDS'] = $dbLink->getCount($config['TABLE'], implode('AND', $_searchBoxFilterString), $beforeConditionHook);
        }

        //$com['RECORDS_ALL'] = $dbLink->getCount($config['TABLE']);
        //var_dump($_SESSION['MPWS_SEARCH_OF_' . $config['TABLE']]);

        $com['CURRENT'] = libraryRequest::getValue($config['PAGEKEY'], 1);
        $com['LIMIT'] = $config['LIMIT'];
        $com['PAGES'] = round($com['RECORDS'] / $com['LIMIT'] + 0.4);

        // cleanup junk page values
        $com['CURRENT'] = mysql_escape_string($com['CURRENT']);
        if (!is_numeric($com['CURRENT']) ||
                $com['CURRENT'] < 1 ||
                $com['CURRENT'] > $com['PAGES'])
            $com['CURRENT'] = 1;

        $com['OFFSET'] = ($com['CURRENT'] - 1) * $com['LIMIT'];

        // fill pages
        $com['PAGELINKS'] = array();
        // get pages offset
        $_edgeLeft = $com['CURRENT'] - $config['SIZE'];
        $_edgeRight = $com['CURRENT'] + $config['SIZE'];
        // validate edges
        if ($com['PAGES'] < ($config['SIZE'] * 2 + 1)) {
            $_edgeLeft = 1;
            $_edgeRight = $com['PAGES'];
        } elseif ($_edgeLeft < 1) {
            $_edgeLeft = 1;
            $_edgeRight = $config['SIZE'] * 2 + 1;
            if ($_edgeRight > $com['PAGES'])
                $_edgeRight = $com['PAGES'];
        }elseif ($_edgeRight > $com['PAGES']) {
            $_edgeRight = $com['PAGES'];
            $_edgeLeft = $_edgeRight - $config['SIZE'] * 2;
            if ($_edgeLeft < 1)
                $_edgeLeft = 1;
        }

        // set left custom edges
        if (!empty($config['EDGES']) && $com['PAGES'] > 2) {
            $_customEdges = explode('-', $config['EDGES']);
            foreach ($_customEdges as $_customEdgeKey)
                switch ($_customEdgeKey) {
                    case 'FIRST':
                        $com['EDGELINKS']['First'] = libraryRequest::getNewUrl($config['PAGEKEY'], 1);
                        break;
                    case 'PREV':
                        if ($com['CURRENT'] > 1)
                            $com['EDGELINKS']['<<'] = libraryRequest::getNewUrl($config['PAGEKEY'], $com['CURRENT'] - 1);
                        break;
                    case 'NEXT':
                        if ($com['CURRENT'] < $com['PAGES'])
                            $com['EDGELINKS']['>>'] = libraryRequest::getNewUrl($config['PAGEKEY'], $com['CURRENT'] + 1);
                        break;
                    case 'LAST':
                        $com['EDGELINKS']['Last'] = libraryRequest::getNewUrl($config['PAGEKEY'], $com['PAGES']);
                        break;
                }
        }

        for ($i = $_edgeLeft; $i <= $_edgeRight; $i++)
            $com['PAGELINKS'][$i] = libraryRequest::getNewUrl($config['PAGEKEY'], $i);

        if (!empty($dbLink)) {
            $dbLink
                    ->reset()
                    ->select('*')
                    ->from($config['TABLE'])
                    ->offset($com['OFFSET'])
                    ->limit($com['LIMIT']);

            $_conditionasAdded = 0;
            // searchbox
            if ($com['SEARCHBOX']['ACTIVE']) {
                $_firstConditionWasAdded = false;
                foreach ($_SESSION[$sessionSearchKey] as $sbKey => $sbVal) {
                    if ($_firstConditionWasAdded)
                        $dbLink->andWhere($sbKey, 'LIKE', $sbVal);
                    else {
                        $dbLink->where($sbKey, 'LIKE', $sbVal);
                        $_firstConditionWasAdded = true;
                    }
                    $_conditionasAdded++;
                }
            }

            if (!empty($condition)) {
                //echo 'adding condition';
                $_cnd = explode(' ', $condition, 3);
                //var_dump($_cnd);
                if ($_conditionasAdded == 0)
                    $dbLink->where(trim($_cnd[0], ' \'`"'), trim($_cnd[1]), trim($_cnd[2], ' \'"') . ' ');
                else
                    $dbLink->andWhere(trim($_cnd[0], ' \'`"'), trim($_cnd[1]), trim($_cnd[2], ' \'"') . ' ');
            }

            // sorting
            $sort = libraryRequest::getValue($config['SORTKEY'], 'ID.desc');
            if (!empty($sort)) {
                $sort = explode('.', trim($sort));
                if (count($sort) == 2 && !empty($sort[0]) && !empty($sort[1])) {
                    $_direction = trim(strtolower($sort[1]));
                    if ($_direction == 'asc' || $_direction == 'desc') {
                        $dbLink
                                ->orderBy($config['TABLE'] . '.' . $sort[0])
                                ->order($_direction);
                    }
                }
            }


            $com['DATA'] = $dbLink->fetchData();
        }

        return $com;
    }

    public static function getDataRecordViewer($config, $dbLink) {

        $oid = libraryRequest::getOID();
        if (empty($oid) && !is_numeric($oid))
            throw new Exception('libraryComponents => getDataRecordViewer: wrong OID value');

        // validate configuration for requested mode
        if (empty($config))
            throw new Exception('libraryComponents => getDataRecordViewer: can not find configuration for standalone view');

        $dbLink->reset();
        if ($config['fields'] == '*')
            $dbLink->select($config['fields']);
        else
            $dbLink->select('ID', implode(', ', $config['fields']));
        $dbLink->from($config['source'])->where('ID', '=', $oid);

        $dtv = array();
        $dtv["RECORD"] = $dbLink->fetchRow();
        $dtv["REFERER"] = libraryRequest::storeOrGetRefererUrl(false);

        return $dtv;
    }

    public static function getDataRecordRemoval($config, $dbLink) {
        $oid = libraryRequest::getOID();
        if (empty($oid) && !is_numeric($oid))
            throw new Exception('libraryComponents => getDataRecordRemoval: wrong OID value');

        // validate configuration for requested mode
        if (empty($config))
            throw new Exception('libraryComponents => getDataRecordRemoval: can not find configuration for standalone view');

        $dbLink->reset();
        if ($config['fields'] == '*')
            $dbLink->select('*');
        else
            $dbLink->select('ID', implode(', ', $config['fields']));
        $dbLink->from($config['source'])->where('ID', '=', $oid);
        $state = 'VIEW';
        $sessionKeyName = 'MPWS_DATARECORDREMOVAL_SESSION';
        // session key
        $_sessionKey = md5(mt_rand(1, 1000));
        $isSessionValid = isset($_SESSION[$sessionKeyName]) && $_SESSION[$sessionKeyName] == libraryRequest::getPostFormField('session');
        // set new session key
        $_SESSION[$sessionKeyName] = $_sessionKey;
        // chack and remove on action
        if ($isSessionValid) {
            if(libraryRequest::isPostFormAction('remove')) {
                $dbLink
                    ->deleteFrom($config['source'])
                    ->where('ID', '=', $oid)
                    ->query();
                $state = 'REMOVED';
            }
            if(libraryRequest::isPostFormAction('cancel'))
                $state = 'CANCELED';
        }
        // control object
        $dtv = array();
        $dtv["PAGE"] = $state;
        $dtv["RECORD"] = $dbLink->fetchRow();
        $dtv["SESSION"] = $_sessionKey;
        $dtv["REFERER"] = libraryRequest::storeOrGetRefererUrl(false);
        $dtv["OID"] = $oid;

        return $dtv;
    }

    public static function getDataTableView($config, $dbLink) {
        // get params
        $viewMode = libraryRequest::getAction('default');
        //echo 'DTV VIEW MODE = ' . $viewMode;
        $isTableMode = $viewMode == 'default' || empty($viewMode);
        $condition = $config['datatable']['condition'];
        $beforeConditionHook = $config['datatable']['conditionHook'];
        $pageName = strtoupper(libraryRequest::getPage('Default'));
        $sessionSearchKey = 'MPWS_SEARCH_OF_' . $config['source'] . '_' . $pageName;

        // table mode view
        if ($isTableMode) {
            // component structure
            $com = array();
            $com['SEARCHBOX'] = array(
                'FIELDS' => array(),
                'ACTIVE' => false,
                'FILTER' => array(),
                'WORDS' => array()
            );
            // add search fields
            $com['SEARCHBOX']['FIELDS'] = $config['searchbox']['fields'];
            /*
              foreach ( as $searchFieldName) {
              [$searchFieldName] = strtolower($searchFieldName);
              } */
            // detect search request
            if (libraryRequest::isPostFormAction('search')) {
                //echo 'SEARCHING';
                $searchbox = array();
                $_emptyFieldsCount = 0;
                foreach ($com['SEARCHBOX']['FIELDS'] as $_searchFieldName) {
                    $_fieldValue = libraryRequest::getPostFormField($_searchFieldName, true, '%');
                    if ($_fieldValue == "%%")
                        $_emptyFieldsCount++;
                    else
                        $searchbox[$_searchFieldName] = $_fieldValue;
                }
                //echo 'EMPTY COUNT: ' . $_emptyFieldsCount;
                //var_dump($searchbox);
                // check if there is even one non-empty value
                if ($_emptyFieldsCount != count($com['SEARCHBOX']['FIELDS'])) {
                    $com['SEARCHBOX']['ACTIVE'] = true;
                    $_SESSION[$sessionSearchKey] = $searchbox;
                } else {
                    //var_dump($searchbox);
                    //echo 'EMPTY ALL SEARCH FIELDS';
                    $com['SEARCHBOX']['ACTIVE'] = false;
                    $_SESSION[$sessionSearchKey] = array();
                }
            }
            if (libraryRequest::isPostFormAction('discard')) {
                //echo 'DISCARD';
                $_SESSION[$sessionSearchKey] = false;
                $com['SEARCHBOX']['ACTIVE'] = false;
            }
            // get actual record count with all applied conditions
            $com['RECORDS_ALL'] = $dbLink->getCount($config['source'], $condition, $beforeConditionHook);

            // search mode
            // -----------
            if (empty($_SESSION[$sessionSearchKey])) {
                //echo 'IS IN ACTIVE';
                $com['SEARCHBOX']['ACTIVE'] = false;
                $com['SEARCHBOX']['FILTER'] = false;
                $com['RECORDS'] = $com['RECORDS_ALL'];
            } else {
                //echo 'IS ACTIVE';
                $com['SEARCHBOX']['ACTIVE'] = true;
                $com['SEARCHBOX']['FILTER'] = $_SESSION[$sessionSearchKey];
                //var_dump($com['SEARCHBOX']['FILTER']);
                $_searchBoxFilterString = array();
                if (!empty($condition))
                    $_searchBoxFilterString[] = $condition . ' ';
                foreach ($_SESSION[$sessionSearchKey] as $sbKey => $sbVal) {
                    $_searchBoxFilterString[] = ' ' . $sbKey . ' LIKE \'' . $sbVal . '\' ';
                    $com['SEARCHBOX']['WORDS'][$sbKey] = trim($sbVal, '%');
                }
                //echo implode('AND', $_searchBoxFilterString);
                $com['RECORDS'] = $dbLink->getCount($config['source'], implode('AND', $_searchBoxFilterString), $beforeConditionHook);
            }

            // pagination
            // -----------
            // 
            //$com['RECORDS_ALL'] = $dbLink->getCount($config['TABLE']);
            //var_dump($_SESSION['MPWS_SEARCH_OF_' . $config['TABLE']]);
            // page state
            $com['CURRENT'] = libraryRequest::getValue($config['pagination']['pageKey'], 1);
            $com['LIMIT'] = $config['datatable']['limit'];
            $com['PAGES'] = round($com['RECORDS'] / $com['LIMIT'] + 0.4);
            // cleanup junk page values
            $com['CURRENT'] = mysql_escape_string($com['CURRENT']);
            if (!is_numeric($com['CURRENT']) ||
                    $com['CURRENT'] < 1 ||
                    $com['CURRENT'] > $com['PAGES'])
                $com['CURRENT'] = 1;
            $com['OFFSET'] = ($com['CURRENT'] - 1) * $com['LIMIT'];
            //var_dump($config);
            // fill pages
            $com['PAGELINKS'] = array();
            $com['EDGELINKS'] = array();

            // edges
            $_edgeLeft = $com['CURRENT'] - $config['pagination']['size'];
            $_edgeRight = $com['CURRENT'] + $config['pagination']['size'];
            // validate edges
            if ($com['PAGES'] < ($config['pagination']['size'] * 2 + 1)) {
                $_edgeLeft = 1;
                $_edgeRight = $com['PAGES'];
            } elseif ($_edgeLeft < 1) {
                $_edgeLeft = 1;
                $_edgeRight = $config['pagination']['size'] * 2 + 1;
                if ($_edgeRight > $com['PAGES'])
                    $_edgeRight = $com['PAGES'];
            }elseif ($_edgeRight > $com['PAGES']) {
                $_edgeRight = $com['PAGES'];
                $_edgeLeft = $_edgeRight - $config['pagination']['size'] * 2;
                if ($_edgeLeft < 1)
                    $_edgeLeft = 1;
            }
            // set left custom edges
            if (!empty($config['pagination']['edges']) && $com['PAGES'] > 2) {
                $_customEdges = explode('-', $config['pagination']['edges']);
                foreach ($_customEdges as $_customEdgeKey)
                    switch ($_customEdgeKey) {
                        case 'FIRST':
                            $com['EDGELINKS']['FIRST'] = libraryRequest::getNewUrl($config['pagination']['pageKey'], 1);
                            break;
                        case 'PREV':
                            if ($com['CURRENT'] > 1)
                                $com['EDGELINKS']['PREV'] = libraryRequest::getNewUrl($config['pagination']['pageKey'], $com['CURRENT'] - 1);
                            break;
                        case 'NEXT':
                            if ($com['CURRENT'] < $com['PAGES'])
                                $com['EDGELINKS']['NEXT'] = libraryRequest::getNewUrl($config['pagination']['pageKey'], $com['CURRENT'] + 1);
                            break;
                        case 'LAST':
                            $com['EDGELINKS']['LAST'] = libraryRequest::getNewUrl($config['pagination']['pageKey'], $com['PAGES']);
                            break;
                    }
            }
            // setup pagelinks
            for ($i = $_edgeLeft; $i <= $_edgeRight; $i++)
                $com['PAGELINKS'][$i] = libraryRequest::getNewUrl($config['pagination']['pageKey'], $i);
        }
        //var_dump($config);
        // database connection
        if (!empty($dbLink)) {
            $dbLink->reset()
                    ->select('ID', implode(', ', $config['datatable']['fields']))
                    ->from($config['source'])
                    ->offset($com['OFFSET'])
                    ->limit($com['LIMIT']);
            $_conditionasAdded = 0;
            // searchbox
            if ($isTableMode && $com['SEARCHBOX']['ACTIVE']) {
                $_firstConditionWasAdded = false;
                foreach ($_SESSION[$sessionSearchKey] as $sbKey => $sbVal) {
                    if ($_firstConditionWasAdded)
                        $dbLink->andWhere($sbKey, 'LIKE', $sbVal);
                    else {
                        $dbLink->where($sbKey, 'LIKE', $sbVal);
                        $_firstConditionWasAdded = true;
                    }
                    $_conditionasAdded++;
                }
            }
            // conditional select
            if (!empty($condition)) {
                //echo 'adding condition';
                $_cnd = explode(' ', $condition, 3);
                //var_dump($_cnd);
                if ($_conditionasAdded == 0)
                    $dbLink->where(trim($_cnd[0], ' \'`"'), trim($_cnd[1]), trim($_cnd[2], ' \'"') . ' ');
                else
                    $dbLink->andWhere(trim($_cnd[0], ' \'`"'), trim($_cnd[1]), trim($_cnd[2], ' \'"') . ' ');
            }
            // sorting
            if ($isTableMode) {
                $sort = libraryRequest::getValue($config['filtering']['sortKey'], 'ID.asc');
                if (!empty($sort)) {
                    $sort = explode('.', trim($sort));
                    if (count($sort) == 2 && !empty($sort[0]) && !empty($sort[1])) {
                        $_direction = trim(strtolower($sort[1]));
                        //echo '#####' . $config['source'].'.'.$sort[0] . '####';
                        if ($_direction == 'asc' || $_direction == 'desc') {
                            $dbLink
                                    ->orderBy($config['source'] . '.' . $sort[0])
                                    ->order($_direction);
                        }
                    }
                }
            }

            // get data
            $com['DATA'] = $dbLink->fetchData();
        }

        // init component
        $dtv = array("MODE" => makeKey($viewMode));
        $dtv["SOURCE"] = array(
            "RECORDS" => $com['DATA'],
            "LIMIT" => $com['LIMIT'],
            "TOTAL" => $com['RECORDS_ALL']
        );
        $dtv["PAGING"] = array(
            "LINKS" => $com['PAGELINKS'],
            "EDGES" => $com['EDGELINKS'],
            "CURRENT" => $com['CURRENT'],
            "LIMIT" => $com['LIMIT'],
            "OFFSET" => $com['OFFSET'],
            "PAGES" => $com['PAGES'],
            "TOTAL" => $com['RECORDS_ALL'],
            "AVAILABLE" => $com['RECORDS']
        );
        $dtv["SEARCH"] = $com['SEARCHBOX'];

        return $dtv;
    }

    public static function getDataEditor($config, $dbLink, $actionHooks = false) {
        /* DB Table Standart Filed Names:
         *
         *  ID
         *  Name
         *  Password
         *  ExternalKey
         *  DataPath
         *  DateUpdated
         *  DateLastAccess
         *  
         */

        $sessionKeyName = 'MPWS_DATAEDITOR_SESSION';

        if (empty($dbLink))
            throw new Exception('libraryComponents: getDataEditor => dbLink is empty');

        // session key
        if (empty($_SESSION[$sessionKeyName]))
            $_SESSION[$sessionKeyName] = md5(time() . mt_rand(1, 1000));
        $_sessionKey = md5(mt_rand(1, 1000));

        // component structure
        $com = array(
            // append form configuration
            "FORM" => $config['form'],
            "SESSION" => $_sessionKey,
            "SOURCE" => false,
            "FIELDS" => false,
            "ISNEW" => true,
            "EDIT_PAGE" => "new",
            "REFERER" => libraryRequest::storeOrGetRefererUrl(false),
            "ERRORS" => false,
            "VALID" => true
        );
        // states
        $isNew = true;
        // get edit page name
        $editPage = strtolower(libraryRequest::getPostFormAction());
        $doNotFetchData = $editPage == "edit";
        // normalize page name
        $editPage = strtolower(trim($editPage));
        // get oid
        $oid = libraryRequest::getOID();
        // adjust states
        if (isset($oid) && !empty($oid))
            $isNew = false;
        if (empty($editPage) && $com['EDIT_PAGE'] == "new")
            $editPage = "edit";
        if ($editPage == "new")
            $isNew = true;
        // validate session key
        if ($_SESSION[$sessionKeyName] != libraryRequest::getPostFormField('session')) {
            // iside this condition we handle refresh action 
            // to prevet multiple savings for the same record
            if ($isNew)
                $editPage = 'new';
            else
                $editPage = 'edit';
        }
        // set new session key
        $_SESSION[$sessionKeyName] = $_sessionKey;

        // get fields
        $_fieldsDB = $dbLink->getFields($config['source']);
        $_fieldsCOM = array();
        if (!empty($config['fields']['editable'])) {
            foreach ($_fieldsDB as $fieldEntry)
                if (in_array($fieldEntry['Field'], $config['fields']['editable']))
                    $_fieldsCOM[] = $fieldEntry;
        } else
            $_fieldsCOM = $_fieldsDB;
        // set fields
        $com['FIELDS'] = $_fieldsCOM;
        //var_dump($com['FIELDS']);
        //echo 'PAGE IS: ' . $editPage;
        // do common work on save or preview actions
        if ($editPage == 'save' || $editPage == 'preview') {
            $validatorRezult = libraryValidator::validateStandartMpwsFields($config['fields']['editable'], $config['validators']);
            $com['ERRORS'] = $validatorRezult['ERRORS'];
            //var_dump($validatorRezult);
            // check unique fields
            if (empty($com["ERRORS"]) && isset($config['fields']['unique']) && !empty($config['fields']['unique'])) {
                //var_dump($config['fields']['unique']);
                foreach ($config['fields']['unique'] as $_fieldThatMustBeUnique) {
                    $dbLink
                            ->reset()
                            ->select('*')
                            ->from($config['source'])
                            ->where($_fieldThatMustBeUnique, '=', $validatorRezult['DATA'][$_fieldThatMustBeUnique]);
                    if (!$isNew)
                        $dbLink->andWhere('ID', '<>', $oid);
                    $_existedRow = $dbLink->fetchRow();
                    if (!empty($_existedRow))
                        $com['ERRORS'][] = 'validationErrorDuplicateValueInField' . $_fieldThatMustBeUnique;
                }
            }
            // do not modify pwd
            // truncate error
            if (!$isNew && !empty($config['fields']['skipIfEditExisted']) && !empty($com["ERRORS"])) {
                //var_dump($com["ERRORS"]);
                foreach ($config['fields']['skipIfEditExisted'] as $skipExistedField)
                    if (isset($com["ERRORS"][$skipExistedField]))
                        unset($com["ERRORS"][$skipExistedField]);
            }
            // set editor state on error
            if (!empty($com["ERRORS"])) {
                //var_dump($com['ERRORS']);
                $editPage = 'edit';
                $com['VALID'] = false;
            }
            // save
            if ($editPage == 'save' && $com['VALID']) {
                // edited data
                $_data = $validatorRezult['DATA'];
                $_existedRow = false;
                // existed data
                if (!$isNew && !empty($oid)) {
                    $_existedRow = $dbLink
                            ->reset()
                            ->select('*')
                            ->from($config['source'])
                            ->where('ID', '=', $oid)
                            ->fetchRow();
                }
                // prepend fields
                $appendFields = getNonEmptyValue($config['fields']['appendBeforeSave'], array());
                foreach ($appendFields as $appendFieldName)
                    $_data[$appendFieldName] = false;
                // before save hook
                if (isset($actionHooks['ON_BEFORE_SAVE'])) {
                    $__action = $actionHooks['ON_BEFORE_SAVE'];
                    $__action($config, $_data);
                }
                // skip fields for existed record
                if (!$isNew && !empty($config['fields']['skipIfEditExisted'])) {
                    foreach ($config['fields']['skipIfEditExisted'] as $skipExistedField)
                        if (isset($_data[$skipExistedField]))
                            unset($_data[$skipExistedField]);
                }
                // standart actions
                // obfuscate passwords
                if (isset($_data['Password']))
                    $_data['Password'] = md5($_data['Password']);
                // init empty fields
                if ($isNew) {
                    if (isset($_data['DateCreated']))
                        $_data['DateCreated'] = date('Y-m-d H:i:s');
                    if (isset($_data['DateLastAccess']))
                        $_data['DateLastAccess'] = date('Y-m-d H:i:s');
                }
                // set external key
                if (isset($_data['ExternalKey'])) {
                    $_data['ExternalKey'] = libraryURLify::mpwsExternalKey($_data['Name']);
                }
                // manage binded data
                if (isset($_data['DataPath'])) {
                    $_owner = explode(BS, $config['source']);
                    $_dataPath = libraryPath::getPathDataObject($_owner[0] . DS . $_data['ExternalKey']);
                    //var_dump($_existedRow);
                    if ($isNew) {
                        libraryFileManager::newDirectory($_dataPath);
                    } else {
                        // move all data from previous to new location
                        // if they are different
                        //echo "<br>Current: " . $_existedRow['DataPath'];
                        //echo "<br>New: " . $_dataPath;
                        libraryFileManager::transferDirectoryData($_existedRow['DataPath'], $_dataPath);
                    }
                    $_data['DataPath'] = $_dataPath;
                }
                // adjust field values
                foreach ($com['FIELDS'] as $fieldEntry) {
                    // checkbox
                    $_type = strtolower($fieldEntry['Type']);
                    if ($_type == 'boolean' || $_type == 'bool' || $_type == 'tinyint(1)') {
                        /* adjust data */
                        if (empty($_data['Active']))
                            $_data['Active'] = 0;
                        else
                            $_data['Active'] = 1;
                    }
                }
                // update date
                if (isset($_data['DateUpdated']))
                    $_data['DateUpdated'] = date('Y-m-d H:i:s');
                // remove fields
                $removeFields = getNonEmptyValue($config['fields']['removeBeforeSave'], array());
                foreach ($removeFields as $removeFieldName)
                    unset($_data[$removeFieldName]);
                // save
                //var_dump($_data);
                $dbLink->reset();

                // update existed record
                if ($editPage == 'save' && isset($oid) && !$isNew) {
                    $dbLink
                            ->update($config['source'])
                            ->set($_data)
                            ->where('ID', '=', $oid);
                } else {
                    $dbLink
                            ->insertInto($config['source'])
                            ->fields(array_keys($_data))
                            ->values(array_values($_data));
                }
                $dbLink->query();
                // after save hook
                if (isset($actionHooks['ON_AFTER_SAVE'])) {
                    $__action = $actionHooks['ON_AFTER_SAVE'];
                    $__action($config, $_data);
                }
                // send email
            }
        }
        // get data
        if ($editPage == 'edit' && !$isNew && !$doNotFetchData) {
            $com['SOURCE'] = $dbLink
                    ->reset()
                    ->select('*')
                    ->from($config['source'])
                    ->where('ID', '=', $oid)
                    ->fetchRow();
            // truncate on load
            $fieldsToTruncate = $config['form']['edit']['truncateOnLoad'];
            foreach ($fieldsToTruncate as $_fldName)
                $com['SOURCE'][$_fldName] = null;
            //var_dump($com['SOURCE']);
        }

        $com['ISNEW'] = $isNew;
        if ($editPage == "save" || $editPage == "cancel" || $isNew)
            $com['FORM_ACTION'] = libraryRequest::getNewUrl(array('oid', 'action'), array(null, 'new'), array('page'));
        else
            $com['FORM_ACTION'] = libraryRequest::getNewUrl(false, false, array('page'));
        $com['EDIT_PAGE'] = getNonEmptyValue($editPage, "new");
        //echo "EDIT PAGE IS:  " . $com['EDIT_PAGE'];
        return $com;
    }

}

?>
