<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class FirebaseService
{
    public function __construct(protected Auth $auth) {}

    public function verifyIdToken(string $idToken): ?array
    {
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $claims        = $verifiedToken->claims();

            return [
                'uid'            => $claims->get('sub'),
                'email'          => $claims->get('email'),
                'phone'          => $claims->get('phone_number'),
                'name'           => $claims->get('name'),
                'picture'        => $claims->get('picture'),
                'email_verified' => $claims->get('email_verified', false),
            ];
        } catch (FailedToVerifyToken $e) {
            \Log::error('Firebase token verification failed: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            \Log::error('Firebase error: ' . $e->getMessage());
            return null;
        }
    }

    // Check if a Firebase account still exists
    // Returns true if exists OR if check fails (fail open to avoid false lockouts)
    public function userExists(string $uid): bool
    {
        try {
            $this->auth->getUser($uid);
            return true;
        } catch (UserNotFound $e) {
            return false;
        } catch (\Exception $e) {
            \Log::warning('Firebase userExists check failed for uid ' . $uid . ': ' . $e->getMessage());
            return true; // Fail open — do not lock out users if Firebase is unreachable
        }
    }
}