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
     * @param Action|mixed|string $job
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
     * @param Action|mixed|string $job
     * @param mixed $id
     * @return mixed
     */
    final protected function makeDestroyAction(Request $request, $job, $id)
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Dispatching delete on controller')->begin();

        $user = $request->user();

        if ((!$job instanceof Action && !str_contains(get_parent_class($job), 'Action')) || !is_object($job)) {
            $job = $job::make();
        }

        assert(is_object($job));
        $query = $request->query();
        assert(is_array($query));
        $data = $request->except(array_keys($query));

        if (is_array($id)) {
            $data = $id;
        } else {
            $data['id'] = $id;
        }

        if (method_exists($job, 'asController')) {
            /* Actions as Laravel Actions ^2 */
            $result = $job->asController($user, $request, $id);
        } elseif (method_exists($job, 'actingAs')) {
            /* Actions as Laravel Actions ^1 */
            $result = $job->actingAs($user)
                ->run($data);
        } elseif (method_exists($job, 'run')) {
            if (method_exists($job, 'fill')) {
                $job->fill($data);
                $job->fillFromRequest($request);
            }
            if (in_array('rules', get_class_methods($job))) {
                $job->validateAttributes();
            }
            $result = $job->handle($data);
        } else {
            throw new \RuntimeException('No job to run with ' . get_class($job));
        }

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

        if ($data === true || $data === null) {
            return $response->noContent();
        }

        return $response->json($data);
    }
}
