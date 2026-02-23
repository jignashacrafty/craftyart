<?php

namespace App\Auth;

use App\Helpers\JwtHelper;
use App\Models\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
  protected $request;
  protected $provider;
  protected $user;

  public function __construct(UserProvider $provider, Request $request)
  {
    $this->request = $request;
    $this->provider = $provider;
    $this->user = null;
  }

  public function check()
  {
    return !is_null($this->user());
  }

  public function guest()
  {
    return !$this->check();
  }

  public function user()
  {
    if (!is_null($this->user)) {
      return $this->user;
    }

    $token = $this->getTokenFromRequest();

    if (!$token) {
      return null;
    }

    try {
      $decoded = JwtHelper::decode($token);

      // Try to get user ID from different possible fields
      $userId = $decoded->id ?? $decoded->user_id ?? null;

      if (!$userId) {
        return null;
      }

      $this->user = User::find($userId);

      return $this->user;
    } catch (\Exception $e) {
      return null;
    }
  }

  public function id()
  {
    if ($user = $this->user()) {
      return $user->getAuthIdentifier();
    }
  }

  public function validate(array $credentials = [])
  {
    return false;
  }

  public function setUser($user)
  {
    $this->user = $user;
    return $this;
  }

  protected function getTokenFromRequest()
  {
    $authHeader = $this->request->header('Authorization');

    if (!$authHeader) {
      return null;
    }

    // Support both "Bearer TOKEN" and just "TOKEN"
    return str_replace('Bearer ', '', $authHeader);
  }

  public function hasUser()
  {
    return !is_null($this->user);
  }
}
