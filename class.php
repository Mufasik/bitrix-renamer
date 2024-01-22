<?

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

class FBRenamer {
    
    public $langPath = '/local/php_interface/user_lang/ru/';
    public $langPHP = '/local/php_interface/user_lang/ru/lang.php';
    public $langTXT = '/local/php_interface/user_lang/ru/lang.txt';
    public $langRoot = '/bitrix';
    public $root;
    public $arrSearch;
    public $arrRename;

    function __construct($root, $arrSearch, $arrRename) {
        $this->langPath = $root . $this->langPath;
        $this->langPHP = $root . $this->langPHP;
        $this->langTXT = $root . $this->langTXT;
        $this->langRoot = $root . $this->langRoot;
        $this->root = $root;
        $this->arrSearch = $this->arrayСonverter($arrSearch);
        $this->arrRename = $this->arrayСonverter($arrRename);
    }

    /** конвертор массива строк в "текст", "Текст", "ТЕКСТ" с очисткой от спец символов */
    function arrayСonverter($arr_data) {
        $arr_temp = [];
        $chars = ["'", "\"", "?", "<", ">", ";"];
        foreach ($arr_data as $arr) {
            $arr = str_replace($chars, '', $arr);
            if ($arr) {
                $arr = mb_strtolower($arr);
                $arr_temp[] = $arr;
                $arr_temp[] = mb_strtoupper(mb_substr($arr, 0, 1)) . mb_substr($arr, 1);
                $arr_temp[] = mb_strtoupper($arr);
            }
        }
        return $arr_temp;
    }

    /** поиск + замена из текущего файла и добавление в сводный языковой файл*/
    function RenameFromFile($file) {
        $data = file_get_contents($file);
        $data = str_replace($this->arrSearch, $this->arrRename, $data, $count);
        if ($count) {
            $phpd = ["<?php", "<?", "?>"];
            $file = str_replace($this->root, "'", $file) . "'";
            $data = str_replace("\$MESS", "\$MESS[$file]", $data);
            $data = str_replace($phpd, "", $data);
            file_put_contents($this->langPHP, $data, FILE_APPEND);
            return true;
        }
        return false;
    }

    /** создаем путь, проверяем / создаем файлы и возвращяем список языковых файлов */
    function GetFiles() {

        // создаем путь через Битрикс апи
        CheckDirPath($this->langPath);

        $f = fopen($this->langPHP, "w") or die("Unable to open file!");
        fwrite($f,"<?\n");
        fclose($f);

        // текстовый файл со списком всех языковых файлов
        if (file_exists($this->langTXT)) {
            $files = explode("\n", file_get_contents($this->langTXT));
        }
        else {
            $files = [];
            $f = fopen($this->langTXT, "w") or die("Unable to open file!");
            $iterator = new RecursiveDirectoryIterator($this->langRoot);
            $display = [ 'lang', 'ru', 'php' ];
            foreach(new RecursiveIteratorIterator($iterator) as $file) {
                $data = explode('/', $file);
                $data[] = strtolower(array_pop(explode('.', $file)));
                $intersect = array_intersect($data, $display);
                if ( count($intersect) == count($display) ) {
                    fwrite($f, "$file\n");
                    $files[] = $file;
                }
            }
            fclose($f);
        }

        return $files;
    }

    /** вывод данных для тестов */
    function GetData() {
        echo "<pre>";
        echo "arrSearch";
        print_r($this->arrSearch);
        echo "arrRename";
        print_r($this->arrRename);
        echo "</pre>";
    }

    /** запуск обработки */
    function Start() {

        // создаем список файлов
        $files = $this->GetFiles();
        echo "<br>всего файлов - " . count($files);

        // проверяем файлы
        $count = 0;
        foreach ($files as $file) {
            if ($this->RenameFromFile($file)) $count += 1;
            // break;
        }
        echo "<br>изменений - " . $count;
    }

}