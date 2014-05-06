<?php

namespace Netzmacht\Bootstrap\Grid\DataContainer;

/**
 * Class ColumnSet provides helper methods for handling the data container and generating the dynamic column set
 * container
 *
 * @package Netzmacht\ColumnSet
 */
class ColumnSet extends \Backend
{


	/**
	 * add column set field to the colsetStart content element. We need to do it dynamically because subcolumns
	 * creates its palette dynamically
	 *
	 * @param $dc
	 */
	public function appendColumnsetIdToPalette($dc)
	{
		if($GLOBALS['TL_CONFIG']['subcolumns'] != 'bootstrap_customizable') {
			return;
		}

		if($dc->table == 'tl_content') {
			$model = \ContentModel::findByPK($dc->id);

			if($model->sc_type > 0) {
				\MetaPalettes::appendFields($dc->table, 'colsetStart', 'colset', array('columnset_id'));
			}
		} else {
			$model = \ModuleModel::findByPk($dc->id);

			if($model->sc_type > 0) {
				if($model->sc_type > 0) {
					$GLOBALS['TL_DCA']['tl_module']['palettes']['subcolumns'] = str_replace(
						'sc_type,',
						'sc_type,columnset_id,',
						$GLOBALS['TL_DCA']['tl_module']['palettes']['subcolumns']
					);
				}
			}
		}
	}


	/**
	 * Append column sizes fields dynamically to the palettes. Not using
	 * @param $dc
	 */
	public function appendColumnSizesToPalette($dc)
	{
		$model = \Database::getInstance()
			->prepare('SELECT * FROM tl_columnset WHERE id=?')
			->limit(1)
			->execute($dc->id);

		$sizes = array_merge(deserialize($model->sizes, true));

		foreach($sizes as $size) {
			$field = 'columnset_' . $size;

			\MetaPalettes::appendFields('tl_columnset', 'columnset', array($field));
		}
	}


	/**
	 * create a MCW row for each column
	 *
	 * @param string $value deseriazable value, for getting an array
	 * @param $mcw multi column wizard or DC_Table
	 * @return mixed
	 */
	public function createColumns($value, $mcw)
	{
		$columns = (int)$mcw->activeRecord->columns;
		$value   = deserialize($value, true);
		$count   = count($value);

		// initialize columns
		if($count == 0) {
			for($i = 0; $i < $columns; $i++) {
				$value[$i]['width'] = floor(12 / $columns);
			}
		} // reduce columns if necessary
		elseif($count > $columns) {
			$count = count($value) - $columns;

			for($i = 0; $i < $count; $i++) {
				array_pop($value);
			}
		} // make sure that column numbers has not changed
		else {
			for($i = 0; $i < ($columns - $count); $i++) {
				$value[$i + $count]['width'] = floor(12 / $columns);
			}
		}

		return $value;
	}


	/**
	 * replace subcolumns getAllTypes method, to load all created columnsets. There is a fallback provided if not
	 * bootstra_customizable is used
	 *
	 * @param DC_Table $dc
	 * @return array
	 */
	public function getAllTypes($dc)
	{
		if($GLOBALS['TL_CONFIG']['subcolumns'] != 'bootstrap_customizable') {
			$sc = new \tl_content_sc();

			return $sc->getAllTypes();
		}

		$this->import('Database');
		$collection = $this->Database->execute('SELECT columns FROM tl_columnset GROUP BY columns ORDER BY columns');

		$types = array();

		while($collection->next()) {
			$types[] = $collection->columns;
		}

		return $types;
	}


	/**
	 * get all columnsets which fits to the selected type
	 * @param $dc
	 * @return array
	 */
	public function getAllColumnsets($dc)
	{
		$collection = \ColumnsetModel::findBy('published=1 AND columns', $dc->activeRecord->sc_type, array('order' => 'title'));
		$set        = array();

		if($collection !== null) {
			while($collection->next()) {
				$set[$collection->id] = $collection->title;
			}
		}

		return $set;
	}


	/**
	 * @param $dc
	 * @return array
	 */
	public function getColumnsForModule($dc)
	{
		if($GLOBALS['TL_CONFIG']['subcolumns'] != 'bootstrap_customizable') {
			$sc = new \tl_module_sc();

			return $sc->getColumns();
		}

		$model = \ModuleModel::findByPK($dc->currentRecord);
		$cols  = array();

		$translate = array('first', 'second', 'third', 'fourth', 'fith');

		for($i = 0; $i < $model->sc_type; $i++) {
			if(!array_key_exists($i, $translate)) {
				break;
			}

			$key        = $translate[$i];
			$cols[$key] = $GLOBALS['TL_LANG']['MSC']['sc_' . $key];
		}

		return $cols;
	}
}