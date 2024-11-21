<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    public function campaign_index()
    {
        $campaigns = Campaign::with('user')->get();
        return view('admin.campaign.list', [
            'campaigns' => $campaigns,
        ]);
    }

    public function campaign_create()
    {
        $uniqueCompanies = User::select('company_name', 'id')
            ->whereNotNull('company_name')
            ->whereRaw('TRIM(company_name) <> ""')
            ->distinct('company_name')
            ->orderBy('company_name')
            ->get();

        return view('admin.campaign.create', [
            'company' => $uniqueCompanies
        ]);
    }

    public function campaign_create_post(Request $request)
    {

        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start-date' => 'required|date',
            'end-date' => 'required|date',
            'type' => 'required|in:0,1',
            'value' => 'required|numeric',
            'desc' => 'required',
            'status' => 'required|in:0,1',
        ]);

        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $campaign = new Campaign();
            $campaign->user_id = $request->input('user_id');
            $campaign->title = $request->input('name');
            $campaign->start_date = $request->input('start-date');
            $campaign->end_date = $request->input('end-date');
            $campaign->campaign_type = $request->input('type');
            $campaign->value = $request->input('value');
            $campaign->desc = $request->input('desc');
            $campaign->status = $request->input('status');
            $campaign->save();

            return redirect()->route('admin.campaign.index')->with('success', 'Kampanya oluşturma işlemi başarılı.');
        }
    }

    public function campaign_edit($id)
    {
        $campaign = Campaign::with('user')->find($id);
        $uniqueCompanies = User::select('company_name', 'id')
            ->whereNotNull('company_name')
            ->whereRaw('TRIM(company_name) <> ""')
            ->distinct('company_name')
            ->orderBy('company_name')
            ->get();

        return view('admin.campaign.edit', [
            'campaign' => $campaign,
            'company' => $uniqueCompanies,
        ]);
    }

    public function campaign_edit_post(Request $request)
    {

        // Formdan gelen verileri doğrulama kurallarına tabi tut
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'start-date' => 'required|date',
            'end-date' => 'required|date',
            'type' => 'required|in:0,1',
            'value' => 'required|numeric',
            'desc' => 'required',
            'status' => 'required|in:0,1',
        ]);


        // Doğrulama başarısız ise hataları göster ve geri dön
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        } else {

            $campaign = Campaign::find($request->input('campaign_id'));
            $campaign->user_id = $request->input('user_id');
            $campaign->title = $request->input('name');
            $campaign->start_date = $request->input('start-date');
            $campaign->end_date = $request->input('end-date');
            $campaign->campaign_type = $request->input('type');
            $campaign->value = $request->input('value');
            $campaign->desc = $request->input('desc');
            $campaign->status = $request->input('status');
            $campaign->save();

            return redirect()->route('admin.campaign.index')->with('success', 'Kampanya güncelleme işlemi başarılı.');
        }
    }

    public function campaign_delete($id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return redirect()->back()->with('Kampanya bulunamadı!');
        } else {
            $campaign->delete();
            return redirect()->back()->with('Kampanya başarıyla silindi.');
        }
    }
}
