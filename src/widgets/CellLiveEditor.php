<?php

namespace eluhr\widgets\addons\widgets;

use eluhr\widgets\addons\widgets\assets\CellLiveEditorAsset;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;
use yii\web\View;

class CellLiveEditor extends Widget
{
    public $moduleId = 'widgets-addons';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!Yii::$app->getModule($this->moduleId)->rbacEditRole) {
            return '';
        }

        $this->registerAssets();
        return $this->render('cell-live-editor');
    }

    /**
     * Register needed assets
     *
     * @return void
     */
    protected function registerAssets()
    {
        CellLiveEditorAsset::register($this->view);

        $widgetInfosUrl = Url::to(['/' . $this->moduleId . '/api/widgets/infos']);
        $widgetContentViewUrl = Url::to(['/' . $this->moduleId . '/api/widgets/content']);
        $widgetContentUpdateUrl = Url::to(['/' . $this->moduleId . '/api/widgets/content-update']);

        $this->view->registerJs(<<<JS
var currentRequest = null;
var replaceContainer = null;
var editor = null;
var submitButton = null;
var cancelButton = null;
var widgetDomainId = null;

function createSaveButton() {
  if (submitButton != null) {
    submitButton.remove()
  }
  
  submitButton = document.createElement('button')
  submitButton.textContent = 'Save'
  submitButton.className = 'btn btn-primary'
  
  $(submitButton).on('click', function() {
      $.post({
            url: '$widgetContentUpdateUrl',
            data: { domainId: widgetDomainId, data: editor.getValue()},
            success: function() {
                resetEditorAndButtons()
            }
      })
  })
    
  $('.cell-live-editor-footer').append(submitButton)
}

function resetEditorAndButtons() {
    editor.destroy()
    submitButton.remove()
    cancelButton.remove()
    $('.cell-live-editor-sidebar').removeClass('open')
}

function createCancelButton() {
  if (cancelButton != null) {
    cancelButton.remove()
  }
  
  cancelButton = document.createElement('button')
  cancelButton.textContent = 'Cancel'
  cancelButton.className = 'btn btn-default'
  
  $(cancelButton).on('click', function() {
        resetEditorAndButtons()
  })
    
  $('.cell-live-editor-footer').append(cancelButton)
}

function createEditor(schemaJson, propertiesJson, templateId) {
    if (editor != null) {
        editor.destroy()
    }
    
    $('.cell-live-editor-sidebar').addClass('open')
    
    editor = new JSONEditor(document.getElementById('$this->id'), {
        schema: schemaJson,
        startval: propertiesJson,
        theme: 'bootstrap3',
        iconlib: 'fontawesome4',
        disable_collapse: true,
        disable_properties: false,
        no_additional_properties: false,
        keep_oneof_values: false,
        expand_height: true
    });
    
    editor.on('ready', function() {
        createSaveButton(editor)
        createCancelButton()
    });
    
    editor.on('change', function() {
        currentRequest = $.post({
            url: '$widgetContentViewUrl',
            data: { templateId: templateId, data: editor.getValue()},
            beforeSend: function() {
                if (currentRequest != null) {
                    currentRequest.abort()
                }
            },
            success: function(data) {
                replaceContainer.html(data.html)
            }
        })
    });
}

$('.hrzg-widget-widget-controls').each(function() {
    var button = document.createElement('button')
    button.innerHTML = '<i class="fa fa-pencil-square-o"></i>'
    button.className = 'btn btn-primary'
    $(this).prepend(button)

    $(button).on('click', function(e) {
        e.preventDefault()
        var that = $(this)
        var widgetContainer = that.closest('.hrzg-widget-widget')
        widgetDomainId = widgetContainer.attr("id").replace("widget-", "");
        replaceContainer = widgetContainer.find(".hrzg-widget-content-frontend");
        $.post({
            url: '$widgetInfosUrl',
            data: { domainId: widgetDomainId},
            success: function(data) {
                createEditor(data.schemaJson, data.propertiesJson, data.templateId)
            }
        })
    })
})
JS
            , View::POS_LOAD);
    }
}
