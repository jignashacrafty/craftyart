<?php

namespace App\Http\Controllers\Api;

use App\Events\DeviceLimitResolved;
use App\Events\ForceLogout;
use App\Events\SessionUpdated;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\BrandKit;
use App\Models\CoinTransaction;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\OTPTable;
use App\Models\PurchaseHistory;
use App\Models\UserData;
use App\Models\UserDataDeleted;
use App\Models\ExportTable;
use App\Models\UserSession;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Contract\Auth;

class UserController extends ApiController
{
    protected Auth $auth;

    // public function __construct(Request $request)
    // {
    //     parent::__construct($request);
    //     $serviceAccountPath = base_path('firebase_credentials.json');
    //     // dd($serviceAccountPath);
    //     $factory = (new Factory)->withServiceAccount($serviceAccountPath);

    //     $this->auth = $factory->createAuth();
    // }

    // In your UserController - UPDATED createUser method
    public function createUser(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $check = $request->get('check', false);
        $get = $request->get('get', false);

        if ($check)
            return $this->userExist($request);
        if ($get)
            return $this->getUser($request);

        $photo_uri = $request->get('photo_uri');
        $name = $request->get('name');
        $email = $request->get('email');
        $login_type = $request->get('type');
        $device_id = $request->get('device_id');
        $remove_device_id = $request->get('remove_device_id', null);
        $utm_medium = $request->get('utm_medium', "craftyart");
        $utm_source = $request->get('utm_source', "craftyart");

        if (empty($device_id)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Device ID is required."));
        }

        $user_data = UserData::whereEmail($email)->first();
        if (!$user_data) {
            $isExists = $this->checkFirebaseUid($email, true);
            if (!$isExists['registered']) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "User not found in Firebase"));
            }

            $userInfo = $isExists['user'];
            $user_data = $this->addUser(
                $request,
                $userInfo['uid'],
                $photo_uri,
                $name,
                $email,
                $login_type,
                $device_id,
                $utm_medium,
                $utm_source
            );

            if (!$user_data) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, "Unable to create user"));
            }
        }

        // Device session management
        $deviceLimit = $user_data->device_limit ?? 1;
        $deletedSessionInfo = [];
        $customToken = hash('sha256', Str::random(60));

        $existingSession = UserSession::where('user_id', $user_data->uid)
            ->where('device_id', $device_id)
            ->first();

        if ($existingSession) {
            $existingSession->update([
                'custom_token' => $customToken,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_active' => now(),
            ]);

            // Broadcast session updated with email
            broadcast(new SessionUpdated($user_data->uid, 'session_updated', $device_id, [
                'active_sessions' => UserSession::whereUserId($user_data->uid)->get()
            ], $user_data->email));

        } else {
            $activeSessionsCount = UserSession::whereUserId($user_data->uid)->count();

            if ($activeSessionsCount >= $deviceLimit) {
                if (!$remove_device_id) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(
                        409,
                        false,
                        "Device limit reached. Please choose a device to logout.",
                        [
                            'current_sessions' => UserSession::where('user_id', $user_data->uid)->get(),
                            'device_limit' => $deviceLimit
                        ]
                    ));
                }

                // Remove old session
                $oldSession = UserSession::whereUserId($user_data->uid)
                    ->where('device_id', $remove_device_id)
                    ->first();

                if ($oldSession) {
                    // Broadcast session removed with email
                    broadcast(new SessionUpdated($user_data->uid, 'session_removed', $oldSession->device_id, [], $user_data->email));
                    broadcast(new DeviceLimitResolved($user_data->email));

                    event(new ForceLogout($user_data->uid, $oldSession->device_id));
                    $deletedSessionInfo[] = $oldSession->toArray();
                    $oldSession->delete();
                }
            }

            // Create new session
            UserSession::create([
                'user_id' => $user_data->uid,
                'device_id' => $device_id,
                'custom_token' => $customToken,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_active' => now(),
            ]);

            // Broadcast session created with email
            broadcast(new SessionUpdated($user_data->uid, 'session_created', $device_id, [
                'active_sessions' => UserSession::whereUserId($user_data->uid)->get()
            ], $user_data->email));
        }

        $responseData = [
            'uid' => $user_data->uid,
            'name' => $user_data->name,
            'email' => $user_data->email,
            'bio' => $user_data->bio,
            'number' => $user_data->number,
            'device_limit' => (int) $deviceLimit,
            'subscription' => $user_data->subscription ?? null,
            'active_sessions' => UserSession::whereUserId($user_data->uid)->get(),
            'deleted_sessions' => $deletedSessionInfo,
            'custom_token' => $customToken
        ];

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Login successful", [
            'user_details' => $responseData
        ]));
    }

    public function getUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        return $this->handleUserSession($request, "User retrieved successfully");
    }

    public function userExist(Request $request): array|string
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        return $this->handleUserSession($request, "User retrieved successfully", true);
    }


    // In your UserController - UPDATED handleUserSession method
    private function handleUserSession(Request $request, string $successMsg, bool $createIfNotExist = false): array|string
    {
        $uid = $request->get('uid');
        $email = $request->get('email');
        $device_id = $request->get('device_id');
        $customToken = $request->get('custom_token');
        $remove_device_id = $request->get('remove_device_id', null);

        if (empty($device_id)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Device ID is required."));
        }

        $user_data = UserData::where('uid', $uid)->orWhere('email', $email)->first();
        if (!$user_data) {
            $session = UserSession::where('custom_token', $uid)->first();
            if ($session) {
                $user_data = UserData::where('uid', $session->user_id)->first();
            } else {
                $user_data = null;
            }
        }

        if (!$user_data && $createIfNotExist && $email) {
            $isExists = $this->checkFirebaseUid($email, true);
            if (!$isExists['registered']) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "User not exist"));
            }

            $userInfo = $isExists['user'];
            $user_data = $this->addUser(
                $request,
                $userInfo['uid'],
                $userInfo['photoUrl'],
                $userInfo['name'],
                $userInfo['email'],
                "email",
                null,
                "craftyart",
                "craftyart"
            );
        }

        if (!$user_data) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "User not found"));
        }

        // Session management
        $deviceLimit = $user_data->device_limit ?? 1;
        $deletedSessionInfo = [];
        $newCustomToken = hash('sha256', Str::random(60));

        $existingSession = UserSession::whereUserId($user_data->uid)
            ->whereDeviceId($device_id)
            ->first();

        if ($existingSession) {
            $existingSession->update([
                'custom_token' => $newCustomToken,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_active' => now(),
            ]);

            $activeSessions = UserSession::whereUserId($user_data->uid)->get()->unique('device_id')->values();

            broadcast(new SessionUpdated($user_data->uid, 'session_updated', $device_id, [
                'active_sessions' => $activeSessions->toArray()
            ], $user_data->email));

        } else {
            $activeSessionsCount = UserSession::whereUserId($user_data->uid)->count();

            if ($activeSessionsCount >= $deviceLimit) {
                if (!$remove_device_id) {
                    return ResponseHandler::sendResponse($request, new ResponseInterface(
                        409,
                        false,
                        "Device limit reached. Please choose a device to logout.",
                        [
                            'current_sessions' => UserSession::where('user_id', $user_data->uid)->get()->toArray(),
                            'device_limit' => $deviceLimit
                        ]
                    ));
                }

                $oldSession = UserSession::whereUserId($user_data->uid)
                    ->where('device_id', $remove_device_id)
                    ->first();

                if ($oldSession) {
                    broadcast(new SessionUpdated($user_data->uid, 'session_removed', $oldSession->device_id, [], $user_data->email));
                    broadcast(new DeviceLimitResolved($user_data->email));

                    event(new ForceLogout($user_data->uid, $oldSession->device_id));

                    $deletedSessionInfo[] = $oldSession->toArray();
                    $oldSession->delete();

                    sleep(1);
                }
            }

            // Create new session
            UserSession::create([
                'user_id' => $user_data->uid,
                'device_id' => $device_id,
                'custom_token' => $newCustomToken,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_active' => now(),
            ]);

            broadcast(new SessionUpdated($user_data->uid, 'session_created', $device_id, [
                'active_sessions' => UserSession::whereUserId($user_data->uid)->get()->toArray()
            ], $user_data->email));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(
            200,
            true,
            $successMsg,
            [
                "user_details" => [
                    'uid' => $user_data->uid,
                    'name' => $user_data->name,
                    'email' => $user_data->email,
                    'bio' => $user_data->bio,
                    'number' => $user_data->number,
                    'device_limit' => (int) $deviceLimit,
                    'subscription' => $user_data->subscription ?? null,
                    'custom_token' => $newCustomToken,
                ],
                'active_sessions' => UserSession::where('user_id', $user_data->uid)->get()->toArray(),
                'deleted_sessions' => $deletedSessionInfo,
            ]
        ));
    }

    public function logout(Request $request)
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $deviceId = $request->get('device_id');
        $email = $request->get('email');
        $uid = $request->get('uid');
        $logoutAll = $request->get('logout_all', false);

        if (!$deviceId && !$logoutAll) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(400, false, "Device ID or logout_all flag required"));
        }

        $user = UserData::when($uid, fn($q) => $q->where('uid', $uid))
            ->when(!$uid && $email, fn($q) => $q->where('email', $email))
            ->first();

        if (!$user) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "User not found"));
        }

        if ($logoutAll) {
            $sessions = UserSession::whereUserId($user->uid)->get();
            foreach ($sessions as $session) {
                broadcast(new SessionUpdated($user->uid, 'session_removed', $session->device_id, [], $user->email));
                event(new ForceLogout($user->uid, $session->device_id));
                $session->delete();
            }

            broadcast(new DeviceLimitResolved($user->email));

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "All devices logged out successfully"));
        }

        $session = UserSession::whereUserId($user->uid)
            ->where('device_id', $deviceId)
            ->first();

        if (!$session) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, "No active session found for this device"));
        }

        broadcast(new SessionUpdated($user->uid, 'session_removed', $deviceId, [], $user->email));

        $remainingSessions = UserSession::whereUserId($user->uid)->count();
        if ($remainingSessions <= $user->device_limit) {
            broadcast(new DeviceLimitResolved($user->email));
        }

        event(new ForceLogout($user->uid, $deviceId));
        $session->delete();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Logged out successfully", [
            'device_id' => $deviceId,
            'user_id' => $user->uid,
        ]));
    }

    function updateUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $photo_uri = $request->file('photo_uri');
        $name = $request->get('name');
        $updateDp = $request->get('update_dp');

        $userData = UserData::whereUid($this->uid)->first();

        if ($request->has('bio')) {
            if (strlen($request->bio) > 100) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Bio must be at most 100 characters.'));
            }
            $userData->bio = $request->bio;
        }

        if (isset($request->user_name) && $request->user_name !== $userData->user_name) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $request->user_name)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Username can only contain letters, numbers, and underscores.'));
            }
            $existingUser = UserData::where('user_name', $request->user_name)->first();
            if ($existingUser) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username already taken by another user.'));
            }
            if ($userData->is_username_update == 1) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username can only be updated once.'));
            }
            $userData->user_name = $request->user_name;
            $userData->is_username_update = 1;
        }

        if ($photo_uri == null) {
            if ($updateDp == 1) {
                $userData->photo_uri = null;
            }
        } else {
            $new_name = $this->uid . '-' . HelperController::generateID('') . '.png';
            StorageUtils::delete($userData->photo_uri);
            StorageUtils::storeAs($photo_uri, 'uploadedFiles/user_dp', $new_name);
            $new_photo_uri = 'uploadedFiles/user_dp/' . $new_name;
            $userData->photo_uri = $new_photo_uri;
        }

        $userData->name = $name;
        $userData->save();

        $userData = UserData::whereUid($this->uid)->first();

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "User updated successfully.", $this->getUserRes($request, $userData)));
    }

    function deleteUser(Request $request)
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $otp = $request->get('otp');
        $idToken = $request->get('idToken');

        if ($otp == null || $idToken == null) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid params"));
        }

        $user_data = UserData::whereUid($this->uid)->first();

        $data = OTPTable::whereMail($user_data->email)->where('type', 1)->get()->last();

        if (!$data || $data->status == "0" || $data->otp != $otp) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Invalid OTP"));
        }

        $res = OTPTable::find($data->id);
        $res->status = "0";
        $res->save();

        try {
            $client = new Client();
            $fbaseRes = $client->post('https://identitytoolkit.googleapis.com/v1/accounts:delete?key=AIzaSyCQP7F26DBVJvXWNgwS3lerBUCGcbH2z4U', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'idToken' => $idToken,
                ],
            ]);

            $statusCode = $fbaseRes->getStatusCode();
            if ($statusCode != 200) {
                $response['success'] = false;
                $response['message'] = 'Bad request';
                return $response;
            }

            $data = json_decode($fbaseRes->getBody(), true);

            if (isset($data['error'])) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Bad Request"));
            }

            $res = new UserDataDeleted();
            $res->user_int_id = $user_data->uid;
            $res->uid = $user_data->uid;
            $res->refer_id = $user_data->refer_id;
            $res->stripe_cus_id = $user_data->stripe_cus_id;
            $res->razorpay_cus_id = $user_data->razorpay_cus_id;
            $res->photo_uri = $user_data->photo_uri;
            $res->name = $user_data->name;
            $res->country_code = $user_data->country_code;
            $res->number = $user_data->number;
            $res->email = $user_data->email;
            $res->login_type = $user_data->login_type;
            $res->total_validity = $user_data->total_validity;
            $res->validity = $user_data->validity;
            $res->is_premium = $user_data->is_premium;
            $res->special_user = $user_data->special_user;
            $res->can_update = $user_data->can_update;
            $res->utm_source = $user_data->utm_source;
            $res->utm_medium = $user_data->utm_medium;
            $res->coins = $user_data->coins;
            $res->device_id = $user_data->device_id;
            $res->fldr_str = $user_data->fldr_str;
            $res->creation_date = $user_data->created_at;
            $res->save();

            UserData::whereUid($this->uid)->delete();

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Your account has been successfully deleted."));

        } catch (RequestException $e) {
            $message = $e->getMessage();

            if ($e->hasResponse()) {
                $fbaseRes = $e->getResponse();
                $statusCode = $fbaseRes->getStatusCode();
                if ($statusCode === 400) {
                    $errorData = json_decode($fbaseRes->getBody()->getContents(), true);
                    $message = $errorData['error']['message'];
                }
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, $message));
        }
    }

    public function addUser(Request $request, $uid, $photo_uri, $name, $email, $login_type, $device_id, $utm_medium, $utm_source, $contactNo = null, $password = null): UserData|null
    {
        $idData = UserData::query()->latest()->first();
        $id = $idData ? $idData->id + 1 : 1;

        $res = new UserData();
        $res->uid = $uid;
        $res->refer_id = $this->generateReferID($id);
        $res->photo_uri = $photo_uri;
        $res->name = $name;
        $res->email = $email;
        $res->login_type = $login_type;
        $res->is_premium = "0";
        $res->utm_medium = $utm_medium;
        $res->utm_source = $utm_source;
        $res->device_id = $device_id;
        $res->number = $contactNo;

        if ($password) {
            $res->password = $password;
        }

        // Handle referral system
        $refer_user = UserData::whereReferId($utm_source)->first();
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

        $user_data = UserData::whereUid($uid)->first();
        if ($user_data) {
            $user_data->business_user = 0;
            $user_data->save();
        }

        return $user_data;
    }


