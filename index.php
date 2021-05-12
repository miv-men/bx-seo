<?php
/*** В каталоге: ***/
$seoData = new miv\SeoText($_SERVER["SERVER_NAME"]);

$pageNumber = intVal($_REQUEST['PAGEN_2']) ? intVal($_REQUEST['PAGEN_2']) : false;
$arSeoParams = array(
    'section_id' => $arResult["VARIABLES"]["SECTION_ID"],
    'page_number' => $pageNumber
);

$seoData->initData('section', $arSeoParams); // Получаем сео тексты в зависимости от раздела и № страницы

/* Задаем мета теги */
$APPLICATION->SetPageProperty("title", $seoData->title);
$APPLICATION->SetPageProperty("description", $seoData->description);

echo '<h1>' . $seoData->h1 . '</h1>'; // Заголовок страницы

/* Описание раздела (выводим только для первой страницы) */
if ( strlen($seoData->top_description['TEXT']) && strpos($_SERVER['REQUEST_URI'], 'PAGEN_') === false): ?>
    <div class="section__top-description">
        <?php if ($seoData->top_description['TYPE'] == 'HTML'): ?>
            <?=html_entity_decode($seoData->top_description['TEXT'])?>
        <?php else: ?>
            <?=$seoData->top_description['TEXT']?>
        <?php endif; ?>
    </div>
<?php endif; ?>
