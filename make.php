<?php

include 'src/QtHP';

$startOffset = strlen(QTHP_LIBRARY_PATH);
$includes = new QMap();

foreach([
    [QTHP_LIBRARY_PATH, './QtHPLibrary', 'QTHP_LIBRARY_PATH'],
    [QTHP_FRAMEWORK_PATH, './QtHPFramework', 'QTHP_FRAMEWORK_PATH']
] as $nfo){
    $files = new QMap;
    listFiles($nfo[0], $files);

    QDir::setCurrentPath(QTHP_PATH);
    QDir::setCurrentPath($nfo[1]);

    foreach($files as $name => $fileInfo){
        $fi = new QFileInfo(substr(QDir::toRelativePath($fileInfo->canonicalFilePath()), 2));
        $includes->insert('../QtHPInclude/' . strtolower($fileInfo->completeBasename()), '<?php include ' . $nfo[2] . '.\'' . $fi->path() . $fi->completeBasename() . '\'.QTHP_CLASS_EXT;?>');
    }
}

QDir::rmDir('../QtHPInclude');
QDir::mkPath('../QtHPInclude');
foreach($includes as $path => $content){
    $f = QFile::openFile($path, QFile::WriteOnly | QFile::Truncate);
    $f->write($content);
    $f->close();
}

function listFiles($path, &$map){
    $d = new QDir($path);
    $ext = substr(QTHP_CLASS_EXT, 1);
    // Get files
    foreach($d->entryInfoList('^[^.].*', QDir::Files | QDir::NoDotAndDotDot) as $fileInfo){
        if($fileInfo->suffix() == $ext){
            if(!$map->has($fileInfo->completeBasename())){
                $map->insert($fileInfo->completeBasename(), $fileInfo);
            } else {
                echo $fileInfo->completeBasename() . ' already exists. Delete one of the following files : ' . "\n";
                echo $fileInfo->canonicalFilePath() . "\n";
                echo $map->value($fileInfo->completeBaseName())->canonicalFilePath();
                exit;
            }
        }
    }
    // Recursvely !
    foreach($d->entryInfoList('^[^.].*', QDir::Dirs | QDir::NoDotAndDotDot) as $dirInfo){
        listFiles($dirInfo->canonicalPath(), $map);
    }
}
