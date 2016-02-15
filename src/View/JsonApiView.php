<?php
namespace JsonApi\View;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\Exception\MissingEntityException;
use Cake\Utility\Hash;
use Cake\View\View;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Parameters\EncodingParameters;
use Neomerx\JsonApi\Schema\Link;

class JsonApiView extends View
{
    /**
     * [$_prefixUrl description]
     * @var null
     */
    protected $_prefixUrl = null;

    /**
     * [$_schemas description]
     * @var array
     */
    protected $_schemas = [];

    /**
     * Hold global meta data
     * @var array
     */
    protected $_meta = [];

    /**
     * Hold global links
     * @var array
     */
    protected $_links = [];

    /**
     * Constructor
     *
     * @param \Cake\Network\Request $request Request instance.
     * @param \Cake\Network\Response $response Response instance.
     * @param \Cake\Event\EventManager $eventManager EventManager instance.
     * @param array $viewOptions An array of view options
     */
    public function __construct(
        Request $request = null,
        Response $response = null,
        EventManager $eventManager = null,
        array $viewOptions = []
    ) {
        parent::__construct($request, $response, $eventManager, $viewOptions);

        if ($response && $response instanceof Response) {
            $response->type('jsonapi');
        }

        // configure the json-api schema mapping
        if (isset($viewOptions['entities'])) {
            $this->_entitiesToSchema($viewOptions['entities']);
        }

        // set the base url for the api
        if (isset($viewOptions['url'])) {
            $this->_prefixUrl = $viewOptions['url'];
        }

        // add metadata to the api response
        if (isset($viewOptions['meta'])) {
            $this->_meta = $viewOptions['meta'];
        }

        if (isset($viewOptions['links'])) {
            $this->_links = $viewOptions['links'];
        }
    }

    /**
     * Map entities to schema files
     * @param  array  $entities An array of entity names that need to be mapped to a schema class
     *   If the schema class does not exist, the default EntitySchema will be used.
     * @return void
     */
    protected function _entitiesToSchema(array $entities)
    {
        $entities = Hash::normalize($entities);
        foreach ($entities as $entityName => $options) {

            $entityclass = App::className($entityName, 'Model\Entity');

            if (!$entityclass) {
                throw new MissingEntityException([$entityName]);
            }

            $schemaClass = App::className($entityName, 'View\Schema', 'Schema');

            if (!$schemaClass) {
                $schemaClass = App::className('JsonApi.Entity', 'View\Schema', 'Schema');
            }

            $schema = function ($factory, $container) use ($schemaClass, $entityName) {
                return new $schemaClass($factory, $container, $this, $entityName);
            };

            $this->_schemas[$entityclass] = $schema;
        }
    }

    /**
     * Serialize view vars
     *
     * ### Special parameters
     * `_serialize` This holds the actual data to pass to the encoder
     * `_include` An array of hash paths of what should be in the 'included' section of the response
     *   see: http://jsonapi.org/format/#fetching-includes
     *   eg: [ 'posts.author' ]
     * `_fieldsets` A hash path of fields a list of names that should be in the resultset
     *   eg: [ 'sites'  => ['name'], 'people' => ['first_name'] ]
     * `_meta` Metadata to add to the document
     * `_links' Links to add to the document
     *  this should be an array of Neomerx\JsonApi\Schema\Link objects.
     *  example:
     * ```
     * $this->set('_links', [
     *     Link::FIRST => new Link('/authors?page=1'),
     *     Link::LAST  => new Link('/authors?page=4'),
     *     Link::NEXT  => new Link('/authors?page=6'),
     *     Link::LAST  => new Link('/authors?page=9'),
     * ]);
     * ```
     *
     * @param string|null $view Name of view file to use
     * @param string|null $layout Layout to use.
     * @return string The serialized data
     */
    public function render($view = null, $layout = null)
    {
        $parameters = $include = $fieldsets = $serialize = null;

        $jsonOptions = $this->_jsonOptions();
        $encoderOptions = new EncoderOptions($jsonOptions, rtrim($this->_prefixUrl, '/'));
        $encoder = Encoder::instance($this->_schemas, $encoderOptions);

        if (isset($this->viewVars['_serialize'])) {
            $serialize = $this->viewVars['_serialize'];
        }

        if (isset($this->viewVars['_include'])) {
            $include = $this->viewVars['_include'];
        }

        if (isset($this->viewVars['_fieldsets'])) {
            $fieldsets = $this->viewVars['_fieldsets'];
        }

        $links = $this->_links;
        if (isset($this->viewVars['_links'])) {
            $links = array_merge($this->_links, $this->viewVars['_links']);
        }

        if ($links) {
            $encoder->withLinks($links);
        }

        $meta = $this->_meta;
        if (isset($this->viewVars['_meta'])) {
            $meta = array_merge($this->_meta, $this->viewVars['_meta']);
        }

        if ($meta) {
            if (empty($serialize)) {
                return $encoder->encodeMeta($meta);
            }
            $encoder->withMeta($meta);
        }

        $parameters = new EncodingParameters($include, $fieldsets);

        return $encoder->encodeData($serialize, $parameters);
    }

    /**
     * Return json options
     *
     * ### Special parameters
     * `_jsonOptions` You can set custom options for json_encode() this way,
     *   e.g. `JSON_HEX_TAG | JSON_HEX_APOS`.
     *
     * @return int json option constant
     */
    protected function _jsonOptions()
    {
        $jsonOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        if (isset($this->viewVars['_jsonOptions'])) {
            if ($this->viewVars['_jsonOptions'] === false) {
                $jsonOptions = 0;
            } else {
                $jsonOptions = $this->viewVars['_jsonOptions'];
            }
        }

        if (Configure::read('debug')) {
            $jsonOptions = $jsonOptions | JSON_PRETTY_PRINT;
        }

        return $jsonOptions;
    }
}
