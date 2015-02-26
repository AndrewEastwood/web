<?php
namespace web\plugins\shop\api;

use \engine\lib\uploadHandler as JqUploadLib;
use \engine\lib\path as Path;
use \engine\lib\api as API;
use PHPExcel as PHPExcel;
use PHPExcel_IOFactory as PHPExcel_IOFactory;
use PHPExcel_Cell_DataValidation as PHPExcel_Cell_DataValidation;
use PHPExcel_Shared_File as PHPExcel_Shared_File;

class feeds {

    public function getDirNameFeeds () {
        return 'feeds';
    }

    public function getUploadedFeedName () {
        return 'import_' . date('Ymd_His');
    }

    public function getGeneratedFeedName () {
        return 'gen_' . date('Ymd_His');
    }

    public function getGeneratedFeedDownloadLink ($name) {
        return $this->getFeedsUploadDir() . $name;
    }

    public function getFeedsUploadInnerDir () {
        $path = Path::createDirPath('shop', $this->getDirNameFeeds());
        return $path;
    }

    public function getFeedsUploadDir () {
        return Path::getUploadDirectory($this->getFeedsUploadInnerDir());
    }

    public function getFeedFilePathByName ($feedName) {
        return $this->getFeedsUploadDir() . $feedName . '.xls';
    }

    public function getGeneratedFeedsFilesList () {
        return glob(Path::rootPath() . $this->getFeedsUploadDir() . 'gen_*\.xls');
    }

    public function getUploadedFeedsFilesList () {
        return glob(Path::rootPath() . $this->getFeedsUploadDir() . 'import_*\.xls');
    }

    public function getFeeds () {
        global $app;
        $attempts = 20;
        $feeds = array();

        do {
            $listFeedsGenerated = $this->getGeneratedFeedsFilesList();
            if (count($listFeedsGenerated) > 10) {
                $pInfo = pathinfo($listFeedsGenerated[0]);
                $success = unlink($listFeedsGenerated[0]);
                if ($success)
                    API::getAPI('system:tasks')->deleteTaskByParams('shop', 'importProductFeed', $pInfo['filename']);
            }
            $attempts--;
        } while (count($listFeedsGenerated) > 10 && $attempts > 0);

        $attempts = 20;
        do {
            $listFeedsUploaded = $this->getUploadedFeedsFilesList();
            if (count($listFeedsUploaded) > 10) {
                unlink($listFeedsUploaded[0]);
            }
            $attempts--;
        } while (count($listFeedsUploaded) > 10 && $attempts > 0);

        if ($listFeedsGenerated)
            foreach ($listFeedsGenerated as $value) {
                $pInfo = pathinfo($value);
                $ftime = filectime($value);
                $feeds[] = array(
                    'ID' => md5($pInfo['filename']),
                    'type' => 'generated',
                    'time' => $ftime,
                    'timeFormatted' => date('Y-m-d H:i:s', $ftime),
                    'name' => $pInfo['filename'],
                    'link' => $this->getGeneratedFeedDownloadLink($pInfo['basename'])
                );
            }
        if ($listFeedsUploaded) {
            // $activeTasks = API::getAPI('system:tasks')->getActiveTasksByGroupName('shop');
            // $completeTasks = API::getAPI('system:tasks')->getCompletedTasksByGroupName('shop');
            // $newTasks = API::getAPI('system:tasks')->getNewTasksByGroupName('shop');
            // $canceledTasks = API::getAPI('system:tasks')->getCanceledTasksByGroupName('shop');
            // $runningFeedNames = array();
            // $completeFeedNames = array();
            // $newFeedNames = array();
            // $canceledFeedNames = array();
            // if ($activeTasks)
            //     foreach ($activeTasks as $taskItem) {
            //         $runningFeedNames[] = $taskItem['Params'];
            //     }
            // if ($completeTasks)
            //     foreach ($completeTasks as $taskItem) {
            //         $completeFeedNames[] = $taskItem['Params'];
            //     }
            // if ($newTasks)
            //     foreach ($newTasks as $taskItem) {
            //         $newFeedNames[] = $taskItem['Params'];
            //     }
            // if ($canceledTasks)
            //     foreach ($canceledTasks as $taskItem) {
            //         $canceledFeedNames[] = $taskItem['Params'];
            //     }
            // var_dump($runningFeedNames);
            foreach ($listFeedsUploaded as $value) {
                $pInfo = pathinfo($value);
                $ftime = filectime($value);
                $task = API::getAPI('system:tasks')->isTaskAdded('shop', 'importProductFeed', $pInfo['filename']);
                // $isActive = in_array($pInfo['filename'], $runningFeedNames);
                // $isCompleted = in_array($pInfo['filename'], $completeFeedNames);
                // $isAdded = in_array($pInfo['filename'], $newFeedNames);
                // $isCanceled = in_array($pInfo['filename'], $canceledFeedNames);
                $isScheduled = $task['Scheduled'];
                $isRunning = $task['IsRunning'];
                $isCompleted = $task['Complete'];
                $isCanceled = $task['ManualCancel'];
                $feeds[] = array(
                    'ID' => md5($pInfo['filename']),
                    'type' => 'uploaded',
                    'time' => $ftime,
                    'timeFormatted' => date('Y-m-d H:i:s', $ftime),
                    'name' => $pInfo['filename'],
                    'new' => !$isScheduled && !$isRunning && !$isCompleted && !$isCanceled,
                    'scheduled' => $task['Scheduled'],
                    'running' => $task['IsRunning'],
                    'complete' => $task['Complete'],
                    'canceled' => $task['ManualCancel'],
                    'results' => $task['Result'],
                    // 'canBeScheduled' => !$task['scheduled'],
                    'status' => $isRunning ? 'active' : ($isCompleted ? 'done' : ($isCanceled ? 'canceled' : ($isScheduled ? 'scheduled' : 'new'))),
                    'link' => $this->getGeneratedFeedDownloadLink($pInfo['basename'])
                );
            }
        }
            // $feeds['active'] = $activeTasks;
        return $feeds;
    }
    public function importProductFeed ($name) {
        global $app;

        $results = array();
        $task = API::getAPI('system:tasks')->isTaskAdded('shop', 'importProductFeed', $name);

        if (ob_get_level() == 0) ob_start();

        $customer = API::getApi('system:customers')->getRuntimeCustomer();
        $feedPath = Path::rootPath() . $this->getFeedFilePathByName($name);
        // $objPHPExcel = new PHPExcel();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel = PHPExcel_IOFactory::load($feedPath);
        // $objPHPExcel->setReadDataOnly(true);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();

        $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
        $headingsArray = $headingsArray[1];

        $r = -1;
        $namedDataArray = array();
        for ($row = 2; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                ++$r;
                foreach($headingsArray as $columnKey => $columnHeading) {
                    $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                }
            }
        }

