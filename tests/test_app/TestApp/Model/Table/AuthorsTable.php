<?php
namespace TestApp\Model\Table;

use Cake\ORM\Table;

class AuthorsTable extends Table
{
    public function initialize(array $config)
    {
        $this->hasMany('Articles');
    }
}
