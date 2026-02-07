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

/** Reasons why a form was canceled. */
enum FormCancelationReason: string{
	case UserBusy = 'UserBusy';
	case UserClosed = 'UserClosed';
}
