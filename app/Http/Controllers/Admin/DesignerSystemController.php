<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignerApplication;
use App\Models\DesignerProfile;
use App\Models\DesignSubmission;
use App\Models\DesignerWithdrawal;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DesignerSystemController extends Controller
{
    /**
     * Show all designer applications
     */
    public function applications(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $applications = DesignerApplication::with('reviewer')
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('designer_system.applications', compact('applications', 'status'));
    }

    /**
     * Approve application
     */
    public function approveApplication(Request $request, $id)
    {
        $application = DesignerApplication::findOrFail($id);

        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Application already processed');
        }

        DB::beginTransaction();
        try {
            // Check if user already exists (frontend account)
            $user = User::where('email', $application->email)->first();
            
            if ($user) {
                // Update existing user to designer role
                $user->update([
                    'user_type' => UserRole::DESIGNER_EMPLOYEE->id(),
                    'status' => 1,
                ]);
                
                $message = 'Application approved! Existing user account updated to Designer role.';
            } else {
                // Create new user account
                $user = User::create([
                    'name' => $application->name,
                    'email' => $application->email,
                    'password' => Hash::make('Designer@123'),
                    'user_type' => UserRole::DESIGNER_EMPLOYEE->id(),
                    'status' => 1,
                ]);
                
                $message = 'Application approved! New designer account created with password: Designer@123';
            }

            // Create designer profile
            $profile = DesignerProfile::create([
                'user_id' => $user->id,
                'application_id' => $application->id,
                'display_name' => $application->name,
                'commission_rate' => $request->commission_rate ?? 30.00,
                'is_active' => true,
            ]);

            // Create wallet
            \App\Models\DesignerWallet::create([
                'designer_id' => $profile->id,
                'balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'pending_amount' => 0,
                'withdrawal_threshold' => 500.00,
            ]);

            // Update application
            $application->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Designer application approval failed', [
                'application_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Failed to approve application: ' . $e->getMessage());
        }
    }

    /**
     * Reject application
     */
    public function rejectApplication(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $application = DesignerApplication::findOrFail($id);

        if ($application->status !== 'pending') {
            return redirect()->back()->with('error', 'Application already processed');
        }

        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Application rejected');
    }

    /**
     * Show all designers
     */
    public function designers(Request $request)
    {
        $designers = DesignerProfile::with(['user', 'wallet'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('designer_system.designers', compact('designers'));
    }

    /**
     * Show design submissions
     */
    public function designSubmissions(Request $request)
    {
        $status = $request->get('status', 'pending_designer_head');
        
        $designs = DesignSubmission::with(['designer.user', 'seoDetails'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('designer_system.design_submissions', compact('designs', 'status'));
    }

    /**
     * Approve design (Designer Head)
     */
    public function approveDesign(Request $request, $id)
    {
        $design = DesignSubmission::findOrFail($id);

        if ($design->status !== 'pending_designer_head') {
            return redirect()->back()->with('error', 'Design already processed');
        }

        $design->update([
            'status' => 'pending_seo',
            'designer_head_notes' => $request->notes,
            'designer_head_reviewed_by' => auth()->id(),
            'designer_head_reviewed_at' => now(),
        ]);

        $design->designer->increment('approved_designs');

        return redirect()->back()->with('success', 'Design approved! Sent to SEO head.');
    }

    /**
     * Reject design (Designer Head)
     */
    public function rejectDesign(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $design = DesignSubmission::findOrFail($id);

        if ($design->status !== 'pending_designer_head') {
            return redirect()->back()->with('error', 'Design already processed');
        }

        $design->update([
            'status' => 'rejected_by_designer_head',
            'designer_head_notes' => $request->notes,
            'designer_head_reviewed_by' => auth()->id(),
            'designer_head_reviewed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Design rejected');
    }

    /**
     * Show SEO submissions
     */
    public function seoSubmissions(Request $request)
    {
        $status = $request->get('status', 'pending_seo');
        
        $designs = DesignSubmission::with(['designer.user', 'seoDetails', 'designerHeadReviewer'])
            ->whereIn('status', ['pending_seo', 'approved_by_seo', 'rejected_by_seo', 'live'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('designer_system.seo_submissions', compact('designs', 'status'));
    }

    /**
     * Approve design and publish (SEO Head)
     */
    public function publishDesign(Request $request, $id)
    {
        $request->validate([
            'meta_title' => 'required|string|max:255',
            'meta_description' => 'required|string|max:500',
        ]);

        $design = DesignSubmission::with('seoDetails')->findOrFail($id);

        if ($design->status !== 'pending_seo') {
            return redirect()->back()->with('error', 'Design is not pending SEO approval');
        }

        // Update or create SEO details
        if ($design->seoDetails) {
            $design->seoDetails->update([
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'slug' => \Illuminate\Support\Str::slug($request->meta_title),
                'keywords' => $request->keywords ? explode(',', $request->keywords) : [],
                'is_featured' => $request->has('is_featured'),
                'is_trending' => $request->has('is_trending'),
            ]);
        } else {
            \App\Models\DesignSeoDetail::create([
                'design_submission_id' => $design->id,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'slug' => \Illuminate\Support\Str::slug($request->meta_title),
                'keywords' => $request->keywords ? explode(',', $request->keywords) : [],
                'is_featured' => $request->has('is_featured'),
                'is_trending' => $request->has('is_trending'),
            ]);
        }

        // Update design status to live
        $design->update([
            'status' => 'live',
            'seo_head_notes' => $request->notes,
            'seo_head_reviewed_by' => auth()->id(),
            'seo_head_reviewed_at' => now(),
            'published_at' => now(),
        ]);

        $design->designer->increment('live_designs');

        return redirect()->back()->with('success', 'Design published successfully!');
    }

    /**
     * Show withdrawals
     */
    public function withdrawals(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $withdrawals = DesignerWithdrawal::with(['designer.user', 'processor'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('designer_system.withdrawals', compact('withdrawals', 'status'));
    }

    /**
     * Process withdrawal
     */
    public function processWithdrawal(Request $request, $id)
    {
        $request->validate([
            'transaction_reference' => 'required|string',
        ]);

        $withdrawal = DesignerWithdrawal::with('designer.wallet')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Withdrawal already processed');
        }

        DB::beginTransaction();
        try {
            $wallet = $withdrawal->designer->wallet;

            $withdrawal->update([
                'status' => 'completed',
                'transaction_reference' => $request->transaction_reference,
                'admin_notes' => $request->admin_notes,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            $wallet->pending_amount -= $withdrawal->amount;
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();

            DB::commit();

            return redirect()->back()->with('success', 'Withdrawal processed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process withdrawal: ' . $e->getMessage());
        }
    }

    /**
     * Reject withdrawal
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $withdrawal = DesignerWithdrawal::with('designer.wallet')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return redirect()->back()->with('error', 'Withdrawal already processed');
        }

        DB::beginTransaction();
        try {
            $wallet = $withdrawal->designer->wallet;

            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Refund to wallet
            $wallet->balance += $withdrawal->amount;
            $wallet->pending_amount -= $withdrawal->amount;
            $wallet->save();

            // Create refund transaction
            \App\Models\DesignerTransaction::create([
                'designer_id' => $withdrawal->designer_id,
                'type' => 'credit',
                'amount' => $withdrawal->amount,
                'balance_before' => $wallet->balance - $withdrawal->amount,
                'balance_after' => $wallet->balance,
                'transaction_type' => 'withdrawal_refund',
                'description' => 'Withdrawal request #' . $withdrawal->id . ' rejected and refunded',
                'reference_id' => $withdrawal->id,
                'reference_type' => 'withdrawal',
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Withdrawal rejected and amount refunded');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject withdrawal: ' . $e->getMessage());
        }
    }
}
