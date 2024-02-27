<?php

namespace App\Admin\Repositories;

use App\Models\ChannelAccount as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ChannelAccount extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
