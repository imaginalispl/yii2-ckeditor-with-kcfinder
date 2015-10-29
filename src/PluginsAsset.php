<?php

/*
 * @author Imaginalis Software TM
 * @link http://imaginalis.pl
 */

namespace imaginalis\ckeditor;

use yii\web\AssetBundle;

class PluginsAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = __DIR__.'/plugins';
	}
}
