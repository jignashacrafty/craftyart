<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function showFeedbacks()
    {
        return view('feedback.index');
    }

    public function showContacts()
    {
        return view('contacts.index');
    }

    public function getChatData(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function send_reply(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function getFeedback($id)
    {
        return response()->json(['success' => true]);
    }

    public function getContact($id)
    {
        return response()->json(['success' => true]);
    }
}
