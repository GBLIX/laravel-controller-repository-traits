<?php

namespace Gblix\Controllers\ApiTraits;

use Clockwork\Clockwork;
use Gblix\Repository\Contracts\NegociatesPresenterContentInterface;
use Gblix\Repository\Contracts\RepositoryInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
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
     * @param Action|mixed|string $job
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
     * @param Action|mixed|string $job
     * @param RepositoryInterface $repository
     * @param mixed $id
     * @return mixed
     */
    final protected function makeUpdate(Request $request, $job, RepositoryInterface $repository, $id)
    {
        /* @var $clockwork Clockwork */
        $clockwork = clock();

        $clockwork->event($clockworkEvent = 'Dispatching update on controller')->begin();

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
     * Prepares the repository based on request received
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     * @return void
     */
    final protected function prepareUpdate(Request $request, RepositoryInterface $repository): void
    {
        $repository->skipPresenter(false);

        $presenter = $this->getUpdatePresenter();
        if ($presenter !== null) {
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
     * @param mixed $data
     * @return Response
     */
    final protected function makeUpdateResponse($data): Response
    {
        /** @var ResponseFactory $response */
        $response = response();
        return $response->json($data);
    }

    /**
     * Prepares the repository based on request received and return the response
     * with the given data
     *
     * @param Request $request
     * @param RepositoryInterface $repository
     * @param mixed $data
     * @return Response
     */
    final protected function updateResponse(Request $request, RepositoryInterface $repository, $data): Response
    {
        $this->prepareUpdate($request, $repository);
        $data = $repository->parserResult($data);
        return $this->makeUpdateResponse($data);
    }
}
