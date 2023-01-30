<?php

namespace eluhr\widgets\addons;

class Module extends \yii\base\Module
{
    /**
     * This permission is used to check if the user is allowed to edit the widget content
     *
     * @var string
     */
    public $rbacEditRole = 'widgets-cell-edit';
    
    /**
     * @inheritdoc 
     */
    public function init()
    {
        parent::init();

        $this->setControllerPath('@vendor/eluhr/yii2-widgets2-addons/src/controllers');
    }
}
