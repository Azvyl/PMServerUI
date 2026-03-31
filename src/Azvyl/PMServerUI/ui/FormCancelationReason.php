<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ui;

/** Reasons why a form was canceled. */
enum FormCancelationReason: string{
	case UserBusy = 'UserBusy';
	case UserClosed = 'UserClosed';
}
