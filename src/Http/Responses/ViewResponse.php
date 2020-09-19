<?php

namespace Eliepse\Argile\Http\Responses;


use Eliepse\Argile\View\ViewFactory;
use Fig\Http\Message\StatusCodeInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Response;

class ViewResponse extends Response
{
	/**
	 * ViewResponse constructor.
	 *
	 * @param string $name
	 * @param array|mixed[] $values
	 * @param HeadersInterface|null $headers
	 */
	public function __construct(
		string $name,
		array $values = [],
		?HeadersInterface $headers = null
	)
	{
		$headers = $headers ?? new Headers();

		parent::__construct(
			StatusCodeInterface::STATUS_OK,
			$headers,
			(new StreamFactory())->createStream(ViewFactory::make($name, $values))
		);
	}
}