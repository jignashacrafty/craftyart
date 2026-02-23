<?php

namespace App\Http\Controllers;

use App\Models\ManageSubscription;
use App\Models\Order;
use App\Models\Size;
use App\Models\TransactionLog;
use App\Models\UserData;
use App\Models\UserActivity;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    }

    public function removeOrdersDuplicate(Request $request): JsonResponse
    {
        $order = Order::create($request->all());
        if ($order->status === 'success') {
            $updatedCount = Order::where('user_id', $order->user_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', ['pending', 'failed'])
                ->update(['status' => 'override']);
        }
        return response()->json([
            'success' => true,
            'order_created' => $order,
            'duplicates_updated' => $updatedCount
        ]);
    }

    public function removeOrdersDuplicate2(Request $request)
    {
        $orders = Order::where('status', 'success')->get();
        foreach ($orders as $order) {
            if ($order->status === 'success') {
                $updatedCount = Order::where('user_id', $order->user_id)
                    ->where('id', '!=', $order->id)
                    ->whereIn('status', ['pending', 'failed'])
                    ->update(['status' => 'override']);
            }
        }
        return response()->json([
            'success' => true,
            'order_created' => $order,
            'duplicates_updated' => $updatedCount
        ]);
    }

    /*public function removeOrdersDuplicate2(Request $request): JsonResponse
    {
        $order = Order::where('status','success')->get();
        if ($order->status === 'success') {
            $updatedCount = Order::where('user_id', $order->user_id)
                ->where('id', '!=', $order->id)
                ->whereIn('status', ['pending', 'failed'])
                ->update(['status' => 'override']);
        }
        return response()->json([
            'success' => true,
            'order_created' => $order,
            'duplicates_updated' => $updatedCount
        ]);
    }*/

    public function show(Request $request)
    {
        $type = $request->input('type', 'all'); // default to 'all'

        if ($type === 'active') {
            $resultData = $this->allActiveSubscriber($request);
        } elseif ($type === 'expired') {
            $resultData = $this->allExpiredSubscriber($request);
        } elseif ($type === 'upcomming') {
            $resultData = $this->allUpcommingSubscriber($request);
        } else {
            $resultData = $this->allUser($request);
        }

        $userArray = $resultData['userArray'] ?? [];
        $userCount = $resultData['userCount'] ?? [];

        $activeSubscriberCount = HelperController::getAllActiveSubscribers($request);
        $expiredSubscriberCount = HelperController::getAllExpiredSubscribers($request);
        $upcommingExpiredSubscriberCount = HelperController::getAllUpCommingExpiredSubscribers($request);

        return view('users/show_users', compact(
            'userArray',
            'userCount',
            'activeSubscriberCount',
            'expiredSubscriberCount',
            'upcommingExpiredSubscriberCount',
            'type' // 🔥 include this
        ));
    }


    public function allUpcommingSubscriber($request)
    {
        $currentDate = now();
        $daysBefore = 7;
        $expirationDate = $currentDate->copy()->addDays($daysBefore);

        $query = $request->input('query', '');
        $sortBy = $request->input('sort_by', 'created_at');  // Default sorting column
        $sortOrder = $request->input('sort_order', 'desc');  // Default sorting order
        $perPage = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        $tempDataCount = $queryBuilder->count();
        $tempData = $queryBuilder->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $total = $tempDataCount;
        $count = min($total, $perPage);
        $start = ($request->input('page', 1) - 1) * $perPage + 1;
        $end = min($total, $start + $perPage - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $tempData;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }

    public function backupallUpcommingSubscriber($request)
    {
        $currentDate = now();
        $daysBefore = 7;
        $expirationDate = $currentDate->copy()->addDays($daysBefore);
        $temp_data_count = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })->orderBy('user_data.created_at', 'desc')->count();

        $temp_data = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" AND expired_at <= "' . $expirationDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
            })
            ->join('transaction_logs', function ($join) {
                $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
            })
            ->orderBy('user_data.created_at', 'desc')
            ->paginate(10);

        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function allExpiredSubscriber($request)
    {
        $currentDate = Carbon::now();
        $query = $request->input('query', '');
        $sortBy = $request->input('sort_by', 'created_at');  // Default sorting column
        $sortOrder = $request->input('sort_order', 'desc');  // Default sorting order
        $perPage = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '<', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        $tempDataCount = $queryBuilder->count();
        $tempData = $queryBuilder->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $total = $tempDataCount;
        $count = min($total, $perPage);
        $start = ($request->input('page', 1) - 1) * $perPage + 1;
        $end = min($total, $start + $perPage - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $tempData;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function backupallExpiredSubscriber($request)
    {
        if ($request->has('query')) {
            $currentDate = Carbon::now();
            $currentDate = now();
            $searchQuery = $request->input('query');
            $temp_data_count = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })
                ->where(function ($query) use ($searchQuery) {
                    $query->orWhere('user_data.name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.country_code', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.number', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.email', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.login_type', 'LIKE', '%' . $searchQuery . '%');
                })->orderBy('user_data.created_at', 'desc')
                ->count();


            $temp_data = DB::table('user_data')->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })
                ->where(function ($query) use ($searchQuery) {
                    $query->orWhere('user_data.name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.country_code', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.number', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.email', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('user_data.login_type', 'LIKE', '%' . $searchQuery . '%');
                })
                ->orderBy('user_data.created_at', 'desc')
                ->paginate(10);



        } else {
            $currentDate = Carbon::now();
            $currentDate = now();
            $temp_data_count = DB::table('user_data')
                ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })->orderBy('user_data.created_at', 'desc')->count();



            $temp_data = DB::table('user_data')
                ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at < "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })
                ->orderBy('user_data.created_at', 'desc')
                ->paginate(10);


        }
        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }

    public function allActiveSubscriber(Request $request)
    {
        $currentDate = Carbon::now();
        $query = $request->input('query', '');
        $sortBy = $request->input('sort_by', 'created_at');  // Default sorting column
        $sortOrder = $request->input('sort_order', 'desc');  // Default sorting order
        $perPage = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '>', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        $tempDataCount = $queryBuilder->count();
        $tempData = $queryBuilder->orderBy($sortBy, $sortOrder)->paginate($perPage);

        $total = $tempDataCount;
        $count = min($total, $perPage);
        $start = ($request->input('page', 1) - 1) * $perPage + 1;
        $end = min($total, $start + $perPage - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $tempData;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];

    }
    public function allActiveSubscriberBackup($request)
    {
        if ($request->has('query')) {
            $currentDate = Carbon::now();
            $currentDate = now();
            $temp_data_count = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join('transaction_logs', function ($join) use ($currentDate) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                        ->where('transaction_logs.expired_at', '>', $currentDate);
                })
                ->where(function ($query) use ($request) {
                    $query->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%');
                })
                ->with([
                    'transactionLogs' => function ($query) use ($currentDate) {
                        $query->where('expired_at', '>', $currentDate)
                            ->orderBy('id', 'desc')
                            ->limit(1);
                    }
                ])
                ->count();

            $temp_data = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join('transaction_logs', function ($join) use ($currentDate) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                        ->where('transaction_logs.expired_at', '>', $currentDate);
                })
                ->where(function ($query) use ($request) {
                    $query->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                        ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%');
                })
                ->with([
                    'transactionLogs' => function ($query) use ($currentDate) {
                        $query->where('expired_at', '>', $currentDate)
                            ->orderBy('id', 'desc')
                            ->limit(1);
                    }
                ])
                ->paginate(10);
        } else {
            $currentDate = Carbon::now();
            $currentDate = now();
            $temp_data_count = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join('transaction_logs', function ($join) use ($currentDate) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                        ->where('transaction_logs.expired_at', '>', $currentDate);
                })
                ->with([
                    'transactionLogs' => function ($query) use ($currentDate) {
                        $query->where('expired_at', '>', $currentDate)
                            ->orderBy('id', 'desc')
                            ->limit(1);
                    }
                ])->count();

            $temp_data = DB::table('user_data')
                ->select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
                ->join(DB::raw('(SELECT MAX(id) as max_log_id, user_id FROM transaction_logs WHERE expired_at > "' . $currentDate . '" GROUP BY user_id) as latest_logs'), function ($join) {
                    $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY latest_logs.user_id'));
                })
                ->join('transaction_logs', function ($join) {
                    $join->on('transaction_logs.id', '=', 'latest_logs.max_log_id');
                })->orderBy('user_data.created_at', 'desc')->paginate(10);


        }
        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['users'] = $temp_data;
        $userCount = UserData::count();
        $userArray = $data;

        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function allUser($request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $per_page = $request->input('per_page', 10);  // Default items per page

        $queryBuilder = UserData::query();

        if ($query) {
            $queryBuilder->where('uid', 'LIKE', '%' . $query . '%')
                ->orWhere('name', 'LIKE', '%' . $query . '%')
                ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                ->orWhere('number', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%')
                ->orWhere('login_type', 'LIKE', '%' . $query . '%');
        }

        $temp_data_count = $queryBuilder->count();
        $temp_data = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($per_page);

        $total = $temp_data_count;
        $count = min($total, $per_page);
        $start = ($request->input('page', 1) - 1) * $per_page + 1;
        $end = min($total, $start + $per_page - 1);

        $data['count_str'] = "Showing $start-$end of $total entries";
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }
    public function backupAllUser($request)
    {
        if ($request->has('query')) {
            $temp_data_count = UserData::where('uid', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('created_at', 'desc')
                ->count();

            $temp_data = UserData::where('uid', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('name', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('country_code', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('number', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('email', 'LIKE', '%' . $request->input('query') . '%')
                ->orWhere('login_type', 'LIKE', '%' . $request->input('query') . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $temp_data_count = UserData::orderBy('created_at', 'desc')->count();
            $temp_data = UserData::orderBy('created_at', 'desc')->paginate(10);
        }
        $total = $temp_data_count;
        $count = $total;
        $diff = 9;

        if ($total < 10) {
            $diff = ($total - 1);
        }

        if ($request->has('page')) {
            $count = $request->input('page') * 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        } else {
            $count = 10;
            if ($count > $total) {
                $diff = 9 - ($count - $total);
                $count = $total;
            }
        }

        if ($total == 0) {
            $ccc = "Showing 0-0 of 0 entries";
        } else {
            $ccc = "Showing " . ($count - $diff) . "-" . $count . " of " . $total . " entries";
        }

        $data['count_str'] = $ccc;
        $data['users'] = $temp_data;

        $userCount = UserData::count();
        $userArray = $data;
        return ["userArray" => $userArray, "userCount" => $userCount];
    }

    public function user_detail(UserData $userData, $id)
    {

        $user = UserData::where('uid', $id)->first();
        $datas['user'] = $user;
        $datas['userAct'] = UserActivity::where('id', $user->id)->get();
        return view('users/user_detail')->with('userData', $datas);
    }





    /* Export */

    public function export(Request $request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $export_all = $request->input('export_all', 0); // Check if export all records

        $queryBuilder = UserData::query();

        if ($query) {
            $queryBuilder->where('uid', 'LIKE', '%' . $query . '%')
                ->orWhere('name', 'LIKE', '%' . $query . '%')
                ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                ->orWhere('number', 'LIKE', '%' . $query . '%')
                ->orWhere('email', 'LIKE', '%' . $query . '%')
                ->orWhere('login_type', 'LIKE', '%' . $query . '%');
        }

        if ($export_all) {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->get();
        } else {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($request->input('per_page', 10));
        }

        $fileName = 'users.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $columns = ['ID', 'UID', 'Photo URI', 'Name', 'Email', 'Number', 'Login Type', 'Is Premium', 'Created At'];

        $callback = function () use ($users, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->uid,
                    $user->photo_uri,
                    $user->name,
                    $user->email,
                    $user->number,
                    $user->login_type,
                    $user->is_premium == '1' ? 'TRUE' : 'FALSE',
                    $user->created_at,
                ];

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function exportActiveSubscribers(Request $request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $export_all = $request->input('export_all', 0); // Check if export all records

        $currentDate = Carbon::now();

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '>', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        if ($export_all) {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->get();
        } else {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($request->input('per_page', 10));
        }

        // Export logic
        $fileName = 'active_subscribers.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, ['ID', 'UID', 'Name', 'Country Code', 'Number', 'Email', 'Login Type', 'Created At']);

            // Write CSV data
            foreach ($users as $user) {
                fputcsv($file, [$user->id, $user->uid, $user->name, $user->country_code, $user->number, $user->email, $user->login_type, $user->created_at]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExpiredSubscribers(Request $request)
    {
        $query = $request->input('query', '');
        $sort_by = $request->input('sort_by', 'created_at');  // Default sorting column
        $sort_order = $request->input('sort_order', 'desc');  // Default sorting order
        $export_all = $request->input('export_all', 0); // Check if export all records

        $currentDate = Carbon::now();

        $queryBuilder = UserData::select('user_data.*', 'transaction_logs.id as log_id', 'transaction_logs.expired_at')
            ->join('transaction_logs', function ($join) use ($currentDate) {
                $join->on(DB::raw('BINARY user_data.uid'), '=', DB::raw('BINARY transaction_logs.user_id'))
                    ->where('transaction_logs.expired_at', '<', $currentDate);
            });

        if ($query) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('uid', 'LIKE', '%' . $query . '%')
                    ->orWhere('name', 'LIKE', '%' . $query . '%')
                    ->orWhere('country_code', 'LIKE', '%' . $query . '%')
                    ->orWhere('number', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%')
                    ->orWhere('login_type', 'LIKE', '%' . $query . '%');
            });
        }

        if ($export_all) {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->get();
        } else {
            $users = $queryBuilder->orderBy($sort_by, $sort_order)->paginate($request->input('per_page', 10));
        }

        // Export logic
        $fileName = 'active_subscribers.csv';
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($file, ['ID', 'UID', 'Name', 'Country Code', 'Number', 'Email', 'Login Type', 'Created At']);

            // Write CSV data
            foreach ($users as $user) {
                fputcsv($file, [$user->id, $user->uid, $user->name, $user->country_code, $user->number, $user->email, $user->login_type, $user->created_at]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show personal details modal
     */
    public function showPersonalDetails($uid)
    {
        try {
            \Log::info('Fetching personal details for UID: ' . $uid);

            $user = UserData::where('uid', $uid)->first();

            if (!$user) {
                \Log::warning('User not found for UID: ' . $uid);
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $personalDetails = \App\Models\PersonalDetails::where('uid', $uid)->first();

            // Try to get brand kit
            $brandKit = null;
            try {
                $brandKit = \App\Models\BrandKit::where('user_id', $uid)->first();
            } catch (\Exception $e) {
                \Log::warning('Brand kit fetch error: ' . $e->getMessage());
            }

            // Get usage type from latest sale
            $latestSale = null;
            try {
                $latestSale = \App\Models\Revenue\Sale::where('email', $user->email)
                    ->orWhere('contact_no', $user->number)
                    ->orderBy('created_at', 'desc')
                    ->first();
            } catch (\Exception $e) {
                \Log::warning('Sale fetch error: ' . $e->getMessage());
            }

            \Log::info('Personal details fetched successfully', [
                'uid' => $uid,
                'has_personal_details' => !is_null($personalDetails),
                'has_brand_kit' => !is_null($brandKit),
                'has_sale' => !is_null($latestSale)
            ]);

            return response()->json([
                'success' => true,
                'user' => $user,
                'personal_details' => $personalDetails,
                'brand_kit' => $brandKit,
                'usage_type' => $latestSale ? $latestSale->usage_type : null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Show Personal Details Error', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update personal details
     */
    public function updatePersonalDetails(Request $request, $uid)
    {
        try {
            \Log::info('Updating personal details for UID: ' . $uid, [
                'request_data' => $request->all()
            ]);

            $user = UserData::where('uid', $uid)->firstOrFail();

            // Update or create personal details
            $personalDetails = \App\Models\PersonalDetails::updateOrCreate(
                ['uid' => $uid],
                [
                    'user_name' => $request->user_name ?? $user->name,
                    'bio' => $request->bio,
                    'country' => $request->country,
                    'state' => $request->state,
                    'city' => $request->city,
                    'address' => $request->address,
                    'interest' => $request->interest,
                    'purpose' => $request->purpose,
                    'usage' => $request->usage,
                    'reference' => $request->reference,
                    'language' => $request->language,
                ]
            );

            \Log::info('Personal details saved', ['personal_details' => $personalDetails]);

            // Update or create brand kit for website and role
            if ($request->has('website') || $request->has('role') || $request->has('usage')) {
                \Log::info('Updating brand kit', [
                    'website' => $request->website,
                    'role' => $request->role,
                    'usage' => $request->usage
                ]);

                $brandKit = \App\Models\BrandKit::updateOrCreate(
                    ['user_id' => $uid],
                    [
                        'website' => $request->website,
                        'role' => $request->role,
                        'usage' => $request->usage,
                    ]
                );

                \Log::info('Brand kit saved', ['brand_kit' => $brandKit]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Personal details updated successfully',
                'personal_details' => $personalDetails
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Personal Details Error', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
