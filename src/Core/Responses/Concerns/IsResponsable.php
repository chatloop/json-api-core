<?php
/*
 * Copyright 2022 Cloud Creativity Limited
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

namespace LaravelJsonApi\Core\Responses\Concerns;

use LaravelJsonApi\Core\Document\JsonApi;
use LaravelJsonApi\Core\Document\Links;
use LaravelJsonApi\Core\Json\Hash;
use LaravelJsonApi\Core\JsonApiService;
use LaravelJsonApi\Core\Server\Concerns\ServerAware;
use function array_merge;

trait IsResponsable
{

    use ServerAware;

    /**
     * @var JsonApi|null
     */
    private ?JsonApi $jsonApi = null;

    /**
     * @var Hash|null
     */
    private ?Hash $meta = null;

    /**
     * @var Links|null
     */
    private ?Links $links = null;

    /**
     * @var int
     */
    private int $encodeOptions = 0;

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * Add the top-level JSON:API member to the response.
     *
     * @param $jsonApi
     * @return $this
     */
    public function withJsonApi($jsonApi): self
    {
        $this->jsonApi = JsonApi::nullable($jsonApi);

        return $this;
    }

    /**
     * @return JsonApi
     */
    public function jsonApi(): JsonApi
    {
        if ($this->jsonApi) {
            return $this->jsonApi;
        }

        return $this->jsonApi = new JsonApi();
    }

    /**
     * Add top-level meta to the response.
     *
     * @param $meta
     * @return $this
     */
    public function withMeta($meta): self
    {
        $this->meta = Hash::cast($meta);

        return $this;
    }

    /**
     * @return Hash
     */
    public function meta(): Hash
    {
        if ($this->meta) {
            return $this->meta;
        }

        return $this->meta = new Hash();
    }

    /**
     * Add top-level links to the response.
     *
     * @param $links
     * @return $this
     */
    public function withLinks($links): self
    {
        $this->links = Links::cast($links);

        return $this;
    }

    /**
     * @return Links
     */
    public function links(): Links
    {
        if ($this->links) {
            return $this->links;
        }

        return $this->links = new Links();
    }

    /**
     * Set JSON encode options.
     *
     * @param int $options
     * @return $this
     */
    public function withEncodeOptions(int $options): self
    {
        $this->encodeOptions = $options;

        return $this;
    }

    /**
     * Set a header.
     *
     * @param string $name
     * @param string|null $value
     * @return $this
     */
    public function withHeader(string $name, string $value = null): self
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Set response headers.
     *
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return array
     */
    protected function headers(): array
    {
        return array_merge(
            ['Content-Type' => 'application/vnd.api+json'],
            $this->headers ?: [],
        );
    }

    /**
     * Set the default top-level JSON:API member.
     *
     * @return void
     */
    protected function defaultJsonApi(): void
    {
        if ($this->jsonApi()->isEmpty()) {
            $jsonApi = new JsonApi(JsonApiService::JSON_API_VERSION);

            if ($server = $this->serverIfExists()) {
                $jsonApi = $server->jsonApi();
            }

            $this->withJsonApi($jsonApi);
        }
    }
}
