<?php

include 'src/QtHP';
define('ROOT_PATH', dirname(__FILE__) . '/');

QCommandLine::cliCheck();
$args = QCommandLine::parseArgs(array(
    'run-test' => false
));

$startOffset = strlen(QTHP_LIBRARY_PATH);
$includes = new QMap();

/*********************************
 * Rebuilding QtHP include paths *
 *********************************/
QCommandLine::coutl('Building QtHP files paths');
$methods = 0;
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
        $refl = new ReflectionClass($name);
        $methods += count($refl->getMethods());
    }
}

QCommandLine::coutl('Writing QtHP include files');
QDir::rmDir('../QtHPInclude');
QDir::mkPath('../QtHPInclude');
foreach($includes as $path => $content){
    $f = QFile::openFile($path, QFile::WriteOnly | QFile::Truncate);
    $f->write($content);
    $f->close();
}


if($args->has('run-tests')){
    
    QCommandLine::coutl('Running unit tests');
    QCommandLine::coutl($methods . ' methods have to be tested', false);
    
    QDir::setCurrentPath(ROOT_PATH);
    $testDir = new QDir('./tests/');
    $tests = $fail = $success = 0;
    $failedTests = new QMap;
    
    foreach($testDir->entryInfoList('\.php$') as $testFile){
        include $testFile->canonicalFilePath();
        $r = new ReflectionClass($testFile->basename());
        $name = substr($testFile->basename(), 0, -8);
        QCommandLine::coutl('Testing class ' . $name);
        $testBeforeClass = $tests;
        $successBeforeClass = $success;
        $failsBeforeClass = $fail;
        $assertionsBeforeClass = QUnitTestCase::assertions();
        foreach($r->getMethods() as $method){
            if(strpos($method->getName(), 'test') === 0){
                try {
                    ++$tests;
                    $method->invoke($r->newInstanceArgs());
                    ++$success;
                } catch(QUnitTestCaseCaseException $e){
                    ++$fail;
                    $failedTests->insert($name . '::' . $method->getName(), $e->getMessage());
                }
            }
        }
        QCommandLine::coutl(
            "\t" . (QUnitTestCase::assertions()-$assertionsBeforeClass) . ' assertions - '
            . ($tests-$testBeforeClass) . ' tests - '
            . ($fails-$failsBeforeClass) . ' tests failed - '
            . ($success-$successBeforeClass) . ' tests succeed'
        );
    }
    QCommandLine::coutl(QCommandLine::endl);
    if($failedTests->size() > 0){
        QCommandLine::cout(QCommandLine::endl . QCommandLine::endl);
        QCommandLine::coutl('================');
        QCommandLine::coutl('= Failed tests =');
        QCommandLine::coutl('================');
        foreach($failedTests as $name => $message){
            QCommandLine::coutl('Test : ' . $name . ' failed with message :');
            QCommandLine::coutl($message);
            QCommandLine::cout(QCommandLine::endl);
        }
        QCommandLine::coutl(QUnitTestCase::assertions() . ' assertions - ' . $tests . ' tests - ' . $fail . ' tests failed - ' . $success . ' tests succeed');
    } else {
        QCommandLine::coutl(QUnitTestCase::assertions() . ' assertions - ' . $tests . ' tests - ' . $fail . ' tests failed - ' . $success . ' tests succeed');
        QCommandLine::cout(QCommandLine::endl . QCommandLine::endl);
        QCommandLine::coutl('====================');
        QCommandLine::coutl('= All tests passed =');
        QCommandLine::coutl('====================');
        QCommandLine::coutl('Congratulations buddy, you\'re all set ! ;)');
    }
}


/*************
 * Functions *
 *************/
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
