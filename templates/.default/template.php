<? ob_start(); ?>

    <? if($arParams['SHOW_FILTER'] == 'Y'): ?>
        <div class="pagetitle-container pagetitle-flexible-space" style="overflow: hidden;">
            <? $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                'FILTER_ID' => $arResult['GRID_ID'],
                'GRID_ID' => $arResult['GRID_ID'],
                'FILTER' => $arResult['UI_FILTER'],
                'ENABLE_LIVE_SEARCH' => $arParams['ENABLE_LIVE_SEARCH'],
                'ENABLE_LABEL' => $arParams['ENABLE_LABEL']
            ]); ?>
        </div>
    <? endif; ?>
    <? if($arParams['LINK_CREATE_NEW']): ?>
        <div class="pagetitle-container pagetitle-align-right-container">
            <a href="<?=$arParams['LINK_CREATE_NEW']?>" class="ui-btn ui-btn-primary ui-btn-icon-add" title="<?=$arResult['LINK_CREATE_NEW_TITLE']?>">
                <?=$arResult['LINK_CREATE_NEW_TITLE']?>
            </a>
        </div>
    <? endif; ?>

<? $pageTitleViewContent = ob_get_clean(); ?>

<?
if($arParams['FILTER_NEAR_TITLE'] == 'Y'){
    $APPLICATION->AddViewContent('pagetitle', $pageTitleViewContent);
}else{
    echo '<div class="ui-grid-top">';
    echo $pageTitleViewContent;
    echo '</div>';
}
?>

<div class="ui-grid-list">
    <? $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['GRID_COLUMNS'],
        'ROWS' => $arResult['GRID_ROWS'],
        'SHOW_ROW_CHECKBOXES' => $arParams['SHOW_ROW_CHECKBOXES'],
        'NAV_OBJECT' => $arResult['NAV'],
        'AJAX_MODE' => $arParams['AJAX_MODE'],
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' =>  [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100'],
            ['NAME' => '500', 'VALUE' => '500'],
        ],
        'AJAX_OPTION_JUMP' => $arParams['AJAX_OPTION_JUMP'],
        'SHOW_CHECK_ALL_CHECKBOXES' => $arParams['SHOW_CHECK_ALL_CHECKBOXES'],
        'SHOW_ROW_ACTIONS_MENU' => $arParams['SHOW_ROW_ACTIONS_MENU'],
        'SHOW_GRID_SETTINGS_MENU' => $arParams['SHOW_GRID_SETTINGS_MENU'],
        'SHOW_NAVIGATION_PANEL' => $arParams['SHOW_NAVIGATION_PANEL'],
        'SHOW_PAGINATION' => $arParams['SHOW_PAGINATION'],
        'SHOW_SELECTED_COUNTER' => $arParams['SHOW_SELECTED_COUNTER'],
        'SHOW_TOTAL_COUNTER' => $arParams['SHOW_TOTAL_COUNTER'],
        'SHOW_PAGESIZE' => $arParams['SHOW_PAGESIZE'],
        'SHOW_ACTION_PANEL' => $arParams['SHOW_ACTION_PANEL'],
        'ALLOW_COLUMNS_SORT' => $arParams['ALLOW_COLUMNS_SORT'],
        'ALLOW_COLUMNS_RESIZE' => $arParams['ALLOW_COLUMNS_RESIZE'],
        'ALLOW_HORIZONTAL_SCROLL' => $arParams['ALLOW_HORIZONTAL_SCROLL'],
        'ALLOW_SORT' => $arParams['ALLOW_SORT'],
        'ALLOW_PIN_HEADER' => $arParams['ALLOW_PIN_HEADER'],
        'AJAX_OPTION_HISTORY' => $arParams['AJAX_OPTION_HISTORY']
    ]); ?>
</div>