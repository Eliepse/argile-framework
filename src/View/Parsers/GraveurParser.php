<?php

namespace Eliepse\Argile\View\Parsers;

use Symfony\Component\Templating\Storage\Storage;
use Symfony\Component\Templating\Storage\StringStorage;

final class GraveurParser implements ParserInterface
{
	public function parse(Storage $template): Storage
	{
		// To prevent commented structure to be catched and executed,
		// any comment is trimed out of the content as a first pass.
		$commented_content = preg_replace("/({#\s*(.+)\s*#})/miU", "", $template->getContent());

		// The second pass actually execute the structures, but safely
		// because comments has be removed.
		$parsed_content = preg_replace_callback("/({([{%#])\s*(.+)\s*[}%#]})/miU", function ($matches) {
			switch ($matches[2]) {
				case '{':
					return '<?= $view->escape(' . trim($matches[3]) . ') ?>';
				case '%':
					return $this->parseLogicalBrackets($matches[3]);
			}
			return $matches[0];
		}, $commented_content);

		if (is_null($parsed_content)) {
			throw new \ErrorException("Unable to parse the view, error with the 'preg_replace_callback'.");
		}

		return new StringStorage($parsed_content);
	}


	/**
	 * @param string $content
	 *
	 * @return string
	 * @throws \ErrorException
	 */
	private function parseLogicalBrackets(string $content): string
	{
		$parsed_bracket = preg_replace_callback("/(\s*([a-z]+)\s*(.*))/mi", function ($matches) {
			switch ($matches[2]) {
				case 'include':
					return '<?= $view->render(' . trim($matches[3]) . ') ?>';
				case 'if':
					return '<?php if(' . trim($matches[3]) . '): ?>';
				case 'elseif':
					return '<?php elseif(' . trim($matches[3]) . '): ?>';
				case 'else':
					return '<?php else: ?>';
				case 'endif':
					return '<?php endif; ?>';
				case 'for':
					return '<?php foreach(' . trim($matches[3]) . '): ?>';
				case 'endfor':
					return '<?php endforeach; ?>';
			}
			return "";
		}, $content);

		if (is_null($parsed_bracket)) {
			throw new \ErrorException("Unable to parse logical bracket.");
		}

		return $parsed_bracket;
	}
}