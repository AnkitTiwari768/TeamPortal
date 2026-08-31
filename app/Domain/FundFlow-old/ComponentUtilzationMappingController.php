<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use Illuminate\Http\Request;
use App\Traits\HasAttribute;
use App\Traits\Respond;

class ComponentUtilzationMappingController
{
    use HasAttribute, Respond;

    private static string $module = 'component-utilization-mapping';

    public function __construct(private ComponentUtilizationMappingService $service) {}

    public function index()
    {
        return view('fund_flow.component_utilization_mapping.index')->with([
            'title'            => __('fund_flow.component_utilization_mapping'),
            'financialYears'   => financial_year(),
            'duration'         => $this->listOf(code: 'duration', skipParent: true),
            'majorComponents'  => $this->listOf(code: 'major-components', skipParent: true),
            'module_url'       => static::$module,
        ]);
    }

    public function create()
    {
        return view('fund_flow.component_utilization_mapping.create')->with([
            'title'            => __('fund_flow.component_utilization_mapping'),
            'financialYears'   => financial_year(),
            'duration'         => $this->listOf(code: 'duration', skipParent: true),
            'majorComponents'  => $this->listOf(code: 'major-components', skipParent: true),
            'module_url'       => static::$module,
        ]);
    }

    public function getDataTable()
    {
        return $this->success(data: app(ListComponentUtilizationMappingAction::class)->execute());
    }

    public function getComponentBalances(Request $request)
    {
        $request->validate([
            'financial_year' => 'required|string',
            'duration_id' => 'nullable|string',
            'sub_duration_id' => 'nullable|string',
            'major_component_id' => 'nullable|string',
            'sub_component_id' => 'nullable|string',
        ]);

        $balances = $this->service->getComponentBalances(
            $request->financial_year,
            $request->major_component_id,
            $request->sub_component_id,
            $request->duration_id,
            $request->sub_duration_id
        );

        return response()->json(['data' => $balances]);
    }

    public function store(ComponentUtilizationMappingRequest $request, StoreComponentUtilizationMappingAction $action, ?string $id = null)
    {
        $dto = ComponentUtilizationMappingDTO::fromArray($request->validated());
        $mapping = $action->execute($dto, $id);

        return $this->created($mapping, __('fund_flow.mapping_saved'));
    }

    public function edit(string $id)
    {
        $data = $this->service->getMappingEditDetails($id);

        return view('fund_flow.component_utilization_mapping.create')->with([
            'title'            => __('fund_flow.edit_component_utilization_mapping'),
            'financialYears'   => financial_year(),
            'duration'         => $this->listOf(code: 'duration', skipParent: true),
            'majorComponents'  => $this->listOf(code: 'major-components', skipParent: true),
            'module_url'       => static::$module,
            'row'              => $data['mapping'],
            'lines'            => $data['details'],
            'id'               => $id,
        ]);
    }

    public function show(string $id)
    {
        $data = $this->service->getMappingViewDetails($id);

        return view('fund_flow.component_utilization_mapping.view')->with([
            'title'            => __('fund_flow.component_utilization_mapping_details'),
            'row'              => $data['mapping'],
            'lines'            => $data['details'],
            'module_url'       => static::$module,
        ]);
    }
}
