<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repository\Contracts\NegociatesPresenterContentInterface;
use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Action;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Update
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 *
 * @mixin Read
 */
trait Update
{

    /**
     * Default action PATCH (UPDATE) methods
     *
     * @param Request $request
     * @return Response
     */
    abstract public function update(Request $request): Response;

    /**
     * Default runner for PATCH (UPDATE) methods
     *
     * @param Request $request
     * @param Action|string $job
     * @param mixed|null $id
     * @return Response
     */
    final public function runUpdate(Request $request, $job, $id = null): Response
    {
        $id = $id ?? $this->getCurrentEntryId($request);
        $data = $this->makeUpdate($request, $job, $this->repository, $id);
        return $this->makeUpdateResponse($data);
    }

    /**
     * Perfoms the data for update methods
     *
     * @param Request $request
     * @param Action|string $job
     * @param RepositoryInterface $repository
     * @param mixed $id
     * @return array|null
     */
    final protected function makeUpdate(Request $request, $job, RepositoryInterface $repository, $id): ?array
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Dispatching update on controller')->begin();

        if (is_array($id)) {
            $data = $id;
        } else {
            $data = $request->except(array_keys($request->query()));
            $data['id'] = $id;
        }

        $user = $request->user();

        if (!$job instanceof Action) {
            $job = new $job();
        }

        $result = $job->actingAs($user)
            ->run($data);

        $clockwork->event($clockworkEvent)->end();

        if (!$result) {
            return $result;
        }

        $clockwork->event($clockworkEvent = 'Parsing update response on controller')->begin();

        $this->prepareUpdate($request, $repository);

        $result = $repository->parserResult($result);

        $clockwork->event($clockworkEvent)->end();

        return $result;
    }

    /**
     * @param Request $request
     * @param RepositoryInterface $repository
     * @return void
     */
    final protected function prepareUpdate(Request $request, RepositoryInterface $repository): void
    {
        $repository->skipPresenter(false);

        $presenter = $this->getUpdatePresenter();
        if ($presenter) {
            $repository->setPresenter($presenter);
        }

        if ($repository instanceof NegociatesPresenterContentInterface) {
            $repository->negociatesPresenterContent($request);
        }
    }

    /**
     * Return the presenter used for update methods - defaults to single
     * @return string
     */
    protected function getUpdatePresenter(): ?string
    {
        return null;
    }

    /**
     * Returns the data for update methods
     *
     * @param array|null $data
     * @return \Illuminate\Http\Response
     */
    final protected function makeUpdateResponse(?array $data): Response
    {
        return response()->json($data);
    }
}
