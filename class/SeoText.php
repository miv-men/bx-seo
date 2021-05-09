<?
namespace mivMen;
\Bitrix\Main\Loader::includeModule('iblock');

/**
* Класс для работы с СЕО-данными
* Данные хранятся в инфоблоке "Сео-тексты"
*/
class SeoText{

    const TYPE_SECTION_PROPERTY_VALUE = 922;
    const TYPE_CATALOG_PROPERTY_VALUE = 923;
    const TYPE_MAIN_PAGE_PROPERTY_VALUE = 924;
	  const TYPE_TRIKOTAZH_PAGE_PROPERTY_VALUE = 940;
    const CITY_IBLOCK_ID = 47; //города
    const SEO_IBLOCK_ID = 54; // ID инфоблока "Сео-тексты"
    const DEFAULT_CITY_ID = 54443; //Идентификатор элемента инфоблока "Города и телефоны, для popup'a, контакктов"
    const cacheDir = '/seo_text';
    
    private $_cityId;
    private $_arSeoData;
    
    function __construct($cityId){
        if (is_numeric($cityId)) {
            $this->_cityId = intVal($cityId);        
        } elseif(is_string($cityId)){
            $this->_cityId = $this->getCityIdByDomain($cityId);
            $this->cityArr = $this->getCityNameByDomain($cityId);
        }
        if(!$this->_cityId)
            $this->_cityId = self::DEFAULT_CITY_ID;
            
        $this->_arSeoData = array();
    }

    function getCityIdByDomain($domain) {
            $rsElements = \CIBlockElement::GetList(
                array(),
                array(
                    'IBLOCK_ID' => self::CITY_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'PROPERTY_REDIRECT' => $domain
                )
            );

            if ($arElement = $rsElements->GetNext()) {
                return $arElement["ID"];
            } else {
                return false;
            }
    }

    function getCityNameByDomain($domain) {
        $rsElements = \CIBlockElement::GetList(
            array(),
            array(
                'IBLOCK_ID' => self::CITY_IBLOCK_ID,
                'ACTIVE' => 'Y',
                'PROPERTY_REDIRECT' => $domain
            ),
            false,
            false,
            Array('ID', 'NAME', 'PROPERTY_REDIRECT', 'PROPERTY_SCLONENIE', 'PROPERTY_DELIVERY_ON_WHAT')
        );

        if ($arElement = $rsElements->GetNext()) {
            return $arElement;
        } else {
            return false;
        }
    }
    
    /**
    * Метод возвращает указанный параметр сео-данных
    */
    function __get($name){
    
        if (array_key_exists($name, $this->_arSeoData)) {
            return $this->_arSeoData[$name];
        }
        
        return null;
    }    

    /**
    * Метод инициализирует сео-данные для указанной сущности
    * @param - $dataType string
    */    
    function initData($dataType, $arParams = array()){
    
        switch($dataType){
            case 'main' :
                $this->_initPageData(self::TYPE_MAIN_PAGE_PROPERTY_VALUE);
                break;
            case 'catalog' :
                $this->_initPageData(self::TYPE_CATALOG_PROPERTY_VALUE);
                break;
			case 'trikotazh' :
                $this->_initPageData(self::TYPE_TRIKOTAZH_PAGE_PROPERTY_VALUE);
                break;
            case 'section' :
                $this->_initSectionData($arParams);
                break;
        }
    }
    
    private function _initPageData($type){
    
        $result = array();
        
        $cacheId = 'seo_text_main_list'.$type;
        $cacheTime = 3600*24*30;
        //$cacheTime = 1;
        
        $obCache = new \CPHPCache;        
        
        if ($obCache->InitCache($cacheTime, $cacheId, self::cacheDir)) {
            $vars = $obCache->GetVars();
            $result = $vars['seo_text_main_list'];
        } else {
            $rsElements = \CIBlockElement::GetList(
                array(),
                array(
                    'IBLOCK_ID' => self::SEO_IBLOCK_ID,
                    'ACTIVE' => 'Y',
                    'PROPERTY_SECTION' => false,
                    'PROPERTY_TYPE' => $type,
                )
            );

            while($obElement = $rsElements->GetNextElement()){
                $arElement = $obElement->GetFields();
                $arElement['PROPERTIES'] = $obElement->GetProperties();
                $cityId = !empty($arElement['PROPERTIES']['CITY']['VALUE']) ? intVal($arElement['PROPERTIES']['CITY']['VALUE']) : 'default';                
                $result[$cityId] = $arElement;
            }

            $obCache->StartDataCache($cacheTime, $cacheId, self::cacheDir);
            
            if (defined("BX_COMP_MANAGED_CACHE")){
                $GLOBALS['CACHE_MANAGER']->startTagCache(self::cacheDir);
                $GLOBALS['CACHE_MANAGER']->RegisterTag('iblock_id_' . self::SEO_IBLOCK_ID);
                $GLOBALS['CACHE_MANAGER']->EndTagCache();
            }                
            $obCache->EndDataCache(array('seo_text_main_list' => $result));            
        }


        //Если для текущего города нет сео текста, то возвращаем данные дефолтного элемента, у которого не указан город
        //Иначе возвращаем данные для текущего города 
        $key = !empty($result[$this->_cityId]) ? $this->_cityId : 'default';
        $this->_arSeoData = array(
            'text' => $result[$key]['PREVIEW_TEXT'],
            'h1' => $result[$key]['PROPERTIES']['H1']['VALUE'],
            'title' => $result[$key]['PROPERTIES']['TITLE']['VALUE'],
            'description' => $result[$key]['PROPERTIES']['DESCRIPTION']['VALUE'],
            'top_description' => $result[$key]['PROPERTIES']['TOP_DESCRIPTION']['VALUE']
        );
    }
    
