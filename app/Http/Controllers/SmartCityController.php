<?php

namespace App\Http\Controllers;

use App\Models\WasteRequest;
use App\Models\User;
use App\Services\HeatmapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmartCityController extends Controller
{
    protected $heatmapService;
    protected $smartCityService;

    public function __construct(HeatmapService $heatmapService, \App\Services\SmartCityService $smartCityService)
    {
        $this->heatmapService = $heatmapService;
        $this->smartCityService = $smartCityService;
    }

    public function dashboard()
    {
        $stats = [
            'total_requests' => WasteRequest::count(),
            'pending_requests' => WasteRequest::where('status', 'pending')->count(),
            'collected_waste' => WasteRequest::whereIn('status', ['collected', 'disposed'])->count(),
            'active_drivers' => User::where('role', 'driver')->count(),
        ];

        $kpis = $this->smartCityService->getKPIs();
        $insights = $this->smartCityService->generateInsights();
        $prediction = $this->smartCityService->predictWaste();

        return view('admin.smart_dashboard', compact('stats', 'kpis', 'insights', 'prediction'));
    }

    public function getPredictionData()
    {
        $predictions = \App\Models\Prediction::orderBy('prediction_date', 'asc')->take(10)->get();
        return response()->json($predictions);
    }

    public function simulate(Request $request)
    {
        $type = $request->type ?? 'heavy_waste';
        $this->smartCityService->triggerSimulation($type);
        return response()->json(['success' => true, 'message' => "Simulation '$type' triggered successfully!"]);
    }

    public function getHeatmapData(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'category_id']);
        return response()->json($this->heatmapService->getGridData($filters));
    }

    public function getAnalyticsData()
    {
        $trends = WasteRequest::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->limit(7)
            ->get();

        $categories = DB::table('waste_categories')
            ->leftJoin('waste_requests', 'waste_categories.id', '=', 'waste_requests.waste_category_id')
            ->select('waste_categories.name', DB::raw('count(waste_requests.id) as count'))
            ->groupBy('waste_categories.id', 'waste_categories.name')
            ->get();

        return response()->json([
            'trends' => $trends,
            'categories' => $categories
        ]);
    }

    public function chatbot(Request $request)
    {
        $message = strtolower($request->message);
        
        // Knowledge Base
        $responses = [
            'greeting' => [
                'keywords' => ['hello', 'hi', 'hey', 'greetings', 'morning', 'evening'],
                'response' => "Hello! I am EcoBot, your Smart City Waste Assistant. I can help you with recycling tips, tracking requests, points, or using the dashboard. What's on your mind?"
            ],
            'plastic' => [
                'keywords' => ['plastic', 'bottle', 'container', 'poly', 'pet'],
                'response' => "Plastic should be cleaned and dried. Recyclable plastics (marked 1-7) go in the 'Recyclable' bin. You earn 10 points per kg!"
            ],
            'dry_waste' => [
                'keywords' => ['dry', 'paper', 'cardboard', 'box', 'magazine', 'news'],
                'response' => "Dry waste includes paper, cardboard, and non-greasy packaging. Keep it dry to maintain its recycling value. It should be requested under 'Dry Waste'."
            ],
            'wet_waste' => [
                'keywords' => ['wet', 'food', 'organic', 'peel', 'kitchen', 'scrap', 'waste'],
                'response' => "Wet waste like food scraps and fruit peels should be composted. Place them in the 'Wet Waste' category for our organic collection service."
            ],
            'hazardous' => [
                'keywords' => ['hazard', 'battery', 'chemical', 'paint', 'medicine', 'bulb', 'toxic'],
                'response' => "Hazardous items (batteries, old meds, chemicals) need special care. Please use the 'Hazardous' category and keep them separate from other waste."
            ],
            'e_waste' => [
                'keywords' => ['electronic', 'phone', 'laptop', 'cable', 'computer', 'e-waste', 'gadget'],
                'response' => "Electronics (E-waste) contain precious metals but also toxic materials. Please use our specialized E-waste collection to ensure safe disposal."
            ],
            'tracking' => [
                'keywords' => ['track', 'where', 'driver', 'status', 'find', 'location', 'map'],
                'response' => "You can track your active requests on your Dashboard. When a driver is 'On the Way', a 'Live Track' button will appear to show their real-time location!"
            ],
            'points' => [
                'keywords' => ['point', 'earn', 'score', 'reward', 'credit', 'how to get'],
                'response' => "You earn points for every successful pickup! Segregating waste correctly earns bonus points. Points help you reach 'Eco Hero' and 'Green Champion' status."
            ],
            'badges' => [
                'keywords' => ['badge', 'award', 'medal', 'achievement', 'unlock'],
                'response' => "Badges are awarded for milestones like 'First 10kg Recycled' or 'Perfect Segregation Streak'. Check your profile to see your collection!"
            ],
            'bins' => [
                'keywords' => ['bin', 'full', 'iot', 'sensor', 'smart bin', 'nearby'],
                'response' => "Our Smart Bins use IoT sensors to monitor fill levels. When a bin hits 90%, it automatically alerts our nearest driver for collection. You can see bin status in the 'Smart Bins' tab."
            ],
            'complaint' => [
                'keywords' => ['complain', 'issue', 'problem', 'dirty', 'stink', 'missed'],
                'response' => "If you have an issue (like a missed pickup or a dirty area), please use the 'Complaints' section. Our admin team reviews all reports within 24 hours."
            ],
            'marketplace' => [
                'keywords' => ['market', 'buy', 'sell', 'recycle', 'item', 'second hand'],
                'response' => "Our Marketplace allows citizens to trade or give away items instead of throwing them away. One person's trash is another's treasure!"
            ],
            'heatmap' => [
                'keywords' => ['heatmap', 'map', 'city', 'density', 'red', 'yellow'],
                'response' => "The Heatmap shows real-time waste density across the city. Red zones indicate high waste areas that need immediate collection. It helps us optimize our fleet!"
            ],
            'thanks' => [
                'keywords' => ['thanks', 'thank you', 'ok', 'good', 'bye', 'cool'],
                'response' => "You're welcome! Let's keep the city green together. 🌍💚"
            ]
        ];

        // Search for match
        foreach ($responses as $key => $data) {
            foreach ($data['keywords'] as $keyword) {
                if (str_contains($message, $keyword)) {
                    return response()->json(['response' => $data['response']]);
                }
            }
        }

        // Generic fallback that uses parts of the user message to sound smart
        $response = "That sounds interesting! I'm still learning about '" . $message . "', but I can definitely help with recycling, tracking, points, or reporting issues. Try asking about one of those!";

        return response()->json(['response' => $response]);
    }
}
