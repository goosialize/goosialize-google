<?php

declare(strict_types=1);

namespace Goosialize\Google\Admin;

use Grav\Common\User\Interfaces\UserInterface;
use Grav\Plugin\Api\PermissionResolver;
use Throwable;

/**
 * Resolve the account ACL exactly as Grav API does. Callers must obtain
 * the user from the authenticated API request/event; this is not an
 * authenticator. Controllers retain their API-key scope caps.
 */
final class ReportingAuthorization
{
    public static function hasPermission(mixed $user, string $permission): bool
    {
        if (!$user instanceof UserInterface || !class_exists(PermissionResolver::class)) {
            return false;
        }

        try {
            if ($user->get('state', 'enabled') !== 'enabled') {
                return false;
            }

            $resolver = new PermissionResolver();

            // Never inherit super from a broad parent grant.
            if ($permission === 'api.super') {
                return $resolver->resolveExact($user, $permission) === true;
            }

            return $resolver->resolve($user, $permission) === true;
        } catch (Throwable) {
            return false;
        }
    }
}
