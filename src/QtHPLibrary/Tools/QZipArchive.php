<?php

/**
 * @author Romain Bala
 * @version 1.0
 */
class QZipArchive extends ZipArchive {

    const Dirs = 0x01,
          Files = 0x02;

    /**
     * Extrait une archive
     * @param string $source Le fichier d'entree
     * @param string $dest Le repertoire de sortie
     * @param bool $keepSubDirs [optionel] true pour cr�er les sous r�pertoire, false sinon [default=true]
     * @throws DZipArchiveException Si l'archive ne peut etre ouverte ou si l'extraction echoue
     */
    public static function unzip($source, $dest, $keepSubDirs = true){
        $z = new self;
        if(($rc = $z->open($source)) !== true){
            throw new QZipArchiveException('Unable to open file "' . $source . '" (Code : "' . $rc . '")', $rc);
        }
        if($keepSubDirs){
            if(!$z->extractTo($dest)){
                throw new QZipArchiveException('Unable to extract from "' . $source . '" vers "' . $dest . '"');
            }
        } else {
            for($i = 0; $i < $z->numFiles; ++$i){
                $filename = $z->getNameIndex($i);
                if(substr($filename, -1) != '/'){
                    $fi = new QFileInfo($filename);
                    copy('zip://' . $source. '#' . $filename, $dest . $fi->filename());
                }
            }
        }
        $z->close();
    }

    /**
     * Cree une archive
     * @param DFileInfo|DFile|ArrayAccess|array|string $entries Le nom du ou des fichiers a archiver et compresser
     * @param string $dest Le nom de l'archive a creer (Flag utilises : ZipArchive::CREATE | ZipArchive::OVERWRITE)
     * @throws DZipArchiveException Si quelque chose se passe mal
     */
    public static function zip($entries, $dest){
        $dest = $dest instanceof QFileInfo ? $dest : new QFileInfo($dest);
        $z = new ZipArchive;
        if(($rc = $z->open($dest->canonicalFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE)) !== true){
            throw new QZipArchiveException('Impossible de créer l\'archive "' . $dest->canonicalFilePath() . '" rc=[' . $rc . ']');
        }
        if(is_array($entries) || $entries instanceof ArrayAccess) {
            foreach($entries as $zipEntry => $entry){
                if(is_int($zipEntry)){
                    $zipEntry = substr($entry, strrpos($entry, '/')+1);
                }
                $entry = new QFileInfo($entry);
                if(!$entry->exists() || !$entry->isReadable() || !$z->addFile($entry->canonicalFilePath(), $zipEntry)){
                    throw new QZipArchiveException('Impossible d\'ajouter le fichier "' . $entry . '" dans l\'archive "' . $dest->filename() . '"');
                }
            }
        } else {
            try {
                $entries = new QFileInfo($entries);
                if(!$entries->exists() || !$entries->isReadable() || !$z->addFile($entries->canonicalFilePath(), $entries->absoluteFilePath())){
                    throw new QZipArchiveException('Impossible d\'ajouter le fichier "' . $entries  . '" dans l\'archive "' . $dest->filename() . '"');
                }
            } catch (QFileInfoException $ex) {
                throw new QZipArchiveException('Impossible de traiter les fichiers (ArrayAccess|array|string|DFile|DFileInfo uniquement)');
            }
        }
        if(!$z->close()){
            throw new QZipArchiveException('Impossible d\'enregistrer l\'archive "' . $dest->canonicalFilePath() . '"');
        }
        return $z;
    }

    public static function listFiles($source, $flags = 0x03){ // $flags = self::Dirs | self::Files
        if(($flags & (self::Dirs & self::Files)) == 0){
            return new QStringList;
        }
        $z = new self;
        $list = new QStringList;
        if(($rc = $z->open($source)) !== true){
            throw new QZipArchiveException('Unable to open file "' . $source . '" (Code : "' . $rc . '")');
        }
        for($i = 0; $i < $z->numFiles; ++$i){
            $filename = $z->getNameIndex($i);
            if(($flags & self::Dirs) && substr($filename, -1) == '/'){
                $list->append($filename);
            } else {
                $list->append($filename);
            }
        }
        return $list;
    }
}

class QZipArchiveException extends Exception{}

?>