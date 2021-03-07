<?php

namespace Eliepse\Argile\View\Parsers;

use Symfony\Component\Templating\Storage\Storage;

interface ParserInterface
{
	public function parse(Storage $template): Storage;
}