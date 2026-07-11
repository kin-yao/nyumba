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

    /**
     * Create a Firebase Auth account server-side (Admin SDK).
     * Used when an owner/manager invites a teammate from Settings —
     * there's no client-side signup flow for invited users.
     * Returns the Firebase UID on success, null on failure.
     */
    public function createUser(string $email, string $password, string $displayName): ?string
    {
        try {
            $userRecord = $this->auth->createUser([
                'email'         => $email,
                'emailVerified' => true,
                'password'      => $password,
                'displayName'   => $displayName,
            ]);

            return $userRecord->uid;
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            \Log::warning('Firebase createUser: email already exists — ' . $email);
            return null;
        } catch (\Exception $e) {
            \Log::error('Firebase createUser failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a Firebase Auth account. Used when removing an invited user.
     */
    public function deleteUser(string $uid): bool
    {
        try {
            $this->auth->deleteUser($uid);
            return true;
        } catch (\Exception $e) {
            \Log::warning('Firebase deleteUser failed for uid ' . $uid . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify an email/password pair against Firebase itself. The Admin SDK
     * cannot check a password directly, so this calls the Identity Toolkit
     * REST API (the same one the client SDK uses) with the project's public
     * Web API key. Used to confirm "current password" before a change.
     */
    public function verifyPassword(string $email, string $password): bool
    {
        $apiKey = config('services.firebase.web_api_key');

        if (!$apiKey) {
            \Log::error('Firebase web_api_key is not configured — cannot verify current password.');
            return false;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post(
                'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . $apiKey,
                [
                    'email'             => $email,
                    'password'          => $password,
                    'returnSecureToken' => false,
                ]
            );

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Firebase verifyPassword failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change a Firebase user's real login password via the Admin SDK.
     * This is the password actually checked by signInWithEmailAndPassword.
     */
    public function changeUserPassword(string $uid, string $newPassword): bool
    {
        try {
            $this->auth->updateUser($uid, ['password' => $newPassword]);
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase changeUserPassword failed for uid ' . $uid . ': ' . $e->getMessage());
            return false;
        }
    }
}