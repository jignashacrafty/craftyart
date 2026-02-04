<?php

namespace App\Http\Controllers;

use App\Models\PaymentConfiguration;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

class PaymentConfigController extends AppBaseController
{
    public function index(Request $request): Factory|View|Application
    {
        // Get all existing configurations grouped by scope
        $configurations = PaymentConfiguration::all()->keyBy('gateway');

        // Get active configurations by scope
        $nationalGateways = PaymentConfiguration::wherePaymentScope('NATIONAL')->get();
        $internationalGateways = PaymentConfiguration::wherePaymentScope( 'INTERNATIONAL')->get();

        return view('payment_configuration.index', [
            'nationalGateways' => $nationalGateways,
            'internationalGateways' => $internationalGateways,
            'configurations' => $configurations,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'scope' => 'required|in:NATIONAL,INTERNATIONAL',
                'gateway' => 'required|string',
            ]);

            $credentials = $request->credentials;
            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credentials are not found'
                ], 422);
            }


            // Deactivate other gateways in same scope
            PaymentConfiguration::wherePaymentScope(strtoupper($request->scope))
                ->whereIsActive(true)
                ->update(['is_active' => false]);
            // Update or create configuration
            PaymentConfiguration::updateOrCreate(
                [
                    'gateway' => Str::slug($request->gateway, '_'),
                ],
                [
                    'payment_scope' => strtoupper($request->scope),
                    'credentials' => $credentials,
                    'is_active' => 1,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $request->gateway . ' configuration saved and activated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function addNewGateway(Request $request)
    {
        try {
            $request->validate([
                'gateway' => 'required|string|unique:payment_configurations,gateway',
                'scope' => 'required|in:NATIONAL,INTERNATIONAL',
                'credential_keys' => 'required|array',
                'credential_values' => 'required|array',
            ]);

            // Prepare credentials from key-value pairs
            $credentials = [];
            $keys = $request->credential_keys;
            $values = $request->credential_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty(trim($keys[$i])) && !empty(trim($values[$i]))) {
                    $credentials[trim($keys[$i])] = trim($values[$i]);
                }
            }

            if (empty($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one credential field is required'
                ], 422);
            }

            // Create new gateway configuration (inactive by default)
            $config = PaymentConfiguration::create([
                'gateway' => Str::slug($request->gateway, '_'),
                'payment_scope' => strtoupper($request->scope),
                'credentials' => json_encode($credentials),
                'is_active' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'New gateway added successfully',
                'gateway' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function updateGateway(Request $request, $id)
    {
        try {
            $config = PaymentConfiguration::findOrFail($id);

            $request->validate([
                'gateway' => 'required|string|unique:payment_configurations,gateway,' . $id,
                'scope' => 'required|in:NATIONAL,INTERNATIONAL',
                'credential_keys' => 'required|array',
                'credential_values' => 'required|array',
            ]);

            // Prepare credentials from key-value pairs
            $credentials = [];
            $keys = $request->credential_keys;
            $values = $request->credential_values;

            for ($i = 0; $i < count($keys); $i++) {
                if (!empty(trim($keys[$i])) && !empty(trim($values[$i]))) {
                    $credentials[trim($keys[$i])] = trim($values[$i]);
                }
            }

            if (empty($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one credential field is required'
                ], 422);
            }

            // Update gateway
            $config->update([
                'gateway' => Str::slug($request->gateway, '_'),
                'payment_scope' => strtoupper($request->scope),
                'credentials' => json_encode($credentials),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gateway updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getGateway($id)
    {
        try {
            $config = PaymentConfiguration::findOrFail($id);

            return response()->json([
                'success' => true,
                'gateway' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $config = PaymentConfiguration::findOrFail($id);

            // Check if this is the active gateway in its scope
            if ($config->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active gateway. Please activate another gateway first.'
                ], 422);
            }

            $config->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gateway deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}