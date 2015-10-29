<?php

/*
 * @author Imaginalis Software TM
 * @link http://imaginalis.pl
 */

namespace imaginalis\ckeditor;

use yii\web\AssetBundle;

class CKEditorAsset extends AssetBundle
{
	public $js = [
		'ckeditor.js',
		'adapters/jquery.js',
	];

	public $depends = [
		'yii\web\YiiAsset',
		'yii\web\JqueryAsset'
	];

	public function init()
	{
		$this->sourcePath = '@vendor/ckeditor/ckeditor';
		parent::init();
	}
}
