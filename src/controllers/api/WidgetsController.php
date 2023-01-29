<?php

namespace eluhr\widgets\addons\controllers\api;

use hrzg\widget\models\crud\WidgetContent;
use hrzg\widget\models\crud\WidgetTemplate;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\twig\ViewRenderer;
use yii\web\NotFoundHttpException;

class WidgetsController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'matchCallback' => function () {
                        return \Yii::$app->user->can();
                    },
                ],
            ],
        ];
        return $behaviors;
    }

    public function verbs(): array
    {
        return [
            'content' => ['POST'],
            'infos' => ['POST'],
            'content-update' => ['POST'],
        ];
    }

    /**
     * @throws LoaderError
     * @throws NotFoundHttpException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     * @return array
     */
    public function actionContent(): array
    {
        $request = Yii::$app->getRequest();

        $template = WidgetTemplate::findOne($request->post('templateId'));

        if ($template === null) {
            throw new NotFoundHttpException('Template not found');
        }

        $config = Yii::$app->getView()->renderers['twig'] ?? null;

        if (empty($config)) {
            throw new NotFoundHttpException('Twig renderer not found');
        }

        /** @var ViewRenderer $twigRenderer */
        $twigRenderer = Yii::createObject($config);

        $twigRenderer->twig->addGlobal('this', $this->view);
        $loader = new ArrayLoader([
            'template' => $template->twig_template
        ]);

        $twigRenderer->twig->setLoader($loader);

        if (!empty($this->lexerOptions)) {
            $twigRenderer->setLexerOptions($this->lexerOptions);
        }

        return [
            'html' => $twigRenderer->twig->render('template', $request->post('data'))
        ];
    }

    /**
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionInfos()
    {
        $request = Yii::$app->getRequest();
        $domainId = $request->post('domainId');

        $content = WidgetContent::find()
            ->where(['domain_id' => $domainId])
            ->one();

        if ($content === null) {
            throw new NotFoundHttpException('Content not found');
        }

        return [
            'templateId' => $content->widget_template_id,
            'propertiesJson' => Json::decode($content->default_properties_json),
            'schemaJson' => Json::decode($content->template->json_schema)
        ];
    }


    /**
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionContentUpdate(): array
    {
        $request = Yii::$app->getRequest();
        $domainId = $request->post('domainId');

        $content = WidgetContent::find()
            ->where(['domain_id' => $domainId])
            ->one();

        if ($content === null) {
            throw new NotFoundHttpException('Content not found');
        }

        $content->default_properties_json = Json::encode($request->post('data'));

        if ($content->save()) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false,
            'errors' => $content->getErrors()
        ];
    }
}
