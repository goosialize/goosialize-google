<?php

declare(strict_types=1);

namespace Goosialize\Google\SearchConsole\Reporting;

enum SearchConsoleDimension: string
{
    case Query = 'query';
    case Page = 'page';
    case Country = 'country';
    case Device = 'device';
    case Date = 'date';
}
