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
                'payment_types' => 'required|array|min:1',
                'payment_types.*' => 'in:caricature,template,video,ai_credit,subscription',
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

            // Auto-generate webhook URL for the gateway
            $gatewaySlug = Str::slug($request->gateway, '_');
            $credentials['webhook_url'] = url('/api/' . strtolower($gatewaySlug) . '/webhook');

            // Check if any selected payment type is already assigned to another gateway in the same scope
            $existingGateways = PaymentConfiguration::where('payment_scope', strtoupper($request->scope))
                ->get();
            
            foreach ($existingGateways as $existingGateway) {
                // Properly decode payment_types if it's a string
                $existingTypes = $existingGateway->payment_types;
                if (is_string($existingTypes)) {
                    $existingTypes = json_decode($existingTypes, true) ?? [];
                } elseif (!is_array($existingTypes)) {
                    $existingTypes = [];
                }
                
                $conflictingTypes = array_intersect($request->payment_types, $existingTypes);
                
                if (!empty($conflictingTypes)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment type(s) ' . implode(', ', $conflictingTypes) . ' already assigned to ' . $existingGateway->gateway
                    ], 422);
                }
            }

            // Create new gateway configuration (inactive by default)
            $config = PaymentConfiguration::create([
                'gateway' => Str::slug($request->gateway, '_'),
                'payment_scope' => strtoupper($request->scope),
                'credentials' => $credentials, // Don't json_encode - model will handle it
                'payment_types' => $request->payment_types, // Don't json_encode - model will handle it
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

            // Auto-generate webhook URL for the gateway (preserve if already exists)
            $gatewaySlug = Str::slug($request->gateway, '_');
            if (!isset($credentials['webhook_url'])) {
                $credentials['webhook_url'] = url('/api/' . strtolower($gatewaySlug) . '/webhook');
            }

            // If payment_types are provided, validate them
            if ($request->has('payment_types') && is_array($request->payment_types) && count($request->payment_types) > 0) {
                $request->validate([
                    'payment_types' => 'array|min:1',
                    'payment_types.*' => 'in:caricature,template,video,ai_credit,subscription',
                ]);

                // Check if any selected payment type is already assigned to another gateway in the same scope
                $existingGateways = PaymentConfiguration::where('payment_scope', strtoupper($request->scope))
                    ->where('id', '!=', $id)
                    ->get();
                
                foreach ($existingGateways as $existingGateway) {
                    // Properly decode payment_types if it's a string
                    $existingTypes = $existingGateway->payment_types;
                    if (is_string($existingTypes)) {
                        $existingTypes = json_decode($existingTypes, true) ?? [];
                    } elseif (!is_array($existingTypes)) {
                        $existingTypes = [];
                    }
                    
                    $conflictingTypes = array_intersect($request->payment_types, $existingTypes);
                    
                    if (!empty($conflictingTypes)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Payment type(s) ' . implode(', ', $conflictingTypes) . ' already assigned to ' . $existingGateway->gateway
                        ], 422);
                    }
                }

                // Update with payment types
                $config->update([
                    'gateway' => Str::slug($request->gateway, '_'),
                    'payment_scope' => strtoupper($request->scope),
                    'credentials' => $credentials, // Don't json_encode - model will handle it
                    'payment_types' => $request->payment_types, // Don't json_encode - model will handle it
                ]);
            } else {
                // Update only credentials, keep existing payment types
                $config->update([
                    'gateway' => Str::slug($request->gateway, '_'),
                    'payment_scope' => strtoupper($request->scope),
                    'credentials' => $credentials, // Don't json_encode - model will handle it
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gateway credentials updated successfully'
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

    public function activate(Request $request, $id)
    {
        try {
            $config = PaymentConfiguration::findOrFail($id);

            $request->validate([
                'scope' => 'required|in:NATIONAL,INTERNATIONAL',
            ]);

            // Deactivate all gateways in the same scope
            PaymentConfiguration::where('payment_scope', $request->scope)
                ->update(['is_active' => 0]);

            // Activate the selected gateway
            $config->update(['is_active' => 1]);

            return response()->json([
                'success' => true,
                'message' => $config->gateway . ' gateway activated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}