     private function _initSectionData($arParams){
    
        $result = array();
        
        $pageNumber = intVal($arParams['page_number']);
        $sectionId = intVal($arParams['section_id']);
        $optionId = intval($arParams['option_id']);
       
        $successData = false;
        $arSectionId = array();
        $rsSectionPath = \CIBlockSection::GetNavChain(false, $sectionId);
        
        while($arSectionPath = $rsSectionPath->Fetch()){
            $arSectionId[] = $arSectionPath['ID'];
        }
        
        $arSectionId = array_reverse($arSectionId);
                
        $arFilter = array(
            'IBLOCK_ID' => self::SEO_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'PROPERTY_TYPE' => self::TYPE_SECTION_PROPERTY_VALUE,
            array(
                'LOGIC' => 'OR',
                array('PROPERTY_CITY' => $this->_cityId),
                array('PROPERTY_CITY' => false)
            )
        );
        
        if($pageNumber){
            $arFilter[] = array(
                'LOGIC' => 'OR',
                array('PROPERTY_PAGE_NUMBER' => $pageNumber),
                array('PROPERTY_PAGE_NUMBER' => false)
            );
        }else{
            $arFilter['PROPERTY_PAGE_NUMBER'] = false;
        }

        if ($optionId){
           $arFilter[] = array(
                'LOGIC' => 'AND',
                array('PROPERTY_OPTION' => $optionId)
            );
        }

        $arSectionId = $arSectionId[0];

            if(!empty($arParams['color'][0]))
                $arFilter['PROPERTY_SEO_COLOR'] = $arParams['color'][0];
        
            $arFilter['PROPERTY_SECTION'] = $sectionId;

            if ($optionId){
                $order['property_OPTION'] = 'desc';
            }
            $order['property_CITY'] = 'desc';
            $order['property_PAGE_NUMBER'] = 'desc';
            
            $rsElements = \CIBlockElement::GetList(
                $order, //Сначала нужно получить те у которых указан город и те у которых указана страница, иначе дефолтные
                $arFilter,
                false,
                array('nTopCount' => 2), //Пробуем получить 2 элемента : с указанным городои и страницей
                array('ID', 'NAME', 'ACTIVE', 'IBLOCK_ID', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'PROPERTY_H1', 'PROPERTY_TITLE', 'PROPERTY_DESCRIPTION', 'PROPERTY_TOP_DESCRIPTION')
            );
           
            $arTmpResult = array();
            while($arElement = $rsElements->Fetch()){
                $arTmpResult[] = $arElement;
            }
            //$result["temp_data"] = $arTmpResult;

            if(count($arTmpResult)){
                
                if ( (empty($optionId) && empty($arTmpResult[0]['PROPERTY_OPTION_VALUE'])) || (!empty($optionId) && !empty($arTmpResult[0]['PROPERTY_OPTION_VALUE']))){
                    $result['text'] = $arTmpResult[0]['PREVIEW_TEXT'];
                }
                
                if ( !empty($arTmpResult[0]['PROPERTY_H1_VALUE']) )
                    $result['h1'] = $arTmpResult[0]['PROPERTY_H1_VALUE'];

                $result['title'] = $arTmpResult[0]['PROPERTY_TITLE_VALUE'];
                $result['description'] = $arTmpResult[0]['PROPERTY_DESCRIPTION_VALUE'];
                $result['top_description'] = $arTmpResult[0]['PROPERTY_TOP_DESCRIPTION_VALUE'];

                if(count($arTmpResult) > 1){
                    $result['banner_id'] = $arTmpResult[1]['PREVIEW_PICTURE'];
                }else{
                    $result['banner_id'] = $arTmpResult[0]['PREVIEW_PICTURE'];
                }
                
                unset($arTmpResult);
            }
        
        $this->_arSeoData = $result;        
    }
}
