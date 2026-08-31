<?php

declare(strict_types=1);

namespace App\Domain\ComponentUtilizationMapping;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\FundAllocation\FundAllocation;
use App\Domain\FundAllocation\FundAllocationService;
use Illuminate\Support\Facades\DB;
use App\Traits\HasAttribute;

class ComponentUtilizationMappingController extends Controller
{
    use HasAttribute;

    protected $service;
    protected $fundAllocationService;

    public function __construct(ComponentUtilizationMappingService $service, FundAllocationService $fundAllocationService)
    {
        $this->service = $service;
        $this->fundAllocationService = $fundAllocationService;
    }

    public function index()
    {
        $title = 'Component Utilization Mapping';
        $history = $this->service->getMappingHistory();
        
        $financialYears = financial_year();
        $duration = $this->listOf(code: 'duration', skipParent: true);
        
        $subDurations = DB::table('attribute_values')
            ->whereIn('parent_id', array_keys($duration))
            ->select('id', 'attribute_value as name', 'parent_id')
            ->get();
            
        // Resolve names for History List
        $allIds = collect();
        foreach ($history as $item) {
            $allIds->push($item->mapping->duration);
            $allIds->push($item->mapping->sub_duration);
            $allIds->push($item->source_major_component);
            $allIds->push($item->source_sub_component);
            $allIds->push($item->target_category);
            $allIds->push($item->target_sub_component);
        }
        
        $attributeNames = DB::table('attribute_values')
            ->whereIn('id', $allIds->filter()->unique())
            ->pluck('attribute_value', 'id');
            
        $userIds = $history->pluck('created_by')->filter()->unique();
        $userNames = DB::table('users')->whereIn('id', $userIds)->get()->mapWithKeys(function ($user) {
            $fullName = trim(preg_replace('/\s+/', ' ', "{$user->first_name} {$user->middle_name} {$user->last_name}"));
            return [$user->id => $fullName];
        });
        
        return view('component-utilization-mapping.index', compact('history', 'title', 'financialYears', 'duration', 'subDurations', 'attributeNames', 'userNames'));
    }

    public function getDurations(Request $request)
    {
        $fy = $request->financial_year;
        $durationIds = FundAllocation::where('financial_year', $fy)->whereNotNull('duration_id')->distinct()->pluck('duration_id');
        
        $durations = DB::table('attribute_values')
            ->whereIn('id', $durationIds)
            ->select('id', 'attribute_value as name')
            ->get();
            
        return response()->json(['durations' => $durations]);
    }

    public function getSubDurations(Request $request)
    {
        $fy = $request->financial_year;
        $durationId = $request->duration_id;
        
        $subDurationIds = FundAllocation::where('financial_year', $fy)
            ->where('duration_id', $durationId)
            ->whereNotNull('sub_duration_id')
            ->distinct()
            ->pluck('sub_duration_id');
            
        $subDurations = DB::table('attribute_values')
            ->whereIn('id', $subDurationIds)
            ->select('id', 'attribute_value as name')
            ->get();
            
        return response()->json(['sub_durations' => $subDurations]);
    }

    public function getUtilizedComponents(Request $request)
    {
        $query = FundAllocation::with('componentMappings');
        
        $allocations = $query->get();
        
        $componentMappings = collect();
        foreach ($allocations as $alloc) {
            foreach ($alloc->componentMappings as $mapping) {
                // Attach the allocation properties to the mapping for easy access
                $mapping->fy = $alloc->financial_year;
                $mapping->durationId = $alloc->duration_id;
                $mapping->subDurationId = $alloc->sub_duration_id;
                $componentMappings->push($mapping);
            }
        }
        
        $attributeIds = $componentMappings->pluck('major_component_id')
            ->merge($componentMappings->pluck('sub_component_id'))
            ->merge($componentMappings->pluck('durationId'))
            ->merge($componentMappings->pluck('subDurationId'))
            ->filter()
            ->unique();
            
        $attributeValues = DB::table('attribute_values')->whereIn('id', $attributeIds)->pluck('attribute_value', 'id');
        
        $fundService = $this->fundAllocationService;

        $components = $componentMappings->map(function ($mapping) use ($attributeValues, $fundService) {
            
            // Calculate total utilized amount from this source component for the same FY, duration, sub_duration
            $mappingQuery = DB::table('component_utilization_mappings')
                ->where('financial_year', $mapping->fy)
                ->where('duration', $mapping->durationId);
            
            if ($mapping->subDurationId !== null) {
                $mappingQuery->where('sub_duration', $mapping->subDurationId);
            } else {
                $mappingQuery->whereNull('sub_duration');
            }

            $mappingIds = $mappingQuery->pluck('id');

            $utilizationQuery = DB::table('component_utilization_mapping_details')
                ->whereIn('mapping_id', $mappingIds)
                ->where('source_major_component', $mapping->major_component_id);

            if ($mapping->sub_component_id !== null) {
                $utilizationQuery->where('source_sub_component', $mapping->sub_component_id);
            } else {
                $utilizationQuery->whereNull('source_sub_component');
            }

            $alreadyAllocated = (float) $utilizationQuery->sum('amount_to_be_allocated');
            $allocated = (float) $mapping->amount;
            
            $distributionQuery = DB::table('fund_distributions')
                ->where('financial_year', $mapping->fy)
                ->where('major_component_id', $mapping->major_component_id);

            if ($mapping->durationId !== null) {
                $distributionQuery->where('duration_id', $mapping->durationId);
            } else {
                $distributionQuery->whereNull('duration_id');
            }

            if ($mapping->subDurationId !== null) {
                $distributionQuery->where('sub_duration_id', $mapping->subDurationId);
            } else {
                $distributionQuery->whereNull('sub_duration_id');
            }

            if ($mapping->sub_component_id !== null) {
                $distributionQuery->where('sub_component_id', $mapping->sub_component_id);
            } else {
                $distributionQuery->whereNull('sub_component_id');
            }

            $released = (float) $distributionQuery->sum('distribution_amount');
            
            $remaining = $allocated - $released - $alreadyAllocated;

            return [
                'financial_year' => $mapping->fy,
                'duration_id' => $mapping->durationId,
                'sub_duration_id' => $mapping->subDurationId,
                'major_component_id' => $mapping->major_component_id,
                'sub_component_id' => $mapping->sub_component_id,
                'major_component' => $attributeValues[$mapping->major_component_id] ?? '-',
                'sub_component' => $attributeValues[$mapping->sub_component_id] ?? '-',
                'duration_name' => $mapping->durationId ? ($attributeValues[$mapping->durationId] ?? '-') : '-',
                'sub_duration_name' => $mapping->subDurationId ? ($attributeValues[$mapping->subDurationId] ?? '-') : '-',
                'allocated_amount' => $allocated,
                'released_amount' => $released < 0 ? 0 : $released,
                'remaining_balance' => $remaining < 0 ? 0 : $remaining,
            ];
        })->values();

        // Get target components for Allocate To Dropdown
        $categories = $fundService->getAttributeValues('major-components', null);
        $targetComponents = [];
        foreach ($categories as $category) {
            $subs = $fundService->getAttributeValues('sub-components', (string) $category->id);
            $targetComponents[] = [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'sub_components' => $subs->map(function($sub) { return ['id' => $sub->id, 'name' => $sub->name]; })->toArray()
            ];
        }

        return response()->json([
            'components' => $components,
            'target_components' => $targetComponents
        ]);
    }

    public function store(ComponentUtilizationMappingRequest $request)
    {
        try {
            $this->service->storeMapping($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Mapping saved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save mapping: ' . $e->getMessage()
            ], 500);
        }
    }
}
