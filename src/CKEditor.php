<?php

/*
 * @author Imaginalis Software TM
 * @link http://imaginalis.pl
 */
namespace imaginalis\ckeditor;

use Yii;
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
			'extraPlugins'=>'youtube',
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
		$this->clientOptions = ArrayHelper::merge($this->toolbars[$this->toolbar], $this->clientOptions);
	}

	public function run()
	{
		if($this->hasModel())
			echo Html::activeTextarea($this->model, $this->attribute, $this->options);
		else
			echo Html::textarea($this->name, $this->value, $this->options);

		if($this->kcfinder)
			$this->registerKCFinder();

		$this->registerPlugin();
	}

	private function registerPlugin()
	{
		CKEditorAsset::register($this->view);
		PluginsAsset::register($this->view);

		$id = $this->options['id'];
		$this->clientOptions = Json::encode($this->clientOptions);

		$js = [ 'CKEDITOR.replace("'.$id.'", '.$this->clientOptions.'); CKEDITOR.instances["'.$id.'"].on("change", function() { CKEDITOR.instances["'.$id.'"].updateElement(); $("#"+"'.$id.'").trigger("change"); });' ];

		foreach($this->plugins as $plugin)
			$js[] = 'CKEDITOR.plugins.addExternal("'.$plugin.'","'.Yii::$app->assetManager->getPublishedUrl('@vendor/imaginalis/ckeditor/src/plugins').'/'.$plugin.'/");';

		$this->view->registerJs(implode("\n", $js));
	}

	protected function registerKCFinder()
	{
		$kcFinderAsset = KCFinderAsset::register($this->view);

		$browseOptions = [
			'filebrowserBrowseUrl'=>$kcFinderAsset->baseUrl.'/browse.php?opener=ckeditor&type=files',
			'filebrowserUploadUrl'=>$kcFinderAsset->baseUrl.'/upload.php?opener=ckeditor&type=files',
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
				'uploadURL'=>Yii::$app->request->baseUrl.'/uploads/editor',
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

			Yii::$app->session->set('KCFINDER', $kcfOptions);
		}
	}
}
