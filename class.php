<?php

namespace Prominado\Components\Ibgrid;

use \Bitrix\Iblock\PropertyEnumerationTable;
use \Bitrix\Main\Grid\Options as GridOptions;
use \Bitrix\Main\UI\PageNavigation;
use \Bitrix\Main\UI\Extension;

class Ibgrid extends \CBitrixComponent
{

    private function getEditableColumns(){
        $arEditableFields = [];
        foreach($this->arParams['COLUMNS'] as $col){
            if($col['editable'] == true){
                $arEditableFields[] = $col['id'];
            }
        }
        return $arEditableFields;
    }

    private function edit($fields){
        $editableCols = $this->getEditableColumns();
        $CIBlockElement = new \CIBlockElement;
        foreach($fields as $itemId => $arFields){
            $arFieldsNew = $arPropsNew = [];
            foreach($arFields as $k => $v){
                if(in_array($k, $editableCols)){
                    if(stristr($k, 'PROPERTY_') === FALSE) {
                        $arFieldsNew[$k] = $v;
                    }else{
                        //$t = preg_match('/^PROPERTY_([a-zA-Z_]+)_VALUE$/', $k, $m);
                        $t = preg_match('/^PROPERTY_([a-zA-Z_]+)$/', $k, $m);
                        if($m[1]){
                            $arPropsNew[$m[1]] = $v;
                        }
                    }
                }
            }
            if(count($arPropsNew) > 0){
                $dbResProps = $CIBlockElement->SetPropertyValuesEx($itemId, false, $arPropsNew);
            }
            $dbRes = $CIBlockElement->Update($itemId, $arFieldsNew);
        }
    }

    public function executeComponent()
    {

        \Bitrix\Main\Loader::includeModule('iblock');
        Extension::load('ui.buttons');
        Extension::load('ui.buttons.icons');

        require_once (__DIR__ . '/src/action_panel.php');

        // Get grid num
        if($this->arParams['GRID_ID']){
            $this->arResult['GRID_ID'] = 'GRIDLIST_' . $this->arParams['GRID_ID'];
        }else{
            $this->arResult['GRID_ID'] = 'GRIDLIST_IB_' . $this->arParams['IBLOCK_ID'];
        }

        if($_REQUEST['action_button_' . $this->arResult['GRID_ID']] == 'edit'){
            $this->edit($_REQUEST['FIELDS']);
        }

        // Prepare grid
        $gridOptions = new GridOptions($this->arResult['GRID_ID']);
        $gridSort = $gridOptions->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
        $gridNav = $gridOptions->GetNavParams();
        $this->arResult['GRID_COLUMNS'] = $this->arParams['COLUMNS'];

        // Prepare nav
        $this->arResult['NAV'] = new PageNavigation($this->arResult['GRID_ID']);
        $this->arResult['NAV']->allowAllRecords(true)->setPageSize($gridNav['nPageSize'])->initFromUri();
        if ($this->arResult['NAV']->allRecordsShown()) {
            $gridNav = false;
        } else {
            $gridNav['iNumPage'] = $this->arResult['NAV']->getCurrentPage();
        }

        // Prepare filter
        $this->arResult['UI_FILTER'] = $this->arParams['UI_FILTER'];
        $filterOption = new \Bitrix\Main\UI\Filter\Options($this->arResult['GRID_ID']);
        $filterDataSrc = $filterOption->getFilter([]);
        $filterIgnore = ['PRESET_ID', 'FILTER_ID', 'FILTER_APPLIED', 'FIND'];
        foreach ($filterDataSrc as $k => $v) {
            if(!in_array($k, $filterIgnore)){
                if(in_array($k, Array('ID'))){
                    $filterData[$k] = $v;
                }else{
                    $filterData[$k] = '%' . $v . '%';
                }
            }
        }
        $arFilter['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        foreach($this->arParams['FILTER'] as $key => $val){
            $arFilter[$key] = $val;
        }
        $arFilter[] = Array(
            'LOGIC' => 'OR',
            $filterData,
            Array('NAME' => '%' . $filterDataSrc['FIND'] . '%'),
        );

        // DB query
        $arSelect = $this->arParams['FIELDS'];
        $dbRes = \CIBlockElement::GetList($gridSort['sort'], $arFilter, false, $gridNav, $arSelect);

        // Set values
        $this->arResult['NAV']->setRecordCount($dbRes->selectedRowsCount());
        $this->arResult['GRID_ROWS'] = [];
        while($item = $dbRes->GetNext()) {

            // Set row params
            $row = [];
            $row['id'] = $item['ID'];

            // Set row actions
            foreach($this->arParams['ROW_ACTIONS'] as $rowActions){
                $actToSet = [];
                foreach($rowActions as $actParam => $paramData){
                    foreach($item as $ik => $iv){
                        $paramData = str_replace('{'.$ik.'}', $iv, $paramData);
                    }
                    $actToSet[$actParam] = $paramData;
                }
                $row['actions'][] = $actToSet;
            }

            // Set column data
            foreach($this->arParams['COLUMNS'] as $col){

                // Prepare field name
                if(strpos($col['id'], 'PROPERTY_') !== false){
                    if(strpos($col['id'], '.') !== false){
                        $fieldId = str_replace('.', '_', $col['id']);
                    }else{
                        $fieldId = $col['id'] . '_VALUE';
                    }
                }else{
                    $fieldId = $col['id'];
                }

                // Set real field data
                if($item[$col['id'] . '_ENUM_ID']){
                    $row['data'][$col['id']] = $item[$col['id'] . '_ENUM_ID'];
                }else{
                    $row['data'][$col['id']] = $item[$fieldId];
                }

                // Set displayed field data
                if($col['out']){
                    foreach($item as $ik => $iv){
                        $col['out'] = str_replace('{'.$ik.'}', $iv, $col['out']);
                    }
                    $row['columns'][$col['id']] = $col['out'];
                }else{
                    if(is_array($item[$fieldId])){
                        $row['columns'][$col['id']] = '<ul>';
                        foreach($item[$col['id']] as $val){
                            $row['columns'][$col['id']] .= '<li>' . $val . '</li>';
                        }
                        $row['columns'][$col['id']] .= '</ul>';
                    }else{
                        $row['columns'][$col['id']] = $item[$fieldId];
                    }
                }
            }

            $this->arResult['GRID_ROWS'][] = $row;
        }

        if($this->arParams['LINK_CREATE_NEW_TITLE']){
            $this->arResult['LINK_CREATE_NEW_TITLE'] = $this->arParams['LINK_CREATE_NEW_TITLE'];
        }else{
            $this->arResult['LINK_CREATE_NEW_TITLE'] = 'Создать';
        }

        $this->includeComponentTemplate();
    }

}