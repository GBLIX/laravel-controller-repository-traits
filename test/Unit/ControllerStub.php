<?php

namespace Gblix\Tests\Unit;

use Gblix\Controllers\ApiTraits\Create;
use Gblix\Controllers\ApiTraits\Delete;
use Gblix\Controllers\ApiTraits\Read;
use Gblix\Controllers\ApiTraits\Retrieve;
use Gblix\Controllers\ApiTraits\Show;
use Gblix\Controllers\ApiTraits\Update;
use Illuminate\Routing\Controller;

abstract class ControllerStub extends Controller
{
    use Retrieve;
    use Read;
    use Show;
    use Create;
    use Update;
    use Delete;
}
