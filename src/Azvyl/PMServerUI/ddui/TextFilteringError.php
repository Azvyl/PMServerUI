<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui;

/**
 * An enum representing the errors that can occur during text filtering. This is used to provide more context about the
 * filtering process.
 */
enum TextFilteringError: string{ // TODO
	/** The text was not filtered because the player disabled text filtering in their settings. */
	case DisabledByPlayer = 'DisabledByPlayer';
	/**
	 * The text was not filtered because the service is unreachable. This can occur if there are network issues or if
	 * the service is down for maintenance.
	 */
	case TextProcessorServiceUnreachable = 'TextProcessorServiceUnreachable';
	/**
	 * An unknown error occurred during text filtering. This can occur if there is an unexpected issue with the text
	 * filtering service or if the service returns an error that is not categorized under the other error types.
	 */
	case Unknown = 'Unknown';
}
