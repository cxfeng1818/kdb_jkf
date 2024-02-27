<?php

namespace App\Admin\Repositories;

use App\Models\Black as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Black extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
