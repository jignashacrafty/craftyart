<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\ResponseHandler;
use App\Http\Controllers\UserController;
use App\Events\ForceLogout;
use App\Events\SessionUpdated;
use App\Helpers\JwtHelper;
use App\Models\OTPTable;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class NewAuthController extends ApiController
{

    private bool $isSessionCheck = true;

    function getUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");
        $user_data = UserData::whereUid($this->uid)->first();
        $userController = new UserController($request);

        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);
        }

        return $this->successed(datas: $userController->getNewUserRes(request: $request, userData: $user_data, isSessionCheck: $this->isSessionCheck));
    }

    private function checkAndUpdateSession($userData, string $deviceId, string $IP, string $userAgent): array
    {
        $activeCount = UserSession::whereUserId($userData->uid)->count();

        $transactionLog = TransactionLog::whereUserId($userData->uid)
            ->whereStatus(1)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        $deviceLimit = 2;

        if ($transactionLog) {
            $planLimit = $transactionLog->plan_limit;
            if (array_key_exists("device_limit", $planLimit)) {
                $deviceLimit = $planLimit['device_limit'];
            }
        }

        $session = UserSession::whereUserId($userData->uid)
            ->whereDeviceId($deviceId)
            ->first();

        if (!$session) {
            if ($activeCount >= $deviceLimit) {
                return [
                    'success' => false,
                    "msg" => "Device limit reached. Please logout from another device first.",
                    "data" => [
                        'current_sessions' => UserSession::whereUserId($userData->uid)->get(),
                        'device_limit' => $deviceLimit
                    ]];
            }

            $session = new UserSession();
            $session->user_id = $userData->uid;
            $session->device_id = $deviceId;
            $session->ip_address = $IP;
            $session->user_agent = $userAgent;
            $session->last_active = now();
            $session->save();
        } else {
            $session->update([
                'ip_address' => $IP,
                'user_agent' => $userAgent,
                'last_active' => now(),
            ]);
        }
        return ['success' => true, 'data' => $session];
    }

    function login(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $password = $request->get('password');

        if (empty($email) || empty($password) || ($this->isSessionCheck && empty($this->deviceId))) return $this->failed(statusCode: 400, msg: "Invalid request");

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) return $this->failed(statusCode: 400, msg: "Email is not registered");

        if (!Hash::check($password, $user_data->password)) return $this->failed(statusCode: 400, msg: "Incorrect Password");

        $userController = new UserController($request);
        $data = $userController->getNewUserRes(request: $request, userData: $user_data, isSessionCheck: $this->isSessionCheck, minimalResponse: true);

        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

            $jwtPayload = [
                'uid' => $user_data->uid,
                'email' => $user_data->email,
                'device_id' => $this->deviceId,
                'session_id' => $sessionResponse['data']['id'],
            ];

            $data['token'] = JwtHelper::generate($jwtPayload);
        }

        return $this->successed(datas: $data);
    }

    function signup(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $otp = $request->get('otp');
        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');
        $contactNo = $request->get('contact');
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        if (empty($otp) || empty($name) || empty($email) || empty($password) || ($this->isSessionCheck && empty($this->deviceId))) return $this->failed(msg: "Invalid request");
        if (UserData::whereEmail($email)->exists()) return $this->failed(msg: "Email is already registered");
        if (strlen($password) < 6) return $this->failed(msg: "Password length is short");

        $data = OTPTable::whereMail($email)->whereType('account_create')->get()->last();
        if (!$data || $data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid otp");
        $success = OTPTable::whereMail($email)->update(["status" => 0]);
        if (!$success) return $this->failed();

        $hashPassword = Hash::make($password);

        $userController = new UserController($request);
        $result = $userController->createFirebaseUser($request, $name, $email, $contactNo, $hashPassword, $this->deviceId, $utm_medium, $utm_source);

        if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);

        $user_data = $result['data'];

        $data = $userController->getNewUserRes(request: $request, userData: $user_data, isSessionCheck: $this->isSessionCheck, minimalResponse: true);

        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

            $jwtPayload = [
                'uid' => $user_data->uid,
                'email' => $user_data->email,
                'device_id' => $this->deviceId,
                'session_id' => $sessionResponse['data']['id'],
            ];
            $data['token'] = JwtHelper::generate($jwtPayload);
        }
        return $this->successed(datas: $data);
    }

    function handleGoogleSignIn(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(statusCode: 400, msg: "Unauthorized");

        $photo_uri = $request->get('photo_uri');
        $name = $request->get('name');
        $email = $request->get('email');
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        if (($this->isSessionCheck && empty($this->deviceId))) return $this->failed(statusCode: 400, msg: $this->deviceId);

        $user_data = UserData::whereEmail($email)->first();
        $userController = new UserController($request);

        if (!$user_data) {
            $isExists = $userController->checkFirebaseUid($email);
            if (!$isExists['registered']) return $this->failed();

            $userController = new UserController($request);

            $userInfo = $isExists['user'];
            $result = $userController->addUser($request, $userInfo['uid'], $photo_uri, $name, $email, null, "Google", $this->deviceId, $utm_medium, $utm_source);

            if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
            $user_data = $result['data'];
        }

        $data = $userController->getNewUserRes(request: $request, userData: $user_data, isSessionCheck: $this->isSessionCheck);

        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $this->deviceId, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

            $jwtPayload = [
                'uid' => $user_data->uid,
                'email' => $user_data->email,
                'device_id' => $this->deviceId,
                'session_id' => $sessionResponse['data']['id'],
            ];

            $data['token'] = JwtHelper::generate($jwtPayload);
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", datas: $data));
    }

    function resetPassword(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $otp = $request->get('otp');
        $password = $request->get('password');

        if (empty($email) || empty($otp) || empty($password)) return $this->failed(msg: "Invalid request");
        if (strlen($password) < 6) return $this->failed(msg: "Password length is short");

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) return $this->failed(msg: "Invalid request");

        $data = OTPTable::whereMail($email)->whereType('forgot_pass')->get()->last();
        if (!$data || $data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid otp");
        $success = OTPTable::whereMail($email)->update(["status" => 0]);
        if (!$success) return $this->failed();

        $password = Hash::make($password);
        $success = UserData::where('email', $email)->update(["password" => $password]);
        if (!$success) return $this->failed();
        if ($this->isSessionCheck) {
            $sessions = UserSession::whereUserId($user_data->uid)->get();
            foreach ($sessions as $session) {
                broadcast(new SessionUpdated($user_data->uid, 'session_removed', $session->device_id, [], $user_data->email));
                broadcast(new ForceLogout($user_data->uid, $session->device_id));
                $session->delete();
            }
        }

        return $this->successed(msg: "Password has been changed successfully");
    }

    function logout(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $deviceId = $request->get('device_id');
        $logoutAll = $request->get('logout_all', false);

        if (!$logoutAll && !$deviceId) {
            return $this->failed(msg: "Device ID required for single logout");
        }

        $user = UserData::whereEmail($email)->first();

        if (!$user) return $this->failed(msg: "User not found");

        if ($logoutAll) {
            $sessions = UserSession::whereUserId($user->uid)->get();

            foreach ($sessions as $session) {
                broadcast(new SessionUpdated($user->uid, 'session_removed', $session->device_id, [], $user->email));
                broadcast(new ForceLogout($user->uid, $session->device_id));
                $session->delete();
            }
            return $this->successed("All devices logged out successfully");
        } else {
            $session = UserSession::where('user_id', $user->uid)
                ->where('device_id', $deviceId)
                ->first();

            if ($session) {
                broadcast(new SessionUpdated($user->uid, 'session_removed', $deviceId, [], $user->email));
                try {
                    broadcast(new ForceLogout($user->uid, $deviceId));
                } catch (\Exception $e) {
                    Log::error("Failed to broadcast ForceLogout: " . $e->getMessage());
                }

                $session->delete();
            }

            return $this->successed("Logged out successfully");
        }
    }

}
