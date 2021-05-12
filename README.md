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


## Использование:
Cоздание экземпляра класса:
```php
$seoData = new miv\SeoText($_SERVER["SERVER_NAME"]); // передаем текущий домен, необходимо если у вас настроена региональность на поддоменах
```

При использовании класса в каталоге необходимо передать в метод initData раздел и страницу:
```php
$pageNumber = intVal($_REQUEST['PAGEN_2']) ? intVal($_REQUEST['PAGEN_2']) : false;
$arSeoParams = array(
    'section_id' => $arResult["VARIABLES"]["SECTION_ID"],
    'page_number' => $pageNumber
);
$seoData->initData('section', $arSeoParams); // Первым параметрам где применять, вторым массив фильтров.
```

Получение мета данных и текстов:
```php
$seoData->title // "title"
$seoData->description // "description"
$seoData->h1 // Заголовок h1
$seoData->text // Основной текст
$seoData->top_description['TEXT'] // Описание
```