        $errors = array();
        $parsedProducts = array();
        $addedCount = 0;
        $updatedCount = 0;
        $errorCount = 0;
        $total = count($namedDataArray);
        $processed = 0;

        $keysToEncode = array('Name', 'Model', 'CategoryName', 'OriginName',
            'Description', 'Features', 'TAGS', 'WARRANTY');
        // convert to native structure
        foreach ($namedDataArray as &$rawProductData) {

            //-- echo "processing product " . $rawProductData['Name'] . PHP_EOL;
            $results[] .= $rawProductData['Name'];
            ob_flush();
            flush();

            $productItem = array();
            $productItem['Name'] = trim($rawProductData['Name']);
            $productItem['Model'] = trim($rawProductData['Model']);
            $productItem['CategoryName'] = trim($rawProductData['CategoryName']) ?: 'noname';
            $productItem['OriginName'] = trim($rawProductData['OriginName']);
            $productItem['Price'] = floatval($rawProductData['Price']);
            $productItem['Status'] = $rawProductData['Status'];
            $productItem['IsPromo'] = $rawProductData['IsPromo'] === '+';
            $productItem['TAGS'] = $rawProductData['TAGS'];
            $productItem['Description'] = trim($rawProductData['Description']);
            $productItem['Features'] = null;
            $productItem['WARRANTY'] = $rawProductData['WARRANTY'];


            // $results[] = "[INFO] " . "set encoding";
            //-- echo "[INFO] " . "set encoding" . PHP_EOL;
            foreach ($keysToEncode as $key) {
                $productItem[$key] = mb_convert_encoding((string)$productItem[$key], 'UTF-8', mb_list_encodings());
            }

            // var_dump($productItem);

            if (empty($productItem['OriginName'])) {
                $errors[] = 'No OriginName for ' . $productItem['Name'] . ' ' . $productItem['Model'];
                $errorCount++;
                continue;
            }

            // $results[] = "[INFO] " . "adjusting features";
            //-- echo "[INFO] " . "adjusting features" . PHP_EOL;
            $featureChunks = explode('|', $rawProductData['Features']);
            $features = array();
            foreach ($featureChunks as $featureChunkItem) {
                $featureKeyValue = explode('=', $featureChunkItem);
                if (count($featureKeyValue) !== 2) {
                    $errors[] = 'Unable to parse feature chunk: ' . $featureChunkItem;
                } else {
                    $features[$featureKeyValue[0]] = $featureKeyValue[1];
                }
            }
            $productItem['Features'] = $features;

            // $results[] = "[INFO] " . "downloading images";
            //-- echo "[INFO] " . "downloading images" . PHP_EOL;
            // var_dump($rawProductData['Images']);
            $images = array();
            $imagesUrls = explode(PHP_EOL, $rawProductData['Images']);
            // $imagesToDownload = array();
            $urls = $app->getSettings('urls');
            $options = array(
                'script_url' =>  $urls->upload,
                'download_via_php' => true,
                'web_import_temp_dir' => Path::rootPath() . Path::getAppTemporaryDirectory(),
                'upload_dir' => Path::rootPath() . Path::getUploadTemporaryDirectory(),
                'print_response' => false,
                'use_unique_hash_for_names' => true,
                'correct_image_extensions' => true,
                'mkdir_mode' => 0777
            );
            $upload_handler = new JqUploadLib($options, false);
            foreach ($imagesUrls as $imgUrl) {
                $urlInfo = parse_url($imgUrl);
                $pInfo = pathinfo($urlInfo['path']);
                if ($urlInfo['host'] !== $customer['HostName']) {
                    // $imagesToDownload[] = $imgUrl;
                    // $results[] = "[INFO] " . "downloading image: " . $imgUrl;
                    //-- echo "[INFO] " . "downloading image" . $imgUrl . PHP_EOL;
                    set_time_limit(120);
                    echo '# ... importing image ' . $imgUrl . PHP_EOL;
                    $res = $upload_handler->importFromUrl($imgUrl, false);
                    foreach ($res['web'] as $impageUploadInfo) {
                        $images[] = $impageUploadInfo->name;
                    }
                } else {
                    $images[] = $pInfo['basename'];
                }
                // currently we support only 5 images
                if (count($images) >= 5) {
                    break;
                }
            }
            // var_dump($imagesToDownload);
            // download image here
            // $urls = array();
            // $urls[] = 'http://upload.wikimedia.org/wikipedia/commons/6/66/Android_robot.png';
            // $urls[] = 'http://www.notebookcheck.net/uploads/tx_nbc2/delXPS14.jpg';
            // var_dump($res);
            for ($i = 0, $cnt = count($images); $i < $cnt; $i++) {
                $productItem['file' . ($i + 1)] = $images[$i];
                
            }
            // // $productItem['Images'] = $images;
            // var_dump($productItem);
            // return;

            // var_dump("[[[[[[[[[[[[[[ inpuda data >>>>>>>>>>>>>>>>>>>>>>>>>>>>");
            // var_dump($productItem);
            // $results[] = "[INFO] " . "saving product";
            // echo "[INFO] " . "saving product" . PHP_EOL;
            $res = API::getAPI('shop:products')->updateOrInsertProduct($productItem);
            // var_dump("***************** result *****************");
            // var_dump($res);
            if ($res['created']) {
                //-- echo "[INFO] new product created" . PHP_EOL;
                $results[] = "[I] cr";
                $addedCount++;
            } elseif ($res['updated']) {
                //-- echo "[INFO] updating existent product " . $res['ID'] . PHP_EOL;
                $results[] = "[I] up. " . $res['ID'];
                $updatedCount++;
            } else {
                $errorCount++;
            }
            if (!empty($res['errors'])) {
                $results[] = "[F] " . $rawProductData['Name'];
                $results[] = print_r($res['errors'], true);
                //-- echo "[FAILED] " . $rawProductData['Name'] . PHP_EOL;
                // var_dump($res['errors']);
                ob_flush();
                flush();
            }
            if ($res['success']) {
                $results[] = "[S] " . $rawProductData['Name'];
                //-- echo "[SUCCESS] " . $rawProductData['Name'] . PHP_EOL;
                ob_flush();
                flush();
            } else {
                $results[] = "[E] " . $rawProductData['Name'];
                //-- echo "[ERROR] " . $rawProductData['Name'] . PHP_EOL;
                ob_flush();
                flush();
            }
            $errors = array_merge($errors, $res['errors']);
            // $parsedProducts[] = $productItem;
            // if (count($parsedProducts) > 1) {
            //     break;
            // }
            $processed++;
            //-- echo "[INFO] " . "parsed products count " . $processed . " of " . $total . PHP_EOL;
            set_time_limit(60);
            // var_dump("********************************************");
        }

