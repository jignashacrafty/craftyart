<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Events\ForceLogout;
use App\Events\SessionUpdated;
use App\Helpers\JwtHelper;
use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\OTPTable;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthController extends ApiController
{

    private bool $isSessionCheck = true;

    public static function createOrder(Request $request)
    {

        $order = new Order();
        $order->user_id = $request->user_id;
        $order->plan_id = $request->plan_id ?? 1;
        $order->crafty_id = \App\Http\Controllers\HelperController::generateStringIds(10,"CRAFT");
        $order->contact_no = $request->contact_no;
        $order->razorpay_order_id = \App\Http\Controllers\HelperController::generateStringIds(10,"RZP");
        $order->status = $request->status == 0 ? "pending" : "failed";
        $order->amount = $request->amount;
        $order->paid = $request->paid ?? 0;
        $order->currency = $request->currency ?? 'INR';
        $order->type = $request->type ?? "old_sub";
        $order->is_deleted = 0;
        $order->email_template_count = 0;
        $order->whatsapp_template_count = 0;
        $order->followup_call = 0;

        $order->emp_id = self::getOrderAssignEmpId($request->user_id);
        $order->save();
        return ResponseHandler::sendResponse($request,new ResponseInterface(200,true,"Order Created Successfully"));
    }

    public static function getOrderAssignEmpId($userId)
    {
        $previousOrderUser = Order::whereUserId($userId)->whereNotNull('emp_id')
            ->where('emp_id', '!=', 0)
            ->orderBy('id', 'desc')
            ->first();

        if($previousOrderUser){
            return $previousOrderUser->emp_id;
        }

        // Get Sales Users
        $salesUsers = User::whereUserType(UserRole::SALES->id())
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        // Safety check
        if (empty($salesUsers)) {
            return 0;
        }

        $salesManagerIds = User::whereUserType(UserRole::SALES_MANAGER->id())
            ->pluck('id')
            ->toArray();

        $lastOrder = Order::whereIn('status', ['pending', 'failed'])
            ->whereNotNull('emp_id')
            ->where('emp_id', '!=', 0)
            ->whereNotIn('emp_id', $salesManagerIds)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastOrder || !$lastOrder->emp_id) {
            return $salesUsers[0];
        }

        $lastEmpId = $lastOrder->emp_id;

        $currentIndex = array_search($lastEmpId, $salesUsers);

        if ($currentIndex === false) {
            return $salesUsers[0];
        }

        $nextIndex = ($currentIndex + 1) % count($salesUsers);
        return $salesUsers[$nextIndex];
    }

    function getUser(Request $request): array|string {
        if ($this->isFakeRequestAndUser($request)) return $this->failed(msg: "Unauthorized");
        $user_data = UserData::whereUid($this->uid)->first();
        $userController = new UserController($request);
        $deviceId = $request->get('device_id');
        if($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $deviceId, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);
        }
        return $this->successed(datas: $userController->getUserRes(request: $request, userData: $user_data,isSessionCheck: $this->isSessionCheck));
    }

    private function checkAndUpdateSession($userData,string $deviceId,string $IP,string $userAgent): array
    {
        $activeCount = UserSession::whereUserId($userData->uid)->count();

        $transactionLog = TransactionLog::whereUserId($userData->uid)
            ->whereStatus(1)
            ->where('expired_at', '>', now())
            ->latest()
            ->first();
        $deviceLimit = 1;
        if($transactionLog){
            $planLimit = $transactionLog->plan_limit;
            if(array_key_exists("device_limit",$planLimit)){
                $deviceLimit =  $planLimit['device_limit'];
            }
        }

        $session = UserSession::whereUserId($userData->uid)
            ->whereDeviceId($deviceId)
            ->first();

        if (!$session) {
            if ($activeCount >= $deviceLimit) {
                return [
                    'success'=> false,
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
        return ['success'=> true,'data' => $session];
    }

    function login(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $password = $request->get('password');

        $deviceId = $request->get('device_id');

        if (is_null($email) || is_null($password) || ($this->isSessionCheck && is_null($deviceId))) return $this->failed(statusCode: 400,msg: "Invalid request");

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) return $this->failed(statusCode: 400,msg: "Email is not registered");

        if (!Hash::check($password, $user_data->password)) return $this->failed(statusCode: 400,msg: "Incorrect Password");

        $userController = new UserController($request);
        $data =  $userController->getUserRes(request: $request, userData: $user_data,isSessionCheck: $this->isSessionCheck, minimalResponse: true);
        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $deviceId, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

            $jwtPayload = [
                'uid'       => $user_data->uid,
                'email'     => $user_data->email,
                'device_id' => $deviceId,
                'session_id'=> $sessionResponse['data']['id'],
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
        $device_id = $request->get('device_id', "");
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");
        if (is_null($otp) || is_null($name) || is_null($email) || is_null($password) || ($this->isSessionCheck && is_null($device_id))) return $this->failed(msg: "Invalid request");
        if (UserData::whereEmail($email)->exists()) return $this->failed(msg: "Email is already registered");
        if (strlen($password) < 6) return $this->failed(msg: "Password length is short");

        $data = OTPTable::whereMail($email)->whereType('account_create')->get()->last();
        if (!$data || $data->status == "0" || $data->otp != $otp) return $this->failed(msg: "Invalid otp");
        $success = OTPTable::whereMail($email)->update(["status" => 0]);
        if (!$success) return $this->failed();

        $hashPassword = Hash::make($password);
        $uid = AuthController::generateUid();

        $userController = new UserController($request);
        $result = $userController->createFirebaseUser($request, $name, $email, $contactNo, $hashPassword, $device_id, $utm_medium, $utm_source);

//        $result = $userController->addUser($request, $uid, null, $name, $email, $contactNo, "Email", $device_id, $utm_medium, $utm_source, password: $password);

        if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);

        $user_data = $result['data'];

        $data = $userController->getUserRes(request: $request, userData: $user_data,isSessionCheck: $this->isSessionCheck , minimalResponse: true);

        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $device_id, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

            $jwtPayload = [
                'uid'       => $user_data->uid,
                'email'     => $user_data->email,
                'device_id' => $device_id,
                'session_id'=> $sessionResponse['data']['id'],
            ];
            $data['token'] = JwtHelper::generate($jwtPayload);
        }
        return $this->successed(datas: $data);
    }

    function handleGoogleSignIn(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

//        $fetch = $request->get('fetch', false);
//        $check = $request->get('check', false);
//        $get = $request->get('get', false);

//        if ($fetch) return $this->getUser($request, true);
//        if ($check) return $this->userExist($request);
//        if ($get) return $this->getUser($request);

        $photo_uri = $request->get('photo_uri');
        $name = $request->get('name');
        $email = $request->get('email');
        $device_id = $request->get('device_id');
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        if (($this->isSessionCheck && is_null($device_id))) return $this->failed(statusCode: 400,msg: "Invalid request");

        $user_data = UserData::whereEmail($email)->first();
        $userController = new UserController($request);

        if (!$user_data) {
//            $isExists = $userController->checkFirebaseUid($email);
//            if (!$isExists['registered']) {
//                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Something went wrong"));
//            }
//
//            $userInfo = $isExists['user'];
            $result = $this->addUser($request, AuthController::generateUid(), $photo_uri, $name, $email, null, "Google", $device_id, $utm_medium, $utm_source);
            if (!$result['success']) return ResponseHandler::sendEncryptedResponse($request, $result);
            $user_data = $result['data'];
        }
        $data = $userController->getUserRes(request: $request, userData: $user_data,isSessionCheck: $this->isSessionCheck);
        if ($this->isSessionCheck) {
            $sessionResponse = $this->checkAndUpdateSession(userData: $user_data, deviceId: $device_id, IP: $request->ip(), userAgent: $request->userAgent());
            if (!$sessionResponse['success']) return $this->failed(msg: $sessionResponse['msg'], datas: $sessionResponse['data']);

            $jwtPayload = [
                'uid'       => $user_data->uid,
                'email'     => $user_data->email,
                'device_id' => $device_id,
                'session_id'=> $sessionResponse['data']['id'],
            ];

            $data['token'] = JwtHelper::generate($jwtPayload);
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Done", datas: $data ));
    }

    public function addUser(Request $request, $uid, $photo_uri, $name, $email, $number, $login_type, $device_id, $utm_medium, $utm_source, $password = null): array
    {
//        $isValid = ValidEmail::passes($email);
//        if (!is_null($isValid)) {
//            return ResponseHandler::sendRealResponse(new ResponseInterface(404, false, $isValid));
//        }

        $res = new UserData();
        $res->uid = $uid;
        $res->refer_id = UserController::generateReferID($uid);
        $res->photo_uri = $photo_uri;
        $res->name = $name;
        $res->email = $email;
        $res->password = $password;
        $res->contact_no = $number;
        $res->login_type = $login_type;
        $res->is_premium = "0";
        $res->utm_medium = $utm_medium;
        $res->utm_source = $utm_source;
        $res->device_id = $device_id;

        $refer_user = UserData::where("refer_id", $utm_source)->first();
        if ($refer_user != null) {
            $refer_user->coins = $refer_user->coins + 10;
            $refer_user->save();

            $coinTransaction = new CoinTransaction();
            $coinTransaction->user_id = $refer_user->uid;
            $coinTransaction->refered_user = $uid;
            $coinTransaction->reason = $name . " has just logged in through your referral link.";
            $coinTransaction->credited = 10;
            $coinTransaction->save();
        }
        $res->save();

        $user_data = UserData::where("uid", $uid)->first();
        if ($user_data) $user_data->business_user = 0;

//        FbPixel::trackEvent(FacebookEvent::COMPLETE_REGISTRATION, $request, $name, $email);

        $isNull = is_null($user_data);
        $statusCode = $isNull ? 404 : 200;
        $success = !$isNull;
        $msg = $isNull ? "Something went wrong." : "valid";
        return ResponseHandler::sendRealResponse(new ResponseInterface($statusCode, $success, $msg, ['data' => $user_data]));
    }


    function resetPassword(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) return $this->failed(msg: "Unauthorized");

        $email = $request->get('email');
        $otp = $request->get('otp');
        $password = $request->get('password');

        if (is_null($email) || is_null($otp) || is_null($password)) return $this->failed(msg: "Invalid request");
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

    public function logout(Request $request): array|string
    {
        if ($this->isFakeRequest($request))
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));

        $deviceId = $request->get('device_id');
        $email = $request->get('email');
//        $uid = $request->get('uid');
        $logoutAll = $request->get('logout_all', false);

        if (!$logoutAll && !$deviceId) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Device ID required for single logout"));
        }

//        $user = UserData::when($uid, fn($q) => $q->where('uid', $uid))
//            ->when(!$uid && $email, fn($q) => $q->where('email', $email))
//            ->first();

        $user = UserData::whereEmail($email)->first();

        if (!$user) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "User not found"));
        }

        if ($logoutAll) {
            $sessions = UserSession::whereUserId($user->uid)->get();

            foreach ($sessions as $session) {
                broadcast(new SessionUpdated($user->uid, 'session_removed', $session->device_id, [], $user->email));
                broadcast(new ForceLogout($user->uid, $session->device_id));
                $session->delete();
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "All devices logged out successfully"));
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

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Logged out successfully"));
        }
    }

    public static function generateUid($id = "", $length = 30): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $uid = $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (UserData::whereUid($uid)->exists());
        return $uid;
    }

}
