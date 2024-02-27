<?php

namespace App\Admin\Repositories;

use App\Models\ComplaintList as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ComplaintList extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