//    public function getUserRes(Request $request, UserData $userData, $minimalResponse = false): array
//    {
//        // Generate username if not exists
//        if (!$userData->user_name || empty($userData->user_name)) {
//            $userName = $this->generateUserName();
//            $userData->user_name = $userName;
//            UserData::where('id', $userData->id)->update(['user_name' => $userName]);
//        }
//
//        $this->addBrandKit($userData);
//
//        // Build user array
//        $user['uid'] = $userData->uid;
//        $user['name'] = $userData->name;
//        $user['email'] = $userData->email;
//        $user['number'] = $userData->number;
//        $user['country_code'] = $userData->country_code;
//        $user['user_name'] = $userData->user_name;
//        $user['is_username_update'] = $userData->is_username_update == 1;
//        $user['bio'] = $userData->bio;
//        $user['creator'] = $userData->creator == 1;
//        $user['hoc'] = $userData->hoc;
//        $user['photo_uri'] = $userData->photo_uri;
//
//        // Handle photo URL
//        if (str_contains($userData->photo_uri, 'uploadedFiles/')) {
//            $user['photo_uri'] = HelperController::$mediaUrl . $userData->photo_uri;
//        }
//
//        // Get video limits
//        $videoLeft = $this->getUsersVideoLimit($userData->uid);
//        $user['total_video_limit'] = $videoLeft['limit'];
//        $user['video_left'] = $videoLeft['left'];
//
//        // Get subscription data
//        $subData = $this->getUserSubHistory($request, $userData);
//
//        if ($subData["current"]) {
//            $user['is_premium'] = 1;
//        } else {
//            $user['is_premium'] = 0;
//        }
//
//        $response['user'] = $user;
//        $response['currentPlan'] = $subData["current"];
//        $response['subsHistory'] = $subData["history"];
//        $response['purHistory'] = $this->getUserPurchaseHistory($userData);
//        $response['ipData'] = HelperController::getIpAndCountry($request);
//
//        return $response;
//    }



    public static function getUsersVideoLimit($uid): array
    {
        return [
            "limit" => 50,
            "left" => ExportTable::whereUid($uid)->where('path', 'LIKE', '%.mp4')->where('created_at', '>=', now()->subDay())->count()
        ];
    }

    private function getUserSubHistory(Request $request, UserData $user_data): array|null
    {
        $singleData = null;
        // $singleDataRow = SubscriptionController::getActivePlan($user_data->uid);
        // if ($singleDataRow != null) {

        //     $subRow = Subscription::find($singleDataRow->plan_id);

        //     $singleData['package_name'] = $subRow->package_name;
        //     $singleData['transaction_id'] = $singleDataRow->transaction_id;
        //     if ($singleDataRow->currency_code == 'Trial') {
        //         $singleData['amount'] = "Trial";
        //     } else {
        //         $singleData['amount'] = $singleDataRow->currency_code . " " . $singleDataRow->paid_amount;
        //     }
        //     $singleData['method'] = $singleDataRow->payment_method;
        //     $singleData['purchase_date'] = $singleDataRow->created_at->format('d/m/Y H:i:s');
        //     $singleData['billing_date'] = Carbon::parse($singleDataRow->expired_at)->format('d/m/Y H:i:s');
        //     $singleData['validity'] = SubscriptionController::findTimeLeft($singleDataRow->expired_at);
        //     $singleData['status'] = HelperController::checkSubsStatus($singleDataRow->status);
        //     $singleData['color'] = HelperController::getSubsColor($singleDataRow->status);
        //     $user_data->is_premium = 1;
        //     $user_data->business_user = $singleDataRow->plan_id == 20 ? 1 : 0;
        // } else {
        //     $user_data->is_premium = DomainChecker::isValidSpecialUser($request, $user_data);
        //     $user_data->business_user = 0;
        // }

        // $multiData = array();
        // $transData = TransactionLog::where("user_id", $user_data->uid)->orderBy('id', 'DESC')->get();
        // if ($transData != null && $transData->count() != 0) {
        //     foreach ($transData as $row) {
        //         $subRow = Subscription::find($row->plan_id);
        //         if ($row->currency_code == 'Trial') {
        //             $amount = "Trial";
        //         } else {
        //             $currency_code = "$";
        //             if ($row->currency_code === "Rs") {
        //                 $currency_code = "₹";
        //             }
        //             $amount = $currency_code . $row->paid_amount;
        //         }
        //         $multiData[] = array(
        //             'package_name' => $subRow->package_name,
        //             'transaction_id' => $row->transaction_id,
        //             'amount' => $amount,
        //             'method' => $row->payment_method,
        //             'purchase_date' => $row->created_at->format('d/m/Y H:i:s'),
        //             'billing_date' => Carbon::parse($row->expired_at)->format('d/m/Y H:i:s'),
        //             'validity' => $subRow->validity . " Days",
        //             'status' => HelperController::checkSubsStatus($row->status),
        //             'color' => HelperController::getSubsColor($row->status)
        //         );
        //     }
        // }

        return ["current" => $singleData, "history" => []];
    }

    private function getUserPurchaseHistory(UserData $user_data): array|null
    {
        $purchaseDatas = PurchaseHistory::whereUserId($user_data->uid)->where('payment_status', 1)->where('status', 1)->get();

        $purchase_rows = [];

        if ($purchaseDatas != null && $purchaseDatas->count() != 0) {
            foreach ($purchaseDatas as $purchaseData) {
                $purchase_rows[] = array(
                    'id' => $purchaseData->product_id,
                    'type' => $purchaseData->product_type,
                );
            }
        }
        return $purchase_rows;
    }

    private function addBrandKit(UserData $user_data): void
    {
        $brand_res = Brandkit::whereUserId($user_data->uid)->first();
        if ($brand_res == null) {
            $brand_res = new Brandkit();
            $brand_res->user_id = $user_data->uid;
            $brand_res->name = $user_data->name;
            $brand_res->email = $user_data->email;
            if ($user_data->number != null) {
                $brand_res->primary_number = $user_data->country_code . " " . $user_data->number;
            }
            $brand_res->save();
        }
    }

    public function changeEmailSubscribe(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $user = UserData::whereUid($this->uid)->first();

        $type = $user->email_preferance;
        if (is_string($type)) {
            $type = json_decode($type, true);
        }
        if (!is_array($type)) {
            $type = ['offer' => 1, 'special_page' => 1, 'feature' => 1];
        }
        foreach ($type as $key => $val) {
            $type[$key] = $val == 0 ? 1 : 0;
        }
        $user->email_preferance = json_encode($type);
        $user->save();
        $responseType = $type['offer'];
        $statusText = $responseType === 1 ? 'You have subscribed' : 'You have unsubscribed';
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, $statusText, [
            'type' => $responseType,
            'subscription' => $type,
        ]));
    }

    public function getSubscribeStatus(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }

        $user = UserData::whereUid($this->uid)->first();

        $subscription = $user->email_preferance;
        if (empty($subscription)) {
            $subscription = ['offer' => 1, 'special_page' => 1, 'feature' => 1];
            $user->email_preferance = json_encode($subscription);
            $user->save();
        } else {
            $subscription = json_decode($subscription, true);
            if (!is_array($subscription)) {
                $subscription = ['offer' => 1, 'special_page' => 1, 'feature' => 1];
                $user->email_preferance = json_encode($subscription);
                $user->save();
            }
        }
        $type = $subscription['offer'];
        $boolSubscription = array_map(fn($val) => (bool) $val, $subscription);
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'success', [
            'type' => $type,
            'subscription' => $boolSubscription,
        ]));
    }

    private function formatCount($count): string
    {
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        }
        if ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }
        return (string) $count;
    }

    public function getPortfolio(Request $request)
    {
        if ($this->isFakeRequest($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }
        $limit = HelperController::getPaginationLimit(size: 50);
        $page = $request->input('page', 1);
        $uidInput = $request->user_name;
        $userData = UserData::whereUserName($uidInput)->where('creator', 1)->first();
        if (!$userData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }
        if ($page == 1 && $this->uid !== $userData->uid) {
            $userData->increment('profile_count', 1);
            $userData->save();
        }
        $formatedCount = $this->formatCount($userData->profile_count);
        $query = Design::whereCreatorId($userData->uid);
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('h2_tag', 'like', '%' . $searchTerm . '%')
                    ->orWhere('id_name', 'like', '%' . $searchTerm . '%');
            });
        }
        $filter = $request->input('filter');
        if ($filter && isset($filter['id'], $filter['type'])) {
            $filterId = $filter['id'];
            $filterType = $filter['type'];
            if ($filterType === 'child') {
                $query->where('new_category_id', $filterId);
            } elseif ($filterType === 'All' || $filterType === 'all') {
                $category = NewCategory::find($filterId);
                if ($category) {
                    if ($category->parent_category_id != 0) {
                        $query->where('new_category_id', $filterId);
                    } else {
                        $childIds = NewCategory::where('parent_category_id', $filterId)->pluck('id')->toArray();
                        $query->whereIn('new_category_id', $childIds);
                    }
                }
            } elseif ($filterType === 'tag') {
                $tagId = (int) $filterId;
                $parentId = (int) ($filter['parent_id'] ?? 0);
                $query->where('new_category_id', $parentId)
                    ->whereJsonContains('new_related_tags', $tagId);
            }
        }
        $transformedCategories = [];
        if ($page == 1) {
            $allTemplates = (clone $query)->get();
            $totalCount = $allTemplates->count();
            $allNewCategoryIds = $allTemplates->pluck('new_category_id')->unique()->values();
            $allNewCategories = NewCategory::whereIn('id', $allNewCategoryIds)
                ->select('id', 'category_name', 'parent_category_id')
                ->get()
                ->keyBy('id');
            $tagsByCategory = $allTemplates->groupBy('new_category_id')->map(function ($designs) {
                return $designs->pluck('new_related_tags')
                    ->filter()
                    ->flatten()
                    ->unique()->values();
            });
            $allTagIds = $tagsByCategory->flatten()->unique()->values();
            $searchTags = NewSearchTag::whereIn('id', $allTagIds)->get()->keyBy('id');
            $categoryTagMap = $tagsByCategory->map(function ($tagIds) use ($searchTags) {
                return $tagIds->map(function ($tagId) use ($searchTags) {
                    $tag = $searchTags->get($tagId);
                    return $tag ? [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'parent_category_id' => $tag->category_id ?? null,
                    ] : null;
                })->filter()->values();
            });
            $parentCategoryIds = $allNewCategories->pluck('parent_category_id')->filter()->unique();
            $parentCategories = NewCategory::whereIn('id', $parentCategoryIds)
                ->select('id', 'category_name', 'parent_category_id')
                ->get();
            $transformedCategories = $parentCategories->map(function ($parent) use ($allNewCategories, $categoryTagMap) {
                $children = $allNewCategories->filter(function ($cat) use ($parent) {
                    return $cat->parent_category_id == $parent->id;
                })->values();
                $subCategories = collect();
                foreach ($children as $child) {
                    $tags = collect();
                    $tags->push([
                        'id' => $child->id,
                        'name' => 'All',
                        'type' => 'all',
                        'display_name' => $child->category_name,
                    ]);
                    if ($categoryTagMap->has($child->id)) {
                        foreach ($categoryTagMap[$child->id] as $tag) {
                            $tags->push([
                                'id' => $tag['id'],
                                'name' => $tag['name'],
                                'display_name' => $tag['name'],
                                'parent_id' => $child->id,
                                'type' => 'tag',
                            ]);
                        }
                    }
                    $subCategories->push([
                        'id' => $child->id,
                        'name' => $child->category_name,
                        'display_name' => $child->category_name,
                        'parent_id' => $child->parent_category_id,
                        'type' => 'child',
                        'tags' => $tags->toArray()
                    ]);
                }
                return [
                    'id' => $parent->id,
                    'name' => $parent->category_name,
                    'display_name' => $parent->category_name,
                    'parent_id' => $parent->parent_category_id,
                    'type' => 'parent',
                    'sub_categories' => collect([
                        [
                            'id' => $parent->id,
                            'name' => 'All',
                            'type' => 'all',
                            'display_name' => $parent->category_name,
                        ]
                    ])->merge($subCategories)->toArray()
                ];
            })->values();
            $allCategoriesOption = collect([
                [
                    'id' => 0,
                    'name' => 'All Categories',
                    'display_name' => 'Category',
                    'parent_id' => 0,
                    'type' => 'parent',
                    'default' => true,
                ]
            ]);
            $transformedCategories = $allCategoriesOption->merge($transformedCategories)->values();
        }
        $templates = $query->paginate($limit, ['*'], 'page', $page);
        $isLastPage = $templates->currentPage() >= $templates->lastPage();
        $allCategoryIds = $templates->getCollection()->pluck('new_category_id')->unique();
        $categories = NewCategory::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        $rates = RateController::getRates();

        $item_rows = collect($templates->items())->map(function ($item) use ($userData, $categories, $rates) {
            $catRow = $categories[$item->new_category_id] ?? null;
            $catLink = HelperController::$webPageUrl . "templates/p/" . $item->id_name;
            if ($catRow != null) {
                $catLink = $catRow->cat_link;
            }
            return HelperController::getItemData(
                uid: $this->uid,
                catRow: $catRow,
                item: $item,
                thumbArray: json_decode($item->thumb_array, true) ?? [],
                catLink: $catLink,
                rates: $rates
            );
        })->filter()->values();

        $user['name'] = $userData->name;
        $user['user_name'] = $userData->user_name;
        $user['unique_name'] = '@' . $userData->user_name;
        $user['bio'] = $userData->bio;
        $user['photo_uri'] = $userData->photo_uri;
        if (str_contains($userData->photo_uri, 'uploadedFiles/')) {
            $user['photo_uri'] = HelperController::$mediaUrl . $userData->photo_uri;
        }

        $responseData = [
            'success' => true,
            'message' => 'Templates and categories loaded successfully.',
            'isLastPage' => $isLastPage,
            'page' => $page,
            'profile_view' => $formatedCount,
            'total_templates' => $totalCount ?? 0,
            'user' => $user,
            'datas' => $item_rows,
            'categories' => $transformedCategories,
        ];
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loading Success!', $responseData));
    }

    public function generateUsernamesForAll()
    {
        UserData::select('id', 'user_name')->where('creator', 1)->whereNull('user_name')->orderBy('id')->chunk(1000, function ($rows) {
            foreach ($rows as $row) {
                UserData::whereId($row->id)
                    ->update(['user_name' => self::generateUserName()]);
            }
        });
        return "done";
    }

    public static function generateUserName($prefix = 'user', $length = 8): string
    {
        $pool = '0123456789';
        do {
            $username = $prefix . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
            $exists = UserData::c($username)->exists();
        } while ($exists);
        return $username;
    }

    function checkFirebaseUid($uidORmail, $isEmail): array
    {
        if (empty($uidORmail)) {
            return ['registered' => false, 'error' => 'Empty UID or Email'];
        }

        try {
            if ($isEmail) {
                $user = $this->auth->getUserByEmail($uidORmail);
            } else {
                $user = $this->auth->getUser($uidORmail);
            }

            $userId = $user->uid ?? null;
            $emailId = $user->email ?? null;

            if ($userId && $emailId) {
                return [
                    'registered' => true,
                    'user' => [
                        'name' => $user->displayName ?? "CraftyArt",
                        'email' => $emailId,
                        'photoUrl' => $user->photoUrl ?? null,
                        'uid' => $userId,
                    ]
                ];
            }
            return ['registered' => false];
        } catch (\Exception | AuthException | FirebaseException $e) {
            return ['registered' => false, 'error' => $e->getMessage()];
        }
    }

    public static function generateReferID($id, $length = 6): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        do {
            $refer_id = $id . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        } while (UserData::whereReferId($refer_id)->exists());
        return $refer_id;
    }

}