<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

/** Reasons why a form response was rejected. */
enum FormRejectReason: string{
	case MalformedResponse = 'MalformedResponse';
	case PlayerQuit = 'PlayerQuit';
	case ServerShutdown = 'ServerShutdown';
}
