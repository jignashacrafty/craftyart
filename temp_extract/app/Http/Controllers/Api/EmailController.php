<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailController extends ApiController
{
    public function send(Request $request): array
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
            'mail_type' => 'nullable|in:text,view'
        ]);
        $mailType = $request->input('mail_type', 'text');
        try {
            if ($mailType === 'view') {
                $fileName = Str::slug($request->subject) . '.blade.php';
                $path = resource_path('views/email_view/' . $fileName);
                file_put_contents($path, $request->body);

                $viewName = 'email_view.' . Str::slug($request->subject);

                // Send mail
                Mail::send($viewName, [], function ($message) use ($request) {
                    $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                        ->to($request->to)
                        ->replyTo(env("MAIL_REPLY_TO"), 'Reply Support')
                        ->subject($request->subject);

                    $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                });
            } else {
                Mail::send([], [], function ($message) use ($request) {
                    $message->from(env("MAIL_FROM_ADDRESS"), env("MAIL_FROM_NAME"))
                        ->to($request->to)
                        ->replyTo(env("MAIL_REPLY_TO"), 'Reply Support')
                        ->subject($request->subject)
                        ->setBody($request->body, 'text/html');

                    $message->getHeaders()->addTextHeader('Precedence', 'bulk');
                });
            }

            if (count(Mail::failures()) > 0) {
                return ResponseHandler::sendRealResponse(new ResponseInterface(
                    500,
                    false,
                    'Email sending failed.',
                    ['failures' => Mail::failures()]
                ));
            }

            return ResponseHandler::sendRealResponse(new ResponseInterface(
                200,
                true,
                'Email sent successfully'
            ));
        } catch (\Throwable $e) {
            return ResponseHandler::sendRealResponse(new ResponseInterface(
                500,
                false,
                'Failed to send email.',
                ['error' => $e->getMessage()]
            ));
        }
    }
}
