<?php

namespace Gblix\Tests\Unit;

use Gblix\Presenters\FractalPresenter;

class PresenterStub extends FractalPresenter
{

    public function getTransformer()
    {
        return new TransformerStub();
    }
}
