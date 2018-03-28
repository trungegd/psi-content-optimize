<?php
if (!empty($_POST)) {

    /** PROCESS DATA **/
    $listHtml = isset($_POST['list']) ? $_POST['list'] : '';
    $html = str_get_html($listHtml);

    $listWebFiles = array();
    $listDuplicateFiles = array();

    if ($html) {
        foreach ($html->find('li > span') as $element) {
            $urlParts = explode("/", $element->{'data-title'});
            $fileName = $urlParts[count($urlParts) - 1];
            $parseUrl = parse_url($element->{'data-title'});

            if (!array_key_exists($fileName, $listWebFiles)) {
                $listWebFiles[$fileName] = [
                    'filename' => $fileName,
                    'path' => str_replace($fileName, '', $parseUrl['path'])
                ];
            } else {
                if (!array_key_exists($fileName, $listDuplicateFiles)) {
                    $listDuplicateFiles[] = $fileName;
                }
            }

            // remove duplicate
            foreach ($listDuplicateFiles as $file => $value) {
                unset($listWebFiles[$file]);
            }
        }
    }
    /** END PROCESS DATA **/

    /** CREATE ZIP FILES **/

    $file = isset($_FILES['file']) ? $_FILES['file'] : null;

    $currentTime = time();

    if (!is_dir('tmp')) {
        mkdir('tmp', 0777);
    }

    $zipPath = 'tmp' . DS . $currentTime;
    if (!is_dir($zipPath)) {
        mkdir($zipPath);
    }

    $zip = new ZipArchive();
    $res = $zip->open($file['tmp_name']);

    foreach ($listWebFiles as $file) {
        if (!is_dir($zipPath . $file['path'])) {
            mkdir($zipPath . $file['path'], 0777, true);
        }

        $zip->extractTo($zipPath . $file['path'], 'image/' . $file['filename']);
    }

    $zip->close();

    /** END CREATE ZIP FILES **/
    $za = new FlxZipArchive;
    $res = $za->open($zipPath . '.zip', ZipArchive::CREATE);

    if ($res === TRUE) {
        $za->addDir($zipPath, '');
        $za->close();
    }

    $zipName = $zipPath . '.zip';
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$zipName");
    header("Content-length: " . filesize($zipName));
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("$zipName");
}