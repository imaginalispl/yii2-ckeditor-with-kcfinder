<?php

/*
 * @author Imaginalis Software TM
 * @link http://imaginalis.pl
 */

namespace imaginalis\ckeditor;

use yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use yii\helpers\Json;

class CKEditor extends InputWidget
{

	public $plugins = [
		'youtube',
	];

	public $toolbars = [
		'full' => [
			'height'=>400,
			'toolbarGroups'=>[
				['name'=>'document','groups'=>['mode','document','doctools']],
				['name'=>'clipboard','groups'=>['clipboard','undo']],
				['name'=>'editing','groups'=>['find','selection','spellchecker']],
				['name'=>'forms'],
				'/',
				['name'=>'basicstyles','groups'=>['basicstyles','colors','cleanup']],
				['name'=>'paragraph','groups'=>['list','indent','blocks','align','bidi']],
				['name'=>'links'],
				['name'=>'insert'],
				'/',
				['name'=>'styles'],
				['name'=>'blocks'],
				['name'=>'colors'],
				['name'=>'tools'],
				['name'=>'others'],
			]
		]
	];

	public $clientOptions = [];
	public $kcfinder = false;
	public $toolbar = 'full';

	public function init()
	{
		parent::init();
		$options = $this->toolbars[$this->toolbar];
		$this->clientOptions = ArrayHelper::merge($options, $this->clientOptions);
	}

	public function run()
	{
		if ($this->hasModel())
			echo Html::activeTextarea($this->model, $this->attribute, $this->options);
		else
			echo Html::textarea($this->name, $this->value, $this->options);

		if($this->kcfinder)
			$this->registerKCFinder();

		$this->registerPlugin();
	}

	private function registerPlugin()
	{
		$view = $this->getView();

		CKEditorAsset::register($view);
		PluginsAsset::register($view);

		$id = $this->options['id'];
		$options = $this->clientOptions = Json::encode($this->clientOptions);

		$js[] ='CKEDITOR.replace("'.$id.'", '.$options.');
				CKEDITOR.instances["'.$id.'"].on("change", function(){
					CKEDITOR.instances["'.$id.'"].updateElement();
					$("#"+"'.$id.'").trigger("change");
				});';

		foreach($this->plugins as $plugin)
			$js[] ='CKEDITOR.plugins.addExternal("'.$plugin.'","'.Yii::$app->assetManager->getPublishedUrl('@vendor/imaginalis/ckeditor/src/plugins').'/'.$plugin.'/");';

		$view->registerJs(implode("\n", $js));
	}

	protected function registerKCFinder()
	{
		$register = KCFinderAsset::register($this->view);
		$kcfinderUrl = $register->baseUrl;

		$browseOptions = [
			'filebrowserBrowseUrl'=>$kcfinderUrl.'/browse.php?opener=ckeditor&type=files',
			'filebrowserUploadUrl'=>$kcfinderUrl.'/upload.php?opener=ckeditor&type=files',
		];

		$this->clientOptions = ArrayHelper::merge($browseOptions, $this->clientOptions);
	}
}
