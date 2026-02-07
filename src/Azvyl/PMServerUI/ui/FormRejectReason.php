<?php

/*
 * PMServerUI
 * https://github.com/Azvyl/PMServerUI
 *
 * Copyright (c) 2026 Azvyl
 *
 * Licensed under the MIT License.
 * See LICENSE file in the project root for details.
 */

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

/**
 * Reasons why a form response was rejected.
 */
enum FormRejectReason: string{
	case MalformedResponse = 'MalformedResponse';
	case PlayerQuit = 'PlayerQuit';
	case ServerShutdown = 'ServerShutdown';
}
