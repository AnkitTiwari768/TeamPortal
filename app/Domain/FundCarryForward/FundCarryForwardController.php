<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Http\Request;
use App\Traits\HasAttribute;
use App\Traits\Respond;

class FundCarryForwardController
{
    use HasAttribute, Respond;

    private static string $module = 'carry-forward-mapping';

    public function __construct(private FundCarryForwardService $service) {}

    public function index()
    {
        return view('fund_flow.carry_forward.index')->with([
            'title' => __('fund_flow.carry_forward_mapping'),
            'financialYears' => financial_year(),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module,
        ]);
    }

    public function create()
    {
        return view('fund_flow.carry_forward.create')->with([
            'title' => __('fund_flow.carry_forward_mapping'),
            'financialYears' => financial_year(),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module,
        ]);
    }

    public function getDataTable()
    {
        return $this->success(data: app(ListFundCarryForwardAction::class)->execute());
    }

    public function getClosingBalances(Request $request)
    {
        $request->validate([
            'financial_year' => 'required|string',
            'duration_id' => 'required|string',
            'sub_duration_id' => 'nullable|string',
        ]);

        $balances = $this->service->getClosingBalances(
            $request->financial_year,
            $request->duration_id,
            $request->sub_duration_id
        );

        return response()->json(['data' => $balances]);
    }

    public function store(FundCarryForwardRequest $request, StoreFundCarryForwardAction $action, ?string $id = null)
    {
        $dto = FundCarryForwardDTO::fromArray($request->validated());
        $carryForward = $action->execute($dto, $id);

        return $this->created($carryForward, __('fund_flow.carry_forward_saved'));
    }

    public function edit(string $id)
    {
        $data = $this->service->getCarryForwardEditDetails($id);

        return view('fund_flow.carry_forward.create')->with([
            'title' => __('fund_flow.edit_carry_forward'),
            'financialYears' => financial_year(),
            'duration' => $this->listOf(code: 'duration', skipParent: true),
            'module_url' => static::$module,
            'row' => $data['carryForward'],
            'lines' => $data['details'],
            'id' => $id,
        ]);
    }

    public function show(string $id)
    {
        $data = $this->service->getCarryForwardViewDetails($id);

        return view('fund_flow.carry_forward.view')->with([
            'title' => __('fund_flow.carry_forward_details'),
            'row' => $data['carryForward'],
            'lines' => $data['details'],
            'module_url' => static::$module,
        ]);
    }
}
