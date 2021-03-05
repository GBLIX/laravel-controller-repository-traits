<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Action;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait Delete
 * @package Gblix\Controllers\ApiTraits
 *
 * @property RepositoryInterface $repository
 *
 * @mixin Read
 */
trait Delete
{

    /**
     * Default action for DELETE methods
     *
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request): Response
    {
        return $this->runDestroy($request);
    }

    /**
     * Default runner for DELETE methods
     *
     * @param Request $request
     * @param Action|string $job
     * @param mixed $id
     * @return Response
     */
    final public function runDestroy(Request $request, $job = null, $id = null): Response
    {
        $id = $id ?? $this->getCurrentEntryId($request);

        if ($job !== null) {
            $result = $this->makeDestroyAction($request, $job, $id);
        } else {
            $result = $this->makeDestroyRepository($this->repository, $id);
        }

        return $this->makeDestroyResponse($result);
    }

    /**
     * @param Request $request
     * @param Action|string $job
     * @param mixed $id
     * @return mixed
     */
    final protected function makeDestroyAction(Request $request, $job, $id)
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Dispatching delete on controller')->begin();

        $user = $request->user();

        if (!$job instanceof Action) {
            $job = new $job();
        }

        /* @var $jobInstance Action */
        if (is_array($id)) {
            $data = $id;
        } else {
            $query = $request->query();
            assert(is_array($query));
            $data = $request->except(array_keys($query));
            $data['id'] = $id;
        }

        $result = $job->actingAs($user)
            ->run($data);

        $clockwork->event($clockworkEvent)->end();

        return $result;
    }

    /**
     * Perfoms the data for delete methods
     *
     * @param RepositoryInterface $repository
     * @param mixed $id
     * @return mixed
     */
    final protected function makeDestroyRepository(RepositoryInterface $repository, $id)
    {

        $this->pushReadCriteria($repository);
        $this->pushDestroyCriteria($repository);

        $repository->skipPresenter(false);
        return $repository->delete($id);
    }

    /**
     * Send additional criteria to show
     *
     * @param RepositoryInterface $repository
     *
     * @return void
     */
    protected function pushDestroyCriteria(RepositoryInterface $repository): void
    {
    }

    /**
     * Returns the data for delete methods
     *
     * @param mixed $data
     * @return Response
     */
    final protected function makeDestroyResponse($data): Response
    {
        /** @var ResponseFactory $response */
        $response = response();

        if ($data === true) {
            return $response->noContent();
        }

        return $response->json($data);
    }
}
