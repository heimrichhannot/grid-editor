<?php

// inject columnset selector for subcolumns
if(\Netzmacht\Bootstrap\Grid\Integration\Subcolumns::isActive()) {
    $GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] =
        array('Netzmacht\Bootstrap\Grid\Integration\Subcolumns', 'appendColumnsetIdToPalette');
}

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['fsc_type']['options_callback'] = array('Netzmacht\Bootstrap\Grid\DataContainer\ColumnSet', 'getAllTypes');
$GLOBALS['TL_DCA']['tl_form_field']['fields']['fsc_type']['eval']['submitOnChange'] = true;

$GLOBALS['TL_DCA']['tl_form_field']['fields']['bootstrap_grid'] = array
(
'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['bootstrap_grid'],
'exclude'                 => true,
'inputType'               => 'select',
'options_callback'        => array('Netzmacht\Bootstrap\Grid\DataContainer\ColumnSet', 'getGrids'),
'reference'               => &$GLOBALS['TL_LANG']['tl_content'],
'eval'                    => array(
'mandatory' => true,
'submitOnChange' => true,
'tl_class' => 'w50'
),
'sql'                     => "int(10) unsigned NOT NULL default '0'"
);