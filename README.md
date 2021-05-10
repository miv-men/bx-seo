# Сео тексты для сайта на 1с-Битрикс
Класс позваляет настроить уникальные сео данные для разных регионов и разных разделов.

## Инфоблоки:
Необходимо в админке добавить насколько ИБ:
1. Список городов
  Свойства:
  - URL поддомена для редиректа (код: REDIRECT)
2. Сео-тексты
  Свойства:
  - (название) - (код)
  - Title - TITLE
  - Description - DESCRIPTION
  - Заголовок H1 - H1
  - Текст под h1 - TOP_DESCRIPTION
  - Город - CITY (привязан к ИБ "Список городов")
  - Раздел каталога - SECTION (привязан к разделам каталога) 

## Установка
### 1. Подключить через init.php:
  1. Добавьте в каталог php_interface данный репозиторий.
  ```bash
    cd bitrix/php_interface
    git clone https://github.com/miv-men/bx-seo.git
  ```
  3. Подключить данный файл в init.php
  ```php
    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/bx-seo/class/SeoText.php"))
      require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/bx-seo/class/SeoText.php";
  ```

### 2. Подклчить через composer
Вот вот добавлю.
