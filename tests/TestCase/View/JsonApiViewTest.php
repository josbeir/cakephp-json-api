<?php
namespace JsonApi\Test\TestCase\View;

use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class JsonApiViewTest extends TestCase
{
    public $fixtures = [
        'core.articles',
        'core.authors'
    ];

    public function setUp()
    {
        parent::setUp();
    }

    protected function _getView($viewOptions = [], $viewVars = [])
    {
        $Request = new Request();
        $Response = new Response();
        $Controller = new Controller($Request, $Response);

        $builder = $Controller->viewBuilder();
        $builder->className('JsonApi\View\JsonApiView');

        if ($viewOptions) {
            $builder->options($viewOptions);
        }

        if ($viewVars) {
            $Controller->set($viewVars);
        }

        return $Controller->createView();
    }

    /**
     * Test Render
     * @return [type] [description]
     */
    public function testRenderUsingBaseSchema()
    {
        $records = TableRegistry::get('Articles')->find()->all();
        $viewOptions = [
            'url' => 'http://localhost',
            'entities' => [
                'Article'
            ]
        ];

        $view = $this->_getView($viewOptions, [
            '_serialize' => $records
        ]);

        $output = $view->render();
        $output = json_decode($output);

        $expected = file_get_contents(ROOT . DS . 'tests' . DS . 'Fixture' . DS . 'articles.json');
        $expected = json_decode($expected);

        $this->assertEquals($expected, $output);
    }

    /**
     * Test Render
     * @return [type] [description]
     */
    public function testRenderUsingCustomSchema()
    {
        $records = TableRegistry::get('Authors')->find()
            ->contain(['Articles'])
            ->all();

        $viewOptions = [
            'url' => 'http://localhost',
            'entities' => [
                'Author',
                'Article'
            ]
        ];

        $view = $this->_getView($viewOptions, [
            '_serialize' => $records
        ]);

        $output = $view->render();
        $output = json_decode($output);

        $expected = file_get_contents(ROOT . DS . 'tests' . DS . 'Fixture' . DS . 'authors.json');
        $expected = json_decode($expected);

        $this->assertEquals($expected, $output);
    }

    public function testViewResponse()
    {
        $records = TableRegistry::get('Articles')->find()->all();

        $viewOptions = [
            'entities' => [ 'Article' ]
        ];

        $view = $this->_getView($viewOptions, [
            '_serialize' => $records
        ]);

        $output = $view->render();

        $this->assertSame('application/vnd.api+json', $view->response->type());
    }
}
