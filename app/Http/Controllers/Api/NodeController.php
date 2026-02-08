<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $apiKey = $request->header('X-API-Key');
            $node = Node::where('api_key', $apiKey)->first();
            
            if (!$node) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $request->attributes->add(['node' => $node]);
            return $next($request);
        });
    }

    public function ping(Request $request)
    {
        $node = $request->attributes->get('node');
        $node->update([
            'ping_ms' => $request->input('ping_ms'),
            'last_ping_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function updateStatus(Request $request)
    {
        $node = $request->attributes->get('node');
        $node->update([
            'used_ports' => $request->input('used_ports', $node->used_ports),
            'is_active' => $request->input('is_active', $node->is_active),
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function getConfig(Request $request)
    {
        $node = $request->attributes->get('node');
        
        return response()->json([
            'wg_interface' => config('app.wg_interface', 'wg0'),
            'wg_network' => config('app.wg_network', '10.66.0.0/16'),
            'wg_dns' => config('app.wg_dns', '1.1.1.1,8.8.8.8'),
            'sstp_network' => config('app.sstp_network', '10.67.0.0/16'),
            'sstp_dns' => config('app.sstp_dns', '1.1.1.1,8.8.8.8'),
        ]);
    }
}
