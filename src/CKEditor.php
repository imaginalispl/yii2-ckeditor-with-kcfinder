<?php

/*
 * @author Imaginalis Software TM
 * @link http://imaginalis.pl
 */
namespace imaginalis\ckeditor;

use yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;

class CKEditor extends InputWidget
{
	public $plugins = [
		'youtube',
	];

	public $toolbars = [
		'custom'=>[
			'height'=>400,
			'toolbarGroups'=>[
				['name'=>'basicstyles', 'groups'=>['basicstyles', 'cleanup']],
				['name'=>'insert', 'groups'=>['insert']],
				['name'=>'links', 'groups'=>['links']],
				['name'=>'paragraph', 'groups'=>['list', 'indent', 'align']],
				'/',
				['name'=>'styles', 'groups'=>['styles']],
				['name'=>'colors', 'groups'=>['colors']],
				['name'=>'clipboard', 'groups'=>['clipboard', 'undo']],
				['name'=>'document', 'groups'=>['mode']],
			],
			'removeButtons'=>'Subscript,Superscript,Scayt,SpecialChar,Smiley,PageBreak,HorizontalRule,Maximize,Blockquote,Styles,Format',
		],
	];

	public $clientOptions = [];
	public $kcfinder = false;
	public $toolbar = 'custom';

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

		$js[] = 'CKEDITOR.replace("'.$id.'", '.$options.'); CKEDITOR.instances["'.$id.'"].on("change", function()
				{
					CKEDITOR.instances["'.$id.'"].updateElement();
					$("#"+"'.$id.'").trigger("change");
				});';

		foreach($this->plugins as $plugin)
			$js[] = 'CKEDITOR.plugins.addExternal("'.$plugin.'","'.Yii::$app->assetManager->getPublishedUrl('@vendor/imaginalis/ckeditor/src/plugins').'/'.$plugin.'/");';

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

		if(Yii::$app->session->get('KCFINDER') === null)
		{
			$kcfOptions = [
				'disabled'=>false,
				'denyZipDownload'=>true,
				'denyUpdateCheck'=>true,
				'denyExtensionRename'=>true,
				'theme'=>'default',
				'uploadURL'=>ImageManager::getBaseUrl().'/editor',
				'uploadDir'=>Yii::getAlias('@app/web/uploads').'/editor',
				'access'=>[
					'files'=>[
						'upload'=>true,
						'delete'=>false,
						'copy'=>false,
						'move'=>false,
						'rename'=>false,
					],
					'dirs'=>[
						'create'=>true,
						'delete'=>false,
						'rename'=>false,
					],
				],
				'types'=>[
					'files'=>[
						'type'=>'',
					],
				],
				'thumbsDir'=>'.thumbs',
				'thumbWidth'=>100,
				'thumbHeight'=>100,
			];

			// Set kcfinder session options
			Yii::$app->session->set('KCFINDER', $kcfOptions);
		}
	}
}
