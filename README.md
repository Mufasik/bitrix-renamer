# bitrix-renamer
 
тестируем замену всех названий на другие

реализация через файл - \bitrix\www\local\php_interface\user_lang\ru\lang.php

## Процесс работы скрипта (поиск в стандартных компонентах папки *ru)
- \bitrix\www\bitrix\components\bitrix\***\lang\ru - и там все файлы php
- считываем все $MESS["код фразы"] = "текст";
- если в тексте есть искомое значение, сохраняем путь к файлу, код фразы и новый текст в файл реализации

Пример меняем "Задач", "Задачи", "Задача" на "Активн."
