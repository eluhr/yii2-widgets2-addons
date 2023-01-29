<?php

namespace eluhr\widgets\addons\widgets\assets;

use dmstr\jsoneditor\JsonEditorAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class CellLiveEditorAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/web/cell-live-editor';

    public $css = [
        'cell-live-editor.less',
    ];

    public $depends = [
        JqueryAsset::class,
        JsonEditorAsset::class
    ];
}
