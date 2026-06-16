<?php

declare(strict_types=1);

namespace Azvyl\PMServerUI\ddui\packets\types;

final class DataStorePropertyType{
	public const NONE = 0;
	public const BOOL = 1;
	public const INT64 = 2;
	public const STRING = 4;
	public const LIST = 5;
	public const MAP = 6;
}
