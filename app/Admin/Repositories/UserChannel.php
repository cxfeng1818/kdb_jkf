<?php

namespace App\Admin\Repositories;

use App\Models\UserChannel as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserChannel extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
