<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Utils\StorageUtils;
use App\Models\Category;
use App\Models\Design;
use App\Models\NewCategory;
use App\Models\NewSearchTag;
use App\Models\UserData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends ApiController
{
    public function changeEmailsubscribe(Request $request): array|string
    {
        $user = UserData::where('uid', $this->uid)->first();
        if (!$user) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }
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
        $user = UserData::where('uid', $this->uid)->first();
        if (!$user) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }
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

    public function generateUsernamesForAll(Request $request): array|string
    {
        $users = UserData::all();
        foreach ($users as $user) {
            if (empty($user->user_name)) {
                $newUsername = self::generateUserName('user', 8);
                $user->user_name = $newUsername;
                $user->save();
            }
        }
        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, true, 'Usernames generated successfully for all users.')
        );
    }
    public static function generateUserName($prefix = 'user', $length = 8): string
    {
        $pool = '0123456789';
        do {
            $username = $prefix . substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
            $exists = UserData::where('user_name', $username)->exists();
        } while ($exists);
        return $username;
    }

    public function updateProfile(Request $request): array|string
    {
        $user = UserData::where('uid', $request->uid)->first();
        if (!$user) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', $request->user_name)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Username can only contain letters, numbers, and underscores.'));
        }
        if (isset($request->user_name) && $request->user_name != $user->user_name) {
            $existingUser = UserData::where('user_name', $request->user_name)
                ->first();
            if ($existingUser) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username already taken by another user.'));
            }
            if ($user->is_username_update == 1) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username can only be updated once.'));
            }
            $user->user_name = $request->user_name;
        }
        if (isset($request->bio)) {
            if (strlen($request->bio) > 100) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Bio must be at most 100 characters.'));
            }
            $user->bio = $request->bio;
            $user->is_username_update = 1;
        }
        $user->save();
        return ResponseHandler::sendResponse($request, new ResponseInterface(
            200,
            true,
            'Profile updated successfully',
            ['user' => $user]
        ));
    }

    public function updateUser(Request $request): array|string
    {
        if ($this->isFakeRequestAndUser($request)) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(401, false, "Unauthorized"));
        }
        $uid = $request->get('uid', $this->uid);
        $user = UserData::where('uid', $uid)->first();
        if (!$user) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }

        if (isset($request->bio)) {
            if (strlen($request->bio) > 100) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Bio must be at most 100 characters.'));
            }
            $user->bio = $request->bio;
        }

        if (isset($request->user_name) && $request->user_name !== $user->user_name) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $request->user_name)) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(422, false, 'Username can only contain letters, numbers, and underscores.'));
            }
            $existingUser = UserData::where('user_name', $request->user_name)->first();
            if ($existingUser) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username already taken by another user.'));
            }
            if ($user->is_username_update == 1) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(403, false, 'Username can only be updated once.'));
            }
            $user->user_name = $request->user_name;
            $user->is_username_update = 1;
        }
        $photo_uri = $request->file('photo_uri');
        $name = $request->get('name');
        $updateDp = $request->get('update_dp');
        if ($name !== null) {
            $user->name = $name;
        }
        if ($photo_uri === null) {
            if ($updateDp == 1) {
                $user->photo_uri = null;
            }
        } else {
            $new_name = $user->uid . '-' . HelperController::generateID('') . '.png';
            StorageUtils::delete($user->photo_uri);
            StorageUtils::storeAs($photo_uri, 'uploadedFiles/user_dp', $new_name);
            $user->photo_uri = 'uploadedFiles/user_dp/' . $new_name;
        }
        // Save all changes 
        $user->save();
        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(
                200,
                true,
                "User updated successfully.",
                $this->getUserRes($request, $user)
            )
        );
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

    public function getPortfolio(Request $request): array|string
    {
        $limit = 10;
        $page = $request->input('page', 1);
        $uidInput = $request->user_name;
        $userData = UserData::where('user_name', $uidInput)->where('creator', 1)->first();
        if (!$userData) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'User not found'));
        }
        if ($page == 1) {
            $userData->increment('profile_count', 1);
            $userData->save();
        }
        $formatedCount = $this->formatCount($userData->profile_count);
        $userData->unique_name = '@' . $userData->user_name;
        $query = Design::where('creator_id', $userData->uid);
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
                    ->flatMap(function ($tagJson) {
                        $tags = json_decode($tagJson, true);
                        return is_array($tags) ? $tags : [];
                    })
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
                    'type' => 'perent',
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
                    'type' => 'perent',
                    'default' => true,
                ]
            ]);
            $transformedCategories = $allCategoriesOption->merge($transformedCategories)->values();
        }
        $templates = $query->paginate($limit, ['*'], 'page', $page);
        $isLastPage = $templates->currentPage() >= $templates->lastPage();
        $allCategoryIds = $templates->pluck('category_id')->unique();
        $categories = Category::whereIn('id', $allCategoryIds)->get()->keyBy('id');
        $item_rows = collect($templates->items())->map(function ($item) use ($userData, $categories) {
            $catRow = $categories[$item->category_id] ?? null;
            $thumbArray = json_decode($item->thumb_array, true) ?? [];
            if (!$catRow)
                return null;
            return HelperController::getItemData($userData->uid, $catRow, $item, $thumbArray);
        })->filter()->values();
        $responseData = [
            'success' => true,
            'message' => 'Templates and categories loaded successfully.',
            'isLastPage' => $isLastPage,
            'page' => $page,
            'profile_view' => $formatedCount,
            'total_templates' => $totalCount ?? 0,
            'user' => $userData,
            'datas' => $item_rows,
            'categories' => $transformedCategories,
        ];
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loading Success!', $responseData));
    }

}