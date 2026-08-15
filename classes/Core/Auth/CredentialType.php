<?php

declare(strict_types=1);

namespace Goosialize\Google\Core\Auth;

enum CredentialType: string
{
    case SERVICE_ACCOUNT_FILE = 'service_account_file';
    case OAUTH = 'oauth';
}
