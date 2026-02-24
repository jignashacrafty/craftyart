<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
  public function sendFeedback(Request $request)
  {
    return response()->json(['success' => true]);
  }

  public function sendMessage(Request $request)
  {
    return response()->json(['success' => true]);
  }

  public function getChatList(Request $request)
  {
    return response()->json(['success' => true]);
  }
}