        // disable all products
        // API::getAPI('shop:products')->archiveAllProducts();

        // var_dump($errors);
        $res = array(
            // 'parsedProducts' => $parsedProducts,
            'total' => count($parsedProducts),
            'productsAdded' => $addedCount,
            'productsUpdated' => $updatedCount,
            'productsInvalid' => $errorCount,
            'success' => empty($errors),
            // 'errors' => $errors,
            'results' => $results
        );

        // var_dump($task);
        // API::getAPI('system:tasks')->setTaskResult($task['ID'], utf8_encode(json_encode($results)));
        API::getAPI('system:tasks')->setTaskResult($task['ID'], print_r($res, true));

        ob_end_flush();
        // if (ob_get_length()) ob_end_clean();
        // ob_end_clean();
        // if (ob_get_level() == 0) ob_start();
        // echo '<pr>';
        // var_dump($namedDataArray);
        // echo '</pre><hr />';
        // var_dump($objPHPExcel);
        // var_dump($feedPath);
        // $urls = array();
        // $urls[] = 'http://upload.wikimedia.org/wikipedia/commons/6/66/Android_robot.png';
        // $urls[] = 'http://www.notebookcheck.net/uploads/tx_nbc2/delXPS14.jpg';
        // $options = array(
        //     'script_url' =>  API::getAPI('system:tasks')->getConfiguration()->urls->upload,
        //     'download_via_php' => true,
        //     'web_import_temp_dir' => Path::getAppTemporaryDirectory(),
        //     'upload_dir' => Path::getUploadTemporaryDirectory(),
        //     'print_response' => $_SERVER['REQUEST_METHOD'] === 'GET'
        // );
        // $upload_handler = new JqUploadLib($options, false);
        // $res = $upload_handler->importFromUrl($urls, false);
        // return $res;
    }

    public function generateProductFeed () {
        global $app;
        $customer = API::getApi('system:customers')->getRuntimeCustomer();
        $options = array('limit' => 0);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("PHPExcel Test Document")
            ->setSubject("PHPExcel Test Document")
            ->setDescription("Test document for PHPExcel, generated using PHP classes.")
            ->setKeywords("office PHPExcel php")
            ->setCategory("Test result file");
        $dataList = API::getAPI('shop:products')->getProducts_List($options, false, false);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Name')
            ->setCellValue('B1', 'Model')
            ->setCellValue('C1', 'CategoryName')
            ->setCellValue('D1', 'OriginName')
            ->setCellValue('E1', 'Price')
            ->setCellValue('F1', 'Status')
            ->setCellValue('G1', 'IsPromo')
            ->setCellValue('H1', 'WARRANTY')
            ->setCellValue('I1', 'TAGS')
            ->setCellValue('J1', 'Features')
            ->setCellValue('K1', 'Description')
            ->setCellValue('L1', 'Images');
        $objPHPExcel->getActiveSheet()->getStyle('A1:K1')->getFont()->setBold(true);
        for ($i = 0, $j = 2, $len = count($dataList['items']); $i < $len; $i++, $j++) {
            $images = array();
            $features = array();
            $warranty = '';
            $tags = '';
            // $expire = '';
            // $isbn = '';
            if (!empty($dataList['items'][$i]['Images'])) {
                foreach ($dataList['items'][$i]['Images'] as $value) {
                    $images[] = $customer['Protocol'] . '://' . $customer['HostName'] . $value['normal'];
                }
            }
            if (isset($dataList['items'][$i]['Attributes'])) {
                if (isset($dataList['items'][$i]['Attributes']['TAGS'])) {
                    $tags = $dataList['items'][$i]['Attributes']['TAGS'];
                }
                if (isset($dataList['items'][$i]['Attributes']['WARRANTY'])) {
                    $warranty = $dataList['items'][$i]['Attributes']['WARRANTY'];
                }
                // if (isset($dataList['items'][$i]['Attributes']['EXPIRE'])) {
                //     $expire = $dataList['items'][$i]['Attributes']['EXPIRE'];
                // }
                // if (isset($dataList['items'][$i]['Attributes']['ISBN'])) {
                //     $isbn = $dataList['items'][$i]['Attributes']['ISBN'];
                // }
            }
            if (isset($dataList['items'][$i]['Features'])) {
                foreach ($dataList['items'][$i]['Features'] as $featureGroupName => $featureGroupItems) {
                    $features[] = $featureGroupName . '=' . join(',', array_values($featureGroupItems));
                }
            }
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('A' . $j, $dataList['items'][$i]['Name']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('B' . $j, $dataList['items'][$i]['Model']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('C' . $j, $dataList['items'][$i]['_category']['Name']);
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('D' . $j, $dataList['items'][$i]['_origin']['Name']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $j, $dataList['items'][$i]['_prices']['price']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $j, $dataList['items'][$i]['Status']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $j, $dataList['items'][$i]['IsPromo'] ? '+' : '');
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('H' . $j, $warranty);//$dataList['items'][$i]['Features']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $j, $tags);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $j, implode('|', $features));//$dataList['items'][$i]['Features']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $j, $dataList['items'][$i]['Description']);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $j, implode(PHP_EOL, $images));

            // add dropdown to status field
            $objValidation = $objPHPExcel->getActiveSheet()->getCell('F' . $j)->getDataValidation();
            $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
            $objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
            $objValidation->setAllowBlank(false);
            $objValidation->setShowInputMessage(true);
            $objValidation->setShowErrorMessage(true);
            $objValidation->setShowDropDown(true);
            $objValidation->setErrorTitle('Input error');
            $objValidation->setError('Value is not in list.');
            $objValidation->setPromptTitle('Pick from list');
            $objValidation->setPrompt('Please pick a value from the drop-down list.');
            $objValidation->setFormula1('"' . join(',', API::getAPI('shop:products')->getProductStatuses()) . '"'); //note this!
        }
        // foreach($objPHPExcel->getActiveSheet()->getRowDimensions() as $rd) { $rd->setRowHeight(-1); }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(150);
        $fileName = Path::rootPath() . $this->getFeedsUploadDir() . $this->getGeneratedFeedName() . '.xls';
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        PHPExcel_Shared_File::setUseUploadTempDirectory(true);
        $objWriter->save($fileName);
        // chmod($fileName, 0644);
        // return $dataList;
    }

    public function get (&$resp, $req) {
        $resp = $this->getFeeds();
    }

    public function post (&$resp, $req) {
        if (isset($req->get['generate'])) {
            $resp = $this->generateProductFeed();
        } elseif (isset($resp['files'])) {
            // var_dump($resp['files']);
            foreach ($resp['files'] as $tempFileItem) {
                $res = Path::moveTemporaryFile($tempFileItem->name, $this->getFeedsUploadInnerDir(), $this->getUploadedFeedName());
                API::getAPI('system:tasks')->addTask('shop', 'importProductFeed', $res['basename']);
                // $this->getPlugin()->saveOwnTemporaryUploadedFile(, , );
            }
        }
    }
    public function patch (&$resp, $req) {
        $activeTasks = API::getAPI('system:tasks')->getActiveTasksByGroupName('shop');
        if (isset($req->data['schedule']) && isset($req->get['name'])) {
            $task = API::getAPI('system:tasks')->isTaskAdded('shop', 'importProductFeed', $req->get['name']);
            if (!empty($task)) {
                if (!$task['Scheduled']) {
                    // API::getAPI('system:tasks')->scheduleTask('shop', 'importProductFeed', $req->get['name']);
                    // this part must be moved into separated process >>>>
                    API::getAPI('system:tasks')->startTask('shop', 'importProductFeed', $req->get['name']);
                    // temporary solution to output json results
                    //-- echo "{ dump: '";
                    $this->importProductFeed($req->get['name']);
                    //-- echo "',";
                    $resp = $this->getFeeds();
                    // <<<< this part must be moved into separated process
                    $resp['success'] = true;
                } else {
                    $this->getFeeds();
                    $resp['error'] = 'CanNotBeStarted';
                }
            } else {
                $this->getFeeds();
                $resp['error'] = 'UnknownTask';
            }
        } else if (isset($req->data['cancel']) && isset($req->get['name'])) {
            $task = API::getAPI('system:tasks')->isTaskAdded('shop', 'importProductFeed', $req->get['name']);
            if (isset($task['Hash'])) {
                API::getAPI('system:tasks')->cancelTask($task['ID']);
                $this->getFeeds();
                $resp['success'] = true;
            } else {
                $this->getFeeds();
                $resp['error'] = 'UnknownTask';
            }
        } else if (count($activeTasks)) {
            $this->getFeeds();
            $resp['error'] = 'ActiveImportFound';
        }
    }

    public function delete (&$resp, $req) {
        $success = false;
        if (isset($req->get['name'])) {
            $task = API::getAPI('system:tasks')->isTaskAdded('shop', 'importProductFeed', $req->get['name']);
            if (isset($taks) && $task['IsRunning']) {
                $resp['error'] = 'UnableToRemoveActiveTask';
            } else {
                $feedPath = Path::rootPath() . $this->getFeedFilePathByName($req->get['name']);
                if (file_exists($feedPath)) {
                    $success = unlink($feedPath);
                    if (isset($task['Hash']) && $success)
                        API::getAPI('system:tasks')->deleteTaskByHash($task['Hash']);
                }
            }
        }
        $resp['success'] = $success;
    }
}

?>