<?php
namespace JsonApi\Test\TestCase\View;

use Cake\Controller\Controller;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Neomerx\JsonApi\Schema\Link;

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

        $this->assertJsonStringEqualsJsonFile(
            ROOT . DS . 'tests' . DS . 'Fixture' . DS . 'articles.json',
            $view->render()
        );
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

        $this->assertJsonStringEqualsJsonFile(
            ROOT . DS . 'tests' . DS . 'Fixture' . DS . 'authors.json',
            $view->render()
        );
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

    /**
     * Test Render
     * @return [type] [description]
     */
    public function testEncodeWithIncludeAndFieldSet()
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
            '_serialize' => $records,
            '_include' => [ 'articles' ],
            '_fieldsets' => [ 'articles' => [ 'title' ] ]
        ]);

        $output = $view->render();
        $output = json_decode($output, true);

        $expectedSubset = [
            'included' => [
                [
                    'type' => 'articles',
                    'id' => '1',
                    'attributes' => [
                        'title' => 'First Article'
                    ]
                ]
            ]
        ];

        $this->assertArraySubset($expectedSubset, $output);
    }

    public function testOnlyMetaData()
    {
        $meta = [ 'meta' => 'data' ];
        $viewOptions = [
            'url' => 'http://localhost',
            'entities' => [
                'Article'
            ]
        ];
        $view = $this->_getView($viewOptions, [
            '_meta' => $meta
        ]);

        $output = $view->render();
        $this->assertEquals(['meta' => $meta ], json_decode($output, true));
    }


    public function testResponseWithLinks()
    {
        $records = TableRegistry::get('Articles')->find()->all();
        $viewOptions = [
            'url' => 'http://localhost',
            'entities' => [
                'Article'
            ]
        ];

        $view = $this->_getView($viewOptions, [
            '_serialize' => $records,
            '_links' => [
                Link::FIRST => new Link('/authors?page=1'),
                Link::LAST => new Link('/authors?page=4'),
                Link::NEXT => new Link('/authors?page=6'),
                Link::LAST => new Link('/authors?page=9', [
                    'meta' => 'data'
                ])
            ]
        ]);

        $output = $view->render();
        $output = json_decode($output, true);

        $expected = [
            'first' => 'http://localhost/authors?page=1',
            'last' => [
                'href' => 'http://localhost/authors?page=9',
                'meta' => [
                    'meta' => 'data'
                ]
            ],
            'next' => 'http://localhost/authors?page=6'
        ];

        $this->assertArraySubset(['links' => $expected], $output);
    }

    public function testJsonOptions()
    {
        $view = $this->_getView([], [
            '_jsonOptions' => JSON_HEX_QUOT
        ]);

        $view->render();
        $this->assertEquals(8, $view->viewVars['_jsonOptions']);

        $view = $this->_getView([], [
            '_jsonOptions' => false
        ]);

        $view->render();
        $this->assertEquals(0, $view->viewVars['_jsonOptions']);
    }

    public function testEmptyView()
    {
        $view = $this->_getView();
        $output = $view->render();

        $this->assertEquals(['data' => null], json_decode($output, true));
    }

    public function testEntityNotFoundException()
    {
        $this->setExpectedException('Cake\ORM\Exception\MissingEntityException');

        $view = $this->_getView([
            'entities' => [ 'FakeEntity' ]
        ]);

        $output = $view->render();
    }
}
