<?php
/*
 * Copyright 2021 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Core\Responses\Internal;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelJsonApi\Core\Resources\JsonApiResource;
use LaravelJsonApi\Core\Responses\Concerns\HasEncodingParameters;
use LaravelJsonApi\Core\Responses\Concerns\IsResponsable;

class ResourceResponse implements Responsable
{

    use HasEncodingParameters;
    use IsResponsable;

    /**
     * @var JsonApiResource|null
     */
    private ?JsonApiResource $resource;

    /**
     * @var bool|null
     */
    private ?bool $created = null;

    /**
     * ResourceResponse constructor.
     *
     * @param JsonApiResource|null $resource
     */
    public function __construct(?JsonApiResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Mark the resource as created.
     *
     * @return $this
     */
    public function didCreate(): self
    {
        return $this->withCreated(true);
    }

    /**
     * Mark the resource as not created.
     *
     * @return $this
     */
    public function didntCreate(): self
    {
        return $this->withCreated(false);
    }

    /**
     * Set the created status of the resource.
     *
     * If a boolean provided, that will be used to determine whether the resource
     * was created.
     *
     * If null is provided, the status will be determined by calling the
     * `JsonApiResource::wasCreated()` method.
     *
     * @param bool|null $created
     * @return $this
     */
    public function withCreated(?bool $created): self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function toResponse($request)
    {
        $links = $this->links();

        if ($this->resource && $self = $this->resource->selfLink()) {
            $links->push($self);
        }

        $encoder = $this->server()->encoder();

        $document = $encoder
            ->withRequest($request)
            ->withIncludePaths($this->includePaths($request))
            ->withFieldSets($this->sparseFieldSets($request))
            ->withResource($this->resource)
            ->withJsonApi($this->jsonApi())
            ->withMeta($this->meta())
            ->withLinks($links)
            ->toJson($this->encodeOptions);

        return new Response(
            $document,
            $this->status(),
            $this->headers()
        );
    }

    /**
     * @return array
     */
    protected function headers(): array
    {
        $headers = \collect(['Content-Type' => 'application/vnd.api+json'])
            ->merge($this->headers ?: [])
            ->all();

        if ($this->resourceWasCreated() && $selfUrl = $this->resource->selfUrl()) {
            $headers['Location'] = $selfUrl;
        }

        return $headers;
    }

    /**
     * @return int
     */
    protected function status(): int
    {
        if ($this->resourceWasCreated()) {
            return Response::HTTP_CREATED;
        }

        return Response::HTTP_OK;
    }

    /**
     * @return bool
     */
    protected function resourceWasCreated(): bool
    {
        if (is_bool($this->created)) {
            return $this->created;
        }

        if ($this->resource) {
            return $this->resource->wasCreated();
        }

        return false;
    }

}
